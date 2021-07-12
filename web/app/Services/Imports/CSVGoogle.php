<?php

namespace App\Services\Imports;

use App\Models\Contact;
use App\Services\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CsvParser;

class CSVGoogle
{
    public $data = [];
    public $file_format = 'csv';


    public function readData($file_data)
    {
        $csv_data = [];
        $lines = explode(PHP_EOL, $file_data);
        foreach ($lines as $line) {
            $csv_data[] = str_getcsv($line);
        }
    }

    /**
     *  Parse the array into the desired format CSV Google.
     *
     * @param $data_array
     * @return bool $data_result
     */
    public function define($data_array)
    {
        $data_result = false;
        $cnt = 1;
        foreach ($data_array as $k => $value)
        {
            foreach ($value as $key => $item)
            {
                if($key == "Phone {$cnt} - Value"){
                    $data_result = true;
                    break;
                }
            }
            $cnt++;
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
            $data_params = ['cnt_name_key' => 0, 'cnt_email_type' => 1, 'cnt_email_value' => 1, 'cnt_email_key_type' =>
                0, 'cnt_email_key_value' => 0, 'cnt_phone_type' => 1, 'cnt_phone_value' => 1, 'cnt_phone_key_type' =>
                0, 'cnt_phone_key_value' => 0, 'cnt_relation_type' => 1, 'cnt_relation_value' => 1, 'cnt_relation_key_type' =>
                0, 'cnt_relation_key_value' => 0, 'cnt_sites_type' => 1, 'cnt_sites_value' => 1, 'cnt_sites_key_type' =>
                0, 'cnt_sites_key_value' => 0, 'cnt_company_info_key' => 1, 'cnt_company_info_value' => 0, 'cnt_chats_value' => 0, 'cnt_chats_type' => 0, 'cnt_address_key' => 1, 'cnt_address_info' => 0,];

            foreach ($value as $key => $item)
            {
                if($key == 'Name'){
                    $data_result[$k]['full_name'] = $item;
                    continue;
                }

                if($key == 'Given Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'first_name';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Family Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'last_name';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Additional Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'surname';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Name Prefix'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'user_prefix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Name Suffix'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'user_suffix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Nickname'){
                    $data_result[$k]['nickname'] = $item;
                    continue;
                }

                if($key == 'Birthday'){
                    $data_result[$k]['birthday'] = date("Y-m-d", strtotime($item));

                    continue;
                }

                if($key == 'Notes'){
                    $data_result[$k]['note'] = $item;
                    continue;
                }

                if($key == 'Photo'){
                    $data_result[$k]['photo'] = $item;
                    continue;
                }

                if($key == "E-mail {$data_params['cnt_email_value']} - Value"){
                    $data_result[$k]['email'][$data_params['cnt_email_key_value']]['value'] = $item;
                    $data_params['cnt_email_value']++;
                    $data_params['cnt_email_key_value']++;
                    continue;
                }

                if($key == "E-mail {$data_params['cnt_email_type']} - Type"){
                    $data_result[$k]['email'][$data_params['cnt_email_key_type']]['type'] = $item;
                    $data_params['cnt_email_type']++;
                    $data_params['cnt_email_key_type']++;
                    continue;
                }

                if($key == "Phone {$data_params['cnt_phone_value']} - Value"){
                    $data_result[$k]['phone'][$data_params['cnt_phone_key_value']]['value'] = $item;
                    $data_params['cnt_phone_value']++;
                    $data_params['cnt_phone_key_value']++;
                    continue;
                }

                if($key == "Phone {$data_params['cnt_phone_type']} - Type"){
                    $data_result[$k]['phone'][$data_params['cnt_phone_key_type']]['type'] = $item;
                    $data_params['cnt_phone_type']++;
                    $data_params['cnt_phone_key_type']++;
                    continue;
                }

                if($key == "Relation {$data_params['cnt_relation_value']} - Value"){
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_value']]['value'] = $item;
                    $data_params['cnt_relation_value']++;
                    $data_params['cnt_relation_key_value']++;
                    continue;
                }

                if($key == "Relation {$data_params['cnt_relation_type']} - Type"){
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_type']]['type'] = $item;
                    $data_params['cnt_relation_type']++;
                    $data_params['cnt_relation_key_type']++;
                    continue;
                }

                if($key == "Website {$data_params['cnt_sites_value']} - Value"){
                    $data_result[$k]['sites'][$data_params['cnt_sites_key_value']]['value'] = $item;
                    $data_params['cnt_sites_value']++;
                    $data_params['cnt_sites_key_value']++;
                    continue;
                }

                if($key == "Website {$data_params['cnt_sites_type']} - Type"){
                    $data_result[$k]['sites'][$data_params['cnt_sites_key_type']]['type'] = $item;
                    $data_params['cnt_sites_type']++;
                    $data_params['cnt_sites_key_type']++;
                    continue;
                }

                if($key == "Organization {$data_params['cnt_company_info_key']} - Name"){
                    $data_result[$k]['company_info']['company'] = $item;
                }

                if($key == "Organization {$data_params['cnt_company_info_key']} - Title"){
                    $data_result[$k]['company_info']['post'] = $item;
                }

                if($key == "Organization {$data_params['cnt_company_info_key']} - Department"){
                    $data_result[$k]['company_info']['department'] = $item;
                }

                if($key == "Group Membership")
                {
                    $categories = explode(' ::: ', $item);
                    foreach ($categories as $category){
                        $data_result[$k]['categories'][] = $category;
                    }
                }

                if($key == "IM 1 - Service")
                {
                    $chats = explode(' ::: ', $item);
                    foreach ($chats as $chat){
                        $data_result[$k]['chats'][$data_params['cnt_chats_type']]['type'] = $chat;
                        $data_params['cnt_chats_type']++;
                    }
                }

                if($key == "IM 1 - Value")
                {
                    $chats = explode(' ::: ', $item);
                    foreach ($chats as $chat){
                        $data_result[$k]['chats'][$data_params['cnt_chats_value']]['value'] = $chat;
                        $data_params['cnt_chats_value']++;
                    }
                }

                // TODO: does not work
                if($value == "Address {$data_params['cnt_address_key']} - Country" || $value == "Address {$data_params['cnt_address_key']} - Postal Code" || $value == "Address {$data_params['cnt_address_key']} - Region" || $value == "Address {$data_params['cnt_address_key']} - City" || $value == "Address {$data_params['cnt_address_key']} - Street" || $value == "Address {$data_params['cnt_address_key']} - Extended Address" || $value == "Address {$data_params['cnt_address_key']} - PO Box")
                {
                    echo 111;
                    if($value == "Address {$data_params['cnt_address_key']} - Country"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'country';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if($key == "Address {$data_params['cnt_address_key']} - Postal Code"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'postcode';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if($key == "Address {$data_params['cnt_address_key']} - Region"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'provinces';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if($key == "Address {$data_params['cnt_address_key']} - City"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'city';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if($key == "Address {$data_params['cnt_address_key']} - Street"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'address_string1';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if($key == "Address {$data_params['cnt_address_key']} - Extended Address"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'address_string2';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if($key == "Address {$data_params['cnt_address_key']} - PO Box"){
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'post_office_box_number';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    $data_params['cnt_address_key']++;
                    $data_params['cnt_address_info']++;
                }
            }
            $data_params['cnt_company_info_key']++;
        }

        return $data_result ?? false;
    }

    function parse_csv($str)
    {
        $str = preg_replace_callback('/([^"]*)("((""|[^"])*)"|$)/s',
            function ($matches) {
                $str = str_replace("\r", "\rR", $matches[3]);

                return preg_replace('/\r\n?/', "\n", $matches[1]) . $str;
            },
            $str);
        dd($str);
        $str = preg_replace('/\n$/', '', $str);

        return array_map(
            function ($line) {
                return array_map(
                    function ($field) {
                        $field = str_replace("\rC", ',', $field);
                        $field = str_replace("\rQ", '"', $field);
                        $field = str_replace("\rN", "\n", $field);
                        $field = str_replace("\rR", "\r", $field);

                        return $field;
                    },
                    explode(',', $line));
            }, explode("\n", $str)
        );
    }

    public function readDataTmp($file_data)
    {
        $user_id = (int)Auth::user()->getAuthIdentifier();

        $googles = $this->parse_csv($file_data);
        dd($googles);

        $header = array_shift($googles);
        $header[] = "tmp";

        $gcontacts = [];
        foreach ($googles as $s) {
            $gcontacts[] = array_combine($header, $s);
        }

        $contacts = [];
        try {
            foreach ($gcontacts as $c) {
                $contact = Contact::create([
                    'user_id' => $user_id,
                    'first_name' => $c['First Name'],
                    'last_name' => $c['Last Name'],
                    'middlename' => $c['Middle Name'],
                    'prefix' => $c['Title'],
                    'suffix' => $c['Suffix'],
                    'nickname' => '',
                    'adrextend' => $c['Home Address PO Box'],
                    'adrstreet' => $c['Home Street'] . "\n" . $c['Home Street2'] . "\n" . $c['Home Street3'],
                    'adrcity' => $c['Home City'],
                    'adrstate' => $c['Home State'],
                    'adrzip' => $c['Home Postal Code'],
                    'adrcountry' => $c['Home Country'],
                    // 'tel1' => $c['Other Phone'] ?? $c['Primary Phone'] ?? $c['Home Phone'] ?? $c['Home Phone 2'] ?? $c['Mobile Phone'],
                    // 'email' => $c['E-mail Address']
                ]);
                $contact->save();
                $contacts[] = $contact;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }

        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);
    }

}
