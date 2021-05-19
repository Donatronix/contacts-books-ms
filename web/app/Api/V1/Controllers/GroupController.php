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
class GroupController extends Controller
{
    /**
     * User's contact list
     *
     * @OA\Get(
     *     path="/v1/contacts/groups",
     *     summary="Load user's contact list",
     *     description="Load user's contact list",
     *     tags={"Contact's Groups"},
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
     *     path="/v1/contacts/groups",
     *     summary="Save contact data in Neo4j",
     *     description="Save contact data in Neo4j",
     *     tags={"Contact's Groups"},
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

    /**
     * Delete contact data
     *
     * @OA\Delete(
     *     path="/v1/contacts/groups/{id}",
     *     summary="Save contact data in Neo4j",
     *     description="Save contact data in Neo4j",
     *     tags={"Contact's Groups"},
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
}
