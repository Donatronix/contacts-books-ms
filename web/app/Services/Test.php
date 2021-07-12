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

    public function test()
    {
        /*$request->validate([
            'contacts' => 'file'
        ]);*/
        $path_image = 'https://lh6.googleusercontent.com/-JtoZVa26Fvo/YMBqYcaMcOI/AAAAAAAAAAA/5XIYN8VHfIQL30j4nliVPDkVqtoxFVkaACOQCEAE/photo.jpg';

        $result = Import::InsertBase64EncodedImage($path_image);
        dump($result);
        die('END');

    }

    private function checkParam($param)
    {
        if($param){
            return $param;
        }
        return false;
    }
}
