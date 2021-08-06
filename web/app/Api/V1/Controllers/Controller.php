<?php

namespace App\Api\V1\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Contacts Book API Microservice",
 *     description="This is API of Contacts Book Microservice",
 *     version="1.0",
 *
 *     @OA\Contact(
 *         email="admin@sumra.net",
 *         name="Support Team"
 *     )
 * )
 */

/**
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     description="Auth Scheme",
 *     name="oAuth2 Access",
 *     securityScheme="default",
 *
 *     @OA\Flow(
 *         flow="implicit",
 *         authorizationUrl="https://is.sumra.net/oauth2",
 *         scopes={
 *             "ManagerRead"="Manager can read",
 *             "User":"User access",
 *             "ManagerWrite":"Manager can write"
 *         }
 *     )
 * )
 */

/**
 * Api Base Class Controller
 *
 * @package App\Api\V1\Controllers
 */
class Controller extends BaseController{}
