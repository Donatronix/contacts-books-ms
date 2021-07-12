<?php

namespace App\Services\Imports;

use App\Services\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\Address;
use App\Models\Work;
use App\Models\Site;
use App\Models\Relation;
use App\Models\Chat;
use App\Models\Group;
use PubSub;

class CSVOutlook
{
    public $data = [];
    public $file_format = 'csv';

    /**
     *  Parse the array into the desired format CSV Outlook.
     *
     * @param $data_array
     * @return bool $data_result
     */
    public function define($data_array)
    {
        $data_result = false;
        foreach ($data_array as $k => $value)
        {
            foreach ($value as $key => $item)
            {
                if($key == "Primary Phone" || $key == "Pager" || $key == 'Other Phone'){
                    $data_result = true;
                    break;
                }
            }
        }
        return $data_result;
    }

    /**
     *  Formats an array from unloading file
     *
     * @param $data_array
     */
    public function getTransformation($data_array)
    {
        $data_result = [];
        foreach ($data_array as $k => $value)
        {
            $data_params = ['cnt_name_key' => 0, 'cnt_email_type' => 2, 'cnt_email_value' => 0, 'cnt_phone_key_value'
            => 0, 'cnt_relation_key_value' => 0, 'cnt_company_info_key' => 0, 'cnt_address_value' => 0, 'cnt_chat_value' => 0];
            foreach ($value as $key => $item)
            {
                if($key == 'Notes')
                {
                    $data = explode("\n", $item);
                    foreach ($data as $chat_data)
                    {
                        if(strstr($chat_data, 'Чат')){
                            $chat = explode(": ", substr(trim($chat_data), 3));
                            $data_result[$k]['chat'][$data_params['cnt_chat_value']]['type'] = $chat[1];
                            $data_result[$k]['chat'][$data_params['cnt_chat_value']]['value'] = $chat[2];
                            $data_params['cnt_chat_value']++;
                        }
                    }
                    continue;
                }

                if($key == 'First Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'first_name';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Last Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'last_name';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Middle Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'surname';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Title'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'user_prefix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Suffix'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'user_suffix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Birthday'){
                    $birthday = strtotime(str_replace('/', '-', $item)) ?: NULL;
                    $data_result[$k]['birthday'] = $birthday == NULL ? NULL : date("Y-m-d", $birthday);
                    continue;
                }

                if($key == 'E-mail Address'){
                    $data_result[$k]['email'][$data_params['cnt_email_value']] = $item;
                    $data_params['cnt_email_value']++;
                    continue;
                }

                if($key == "E-mail {$data_params['cnt_email_type']} Address"){
                    $data_result[$k]['email'][$data_params['cnt_email_value']] = $item;
                    $data_params['cnt_email_value']++;
                    $data_params['cnt_email_type']++;
                    continue;
                }

                if($key == "Primary Phone" || $key == "Pager" || $key == 'Other Phone'){
                    $data_result[$k]['phone'][$data_params['cnt_phone_key_value']] = (string) trim(str_replace(' ', '', $item, ));
                    $data_params['cnt_phone_key_value']++;
                    continue;
                }

                if($key == "Spouse" || $key == "Children" || $key == 'Assistant\'s Name'){
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_value']]['type'] = strtolower($key);
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_value']]['value'] = $item;
                    $data_params['cnt_relation_key_value']++;
                    continue;
                }

                if($key == "Company" || $key == "Job Title" || $key == 'Department'){
                    $data_result[$k]['company_info'][$data_params['cnt_company_info_key']]['type'] = $key;
                    $data_result[$k]['company_info'][$data_params['cnt_company_info_key']]['value'] = $item;
                    $data_params['cnt_company_info_key']++;
                    continue;
                }

                if($key == "Categories"){
                    $categories = explode(';', $item);
                    foreach ($categories as $category){
                        $data_result[$k]['categories'][] = $category;
                    }
                    continue;
                }

                if($key == 'Notes'){
                    $data_result[$k]['note'] = $item;
                    continue;
                }
                if($key == 'Other Street' || $key == 'Other Address PO Box' || $key == 'Other City' || $key == 'Other Postal Code' || $key == 'Other Country')
                {
                    if($key == 'Other Country') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'country';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if($key == 'Other City') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'city';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if($key == 'Other Street') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'address_string1';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if($key == 'Other Address PO Box') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'post_office_box_number';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if($key == 'Other Postal Code') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'postcode';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    $data_params['cnt_address_value']++;
                }
            }
        }
        return $data_result ?? false;
    }

    /**
     *  Adding data from the downloaded Outlook structure file to the database and sending avatar information to the file microservice.
     *  There is no link to the avatar in the Outlоok structure!!!!
     *
     * @param $data_arr array
     * @return mixed
     */
    public function insertContactToBb($data_arr)
    {
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

                if($user_id){
                    $contact_info = ['table' => 'contacts', 'id' => $user_id];
                    $contact_info = Import::searchContact($contact_info);

                }
                $user->save();

            }
            $this->insertToOther($data_arr, $contact_info);

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

    /**
     *  Adding data to other tables for Outlook
     *
     * @param array $data_arr
     * @param string $data_contact
     *
     * @return mixed
     */
    public function insertToOther($data_arr, $contact_info)
    {
        $info_db = $contact_info[0];
        foreach ($data_arr as $k => $param)
        {
            if(isset($param['email']))
            {
                foreach ($param['email'] as $key => $item)
                {
                    $data = new ContactEmail();
                    if(!isset($param['email'][$key])){
                        continue;
                    }

                    $data->email = $param['email'][$key];
                    $data->contact_id = $info_db->id;
                    $data->save();
                }
            }

            if(isset($param['relation']))
            {

                foreach ($param['relation'] as $key => $item)
                {
                    $data = new Relation();
                    if(!isset($param['relation'][$key]['type'])){
                        continue;
                    }

                    $data->relation = $param['relation'][$key]['value'];
                    $data->relation_name = $param['relation'][$key]['type'];
                    $data->contact_id = $info_db->id;

                    $data->save();
                }
            }

            if(isset($param['phone']))
            {
                foreach ($param['phone'] as $key => $item)
                {
                    $data = new ContactPhone();
                    if(!isset($param['phone'][$key])){
                        continue;
                    }

                    $data->phone = $param['phone'][$key];
                    $data->contact_id = $info_db->id;
                    $data->save();
                }
            }

            if(isset($param['chat']))
            {

                foreach ($param['chat'] as $key => $item)
                {
                    $data = new Chat();
                    if(is_array($item)){
                        $data->chat = $param['chat'][$key]['value'];
                        $data->chat_name = $param['chat'][$key]['type'];
                    }
                    else{
                        $data->chat = $item;
                        $data->chat_name = $key;
                    }
                    $data->contact_id = $info_db->id;

                    $data->save();
                }
            }

            if(isset($param['address']))
            {

                foreach ($param['address'] as $item)
                {
                    $data = new Address();
//                    dump($item['type']);
                    $data->contact_id = $info_db->id;

                    if($item['type'] == 'country') {
                        $data->country = $item['value'];
                    }

                    if($item['type'] == 'postcode') {
                        $data->postcode = $item['value'];
                    }

                    if($item['type'] == 'provinces') {
                        $data->provinces = $item['value'];
                    }

                    if($item['type'] == 'city') {
                        $data->provinces = $item['value'];
                    }

                    if($item['type'] == 'post_office_box_number') {
                        $data->post_office_box_number = $item['value'];
                    }


                    if($item['type'] == 'address_string1' || $item['type'] == 'address_string2')
                    {
                        if($item['type'] == 'address_string1'){
                            $data_address_path1 = $item['value'];
                        }

                        if($item['type'] == 'address_string1'){
                            $data_address_path2 = $item['value'];
                        }

                        $data->address = $data_address_path1 . ', ' . $data_address_path2;
                    }
                    $data->save();

                }
            }

            if(isset($param['company_info']))
            {
                foreach ($param['company_info'] as $key => $item)
                {
                    $data = new Work();
                    $data->contact_id = $info_db->id;

                    if($item['type'] == 'Company'){
                        $data->company = $item['value'];
                    }

                    if($item['type'] == 'Department'){
                        $data->department = $item['value'];
                    }

                    if($item['type'] == 'Job Title'){
                        $data->post = $item['value'];
                    $data->save();
                    }
                }
            }

            if(isset($param['categories']))
            {
                $cnt = 0;
                foreach ($param['categories'] as $key =>  $item)
                {
                    $data = new Group();
                    if(isset($item)){
                        $data->user_id = $info_db->id;
                        $data->name = $param['categories'][$cnt];
                    $data->save();
                    }

                    $cnt++;
                }
            }
        }
    }
}
