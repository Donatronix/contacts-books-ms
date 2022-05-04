<?php

namespace App\Services;

use App\Services\Imports\Vcard;
use Exception;
use Illuminate\Http\Request;

class Import
{
    /**
     *  Loops through the possible parsing options and returns an array in the desired format, if possible.
     *
     * @param Request $request
     *
     * @return array $data_result | false
     */
    public function exec(Request $request)
    {
        // get list classes by path app/Services/Imports/
//        $path = __DIR__ . '/Imports/';
//        $classes = $this->getClassList($path);
        $classes = null;

        // trying to parse the contents of the file
        return $this->parse($request, $classes) ?? [];
    }

    /**
     * Loops through the classes from the import directory,
     * trying to find the required file format and, if it finds it, tries to parse it.
     *
     * @param string $file_data
     * @param array  $classes
     *
     * @return false|array $data_result
     * @throws \Exception
     */
    public function parse($request, $classes = null)
    {
        $inputFile = $request->file('contacts');

//        $file_extension = $inputFile->clientExtension();
        $file_extension = $inputFile->extension();

//        foreach ($classes as $class) {

        if ($file_extension == 'vcard' || $file_extension == 'vcf') {
            $data_parse = (new Vcard($inputFile->get()))->parse();
        }

        if ($file_extension == 'csv' || $file_extension == 'txt') {
            $data_parse = (new CsvParser())->parse($inputFile);
        }

//        }

        $data_result = $this->insertContactToDB($data_parse);

        return $data_result ?? [];
    }

    /**
     * Adding data from the uploaded file to the database and sending the avatar information to the file microservice.
     *
     * @param $data_arr
     *
     * @return string[]
     * @throws \Exception
     */
    public function insertContactToDB($data_arr): array
    {
        try {
            $info_send_rabbitmq_body = [];

            $totalAdded = 0;
            foreach ($data_arr as $inputData) {
               // dd($inputData);

                $result = ContactHelper::save($inputData);

                if ($result['contact']) {
                    $totalAdded++;
                }

                if($result['avatar']){
                    $info_send_rabbitmq_body[] = $result['avatar'];
                }
            }

            // Send to batch process contact's avatars
            ContactHelper::saveAvatars($info_send_rabbitmq_body);

            // Return result
            return [
                'count' => $totalAdded
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     *  Reads the class import directory and creates an array from them.
     *
     * @param string $dirpath
     *
     * @return array $result
     */
    private function getClassList($dirpath): array
    {
        $result = [];
        $cdir = scandir($dirpath);
        foreach ($cdir as $value) {
            if (!in_array($value, [".", ".."]) && !is_dir($dirpath . DIRECTORY_SEPARATOR . $value)) {
                $value = trim(substr($value, 0, -4));
                $result[] = $value;
            }
        }

        return $result;
    }
}
