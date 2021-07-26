<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactEmail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class ContactEmailController
 *
 * @package App\Api\V1\Controllers
 */
class ContactEmailController extends Controller
{
    /**
     * Store a newly contact email in storage.
     *
     * @OA\Post(
     *     path="/v1/contacts/emails",
     *     summary="Add contact email",
     *     tags={"Contact Emails"},
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
     *                 description="Contact ID",
     *                 example="2"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 description="Email of contact",
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $contact = Contact::find($request->get('contact_id', 0));

        if (!$contact) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.ContactNotFound')
            ], 404);
        }

        try {
            $is_default = $request->get('is_default', null);

            // Reset is_default for other emails
            if ($is_default) {
                foreach ($contact->emails as $email) {
                    $email->is_default = false;
                    $email->save();
                }
            }

            // Create new
            $email = new ContactEmail();
            $email->contact()->associate($contact);
            $email->email = $request->get('email');
            $email->is_default = $is_default;
            $email->save();

            return response()->jsonApi($email);
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
     * Update email of contact
     *
     * @OA\Put(
     *     path="/v1/contacts/emails/{id}",
     *     summary="Update email of contact",
     *     description="Can send one parameter",
     *     tags={"Contact Emails"},
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
     *                 description="Email of contact",
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
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $email = ContactEmail::with(['contact'])->where('id', $id)->first();

        if (!$email) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.ContactEmailNotFound')
            ], 404);
        }

        try {
            if ($request->has('is_default')) {
                $is_default = $request->get('is_default', 'false');

                // Reset is_default for other emails
                if ($is_default && $email->contact) {
                    foreach ($email->contact->emails as $old_email) {
                        $old_email->is_default = false;
                        $old_email->save();
                    }
                }

                $email->is_default = $is_default;
            }

            if ($request->has('email')) {
                $email->email = $request->get('email');
            }

            $email->save();

            return response()->jsonApi($email);
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
     * Delete contact email from storage.
     *
     * @OA\Delete(
     *     path="/v1/contacts/emails/{id}",
     *     summary="Delete contact email",
     *     tags={"Contact Emails"},
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
     *         description="Email Id",
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
     *                     example="Contact email with id: 123 was deleted"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Contact email not found",
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
     * @return mixed
     */
    public function destroy($id)
    {
        $email = ContactEmail::find($id);

        if (!$email) {
            return response()->jsonApi([
                'error' => Config::get('constants.errors.ContactEmailNotFound')
            ], 404);
        }
        $email->delete();

        return response()->jsonApi([
            'success' => [
                'message' => 'Contact\'s email with id: ' . $id . ' was deleted'
            ]
        ]);
    }

    /**
     * @param $id
     */
    private function getObject($id)
    {

    }
}
