<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try {
            $groups = Group::byOwner()->get();

            // Return response
            return response()->jsonApi($groups->toArray(), 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => "Get groups list",
                'message' => $e->getMessage()
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
     *                 example="My Group 1"
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
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, $this->rules());

        try {
            $group = Group::create([
                'name' => $request->get('name'),
                'user_id' => (int)Auth::user()->getAuthIdentifier()
            ]);

            return response()->jsonApi([
                'status' => 'success',
                'title' => 'Group creating',
                'message' => "Group {$group->name} successfully added"
            ], 200);
        }catch (Exception $e){
            return response()->jsonApi([
                'status' => 'error',
                'title' => 'Group creating',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update user's group data
     *
     * @OA\Put(
     *     path="/v1/contacts/groups/{id}",
     *     summary="Update user's group data",
     *     description="Update user's group data",
     *     tags={"Contact Groups"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Group Id",
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
     *                 property="name",
     *                 type="string",
     *                 description="Name of group",
     *                 example="My Group 111"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
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
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // Validate input
        $this->validate($request, $this->rules());

        // Get payment order model
        $group = $this->getObject($id);
        if(!$group instanceof Group){
            return $group;
        }

        try {
            $group->fill($request->toArray());
            $group->save();

            return response()->jsonApi([
                'status' => 'success',
                'title' => 'Group updating',
                'message' => "Group {$group->name} successfully updated"
            ], 200);
        }catch (Exception $e){
            return response()->jsonApi([
                'status' => 'error',
                'title' => 'Group updating',
                'message' => $e->getMessage()
            ], 400);
        }
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

            return response()->jsonApi([
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
