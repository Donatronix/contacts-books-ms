<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Class ContactController
 *
 * @package App\Api\V1\Controllers
 */
class GroupController extends Controller
{
    /**
     * List of user's groups
     *
     * @OA\Get(
     *     path="/v1/contacts/groups",
     *     summary="Load user's groups list",
     *     description="Load user's groups list",
     *     tags={"Contact Groups"},
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
            $groups = Group::all(); //where('user_id', $user_id)->get();

            // Return response
            return response()->json([
                'success' => true,
                'data' => $groups
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Save user's group data
     *
     * @OA\Post(
     *     path="/v1/contacts/groups",
     *     summary="Save user's group data",
     *     description="Save user's group data",
     *     tags={"Contact Groups"},
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
     *                 property="name",
     *                 type="text",
     *                 description="Group Name",
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
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules());

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
     * Update email of client
     *
     * @OA\Put(
     *     path="/v1/contacts/emails/{id}",
     *     summary="Update email of client",
     *     description="Can send one parameter",
     *     tags={"Contact Emails"},
     *     security={{"bearerAuth":{}}},
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input
        $this->validate($request, $this->rules());

        // Get payment order model
        $group = $this->getObject($id);



    }

    /**
     * Delete contact's group
     *
     * @OA\Delete(
     *     path="/v1/contacts/groups/{id}",
     *     summary="Delete contact's group",
     *     description="Delete contact's group",
     *     tags={"Contact Groups"},
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
     *         description="Group ID",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer"
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
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        // Check group model
        $group = $this->getObject($id);
        if(!$group instanceof Group){
            return $group;
        }

        try {
            $group->delete();

            return response()->json([
                'status' => 'success',
                'title' => "Delete of contact's group",
                'message' => 'Group of contacts is successfully deleted'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'error',
                'title' => "Delete of contact's group",
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @return string[]
     */
    private function rules(): array
    {
        return [
            'name' => 'required|min:3|string'
        ];
    }

    /**
     * Contact's group not found
     *
     * @param $id
     *
     * @return mixed
     */
    private function getObject($id){
        try {
            return Group::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => "Get contact's group",
                'message' => "Contact's group #{$id} not found"
            ], 404);
        }
    }
}
