<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;

/**
 * Class CategoryController
 *
 * @package App\Api\V1\Controllers
 */
class CategoryController extends Controller
{
    /**
     * List of categories
     *
     * @OA\Get(
     *     path="/categories",
     *     summary="Load categories list",
     *     description="Load user's categories list",
     *     tags={"Categories"},
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
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function __invoke()
    {
        try {
            $categories = Category::structure()->get();

            // Return response
            return response()->jsonApi($categories->toArray(), 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get categories list",
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
