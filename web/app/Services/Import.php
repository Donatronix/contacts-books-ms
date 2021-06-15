<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Services\Imports\Vcard;
use Illuminate\Http\UploadedFile;

class Import
{
    public function run(Request $request)
    {
        return view("tests.import1");
    }

    public function check(Request $request)
    {
        // get list classes by path app/Services/Imports/
        $path = __DIR__ . '/Imports/';
        $classes = $this->getClassList($path);

        // read the uploaded file
        $file_data = $this->readFile($request);
        // trying to parse the contents of the file
        $data_result = $this->parse($file_data, $classes) ?? false;
        dd($data_result);
        return $data_result;
    }

    public function readFile($request)
    {
        $file = $request->file('contacts');
        $path_file = $file->getPathname();

        return file_get_contents($path_file) ?? false;
    }

    public function parse($file_data, $classes)
    {
        $data_result = false;
        $path_to_dir = '\App\Services\Imports\\';
        foreach ($classes as $k => $class)
        {
            if($path_to_dir . $class)
            {
                $path_to_class = $path_to_dir . $class;
                $data_object = new $path_to_class($file_data);
                $data_check = $this->checkArrayByEmpty((array)$data_object);
                if($data_check == null)
                {
                    unset($data_object);
                }
                else{
                    $data_result = $data_object->parse($data_object->data);
                    break;
                }
            }
            else{
                break;
            }
        }
        dump($data_result);
        die('END');

        return $data_result ?? false;
    }

    public function getClassList($dirpath)
    {
        $result = [];
        $cdir = scandir($dirpath);
        foreach ($cdir as $value)
        {
            if (!in_array($value,array(".", "..")) && !is_dir($dirpath . DIRECTORY_SEPARATOR . $value))
            {
                $value = trim(substr($value, 0, -4));
                $result[] = $value;
            }
        }
        return $result;
    }

    public function checkArrayByEmpty($list)
    {
        foreach ($list as $value){
            return $value;
        }
    }
}
