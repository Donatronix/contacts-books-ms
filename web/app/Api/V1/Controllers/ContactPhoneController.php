<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactPhone;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class ContactPhoneController
 *
 * @package App\Api\V1\Controllers
 */
class ContactPhoneController extends Controller
{
    /**
     * Store a newly contact phone in storage.
     *
     * @OA\Post(
     *     path="/v1/contacts/phones",
     *     summary="Add contact phone",
     *     tags={"Contact Phones"},
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
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="contact_id",
     *                 type="integer",
     *                 description="contact ID",
     *                 example="2"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 description="Phone of contact",
     *                 example="(555)-777-1234"
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="string",
     *                 description="Phone is_default (text, voice or nothing)",
     *                 enum={"text","voice",""}
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
     *
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
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $contact = Contact::find($request->get('contact_id', 0));

        if (!$contact) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.contactNotFound')
            ], 404);
        }

        try {
            $phone = new ContactPhone();
            $phone->contact()->associate($contact);

            $phone->phone = $request->get('phone');

            $phone->is_default = $request->get('is_default', false);

            $phone->save();

            return response()->jsonApi($phone);
        } catch (Exception $e) {
            return response()->jsonApi([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Update phone of contact
     *
     * @OA\Put(
     *     path="/v1/contacts/phones/{id}",
     *     summary="Update phone of contact",
     *     description="Can send one parameter",
     *     tags={"Contact Phones"},
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
     *         in="path",
     *         description="Phone Id",
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
     *                 property="phone",
     *                 type="string",
     *                 description="Phone of contact",
     *                 example="(555)-777-1234"
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="string",
     *                 description="Phone is_default (text, voice or nothing)",
     *                 enum={"text","voice",""}
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
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $phone = ContactPhone::find($id);

        if (!$phone) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.ContactPhoneNotFound')
            ], 404);
        }

        try {
            if ($request->has('phone')) {
                $phone->phone = $request->get('phone');
            }

            $phone->is_default = $request->get('is_default', false);

            $phone->save();

            return response()->jsonApi($phone);
        } catch (Exception $e) {
            return response()->jsonApi([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Delete contact phone from storage.
     *
     * @OA\Delete(
     *     path="/v1/contacts/phones/{id}",
     *     summary="Delete contact phone",
     *     tags={"Contact Phones"},
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
     *         in="path",
     *         description="Phone Id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully delete",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 type="object",
     *                 property="success",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="contact's phone with id: 123 was deleted"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="contact phone not found",
     *
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
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $phone = ContactPhone::find($id);

        if (!$phone) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.ContactPhoneNotFound')
            ], 404);
        }
        $phone->delete();

        return response()->jsonApi([
            'success' => [
                'message' => 'contact\'s phone with id: ' . $id . ' was deleted'
            ]
        ]);
    }
}
