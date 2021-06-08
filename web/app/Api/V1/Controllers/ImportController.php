<?php

namespace App\Api\V1\Controllers;

use App\Services\Imports\Vcard;
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
class ImportController extends Controller
{

    /**
     * import user contacts
     *
     * @OA\Post(
     *     path="/v1/contacts/import/create",
     *     summary="create contacts from vCard",
     *     description="create contacts from vCard",
     *     tags={"Import Contacts"},
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
     *         name="vcards",
     *         description="Import file",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="file"
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

    public function create(Request $request)
    {
        $cards = (new Vcard())->readData($request->vcards);
        dd($cards);
    }

    /**
     * User's contact list: add contacts from vCard
     *
     * @OA\Post(
     *     path="/v1/contacts/import/vcard",
     *     summary="Add contacts from vCard",
     *     description="Add contacts from vCard",
     *     tags={"Import Contacts"},
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
     *         name="vcards",
     *         description="vCard text",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer",
     *              default = 0
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
    public function addvcard(Request $request)
    {

        $user_id = (int)Auth::user()->getAuthIdentifier();

        dd($request->vcards);
        $cards = (new Vcard())->readData($request->vcards);

        $contacts = [];
        try {
            foreach ($cards as $c) {
                $contact = Contact::create([
                    'user_id' => $user_id,
                    'first_name' => $c['N'][0]['value'][1][0],
                    'last_name' => $c['N'][0]['value'][0][0],
                    'middlename' => $c['N'][0]['value'][2][0],
                    'prefix' => $c['N'][0]['value'][3][0],
                    'suffix' => $c['N'][0]['value'][4][0],
                    'nickname' => $c['NICKNAME'][0]['value'][0][0],
                    'adrextend' => $c['ADR'][0]['value'][0][0],
                    'adrstreet' => $c['ADR'][0]['value'][2][0] . "\n" . $c['ADR'][0]['value'][1][0],
                    'adrcity' => $c['ADR'][0]['value'][3][0],
                    'adrstate' => $c['ADR'][0]['value'][4][0],
                    'adrzip' => $c['ADR'][0]['value'][5][0],
                    'adrcountry' => $c['ADR'][0]['value'][6][0],

                    //'tel1' => $c['TEL'][0]['value'][0][0],
                    //'email' => $c['EMAIL'][0]['value'][0][0]
                ]);

                $contact->save();

                $contacts[] = $contact;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }

        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);
    }

    /**
     * User's contact list: add contacts from Google export
     *
     * @OA\Post(
     *     path="/v1/contacts/import/google",
     *     summary="Add contacts from Google export",
     *     description="Add contacts from Google export",
     *     tags={"Import Contacts"},
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
     *         name="googleexport",
     *         description="Google export text",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer",
     *              default = 0
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
    public function addgoogle(Request $request)
    {
        $user_id = (int)Auth::user()->getAuthIdentifier();

        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);
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
}
