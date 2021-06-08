<?php


namespace App\Services;

use PubSub;
use Exception;
use App\Services\Imports\Vcard;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Test
{
    public function test(Request $request)
    {

//        dd($request->files);
        return view("tests.import");
    }
}
