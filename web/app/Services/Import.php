<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Services\Imports\Vcard;
use Illuminate\Http\UploadedFile;

class Import
{
    // Temporary method for tests.
    public function run(Request $request)
    {
        return view("tests.import1");
    }

    /**
     *  Loops through the possible parsing options and returns an array in the desired format, if possible.
     *
     * @param Request $request
     * @return array $data_result | false
     */
    public function check(Request $request)
    {
        // get list classes by path app/Services/Imports/
        $path = __DIR__ . '/Imports/';
        $classes = $this->getClassList($path);

        // read the uploaded file
        $file_data = $this->readFile($request);
        // trying to parse the contents of the file
        $data_result = $this->parse($file_data, $classes) ?? false;
        dd($data_result); // TODO: Remove when finished working with parsing of all possible formats.
        return $data_result;
    }

    /**
     *  Reads the downloaded file and returns its contents.
     *
     * @param $request
     * @return false|string
     */
    public function readFile($request)
    {
        // TODO: Validation !!!

        $file = $request->file('contacts');
        $path_file = $file->getPathname();

        return file_get_contents($path_file) ?? false;
    }

    /**
     *  Loops through the classes from the imports directory, trying to find the required file format and, if it finds it, tries to parse it.
     *
     * @param string $file_data
     * @param array $classes
     * @return false|array $data_result
     */
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
                if($data_check == null){
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

        return $data_result ?? false;
    }

    /**
     *  Reads the class import directory and creates an array from them.
     *
     * @param string $dirpath
     * @return array $result
     */
    public function getClassList($dirpath)
    {
        $result = [];
        $cdir = scandir($dirpath);
        foreach ($cdir as $value)
        {
            if (!in_array($value,array(".", "..")) && !is_dir($dirpath . DIRECTORY_SEPARATOR . $value)){
                $value = trim(substr($value, 0, -4));
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     *   Loops through an array to determine an internal empty element.
     *
     * @param array $list
     * @return mixed
     */
    public function checkArrayByEmpty($list)
    {
        foreach ($list as $value){
            return $value;
        }
    }
}
