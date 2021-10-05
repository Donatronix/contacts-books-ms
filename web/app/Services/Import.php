<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Email;
use App\Models\Phone;
use App\Models\Group;
use App\Models\Relation;
use App\Models\Site;
use App\Models\Work;
use App\Services\Imports\Vcard;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PubSub;

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
            // Read user Id
            $user_id = (string)Auth::user()->getAuthIdentifier();

            // Read selected custom group
            $selectedGroup = request()->get('group_id', null);

            $info_send_rabbitmq_body = [];

            $totalAdded = 0;
            foreach ($data_arr as $param) {
                // First, Create contact
                $contact = new Contact();
                $contact->fill($param);

                if (isset($param['birthday']) && !empty($param['birthday'])) {
                    $contact->birthday = Carbon::parse($param['birthday']);
                }

                $contact->user_id = $user_id;
                $contact->save();

                // Save contact's phones
                if (isset($param['phones'])) {
                    $count = sizeof($param['phones']);

                    foreach ($param['phones'] as $key => $item) {
                        $row = new Phone();
                        $row->phone = str_replace(['(', ')', ' ', '-'], '', $param['phones'][$key]['value']);
                        $row->type = !isset($param['phones'][$key]['type']) ?? 'other';
                        $row->is_default = $count == 1 || $key == 0;
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's emails
                if (isset($param['emails'])) {
                    $count = sizeof($param['phones']);

                    foreach ($param['emails'] as $key => $item) {
                        $row = new Email();
                        $row->email = $param['emails'][$key]['value'];
                        $row->type = !isset($param['emails'][$key]['type']) ?? 'other';
                        $row->is_default = $count == 1 || $key == 0;
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's sites if exist
                if (isset($param['sites'])) {
                    foreach ($param['sites'] as $key => $item) {
                        $row = new Site();
                        $row->url = $param['sites'][$key]['value'];
                        $row->type = !isset($param['sites'][$key]['type']) ?? 'other';
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's relations if exist
                if (isset($param['relations'])) {
                    foreach ($param['relations'] as $key => $item) {
                        $row = new Relation();
                        $row->relation = $param['relations'][$key]['value'];
                        $row->type = !isset($param['relations'][$key]['type']) ?? 'other';
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's chats if exist
                if (isset($param['chats'])) {
                    foreach ($param['chats'] as $key => $item) {
                        $row = new Chat();
                        if (is_array($item)) {
                            $row->chat = $param['chats'][$key]['value'];
                            $row->type = $param['chats'][$key]['type'];
                        } else {
                            $row->chat = $item;
                            $row->type = $key;
                        }
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's addresses if exist
                if (isset($param['addresses'])) {
                    foreach ($param['addresses'] as $key => $item) {
                        $row = new Address();
                        $row->fill($item);
                        $row->is_default = $key == 0;
                        $row->contact()->associate($contact);
                        $row->save();
                    }
                }

                // Save contact's works if exist
                if (isset($param['company_info'])) {
                    $row = new Work();

                    foreach ($param['company_info'] as $key => $value) {
                        $row->{$key} = $value;
                    }

                    $row->contact()->associate($contact);
                    $row->save();
                }

                // Add contact to group
                // If user select custom group
                if ($selectedGroup){
                    $group = Group::find($selectedGroup);
                    if($group){
                        $contact->groups()->attach($group);
                    }
                }

                // If user not select custom group and has groups in file
                if (isset($param['groups']) && !$selectedGroup) {
                    foreach ($param['groups'] as $name) {
                        if(Str::endsWith($name, 'starred')){
                            $contact->is_favorite = true;
                            $contact->save();

                            continue;
                        }

                        $group = Group::byOwner()->where('name', $name)->first();
                        if(!$group){
                            $group = Group::create([
                                'name' => $name,
                                'user_id' => (string)Auth::user()->getAuthIdentifier()
                            ]);
                        }

                        $contact->groups()->attach($group);
                    }
                }

                // Save
                if (isset($param['photo'])) {
                    $file_check_data = Import::checkFileFormat($param['photo']);

                    if ($file_check_data) {
                        $info_send_rabbitmq_body[] = [
                            'entity_id' => $contact->id,
                            'url' => preg_replace('/[^[:print:]]+/', '', $param['photo'])
                        ];
                    }
                }

                $totalAdded++;
            }

            // Send to batch process contact;s avatars
            if (!empty($info_send_rabbitmq_body)) {
                $info_send_rabbitmq = [
                    'entity' => 'contact',
                    'user_id' => $user_id,
                    'avatars' => $info_send_rabbitmq_body
                ];

                PubSub::publish('SaveAvatars', $info_send_rabbitmq, config('settings.exchange_queue.files'));
            }

            // Return result
            return [
                'count' => $totalAdded
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public static function checkFileFormat($file): bool
    {
        $avatar = strtolower(substr($file, -3));
        $result_file = false;

        if ($avatar == 'jpg' || $avatar == 'gif' || $avatar == 'png' || $avatar == 'bmp' || $avatar == 'jpeg' || $avatar == 'tiff' || $avatar == 'webp') {
            $result_file = true;
        }

        return $result_file;
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
