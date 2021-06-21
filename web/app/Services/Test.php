<?php


namespace App\Services;

use PubSub;
use Exception;
use App\Services\Imports\Vcard;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class Test
{
    public function run(Request $request)
    {
        return view("tests.import1");
    }

    public function test(Request $request, $file_data_array)
    {
        /*$request->validate([
            'contacts' => 'file'
        ]);*/

        dump('END');

    }

    private function checkParam($param)
    {
        if($param){
            return $param;
        }
        return false;
    }
}
