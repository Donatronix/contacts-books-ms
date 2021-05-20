<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactPhone;
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
     * @OA\Post(
     *     path="/v1/contacts/phones",
     *     summary="Add client phone",
     *     tags={"Contact Phones"},
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
     *                 property="phone",
     *                 type="string",
     *                 description="Phone of client",
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
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Store a newly client phone in storage.
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
            $phone = new ContactPhone();
            $phone->contact()->associate($contact);

            $phone->phone = $request->get('phone');

            $phone->is_default = $request->get('is_default', false);

            $phone->save();

            return response()->json($phone);
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
     * Update phone of client
     *
     * @OA\Put(
     *     path="/v1/contacts/phones/{id}",
     *     summary="Update phone of client",
     *     description="Can send one parameter",
     *     tags={"Contact Phones"},
     *     security={{"bearerAuth":{}}},
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
     *                 description="Phone of client",
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $phone = ContactPhone::find($id);

        if (!$phone) {
            return response()->json([
                'error' => Config::get('constants.errors.ContactPhoneNotFound')
            ], 404);
        }

        try {
            if ($request->has('phone')) {
                $phone->phone = $request->get('phone');
            }

            $phone->is_default = $request->get('is_default', false);

            $phone->save();

            return response()->json($phone);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Delete client phone
     *
     * @OA\Delete(
     *     path="/v1/contacts/phones/{id}",
     *     summary="Delete client phone",
     *     tags={"Contact Phones"},
     *     security={{"bearerAuth":{}}},
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
     *                     example="Client's phone with id: 123 was deleted"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Client phone not found",
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
    /**
     * Remove the client phone from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $phone = ContactPhone::find($id);

        if (!$phone) {
            return response()->json([
                'error' => Config::get('constants.errors.ContactPhoneNotFound')
            ], 404);
        }
        $phone->delete();

        return response()->json([
            'success' => [
                'message' => 'Client\'s phone with id: ' . $id . ' was deleted'
            ]
        ]);
    }

    /**
     * @return array[]
     */
    private function rules()
    {
        return [
            'phone' => [
                'required',
                'max:15',
                //'regex:/(0)[0-9\(\)]{15}/',
                Rule::unique('contact_phones')->where(function ($query) {
                    return $query->where('contact_id', $this->request->get('contact_id'));
                })
            ]
        ];
    }
}
