<?php

use App\Http\Controllers\Controller;
use Exception;
use GraphAware\Neo4j\Client\ClientBuilder;
use Illuminate\Http\Request;

/**
 * Class ContactController
 *
 * @package App\Api\V1\Controllers
 */
class ContactController extends Controller
{
    /**
     * Save contact data
     *
     * @ OA\Post (
     *     path="/v1/contacts/11",
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
     *
     *     @ OA\RequestBody(
     *         required=true,
     *         @ OA\JsonContent(
     *             @ OA\Property(
     *                 property="contacts",
     *                 type="text",
     *                 description="Contacts in JSON",
     *                 example=""
     *             ),
     *            @ OA\Property(
     *                 property="deleteAbsent",
     *                 type="integer",
     *                 description="Delete contacts, absent in JSON, or not",
     *                 example="0"
     *             )
     *         )
     *     ),
     *
     *     @ OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @ OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @ OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @ OA\Response(
     *         response="404",
     *         description="Not Found"
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
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Data is not valid',
                'message' => implode(', ', $errors)
            ], 400);

        $result = $this->save($userID, $json, $deleteAbsent);
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
     * @ OA Delete(
     *     path="/v1/contacts/{id}/11",
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
     *
     *     @ OA\Parameter(
     *         name="userID",
     *         description="user id",
     *         required=true,
     *         in="query",
     *          @ OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @ OA\Parameter(
     *         name="contacts",
     *         description="Contacts in JSON",
     *         required=true,
     *         in="query",
     *          @ OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @ OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @ OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @ OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @ OA\Response(
     *         response="404",
     *         description="Not Found"
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
            return response()->jsonApi([
                'type' => 'danger',
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
        } catch (Exception $e) {

        }
    }
}
