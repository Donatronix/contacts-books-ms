<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Email;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class EmailController
 *
 * @package App\Api\V1\Controllers
 */
class EmailController extends Controller
{
    /**
     * Store a newly contact email in storage.
     *
     * @OA\Post(
     *     path="/emails",
     *     summary="Save a new email for current contact",
     *     description="Save a new email for current contact",
     *     tags={"Contact Emails"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="value",
     *                 type="string",
     *                 description="Email of contact",
     *                 example="test@tes.com"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Email type (home, work, etc)",
     *                 enum={"home", "work", "other", "main"}
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Email by default. Accept 1, 0, true, false",
     *                 example="true"
     *             ),
     *             @OA\Property(
     *                 property="contact_id",
     *                 type="string",
     *                 description="Contact ID",
     *                 example="9406d5e9-2273-4807-8761-d5397205112b3"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
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
        // Validate input
        $this->validate($request, Email::validationRules());

        $contactId = $request->get('contact_id', null);
        try {
            $contact = Contact::findOrFail($contactId);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contact object",
                'message' => "Contact with id #{$contactId} not found: " . $e->getMessage(),
                'data' => null
            ], 404);
        }

        // Try to add new email
        try {
            // Reset is_default for other emails
            if ($request->boolean('is_default')) {
                foreach ($contact->emails as $oldEmail) {
                    $oldEmail->is_default = false;
                    $oldEmail->save();
                }
            }

            // Create new
            $email = new Email();
            $email->fill($request->all());
            $email->contact()->associate($contact);
            $email->save();

            // Remove contact object from response
            unset($email->contact);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Adding new email',
                'message' => "Contact's email {$email->value} successfully added",
                'data' => $email->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Adding new email',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Update email for current contact
     *
     * @OA\Put(
     *     path="/emails/{id}",
     *     summary="Update email for current contact",
     *     description="Update email for current contact",
     *     tags={"Contact Emails"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
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
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="value",
     *                 type="string",
     *                 description="Email of contact",
     *                 example="test@tes.com"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Email type (home, work, etc)",
     *                 enum={"home", "work", "other", "main"}
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Email by default. Accept 1, 0, true, false",
     *                 example="true"
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
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // Validate input
        $this->validate($request, Email::validationRules());

        // Read contact group model
        $email = $this->getObject($id);
        if (is_a($email, 'Sumra\SDK\JsonApiResponse')) {
            return $email;
        }

        // Try update email data
        try {
            // Reset is_default for other emails
            if ($request->boolean('is_default') && $email->contact) {
                foreach ($email->contact->emails as $oldEmail) {
                    $oldEmail->is_default = false;
                    $oldEmail->save();
                }
            }

            // Update data
            $email->fill($request->all());
            $email->save();

            // Remove contact object from response
            unset($email->contact);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Changing contact email',
                'message' => "Contact email {$email->value} successfully updated",
                'data' => $email->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Changing contact email',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Delete contact email from storage.
     *
     * @OA\Delete(
     *     path="/emails/{id}",
     *     summary="Delete contact email from storage",
     *     description="Delete contact email from storage",
     *     tags={"Contact Emails"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Email Id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully delete"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Contact email not found"
     *     )
     * )
     *
     * @param $id
     *
     * @return mixed|\Sumra\SDK\JsonApiResponse
     */
    public function destroy($id)
    {
        // Read contact group model
        $phone = $this->getObject($id);
        if (is_a($phone, 'Sumra\SDK\JsonApiResponse')) {
            return $phone;
        }

        // Try to delete email
        try {
            $phone->delete();

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Delete of contact's email",
                'message' => 'Email of contacts is successfully deleted',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Delete of contact's email",
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Get contact's email object
     *
     * @param $id
     *
     * @return mixed
     */
    private function getObject($id)
    {
        try {
            return Email::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contact's email",
                'message' => "Contact's email with id #{$id} not found",
                'data' => null
            ], 404);
        }
    }
}
