<?php

namespace App\Services\Imports;

use App\Services\Import;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;

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
                    $data_result[$k]['birthday'] = date("Y-m-d", strtotime($item));;
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
