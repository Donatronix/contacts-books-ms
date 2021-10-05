<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Email;
use App\Models\Phone;
use App\Models\Relation;
use App\Services\Import;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Sumra\JsonApi\JsonApiResponse;
use Sumra\PubSub\PubSub;

/**
 * Class ContactController
 *
 * @package App\Api\V1\Controllers
 */
class ContactController extends Controller
{
    /**
     * User's contact list
     *
     * @OA\Get(
     *     path="/v1/contacts",
     *     summary="Load user's contact list",
     *     description="Load user's contact list",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit contacts of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count contacts of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keywords",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="isFavorite",
     *         in="query",
     *         description="Show contacts that is favorite",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="isRecently",
     *         in="query",
     *         description="Show recently added contacts",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="byLetter",
     *         in="query",
     *         description="Show contacts by letter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="groupId",
     *         in="query",
     *         description="Get contacts by group id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort[by]",
     *         in="query",
     *         description="Sort by field (name, email, phone)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort[order]",
     *         in="query",
     *         description="Sort order (asc, desc)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(Request $request)
    {
        try {
            $contactsQuery = Contact::byOwner()
                ->select('*')
                ->with([
                    'phones' => function ($q) use ($request) {
                        return $q->where('is_default', true);
                    },
                    'emails' => function ($q) use ($request) {
                        return $q->where('is_default', true);
                    },
                    'groups',
                ])
                ->when($request->has('isFavorite'), function ($q) use ($request) {
                    return $q->where('is_favorite', $request->boolean('isFavorite'));
                })
                ->when($request->has('isRecently'), function ($q) use ($request) {
                    return $q->latest();
                })
                ->when(($request->has('groupId') && !empty($request->get('groupId'))), function ($q) use ($request) {
                    return $q->whereHas('groups', function ($q) use ($request) {
                        return $q->where('group_id', $request->get('groupId'));
                    });
                })
                ->when(($request->has('byLetter') && !empty($request->get('byLetter'))), function ($q) use ($request) {
                    $letter = $request->get('byLetter', '');

                    return $q->where(function ($q) use ($letter) {
                        return $q->where(DB::raw('TRIM(CONCAT_WS(" ", prefix_name, first_name, middle_name, last_name, suffix_name))'), 'like', "{$letter}%")
                            ->orWhere('write_as_name', 'like', "{$letter}%");
                    });
                })
                ->when(($request->has('search') && !empty($request->get('search'))), function ($q) use ($request) {
                    $search = $request->get('search');

                    return $q->where(function ($q) use ($search) {
                        return $q->where(DB::raw('TRIM(CONCAT_WS(" ", prefix_name, first_name, middle_name, last_name, suffix_name))'), 'like', "%{$search}%")
                            ->orWhere('write_as_name', 'like', "%{$search}%")
                            ->orWhere('nickname', 'like', "%{$search}%")
                            ->orWhere('note', 'like', "%{$search}%")
                            ->orWhereHas('emails', function ($q) use ($search) {
                                return $q->where('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('phones', function ($q) use ($search) {
                                return $q->where('phone', 'like', "%{$search}%");
                            });
                    });
                });

            // Sorting all records
            $sort = $request->get('sort', ['by' => 'name']);
            $sort['by'] = $sort['by'] ?? 'name';
            $sort['order'] = $sort['order'] ?? 'asc';

            if (!empty($sort['by']) && $sort['by'] === 'name') {
                $contactsQuery->selectRaw('TRIM(CONCAT_WS(" ", prefix_name, first_name, middle_name, last_name, suffix_name)) as name')
                    ->orderBy('name', $sort['order'])
                    ->orderBy('write_as_name', $sort['order']);
            }

            if (!empty($sort['by']) && $sort['by'] === 'email') {
                $contactsQuery->whereHas('emails', function ($q) use ($sort) {
                    return $q->orderBy($sort['by'], $sort['order']);
                });
            }

            if (!empty($sort['by']) && $sort['by'] === 'phone') {
                $contactsQuery->whereHas('phones', function ($q) use ($sort) {
                    return $q->orderBy($sort['by'], $sort['order']);
                });
            }

            // Add pagination
            $contacts = $contactsQuery->paginate($request->get('limit', config('settings.pagination_limit')));

            // Transform collection objects
            $contacts->map(function ($object) {
                $object->setAttribute('avatar', $this->getImagesFromRemote($object->id));

                $email = $object->emails->first();
                $object->setAttribute('email', $email ? $email->email : null);

                $phone = $object->phones->first();
                $object->setAttribute('phone', $phone ? sprintf('%s (%s)', $phone->phone, $phone->type) : null);

                unset(
                    $object->name,
                    $object->phones,
                    $object->emails
                );
            });

            // Get first letters
            $dn_letters = Contact::selectRaw('SUBSTR(TRIM(write_as_name), 1, 1) as letter')
                ->when($request->has('isFavorite'), function ($q) use ($request) {
                    return $q->where('is_favorite', $request->boolean('isFavorite'));
                })
                ->when(($request->has('groupId') && !empty($request->get('groupId'))), function ($q) use ($request) {
                    return $q->whereHas('groups', function ($q) use ($request) {
                        return $q->where('group_id', $request->get('groupId'));
                    });
                });

            $letters = Contact::selectRaw('SUBSTR(TRIM(CONCAT_WS(" ", prefix_name, first_name, middle_name, last_name, suffix_name)), 1, 1) as letter')
                ->when($request->has('isFavorite'), function ($q) use ($request) {
                    return $q->where('is_favorite', $request->boolean('isFavorite'));
                })
                ->when(($request->has('groupId') && !empty($request->get('groupId'))), function ($q) use ($request) {
                    return $q->whereHas('groups', function ($q) use ($request) {
                        return $q->where('group_id', $request->get('groupId'));
                    });
                })
                ->distinct()
                ->union($dn_letters)
                ->orderBy('letter')
                ->get()
                ->pluck('letter')
                ->toArray();
            $letters = array_values(array_filter(array_unique($letters, SORT_LOCALE_STRING)));

            // Return response
            return response()->json(array_merge(
                [
                    'type' => 'success',
                    'title' => "Get contacts list",
                    'message' => 'Contacts list received',
                    'letters' => $letters
                ],
                $contacts->toArray()
            ), 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contacts list",
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Save user's contacts
     *
     * @OA\Post(
     *     path="/v1/contacts",
     *     summary="Save user's contacts",
     *     description="Save user's contacts",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Validate input
        $this->validate($request, Contact::rules());

        try {
            // First, Create contact
            $contact = new Contact();
            $contact->fill($request->all());
            $contact->birthday = Carbon::parse($request->get('birthday'));
            $contact->write_as_name = $request->get('display_name');
            $contact->user_id = (string)Auth::user()->getAuthIdentifier();
            $contact->save();

            // Save contact's phones
            if ($request->has('phones') && count($request->get('phones')) > 0) {
                foreach ($request->get('phones') as $phone) {
                    $row = new Phone();
                    $row->fill($phone);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's emails
            if ($request->has('emails') && count($request->get('emails')) > 0) {
                foreach ($request->get('emails') as $email) {
                    $row = new Email();
                    $row->fill($email);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's works if exist
            if ($request->has('works') && count($request->get('works')) > 0) {
                foreach ($request->get('works') as $company) {
                    $row = new Work();
                    $row->fill($company);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's addresses if exist
            if ($request->has('addresses') && count($request->get('addresses')) > 0) {
                foreach ($request->get('addresses') as $address) {
                    $row = new Address();
                    $row->fill($address);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's sites if exist
            if ($request->has('sites') && count($request->get('sites')) > 0) {
                foreach ($request->get('sites') as $site) {
                    $row = new Site();
                    $row->fill($site);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's chats if exist
            if ($request->has('chats') && count($request->get('chats')) > 0) {
                foreach ($request->get('chats') as $chat) {
                    $row = new Chat();
                    $row->fill($chat);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's relations if exist
            if ($request->has('relations') && count($request->get('relations')) > 0) {
                foreach ($request->get('relations') as $relation) {
                    $row = new Relation();
                    $row->fill($relation);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Adding new contact",
                'message' => "User's contacts successfully saved",
                'data' => $contact->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Adding new contact",
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Get detail info about contact
     *
     * @OA\Get(
     *     path="/v1/contacts/{id}",
     *     summary="Get detail info about contact",
     *     description="Get detail info about contact",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Contacts ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Data of contact"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Contact not found",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="object",
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      description="code of error"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      description="error message"
     *                  )
     *              )
     *          )
     *     )
     * )
     *
     * @param $id
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show($id)
    {
        // Get object
        $contact = $this->getObject($id);

        if ($contact instanceof JsonApiResponse) {
            return $contact;
        }

        // Load linked relations data
        $contact->load([
            'phones',
            'emails',
            'groups',
            'works',
            'addresses',
            'sites',
            'chats',
            'relations'
        ]);

        // Read big size of avatar from storage
        $contact->setAttribute('avatar', $this->getImagesFromRemote($id, 'big'));

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Contact details',
            'message' => "contact details received",
            'data' => $contact->toArray()
        ], 200);
    }

    /**
     * Update user's contact (in JSON format)
     *
     * @OA\Put(
     *     path="/v1/contacts/{id}",
     *     summary="Update user's contacts in JSON format",
     *     description="Update user's contacts in JSON format",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Contacts ID",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Validate input
        $this->validate($request, Contact::rules());

        // Read contact model
        $contact = $this->getObject($id);
        if (is_a($contact, 'Sumra\JsonApi\JsonApiResponse')) {
            return $contact;
        }

        // Try update contact model
        try {
            // First, update mail contact data
            $contact->fill($request->all());
            $contact->write_as_name = $request->get('display_name');
            $contact->save();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Change a contact data',
                'message' => "Contact {$contact->display_name} successfully updated",
                'data' => $contact->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Change a contact data',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Delete selected contact / contacts
     *
     * @OA\Delete(
     *     path="/v1/contacts/{id}",
     *     summary="Delete selected contact / contacts",
     *     description="Delete selected contact / contacts",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Contacts ID",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 description="Contacts Ids in JSON",
     *
     *                 @OA\Items(
     *                     type="item",
     *                     example="0aa06e6b-35de-3235-b925-b0c43f8f7c75"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     */
    public function destroy($id, Request $request): JsonApiResponse
    {
        if ($id === '0' && $request->has('ids')) {
            try {
                // now this will destroy campaigns with ids
                Contact::destroy($request->get('ids'));

                return response()->jsonApi([
                    'type' => 'success',
                    'title' => 'Delete contacts',
                    'message' => 'The selected contacts have been successfully deleted'
                ], 200);
            } catch (Exception $e) {
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => 'Delete contacts',
                    'message' => 'Error while deleting contacts: ' . $e->getMessage()
                ], 400);
            }
        } else {
            // Get object
            $contact = $this->getObject($id);

            if ($contact instanceof JsonApiResponse) {
                return $contact;
            }

            try {
                $contact->delete();

                return response()->jsonApi([
                    'type' => 'success',
                    'title' => 'Delete contact',
                    'message' => 'The selected contact have been successfully deleted'
                ], 200);
            } catch (Exception $e) {
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => 'Delete contact',
                    'message' => 'Error while deleting contact: ' . $e->getMessage()
                ], 400);
            }
        }
    }

    /**
     * Merge selected contacts to one
     *
     * @OA\Post(
     *     path="/v1/contacts/merge",
     *     summary="Merge selected contacts to one",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="contacts",
     *                 type="array",
     *                 description="Array of contacts id's for merge",
     *                 @OA\Items(
     *                     type="object",
     *                     example="f97b1458-b4ed-35fe-9fc9-3717b6768339"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully merged"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function merge(Request $request): JsonResponse
    {
        // Validate input
        $this->validate($request, [
            'contacts' => 'required|array|min:2',
            'contacts.*' => 'required|string|distinct|min:36'
        ]);

        // Try process operations with contacts
        try {
            $selectedContacts = Contact::find($request->get('contacts'));

            $contact_to = $selectedContacts->shift();
            $contact_to->makeHidden('display_name');
            $contact_to->makeVisible('write_as_name');

            $avatars = [];
            foreach ($selectedContacts as $contact) {
                // Add contact id for photo delete
                $avatars[] = $contact->id;

                // Merge attributes two objects and save recipient
                $attributesTo = collect($contact_to)->except(['id', 'user_id', 'is_favorite', 'birthday', 'display_name']);

                $attributesFrom = $contact->toArray();
                foreach ($attributesTo as $key => $value) {
                    if ($value !== $attributesFrom[$key]) {
                        $attributesTo[$key] = trim(implode(', ', [$value, $attributesFrom[$key]]), ', ');
                    }
                }

                $contact_to->fill($attributesTo->toArray());
                $contact_to->save();

                // Moving phones to contact recipient
                if ($contact->phones->count()) {
                    $contact_to->phones()->saveMany($contact->phones);
                }

                // Moving emails to contact recipient
                if ($contact->emails->count()) {
                    $contact_to->emails()->saveMany($contact->emails);
                }

                // Moving sites to contact recipient
                if ($contact->sites->count()) {
                    $contact_to->sites()->saveMany($contact->sites);
                }

                // Moving relations to contact recipient
                if ($contact->relations->count()) {
                    $contact_to->relations()->saveMany($contact->relations);
                }

                // Moving chats to contact recipient
                if ($contact->chats->count()) {
                    $contact_to->chats()->saveMany($contact->chats);
                }

                // Moving addresses to contact recipient
                if ($contact->addresses->count()) {
                    $contact_to->addresses()->saveMany($contact->addresses);
                }

                // Moving works to contact recipient
                if ($contact->works->count()) {
                    $contact_to->works()->saveMany($contact->works);
                }

                // Sync contact's groups
                if ($contact->groups->count()) {
                    if (!$contact_to->groups()->exists($contact->groups)) {
                        $contact_to->groups()->attach($contact->groups);
                    }
                }

                // Delete permanently donor contact
                $contact->forceDelete();
            }

            // Send request for delete avatars from storage
            if (!empty($avatars)) {
                PubSub::publish(
                    'DeleteAvatars',
                    [
                        'entity' => 'contact',
                        'user_id' => (string)Auth::user()->getAuthIdentifier(),
                        'avatars' => $avatars
                    ],
                    config('settings.exchange_queue.files')
                );
            }

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Merging contacts',
                'message' => 'Contacts was merged successfully',
                'data' => $contact_to->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Merging contacts',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Add / delete contacts to/from favorites
     *
     * @OA\Get(
     *     path="/v1/contacts/{id}/favorite",
     *     summary="Add / delete contacts to / from favorites",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contact ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Successfully updated"
     *     )
     * )
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favorite($id): JsonResponse
    {
        // Get object
        $contact = $this->getObject($id);

        if ($contact instanceof JsonApiResponse) {
            return $contact;
        }

        try {
            $contact->update([
                'is_favorite' => !$contact->is_favorite
            ]);

            // Load contact's group
            $contact->load('groups');

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Favorites list',
                'message' => sprintf("%s was successfully %s favorites", $contact->display_name, $contact->is_favorite ? 'added to' : 'removed from'),
                'data' => $contact->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Favorites list",
                'message' => "Can't change status for contacts {$contact->display_name}"
            ], 404);
        }
    }

    /**
     * Join contacts to selected groups
     *
     * @OA\Post(
     *     path="/v1/contacts/join-groups",
     *     summary="Join contacts to selected groups",
     *     description="Join contacts to selected groups",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="contacts",
     *                 type="array",
     *                 description="Array of contacts Id's",
     *                 @OA\Items(
     *                     type="object",
     *                     example="f97b1458-b4ed-35fe-9fc9-3717b6768339"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="groups",
     *                 type="array",
     *                 description="Array of groups id's",
     *                 @OA\Items(
     *                     type="string",
     *                     example="ca5eb9e6-8509-3280-8da7-bc3cd1efadc3"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function joinGroups(Request $request)
    {
        // Validate input
        $this->validate($request, [
            'contacts' => 'required|array|min:1',
            'contacts.*' => 'required|string|distinct|min:36',
            'groups' => 'required|array|min:1',
            'groups.*' => 'required|string|distinct|min:36'
        ]);

        try {
            foreach ($request->get('contacts') as $contact_id) {
                $contact = Contact::find($contact_id);

                if ($contact) {
                    $ids = $request->get('groups', []);

                    $contact->groups()->sync($ids);
                }
            }

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Join / delete contact to / from group',
                'message' => 'Operation was been successfully',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Join / delete contact to / from group',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Batch Import user's contacts using saved file (Google CSV, Outlook CSV, vCard, etc.)
     *
     * @OA\Post(
     *     path="/v1/contacts/import/file",
     *     summary="Batch Import user's contacts using saved file (Google CSV, Outlook CSV, vCard, etc)",
     *     description="Batch Import user's contacts using saved file (Google CSV, Outlook CSV, vCard, etc)",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="contacts",
     *                     type="file",
     *                     description="Selected media files",
     *                 ),
     *                 @OA\Property(
     *                     property="group_id",
     *                     type="string",
     *                     description="Input Group ID",
     *                     example="3d9319c9-59ee-3efc-900f-eec98811c96b"
     *                 )
     *             )
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Import of data create successful"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="vcards",
     *                  type="text",
     *                  description="Required parameter must be specified for execution amount"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              )
     *          )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function importFile(Request $request)
    {
        // Validate input
        $this->validate($request, [
            'contacts' => 'file|mimes:csv,vcf,vcard,txt,xls,xlsx',
            'group_id' => 'nullable|string|max:36'
        ]);

        try {
            $import = new Import();
            $result = $import->exec($request);

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Batch import of contacts from file',
                'message' => "Successfully imported {$result['count']} contact(s)",
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Batch import of contacts from file',
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Batch Import contacts using json data
     *
     * @OA\Post(
     *     path="/v1/contacts/import/json",
     *     summary="Batch import user's contacts using json data",
     *     description="Batch save user's contacts using json data",
     *     tags={"Contacts"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="group_id",
     *                 type="string",
     *                 description="Input Group ID",
     *                 example="3d9319c9-59ee-3efc-900f-eec98811c96b"
     *             ),
     *             @OA\Property(
     *                 property="contacts",
     *                 type="array",
     *                 description="User contacts array in JSON",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(
     *                         property="display_name",
     *                         type="string",
     *                         description="Display name data in string",
     *                         example=""
     *                     ),
     *                     @OA\Property(
     *                         property="avatar",
     *                         type="string",
     *                         description="Photo body in base64 format",
     *                         example=""
     *                     ),
     *                     @OA\Property(
     *                         property="phones",
     *                         type="array",
     *                         description="Contacts phones / Msisdns data in JSON",
     *
     *                         @OA\Items(
     *                             type="string",
     *                             example="+3521234562545"
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="emails",
     *                         type="array",
     *                         description="Contacts emails",
     *
     *                         @OA\Items(
     *                             type="string",
     *                             example="client1@client.com"
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="is_shared",
     *                         type="boolean",
     *                         description="Need shared contacts data (1, 0, true, false)",
     *                         example="false"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              )
     *          )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function importJson(Request $request)
    {
        // Validate input
        $this->validate($request, [
            'contacts' => 'array'
        ]);

        try {
            foreach ($request->get('contacts') as $lead) {
                // First, Create contact
                $contact = new Contact();
                $contact->write_as_name = $request->get('display_name', '');
                $contact->user_id = (string)Auth::user()->getAuthIdentifier();
                $contact->save();

                // Save contact's phones
                if (isset($lead['phones']) && count($lead['phones']) > 0) {
                    foreach ($lead['phones'] as $phone) {
                        $row = new Phone();
                        $row->fill([
                            'phone' => $phone
                        ]);
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's emails
                if ($lead['emails'] && count($lead['emails']) > 0) {
                    foreach ($lead['emails'] as $email) {
                        $row = new Email();
                        $row->fill([
                            'email' => $email
                        ]);
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }
            }

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Batch import of contacts",
                'message' => "Contacts was imported successfully"
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Batch import of contacts",
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Contact's not found
     *
     * @param $id
     *
     * @return mixed
     */
    private function getObject($id)
    {
        try {
            return Contact::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contact object",
                'message' => "Contact with #{$id} not found: {$e->getMessage()}"
            ], 404);
        }
    }

    /**
     * @param $id
     *
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getImagesFromRemote($id)
    {
        $images = null;

        $client = new Client(['base_uri' => config('settings.api.files.host')]);

        try {
            $response = $client->request('GET', config('settings.api.files.version') . "/files?entity=contact&entity_id={$id}", [
                'headers' => [
                    'user-id' => Auth::user()->getAuthIdentifier(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $response = json_decode($response->getBody(), JSON_OBJECT_AS_ARRAY);

                if (isset($response['attributes']['path'])) {
                    $images = $response['attributes']['path'];
                }
            }

        } catch (Exception $e) {
        }

        return $images;
    }
}
