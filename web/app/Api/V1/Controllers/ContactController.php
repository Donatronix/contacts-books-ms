<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $user_id = (int)Auth::user()->getAuthIdentifier();

        try {
            $contacts = Contact::where('user_id', $user_id)->with(['phones', 'emails'])->get();

            // Return response
            return response()->jsonApi($contacts->toArray(), 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => "Get contacts list",
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Save user's contacts data
     *
     * @OA\Post(
     *     path="/v1/contacts",
     *     summary="Save user's contacts data",
     *     description="Save user's contacts data",
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
    public function store(Request $request)
    {
        // Validate input
        $this->validate($request, $this->rules());

        try {
            foreach($request->get('contacts') as $item){
                // First, Create contact
                $contact = Contact::create([
                    'first_name' => $item['first_name'],
                    'last_name' => $item['last_name'],
                    'username' => $item['username'],
                    'user_id' => (int)Auth::user()->getAuthIdentifier()
                ]);

                // Save contact's phones
                if(isset($item['phones']) && count($item['phones']) > 0){
                    foreach ($item['phones'] as $x => $phone) {
                        ContactPhone::create([
                            'phone' => $phone,
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }

                // Save contact's emails
                if(isset($item['emails']) && count($item['emails']) > 0){
                    foreach ($item['emails'] as $x => $email) {
                        ContactEmail::create([
                            'email' => $email,
                            'is_default' => $x === 0,
                            'contact_id' => $contact->id
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'title' => "Upload user's contacts",
                'message' => "User's contacts successfully saved"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'title' => "Upload user's contacts",
                'message' => $e->getMessage()
            ], 400);
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
     *         description="user id",
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
        try {
            return response()->json([
                'status' => 'success',
                'title' => 'Contacts are deleted',
                'message' => 'Contacts are deleted'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'title' => 'Contacts are not saved',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/contacts/merge",
     *     summary="Merge 2 contacts to 1",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function merge(Request $request)
    {
        // Check exist contacts
        $contact_from = Contact::find($request->contact_id_from);
        if (!$contact_from) {
            return response()->json([
                'error' => Config::get('constants.errors.ClientNotFound')
            ], 404);
        }

        $contact_to = Contact::find($request->contact_id_to);
        if (!$contact_to) {
            return response()->json([
                'error' => Config::get('constants.errors.ClientNotFound')
            ], 404);
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

            // Merge associations
            foreach ($contact_from->clientAssociations as $association) {
                // Check client, don't association with self
                if ($association->association_contact_id == $contact_to->id) {
                    continue;
                }

                $association->contact()->associate($contact_to);
                $association->save();
            }

            // Merge properties
            foreach ($contact_from->clientProperties as $property) {
                $property->contact()->associate($contact_to);
                $property->save();
            }

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

            // Merge contacts feedbacks
            foreach ($contact_from->feedbacks as $feedback) {
                $feedback->contact()->associate($contact_to);
                $feedback->save();
            }

            // Merge contacts reviews
            foreach ($contact_from->reviews as $review) {
                $review->contact()->associate($contact_to);
                $review->save();
            }

            // Delete donor client
            $contact_from->delete();

            // Return response
            return response()->json([
                'success' => [
                    'message' => 'Contacts was merged successfull'
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * @return string[]
     */
    private function rules(): array
    {
        return [
            'contacts' => 'required|array'
        ];
    }
}
