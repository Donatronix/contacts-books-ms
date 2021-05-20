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
     * Store a newly client email in storage.
     *
     * @OA\Post(
     *     path="/v1/contacts/emails",
     *     summary="Add client email",
     *     tags={"Contact Emails"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="contact_id",
     *                 type="integer",
     *                 description="Client ID",
     *                 example="2"
     *             ),
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
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $contact = Contact::find($request->get('contact_id', 0));

        if (!$contact) {
            return response()->json([
                'error' => Config::get('constants.errors.ClientNotFound')
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

            return response()->json($email);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
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
        $email = ContactEmail::with(['client'])->where('id', $id)->first();

        if (!$email) {
            return response()->json([
                'error' => Config::get('constants.errors.ContactEmailNotFound')
            ], 404);
        }

        try {
            if ($request->has('is_default')) {
                $is_default = $request->get('is_default', 'false');

                // Reset is_default for other emails
                if ($is_default && $email->client) {
                    foreach ($email->client->emails as $old_email) {
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

            return response()->json($email);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Delete client email
     * Remove the client email from storage.
     *
     * @OA\Delete(
     *     path="/v1/contacts/emails/{id}",
     *     summary="Delete client email",
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
     *                     example="Client email with id: 123 was deleted"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Client email not found",
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
     */
    public function destroy($id)
    {
        $email = ContactEmail::find($id);

        if (!$email) {
            return response()->json([
                'error' => Config::get('constants.errors.ContactEmailNotFound')
            ], 404);
        }
        $email->delete();

        return response()->json([
            'success' => [
                'message' => 'Client\'s email with id: ' . $id . ' was deleted'
            ]
        ]);
    }

    private function rules()
    {
        return [
            'email' => [
                'required',
                'regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/',
                Rule::unique('contact_emails')->where(function ($query) {
                    return $query->where('contact_id', $this->request->get('contact_id'));
                })
            ]
        ];
    }
}
