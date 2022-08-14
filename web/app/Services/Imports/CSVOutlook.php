<?php

namespace App\Services\Imports;

use App\Models\Address;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Email;
use App\Models\Phone;
use App\Models\Group;
use App\Models\Relation;
use App\Models\Work;
use App\Services\Import;
use Exception;
use Illuminate\Support\Facades\Auth;

class CSVOutlook
{
    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string
     */
    public $file_format = 'csv';

    /**
     *  Parse the array into the desired format CSV Outlook.
     *
     * @param $data_array
     *
     * @return bool $data_result
     */
    public function define($data_array)
    {
        $data_result = false;
        foreach ($data_array as $k => $value) {
            foreach ($value as $key => $item) {
                if ($key == "Primary Phone" || $key == "Pager" || $key == 'Other Phone') {
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
        foreach ($data_array as $k => $value) {
            $data_params = [
                'cnt_name_key' => 0,
                'cnt_email_type' => 2,
                'cnt_email_value' => 0,
                'cnt_phone_key_value' => 0,
                'cnt_relation_key_value' => 0,
                'cnt_company_info_key' => 0,
                'cnt_address_value' => 0,
                'cnt_chat_value' => 0
            ];

            foreach ($value as $key => $item) {
                if ($key == 'Notes') {
                    $data = explode("\n", $item);
                    foreach ($data as $chat_data) {
                        if (strstr($chat_data, 'Чат')) {
                            $chat = explode(": ", substr(trim($chat_data), 3));
                            $data_result[$k]['chat'][$data_params['cnt_chat_value']]['type'] = $chat[1];
                            $data_result[$k]['chat'][$data_params['cnt_chat_value']]['value'] = $chat[2];
                            $data_params['cnt_chat_value']++;
                        }
                    }
                    continue;
                }

                if ($key == 'First Name') {
                    $data_result[$k]['first_name'] = $item;

                    continue;
                }

                if ($key == 'Last Name') {
                    $data_result[$k]['last_name'] = $item;

                    continue;
                }

                if ($key == 'Middle Name') {
                    $data_result[$k]['middle_name'] = $item;

                    continue;
                }

                if ($key == 'Title') {
                    $data_result[$k]['prefix_name'] = $item;

                    continue;
                }

                if ($key == 'Suffix') {
                    $data_result[$k]['suffix_name'] = $item;

                    continue;
                }

                if ($key == 'Birthday') {
                    $data_result[$k]['birthday'] = $item;

                    continue;
                }

                if ($key == 'E-mail Address') {
                    $data_result[$k]['email'][$data_params['cnt_email_value']] = $item;
                    $data_params['cnt_email_value']++;
                    continue;
                }

                if ($key == "E-mail {$data_params['cnt_email_type']} Address") {
                    $data_result[$k]['email'][$data_params['cnt_email_value']] = $item;
                    $data_params['cnt_email_value']++;
                    $data_params['cnt_email_type']++;
                    continue;
                }

                if ($key == "Primary Phone" || $key == "Pager" || $key == 'Other Phone') {
                    $data_result[$k]['phone'][$data_params['cnt_phone_key_value']] = (string)trim(str_replace(' ', '', $item,));

                    $data_params['cnt_phone_key_value']++;
                    continue;
                }

                if ($key == "Spouse" || $key == "Children" || $key == 'Assistant\'s Name') {
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_value']]['type'] = strtolower($key);
                    $data_result[$k]['relation'][$data_params['cnt_relation_key_value']]['value'] = $item;
                    $data_params['cnt_relation_key_value']++;
                    continue;
                }

                if ($key == "Company" || $key == "Job Title" || $key == 'Department') {
                    $data_result[$k]['company_info'][$data_params['cnt_company_info_key']]['type'] = $key;
                    $data_result[$k]['company_info'][$data_params['cnt_company_info_key']]['value'] = $item;
                    $data_params['cnt_company_info_key']++;
                    continue;
                }

                if ($key == "Categories") {
                    $categories = explode(';', $item);
                    foreach ($categories as $category) {
                        $data_result[$k]['groups'][] = $category;
                    }

                    continue;
                }

                if ($key == 'Notes') {
                    $data_result[$k]['note'] = $item;

                    continue;
                }

                if ($key == 'Other Street' || $key == 'Other Address PO Box' || $key == 'Other City' || $key == 'Other Postal Code' || $key == 'Other Country') {
                    if ($key == 'Other Country') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'country';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if ($key == 'Other City') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'city';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if ($key == 'Other Street') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'address_string1';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if ($key == 'Other Address PO Box') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'po_box';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    if ($key == 'Other Postal Code') {
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['type'] = 'postcode';
                        $data_result[$k]['address'][$data_params['cnt_address_value']]['value'] = $item;
                    }

                    $data_params['cnt_address_value']++;
                }
            }
        }

        return $data_result ?? false;
    }
}
