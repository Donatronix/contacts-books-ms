<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\Imports\Vcard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;


class Import
{
    // Temporary method for tests.
    public function run(Request $request)
    {
        return view("tests.import");
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

        // trying to parse the contents of the file
        return $this->parse($classes, $request) ?? false;
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
    public function parse($classes, $request)
    {
        $data_result = false;
        $path_to_dir = '\App\Services\Imports\\';
        $file_data = '';
        $file_extension = $request->file()['contacts']-> clientExtension();
        foreach ($classes as $class)
        {
            if($file_extension == 'vcard'){
                $file = $this->readFile($request);
                $file_data = new Vcard($file);
                $data_parse = $file_data->parse($file_data);
//                $data_result = $this->test($data_parse);
                $data_result = $this->insertContactToBb($data_parse);
                dd($data_result);
            }

            if($file_extension == 'csv'){
                $file = new CsvParser();
                $data_result = $file->run($request);
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

    public static function InsertBase64EncodedImage($path_img)
    {
        return base64_encode(file_get_contents($path_img));
    }

    public static function searchContact($data)
    {
        return DB::select("SELECT * FROM {$data['table']} WHERE `user_id` = {$data['id']} ORDER BY `user_id` DESC LIMIT 1") ?? false;
    }

    public static function checkFileFormat($file)
    {
        $avatar = strtolower(substr($file, -3));
        $result_file = false;

        if($avatar == 'jpg' || $avatar == 'gif' || $avatar == 'png' || $avatar == 'bmp' || $avatar == 'jpeg' || $avatar == 'tiff' || $avatar == 'webp'){
            $result_file = true;
        }

        return $result_file;
    }

    /**
     *  Adding data from the uploaded file to the database and sending the avatar information to the file microservice.
     *
     * @param $data_arr array
     * @return mixed
     */
    public function insertContactToBb($data_arr)
    {
        dump($data_arr);
//        $user_id = (int)Auth::user()->getAuthIdentifier();
        $user_id = 10; // TODO: Remove demo-user id
        $data_cnt = ['name_param_cnt' => 0];
        $contact_info = [];
        $info_send_rabbitmq = [];

        try
        {
            foreach ($data_arr as $k => $param)
            {
                $user = new Contact();

                if(!$param['full_name'] || !isset($param['full_name'])){
                    $param['full_name'] = false;
                }

                if(isset($param['photo'])){
                    $file_check_data = Import::checkFileFormat($param['photo']);
                }

                if(isset($param['name_param']))
                {
                    foreach ($param['name_param'] as $key => $item)
                    {
                        $user_value = $param['name_param'][$key]['value'];

                        if(!isset($param['name_param'][$key]['type'])){
                            continue;
                        }

                        if($param['name_param'][$key]['type'] == 'last_name'){
                            $user->last_name = $user_value;
                        }
                        if($param['name_param'][$key]['type'] == 'first_name'){
                            $user->first_name = $user_value;
                        }
                        if($param['name_param'][$key]['type'] == 'surname'){
                            $user->surname = $user_value;
                        }
                        if($param['name_param'][$key]['type'] == 'user_prefix'){
                            $user->user_prefix = $user_value;
                        }
                        if($param['name_param'][$key]['type'] == 'user_suffix'){
                            $user->user_suffix = $user_value;
                        }
                    }
                }

                if(isset($param['birthday'])){
                    $user->birthday = $param['birthday'];
                }

                if(isset($param['nickname'])){
                    $user->nickname = $param['nickname'];
                }

                $user->user_id = $user_id;
//                $user->save();

                if(isset($param['photo']) && $file_check_data)
                {
                    $contact_info = ['table' => 'contacts', 'id' => $user_id];
                    $contact_info = Import::searchContact($contact_info);

                    $info_send_rabbitmq[] = ['contact_id' => $contact_info[0]->id, 'avatar' => $param['photo']];
                }
            }

            if($info_send_rabbitmq){
                PubSub::publish('getUrlAvatar', $info_send_rabbitmq, 'files');
            }

            return response()->jsonApi([
                'status' => 'success',
                'title' => 'Create was success',
                'message' => 'The operation to add data to the database was successful',
            ], 200);

        }
        catch (\Exception $e)
        {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Operation not successful',
                'message' => 'The operation for insert was unsuccessful'
            ], 404);
        }
    }
}
