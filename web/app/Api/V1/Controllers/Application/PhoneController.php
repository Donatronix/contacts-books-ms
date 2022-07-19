<?php

namespace App\Api\V1\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Phone;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class PhoneController
 *
 * @package App\Api\V1\Controllers\Application
 */
class PhoneController extends Controller
{
    /**
     * Save a new phone number for current contact
     *
     * @OA\Post(
     *     path="/phones",
     *     summary="Save a new phone number for current contact",
     *     description="Save a new phone number for current contact",
     *     tags={"Contact Phones"},
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
     *                 description="Phone number of contact",
     *                 example="(555)-777-1234"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Phone type (home, work, cell, etc)",
     *                 enum={"home", "work", "cell", "other", "main", "homefax", "workfax", "googlevoice", "pager"}
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Phone by default. Accept 1, 0, true, false",
     *                 example="false"
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
     *         response="401",
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
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate input
        $this->validate($request, Phone::validationRules());

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

        // Try to add new phone number
        try {
            // Reset is_default for other emails
            if ($request->boolean('is_default')) {
                foreach ($contact->phones as $oldPhone) {
                    $oldPhone->is_default = false;
                    $oldPhone->save();
                }
            }

            // Create new
            $phone = new Phone();
            $phone->fill($request->all());
            $phone->contact()->associate($contact);
            $phone->save();

            // Remove contact object from response
            unset($phone->contact);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Adding new phone number',
                'message' => "Contact's phone number {$phone->value} successfully added",
                'data' => $phone->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Adding new phone number',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Update phone number for current contact
     *
     * @OA\Put(
     *     path="/phones/{id}",
     *     summary="Update phone number for current contact",
     *     description="Update phone number for current contact",
     *     tags={"Contact Phones"},
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
     *         description="Phone number Id",
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
     *                 description="Phone number of contact",
     *                 example="(555)-777-1234"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Phone type (home, work, cell, etc)",
     *                 enum={"home", "work", "cell", "other", "main", "homefax", "workfax", "googlevoice", "pager"}
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Phone by default. Accept 1, 0, true, false",
     *                 example="false"
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
        $this->validate($request, Phone::validationRules());

        // Read contact group model
        $phone = $this->getObject($id);
        if (is_a($phone, 'Sumra\SDK\JsonApiResponse')) {
            return $phone;
        }

        // Try update phone number data
        try {
            // Reset is_default for other phones
            if ($request->boolean('is_default') && $phone->contact) {
                foreach ($phone->contact->phones as $oldPhone) {
                    $oldPhone->is_default = false;
                    $oldPhone->save();
                }
            }

            // Update data
            $phone->fill($request->all());
            $phone->save();

            // Remove contact object from response
            unset($phone->contact);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Changing phone number',
                'message' => "Phone number {$phone->value} successfully updated",
                'data' => $phone->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Change a contact group',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Delete contact phone number from storage
     *
     * @OA\Delete(
     *     path="/phones/{id}",
     *     summary="Delete contact phone number from storage",
     *     description="Delete contact phone number from storage",
     *     tags={"Contact Phones"},
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
     *         description="Phone number Id",
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
     *         description="Contact phone not found"
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

        // Try to delete phone
        try {
            $phone->delete();

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Delete of contact's phone",
                'message' => 'Phone of contacts is successfully deleted',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Delete of contact's phone",
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Get contact's phone number object
     *
     * @param $id
     *
     * @return mixed
     */
    private function getObject($id)
    {
        try {
            return Phone::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contact's phone number",
                'message' => "Contact's phone number with id #{$id} not found",
                'data' => null
            ], 404);
        }
    }
}
