<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RemoteController extends Controller
{

    public function remote(Request $request)
    {
        try {
            $json = json_decode($request, true);
            $result = [];

            foreach ($json as $k => $item) {
                if (isset($json['display_name'])) {
                    $result[$k]['name_param'] = $json['display_name'];
                }
                if (isset($json['msisdns'])) {
                    $result[$k]['phone'] = $json['msisdns'];
                }
                if (isset($json['emails'])) {
                    $result[$k]['email'] = $json['emails'];
                }
                if (isset($json['photo_uri'])) {
                    $result[$k]['photo'] = $json['photo_uri'];
                }
            }

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Get data was success",
                'message' => "Get data from remote server was successfully"
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Get data from remote server was unsuccessful",
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
