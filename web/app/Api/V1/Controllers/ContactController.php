<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\Work;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
     *         ),
     *         style="form"
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
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function index(Request $request)
    {
        try {
            $contacts = Contact::with([
                    'phones',
                    'emails',
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
                ->byOwner()
                ->get();

            $contacts->map(function($object){
                $object->setAttribute('avatar', $this->getImagesFromRemote($object->id)[0]);
            });

            // Return response
            return response()->jsonApi($contacts, 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'error',
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
                            'phone_type' => $item['phone_type'],
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
                            'email_type' => $item['email_type'],
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
                'status' => 'success',
                'title' => "Upload user's contacts",
                'message' => "User's contacts successfully saved"
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'error',
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
     */
    public function show($id)
    {
        // Get contact model
        try {
            $contact = Contact::with(['phones', 'emails', 'groups'])
                ->where('id', $id)
                ->first();

            $contact->setAttribute('avatar', $this->getImagesFromRemote($id)[0]);

            return response()->jsonApi($contact, 200);
        } catch (ModelNotFoundException | GuzzleException $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Contact not found',
                'message' => "Contact with #{$id} not found: {$e}"
            ], 404);
        }
    }

    /**
     * Update contact
     *
     * @OA\Put(
     *     path="/v1/contacts/{id}",
     *     summary="Update contact",
     *     description="Update contact",
     *     tags={"Contact Emails"},
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
     *         description="Email Id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 description="Email of client",
     *                 example="test@tes.com"
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Communication prefernce",
     *                 example="true"
     *             )
     *         )
     *     ),
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
        $email = ContactEmail::with(['client'])->where('id', $id)->first();

        if (!$email) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.ContactEmailNotFound')
            ], 404);
        }

        try {
            if ($request->has('is_default')) {
                $is_default = $request->get('is_default', 'false');

                // Reset is_default for other emails
                if ($is_default && $email->client) {
                    foreach ($email->client->emails as $old_email) {
                        $old_email->is_default = false;
                        $old_email->save();
                    }
                }

                $email->is_default = $is_default;
            }

            if ($request->has('email')) {
                $email->email = $request->get('email');
            }

            $email->save();

            return response()->jsonApi($email);
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
                'status' => 'success',
                'title' => 'Contact are deleted',
                'message' => 'Contact are deleted'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'error',
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

            return response()->jsonApi([
                'status' => 'success',
                'title' => 'Favorites list',
                'message' => sprintf("%s was successfully %s favorites", $contact->display_name, $contact->is_favorite ? 'added to' : 'removed from')
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => "Favorites list",
                'message' => "Can't change status for contacts {$contact->display_name}"
            ], 404);
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
                'type' => 'error',
                'title' => "Get contact object",
                'message' => "Contact with #{$id} not found"
            ], 404);
        }
    }

    /**
     * @param $id
     *
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getImagesFromRemote($id): array
    {
        $images = null;

        $client = new Client(['base_uri' => env('FILES_MICROSERVICE_HOST')]);

        $contact_image = $client->request('GET', env('API_FILES', '/v1') . '/files' . "?entity=contact&entity_id={$id}");
        $contact_image = json_decode($contact_image->getBody(), JSON_OBJECT_AS_ARRAY);

        foreach ($contact_image['data'] as $image) {
            $images = $image['attributes']['path'];
        }

        return $images;
    }
}
