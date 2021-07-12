<?php


namespace App\Api\V1\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RemoteController extends Controller
{
    /**
     * Save user's contacts data
     *
     * @OA\Post(
     *     path="/v1/contacts/remote",
     *     summary="Save user's contacts data from remote",
     *     description="Save user's contacts data from remote",
     *     tags={"Remote"},
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
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="display_name",
     *                 type="string",
     *                 description="Display name data in string",
     *                 example=""
     *             )
     *             @OA\Property(
     *                 property="photo_uri",
     *                 type="string",
     *                 description="Photo uri data in string",
     *                 example=""
     *             )
     *             @OA\Property(
     *                 property="msisdns",
     *                 type="array",
     *                 description="Msisdns data in JSON",
     *                 example=""
     *             )
     *             @OA\Property(
     *                 property="emails",
     *                 type="array",
     *                 description="Emails data in JSON",
     *                 example=""
     *             )
     *             @OA\Property(
     *                 property="shared",
     *                 type="boolean",
     *                 description="Shared data",
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
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function remote(Request $request)
    {
        try
        {
            $json = json_decode($request, true);
            $result = [];

            foreach($json as $k => $item)
            {
                if(isset($json['display_name'])){
                    $result[$k]['name_param'] = $json['display_name'];
                }
                if(isset($json['msisdns'])){
                    $result[$k]['phone'] = $json['msisdns'];
                }
                if(isset($json['emails'])){
                    $result[$k]['email'] = $json['emails'];
                }
                if(isset($json['photo_uri'])){
                    $result[$k]['photo'] = $json['photo_uri'];
                }
            }

            return response()->json([
                'status' => 'success',
                'title' => "Get data was success",
                'message' => "Get data from remote server was successfully"
            ], 200);
        }
        catch (Exception $e)
        {
            return response()->json([
                'status' => 'danger',
                'title' => "Get data from remote server was unsuccessful",
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
