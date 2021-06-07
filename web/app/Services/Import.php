<?php

namespace App\Services;

use Illuminate\Http\Request;

class Import
{
    public static function readFile($request, $file = 'vcard_ios')
    {

        if ($request->file('vcard_ios.vcf')) {
            dd('upload');
            //return response()->json(app('MediaUploaderService')->upload($request->media));
        }
        dd($request->file('vcard_ios.vcf'));
    }
}
