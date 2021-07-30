<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\Work;
use App\Services\Import;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sumra\JsonApi\JsonApiResponse;

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
     *         description="Sort by field",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort[order]",
     *         in="query",
     *         description="Sort order",
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
            $contacts = Contact::byOwner()
                ->with([
                    'phones' => function ($q) use ($request){
                        return $q->where('is_default', true);
                    },
                    'emails' => function ($q) use ($request){
                        return $q->where('is_default', true);
                    },
                    'groups',
                ])
                ->when($request->has('search'), function ($q) use ($request) {
                    $search = $request->get('search');

                    return $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")

                        ->orWhereHas('emails', function ($q) use ($search) {
                            return $q->where('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('phones', function ($q) use ($search) {
                            return $q->where('phone', 'like', "%{$search}%");
                        });
                })

                ->when($request->has('isFavorite'), function ($q) use ($request) {
                    return $q->where('is_favorite', $request->get('isFavorite'));
                })
                ->when($request->has('isRecently'), function ($q) use ($request) {
                    return $q->sortBy('created_at', 'desc');
                })
                ->when($request->has('groupId'), function ($q) use ($request) {
                    return $q->whereHas('groups', function ($q) use ($request) {
                        return $q->where('group_id', $request->get('groupId'));
                    });
                })
                ->when($request->has('byLetter'), function ($q) use ($request) {
                    $letter = $request->get('byLetter', '');

                    return $q->where('first_name', 'like', "{$letter}%")
                        ->orWhere('last_name', 'like', "{$letter}%");
                })
                ->when($request->has('sort'), function ($q) use ($request) {
                    $sort = request()->get('sort', null);

//                    if($sort['by'] === 'email'){
//                        return $q->whereHas('emails', function ($q) use ($sort) {
//                            return $q->orderBy('emails.email', $sort['order'] ?? 'asc');
//                        });
//                    }

//                    if($sort['by'] === 'phone'){
//                        return $q->whereHas('emails', function ($q) use ($sort) {
//                            return $q->orderBy($sort['by'], $sort['order'] ?? 'asc');
//                        });
//                    }
//
//                    return $q->when((!is_null($sort) && $sort['by'] === 'email'), function ($q) use ($sort) {
//                        return $q->join('emails', 'users.role_id', '=', 'roles.id')->orderBy('emails.email', $sort['order'] ?? 'asc');
//                    });
                })
                ->paginate($request->get('limit', 1000));

            // Transform collection objects
            $contacts->map(function ($object) {
                $object->setAttribute('avatar', $this->getImagesFromRemote($object->id));

                $email = $object->emails->first();
                $object->setAttribute('email', $email ? $email->email : null);

                $phone = $object->phones->first();
                $object->setAttribute('phone', $phone ? sprintf('%s (%s)', $phone->phone, $phone->type) : null);

                unset($object->phones, $object->emails);
            });

            // Get first letters
            $letters = Contact::selectRaw('substr(first_name,1,1) as letter')
                ->when($request->has('isFavorite'), function ($q) use ($request) {
                    return $q->where('is_favorite', $request->get('isFavorite'));
                })
                ->distinct()
                ->orderBy('letter')
                ->get()
                ->pluck('letter')
                ->toArray();
            $letters = array_values(array_unique($letters, SORT_LOCALE_STRING));

            // Return response
            return response()->jsonApi(array_merge(['letters' => $letters], $contacts->toArray()), 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contacts list",
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Save user's contacts in JSON format
     *
     * @OA\Post(
     *     path="/v1/contacts",
     *     summary="Save user's contacts in JSON format",
     *     description="Save user's contacts in JSON format",
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
     *                 type="json",
     *                 description="Contacts data in JSON",
     *                 example=""
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
        $input = (object)$this->validate($request, Contact::rules());

        try {
            foreach ($input->contacts as $item) {
                // First, Create contact
                $contact = Contact::create([
                    'first_name' => $item['first_name'],
                    'last_name' => $item['last_name'],
                    'surname' => $item['surname'],
                    'avatar' => $item['avatar'],
                    'birthday' => $item['birthday'],
                    'nickname' => $item['nickname'],
                    'user_prefix' => $item['user_prefix'],
                    'user_suffix' => $item['user_suffix'],
                    'user_id' => (int)Auth::user()->getAuthIdentifier()
                ]);

                // Save contact's phones
                if (isset($item['phones']) && count($item['phones']) > 0) {
                    foreach ($item['phones'] as $x => $phone) {
                        ContactPhone::create([
                            'phone' => $phone,
                            'type' => $item['type'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                // Save contact's emails
                if (isset($item['emails']) && count($item['emails']) > 0) {
                    foreach ($item['emails'] as $x => $email) {
                        ContactEmail::create([
                            'email' => $email,
                            'type' => $item['type'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                if (isset($item['works']) && count($item['works']) > 0) {
                    foreach ($item['works'] as $x => $company) {
                        Work::create([
                            'company' => $company,
                            'department' => $item['department'],
                            'post' => $item['post'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                if (isset($item['addresses']) && count($item['addresses']) > 0) {
                    foreach ($item['addresses'] as $x => $country) {
                        Work::create([
                            'country' => $country,
                            'provinces' => $item['provinces'],
                            'city' => $item['city'],
                            'address' => $item['address'],
                            'address_type' => $item['address_type'],
                            'postcode' => $item['postcode'],
                            'post_office_box_number' => $item['post_office_box_number'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                if (isset($item['sites']) && count($item['sites']) > 0) {
                    foreach ($item['sites'] as $x => $site) {
                        Work::create([
                            'site' => $site,
                            'site_type' => $item['site_type'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                if (isset($item['chats']) && count($item['chats']) > 0) {
                    foreach ($item['chats'] as $x => $chat) {
                        Work::create([
                            'site' => $chat,
                            'chat_name' => $item['chat_name'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                if (isset($item['relation']) && count($item['relation']) > 0) {
                    foreach ($item['relation'] as $x => $relation) {
                        Work::create([
                            'relation' => $relation,
                            'relation_name' => $item['relation_name'],
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }
            }

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Upload user's contacts",
                'message' => "User's contacts successfully saved"
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Upload user's contacts",
                'message' => $e->getMessage()
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
        // Get contact model
        try {
            $contact = Contact::with(['phones', 'emails', 'groups'])
                ->where('id', $id)
                ->first();

            $contact->setAttribute('avatar', $this->getImagesFromRemote($id));

            return response()->jsonApi($contact, 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Contact not found',
                'message' => "Contact with #{$id} not found: {$e}"
            ], 404);
        }
    }

    /**
     * Update user's contacts in JSON format
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
     *         description="Contacts Id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="contacts",
     *                 type="json",
     *                 description="Contacts data in JSON",
     *                 example=""
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
     *         description="Unknown error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     description="code of error"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     description="error message"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {

    }

    /**
     * Delete contact data
     *
     * @OA\Delete(
     *     path="/v1/contacts/{id}",
     *     summary="Save contact data in Neo4j",
     *     description="Save contact data in Neo4j",
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
     *         name="userID",
     *         description="Contacts ID",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter(
     *         name="contacts",
     *         description="Contacts in JSON",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="string"
     *          )
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function destroy($id)
    {
        // Get object
        $contact = $this->getObject($id);

        if ($contact instanceof JsonApiResponse) {
            return $contact;
        }

        try {
            $contact->delete();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Contact are deleted',
                'message' => 'Contact are deleted'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Contact are not saved',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Merge 2 contacts to 1
     *
     * @OA\Post(
     *     path="/v1/contacts/merge",
     *     summary="Merge 2 contacts to 1",
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
     *                 property="contact_id_from",
     *                 type="integer",
     *                 description="Contact ID From",
     *                 example="2"
     *             ),
     *             @OA\Property(
     *                 property="contact_id_to",
     *                 type="integer",
     *                 description="Contact ID To",
     *                 example="5"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully merged"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     description="code of error"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     description="error message"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function merge(Request $request): JsonResponse
    {
        // Check exist contacts
        $contact_from = $this->getObject($request->get('contact_id_from'));
        if ($contact_from instanceof JsonApiResponse) {
            return $contact_from;
        }

        $contact_to = $this->getObject($request->get('contact_id_to'));
        if ($contact_to instanceof JsonApiResponse) {
            return $contact_to;
        }

        try {
            // Update recipient client
            $contact_to->update([
                // client name
                'name' => $contact_to->name . ' (' . $contact_from->name . ')',

                // Notes
                'note' => $contact_to->note . "\n\n" . $contact_from->note,

                // Merge contacts tags
                'tags' => array_unique(array_merge($contact_to->tags, $contact_from->tags))
            ]);

            // Merge contacts phones
            foreach ($contact_from->phones as $phone) {
                // Check phone
                $new_phones = $contact_to->phones->pluck('phone')->toArray();

                if (in_array($phone->phone, $new_phones)) {
                    continue;
                }

                // Update client to phone
                $phone->contact()->associate($contact_to);
                $phone->save();
            }

            // Merge contacts emails
            foreach ($contact_from->emails as $email) {
                // Check email
                $new_emails = $contact_to->emails->pluck('email')->toArray();

                if (in_array($email->email, $new_emails)) {
                    continue;
                }

                // Update client to phone
                $email->contact()->associate($contact_to);
                $email->save();
            }

            // Delete donor client
            $contact_from->delete();

            // Return response
            return response()->jsonApi([
                'success' => [
                    'message' => 'Contacts was merged successfully'
                ]
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Add / delete contacts to/from favorites
     *
     * @OA\Put(
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

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Favorites list',
                'message' => sprintf("%s was successfully %s favorites", $contact->display_name, $contact->is_favorite ? 'added to' : 'removed from')
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
     * Batch Import user contacts using saved file (Google CSV, Outlook CSV, vCard, etc)
     *
     * @OA\Post(
     *     path="/v1/contacts/import/file",
     *     summary="Batch Import user contacts using saved file (Google CSV, Outlook CSV, vCard, etc)",
     *     description="Batch Import user contacts using saved file (Google CSV, Outlook CSV, vCard, etc)",
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
     *                     description="Selected media files"
     *                 ),
     *                 @OA\Property(
     *                     property="group_id",
     *                     type="string",
     *                     description="Input Group ID",
     *                     example="3d9319c9-59ee-3efc-900f-eec98811c96b"
     *                 ),
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
     *              ),
     *          ),
     *     ),
     *
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function importFile(Request $request)
    {
        $user_id = (int)Auth::user()->getAuthIdentifier();

        try {
            $import = new Import();
            $result = $import->exec($request);

            // Return response
            return response()->jsonApi([
                'success' => true,
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'success' => false,
                'error' => $e->getMessage()
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
     *     @OA\Parameter(
     *         name="id",
     *         description="user id",
     *         required=true,
     *         in="query",
     *         @OA\Schema (
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="display_name",
     *                     type="string",
     *                     description="Display name data in string",
     *                     example=""
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     description="Photo body in base64 format",
     *                     example=""
     *                 ),
     *                 @OA\Property(
     *                     property="phones",
     *                     type="array",
     *                     description="Contacts phones / Msisdns data in JSON",
     *
     *                     @OA\Items(
     *                         type="string",
     *                         example="+3521234562545"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="emails",
     *                     type="array",
     *                     description="Contacts emails",
     *
     *                     @OA\Items(
     *                         type="string",
     *                         example="client1@client.com"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="is_shared",
     *                     type="boolean",
     *                     description="Need shared contacts data (1, 0, true, false)",
     *                     example="false"
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
        try {
            $json = json_decode($request, true);

            $result = [];
            foreach ($json as $k => $item) {
                if (isset($json['display_name'])) {
                    $result[$k]['name_param'] = $json['display_name'];
                }

                if (isset($json['msisdns'])) {
                    $result[$k]['phone'] = $json['msisdns'];
                }

                if (isset($json['emails'])) {
                    $result[$k]['email'] = $json['emails'];
                }

                if (isset($json['photo_uri'])) {
                    $result[$k]['photo'] = $json['photo_uri'];
                }
            }

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Get data was success",
                'message' => "Get data from remote server was successfully"
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Get data from remote server was unsuccessful",
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Contact's group not found
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
                'message' => "Contact with #{$id} not found"
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

        $client = new Client(['base_uri' => env('FILES_MICROSERVICE_HOST')]);

        try {
            $response = $client->request('GET', env('API_FILES', '/v1') . '/files' . "?entity=contact&entity_id={$id}", [
                'headers' => [
                    'user-id' => '100',
                    'Accept' => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $response = json_decode($response->getBody(), JSON_OBJECT_AS_ARRAY);

                if (isset($response['attributes']['path'])) {
                    $images = $response['attributes']['path'];
                }
            }

        } catch (RequestException $e) {
        }

        return $images;
    }
}
