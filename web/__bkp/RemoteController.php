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
     *         @OA\Schema (
     *             type="integer"
     *         )
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
     *             ),
     *             @OA\Property(
     *                 property="photo_uri",
     *                 type="string",
     *                 description="Photo uri data in string",
     *                 example=""
     *             ),
     *             @OA\Property(
     *                 property="msisdns",
     *                 type="array",
     *                 description="Msisdns data in JSON",
     *                 example=""
     *             ),
     *             @OA\Property(
     *                 property="emails",
     *                 type="array",
     *                 description="Emails data in JSON",
     *                 example=""
     *             ),
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
     *              )
     *          )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
}
