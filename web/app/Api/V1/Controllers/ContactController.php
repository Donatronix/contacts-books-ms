<?php

namespace App\Api\V1\Controllers;

use App\Helpers\Vcard;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Exception;
use GraphAware\Neo4j\Client\ClientBuilder;
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
            $contacts = Contact::where('user_id', $user_id)->get();

            // Return response
            return response()->json([
                'success' => true,
                'data' => $contacts
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Save contact data
     *
     * @OA\Post(
     *     path="/v1/contacts",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 description="User ID",
     *                 example="124"
     *             ),
     *             @OA\Property(
     *                 property="contacts",
     *                 type="text",
     *                 description="Contacts in JSON",
     *                 example=""
     *             ),
     *            @OA\Property(
     *                 property="deleteAbsent",
     *                 type="integer",
     *                 description="Delete contacts, absent in JSON, or not",
     *                 example="0"
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
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function store(Request $request)
    {
        $userID = 0;
        $json = '';
        $deleteAbsent = false;
        $errors = $this->validation($request, $userID, $json, $deleteAbsent);

        if (count($errors) > 0)
            return response()->json([
                'status' => 'error',
                'title' => 'Data is not valid',
                'message' => implode(', ', $errors)
            ], 400);

        $result = $this->save($userID, $json, $deleteAbsent);

        if ($result == 'Ok')
            return response()->json([
                'status' => 'success',
                'title' => 'Contacts are saved',
                'message' => 'Contacts are saved'
            ], 200);
        else {
            return response()->json([
                'status' => 'error',
                'title' => 'Contacts are not saved',
                'message' => $result
            ], 400);
        }
    }

    /***********************************
     *  P R I V A T E
     ************************************/
    private function validation(Request $request, &$userID, &$json, &$deleteAbsent)
    {
        $errors = [];

        if (!isset($request->userID))
            $errors[] = 'No user ID';
        else {
            $userID = (int)$request->userID;
            if ($userID == 0)
                $errors[] = 'Invalid user ID';
        }

        if (isset($request->deleteAbsent)) {
            $absent = (int)$request->deleteAbsent;
            $deleteAbsent = ($absent > 0);
        }

        $json = '';

        if (!isset($request->contacts))
            $errors[] = 'No contacts';
        else {
            $json = json_decode($request->contacts);
            $msg = json_last_error();

            if ($msg !== JSON_ERROR_NONE) {
                $string = json_last_error_msg();
                $errors[] = 'Invalid contacts: ' . $string;
            }
        }

        return $errors;
    }

    private function save($userID, $json, $deleteAbsent)
    {
        try {
            $client = ClientBuilder::create()
                ->addConnection('default', env('NEO_DEFAULT_URL', 'http://neo4j:kanku@localhost:7474')) // Example for HTTP connection configuration (port is optional)
                ->addConnection('bolt', env('NEO_BOLT_URL', 'bolt://neo4j:kanku@localhost:7687')) // Example for BOLT connection configuration (port is optional)
                ->build();

            //Look for a user id= $userID. If not found, create such a user.
            $query = "MERGE (person:User {  id:$userID })
RETURN person";
            $client->run($query);

            //get existing contacts
            $query = "MATCH (person:User {  id:$userID })-[:LISTEN]->(Contact) RETURN collect(Contact) as contacts";
            $result = $client->run($query);
            $existing = [];
            $records = $result->getRecords();
            foreach ($records as $record) {
                foreach ($record->value('contacts') as $one) {
                    $name = $one->value('name');
                    $value = $one->value('text');
                    $existing[] = ['name' => $name, 'text' => $value, 'inJson' => 0];
                }
            }

            foreach ($json as $one) {
                $arr = (array)$one;
                foreach ($arr as $key => $value) {
                    $name = $key;
                    $text = $value;

                    $exists = false;

                    foreach ($existing as $index => $one) {
                        if (($one['name'] == $name) && ($one['text'] == $text)) {
                            $exists = true;

                            $existing[$index]['inJson'] = 1;

                            break;
                        }
                    }

                    if ($exists)
                        continue;

                    $query = "MATCH (person:User) WHERE person.id = $userID
                        CREATE (ct:Contact {name: \"$name\", text:\"" . $text . "\"}),
                        (person)-[:LISTEN]->(ct)
                        ";
                    $client->run($query);
                }
            }

            if ($deleteAbsent) {
                foreach ($existing as $one) {
                    if ($one['inJson'] == 0) {
                        $name = $one['name'];
                        $text = $one['text'];
                        $query = "MATCH (person:User {  id:$userID })-[:LISTEN]->(ct:Contact {name: \"$name\", text:\"" . $text . "\"}) DETACH DELETE ct";
                        $client->run($query);
                    }
                }
            }

            return 'Ok';
        } catch (Exception $e) {
            return $e->getMessage();
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
        $userID = 0;
        $json = '';
        $errors = $this->validation($request, $userID, $json);

        if (count($errors) > 0)
            return response()->json([
                'status' => 'error',
                'title' => 'Data is not valid',
                'message' => implode(', ', $errors)
            ], 400);

        try {
            $client = ClientBuilder::create()
                ->addConnection('default', env('NEO_DEFAULT_URL', 'http://neo4j:kanku@localhost:7474')) // Example for HTTP connection configuration (port is optional)
                ->addConnection('bolt', env('NEO_BOLT_URL', 'bolt://neo4j:kanku@localhost:7687')) // Example for BOLT connection configuration (port is optional)
                ->build();

            foreach ($json as $one) {
                $arr = (array)$one;
                foreach ($arr as $key => $value) {
                    $name = $key;
                    $text = $value;

                    $query = "MATCH (person:User {  id:$userID })-[:LISTEN]->(ct:Contact {name: \"$name\", text:\"" . $text . "\"}) DETACH DELETE ct";
                    $client->run($query);
                }
            }

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
}
