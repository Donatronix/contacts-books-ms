<?php

namespace App\Services\Imports;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

class CSVGoogle
{
    /**
     * @var array
     */
    public array $data = [];

    /**
     * @var string
     */
    public string $file_format = 'csv';

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
     *
     * @return bool $data_result
     */
    public function define($data_array)
    {
        $data_result = false;
        $cnt = 1;
        foreach ($data_array as $k => $value) {
            foreach ($value as $key => $item) {
                if ($key == "Phone {$cnt} - Value") {
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

        foreach ($data_array as $k => $value) {
            $data_params = [
                'cnt_name_key' => 0,
                'cnt_email_type' => 1,
                'cnt_email_value' => 1,
                'cnt_email_key_type' => 0,
                'cnt_email_key_value' => 0,
                'cnt_phone_type' => 1,
                'cnt_phone_value' => 1,
                'cnt_phone_key_type' => 0,
                'cnt_phone_key_value' => 0,
                'cnt_relation_type' => 1,
                'cnt_relation_value' => 1,
                'cnt_relation_key_type' => 0,
                'cnt_relation_key_value' => 0,
                'cnt_sites_type' => 1,
                'cnt_sites_value' => 1,
                'cnt_sites_key_type' => 0,
                'cnt_sites_key_value' => 0,
                'cnt_company_info_key' => 1,
                'cnt_company_info_value' => 0,
                'cnt_chats_value' => 0,
                'cnt_chats_type' => 0,
                'cnt_address_key' => 1,
                'cnt_address_info' => 0
            ];

            foreach ($value as $key => $item) {
                if ($key == 'Given Name') {
                    $data_result[$k]['first_name'] = $item;

                    continue;
                }

                if ($key == 'Family Name') {
                    $data_result[$k]['last_name'] = $item;

                    continue;
                }

                if ($key == 'Additional Name') {
                    $data_result[$k]['middle_name'] = $item;

                    continue;
                }

                if ($key == 'Name Prefix') {
                    $data_result[$k]['prefix_name'] = $item;

                    continue;
                }

                if ($key == 'Name Suffix') {
                    $data_result[$k]['suffix_name'] = $item;

                    continue;
                }

                if ($key == 'Nickname') {
                    $data_result[$k]['nickname'] = $item;

                    continue;
                }

                if ($key == 'Birthday') {
                    $data_result[$k]['birthday'] = $item;

                    continue;
                }

                if ($key == 'Notes') {
                    $data_result[$k]['note'] = $item;

                    continue;
                }

                if ($key == 'Photo') {
                    $data_result[$k]['photo'] = $item;
                    continue;
                }

                if ($key == "E-mail {$data_params['cnt_email_value']} - Value") {
                    $data_result[$k]['email'][$data_params['cnt_email_key_value']]['value'] = $item;
                    $data_params['cnt_email_value']++;
                    $data_params['cnt_email_key_value']++;
                    continue;
                }

                if ($key == "E-mail {$data_params['cnt_email_type']} - Type") {
                    $data_result[$k]['email'][$data_params['cnt_email_key_type']]['type'] = $item;
                    $data_params['cnt_email_type']++;
                    $data_params['cnt_email_key_type']++;
                    continue;
                }

                if ($key == "Phone {$data_params['cnt_phone_value']} - Value") {
                    $data_result[$k]['phone'][$data_params['cnt_phone_key_value']]['value'] = $item;
                    $data_params['cnt_phone_value']++;
                    $data_params['cnt_phone_key_value']++;
                    continue;
                }

                if ($key == "Phone {$data_params['cnt_phone_type']} - Type") {
                    $data_result[$k]['phone'][$data_params['cnt_phone_key_type']]['type'] = $item;
                    $data_params['cnt_phone_type']++;
                    $data_params['cnt_phone_key_type']++;
                    continue;
                }

                if ($key == "Relation {$data_params['cnt_relation_value']} - Value") {
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_value']]['value'] = $item;
                    $data_params['cnt_relation_value']++;
                    $data_params['cnt_relation_key_value']++;
                    continue;
                }

                if ($key == "Relation {$data_params['cnt_relation_type']} - Type") {
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_type']]['type'] = $item;
                    $data_params['cnt_relation_type']++;
                    $data_params['cnt_relation_key_type']++;
                    continue;
                }

                if ($key == "Website {$data_params['cnt_sites_value']} - Value") {
                    $data_result[$k]['sites'][$data_params['cnt_sites_key_value']]['value'] = $item;
                    $data_params['cnt_sites_value']++;
                    $data_params['cnt_sites_key_value']++;
                    continue;
                }

                if ($key == "Website {$data_params['cnt_sites_type']} - Type") {
                    $data_result[$k]['sites'][$data_params['cnt_sites_key_type']]['type'] = $item;
                    $data_params['cnt_sites_type']++;
                    $data_params['cnt_sites_key_type']++;
                    continue;
                }

                if ($key == "Organization {$data_params['cnt_company_info_key']} - Name") {
                    $data_result[$k]['company_info']['company'] = $item;
                }

                if ($key == "Organization {$data_params['cnt_company_info_key']} - Title") {
                    $data_result[$k]['company_info']['post'] = $item;
                }

                if ($key == "Organization {$data_params['cnt_company_info_key']} - Department") {
                    $data_result[$k]['company_info']['department'] = $item;
                }

                if ($key == "Group Membership") {
                    $categories = explode(' ::: ', $item);
                    foreach ($categories as $category) {
                        $data_result[$k]['groups'][] = $category;
                    }
                }

                if ($key == "IM 1 - Service") {
                    $chats = explode(' ::: ', $item);
                    foreach ($chats as $chat) {
                        $data_result[$k]['chats'][$data_params['cnt_chats_type']]['type'] = $chat;
                        $data_params['cnt_chats_type']++;
                    }
                }

                if ($key == "IM 1 - Value") {
                    $chats = explode(' ::: ', $item);
                    foreach ($chats as $chat) {
                        $data_result[$k]['chats'][$data_params['cnt_chats_value']]['value'] = $chat;
                        $data_params['cnt_chats_value']++;
                    }
                }

                // TODO: does not work
                if ($value == "Address {$data_params['cnt_address_key']} - Country" || $value == "Address {$data_params['cnt_address_key']} - Postal Code" || $value == "Address {$data_params['cnt_address_key']} - Region" || $value == "Address {$data_params['cnt_address_key']} - City" || $value == "Address {$data_params['cnt_address_key']} - Street" || $value == "Address {$data_params['cnt_address_key']} - Extended Address" || $value == "Address {$data_params['cnt_address_key']} - PO Box") {
                    if ($value == "Address {$data_params['cnt_address_key']} - Country") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'country';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if ($key == "Address {$data_params['cnt_address_key']} - Postal Code") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'postcode';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if ($key == "Address {$data_params['cnt_address_key']} - Region") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'provinces';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if ($key == "Address {$data_params['cnt_address_key']} - City") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'city';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if ($key == "Address {$data_params['cnt_address_key']} - Street") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'address_string1';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if ($key == "Address {$data_params['cnt_address_key']} - Extended Address") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'address_string2';
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['value'] = $item;
                    }

                    if ($key == "Address {$data_params['cnt_address_key']} - PO Box") {
                        $data_result[$k]['address'][$data_params['cnt_address_info']]['type'] = 'po_box';
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
}
