<?php

namespace App\Services;

use Illuminate\Http\Request;

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
}
