<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Services\Imports\Vcard;
use Illuminate\Http\UploadedFile;

class Import
{
    public function check(Request $request)
    {
        // get list classes by path app/Services/Imports/
        $path = __DIR__ . '/Imports/';
        $classes = $this->getClassList($path);

        // read the uploaded file
        $file_data = $this->readFile($request);
        // trying to parse the contents of the file
        return $this->parse($file_data, $classes) ?? false;
    }

    public function readFile($request)
    {
        $file = $request->file('contacts');
        $path_file = $file->getPathname();
        return trim(file_get_contents($path_file)) ?? false;
    }

    public function parse($file_data, $classes)
    {

        for($i=1; $i < count($classes); $i++)
        {


                $file_data_array = (new Vcard())->readData($file_data);
                if($file_data_array){
                    $vcard = new Vcard();
                    $data_result = $vcard->parse($file_data_array, $vcard);
                    break;
                }
            }
        }
        /*dump($data_result);
        dd($file_data_array);*/

//        return $file_data_array ?? false;
    }

    public function getClassList($dirpath)
    {
        $result = [];
        $cdir = scandir($dirpath);
        foreach ($cdir as $value) {
            if (!in_array($value,array(".", "..")) && !is_dir($dirpath . DIRECTORY_SEPARATOR . $value)) {
                $value = substr($value, 0, -4);
                $result[] = $value;
            }
        }
        return $result;
    }
}
