<?php

namespace App\Services;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class CsvParser
{
    public function run(Request $request)
    {
        return view("tests.import");
    }

    public function test(Request $request)
    {
        $file = $request->file('contacts');
        $path_file = $file->getPathname();
        $this->load($path_file);
    }

    public function load($file)
    {
        $reader = new Csv();
        $spreadsheet = $reader->load($file);
        return $this->parse($spreadsheet);
    }

    public function parseByLetter($data_arr)
    {
        $array_letter = array_shift($data_arr);
        $data_arr = array_values($data_arr);

        foreach ($array_letter as $key => $item)
        {
            for($i=0; $i < count($data_arr); $i++)
            {
                if(isset($data_arr[$i][$key])){
                    $data = $array_letter[$key];
                    $data_result[$i][$data] = $data_arr[$i][$key];
                }
                else{
                    continue;
                }
            }
        }
        /*dump($data_result);
        die('END');*/

        return $this->getTransformationFromOutlookCSV($data_result);

//        return $this->getTranformationFromGoogleCSV($data_result);
    }

    /**
     *  Formats an array from unloading Outlook CSV
     *
     * @param $data_array
     */
    public function getTransformationFromOutlookCSV($data_array)
    {
        $data_result = [];

        foreach ($data_array as $k => $value)
        {
            $data_params = ['cnt_name_key' => 0, 'cnt_email_type' => 2, 'cnt_email_value' => 0, 'cnt_phone_key_value'
                => 0, 'cnt_relation_key_value' => 0, 'cnt_company_info_key' => 0, 'cnt_address_value' => 0];
            foreach ($value as $key => $item)
            {
                if($key == 'First Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'firstname';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Last Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'lastname';
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
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'prefix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Suffix'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'suffix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Birthday'){
                    $data_result[$k]['birthday'] = $item;
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
                }

                if($key == 'Notes'){
                    $data_result[$k]['note'] = $item;
                    continue;
                }
            }
        }
        dump($data_result);
        die('END');
    }

    /**
     *  Formats an array from unloading Google CSV
     *
     * @param $data_array
     */
    public function getTranformationFromGoogleCSV($data_array)
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
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'firstname';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Family Name'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'lastname';
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
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'prefix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Name Suffix'){
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['value'] = $item;
                    $data_result[$k]['name_param'][$data_params['cnt_name_key']]['type'] = 'suffix';
                    $data_params['cnt_name_key']++;
                    continue;
                }

                if($key == 'Nickname'){
                    $data_result[$k]['nickname'] = $item;
                    continue;
                }

                if($key == 'Nickname'){
                    $data_result[$k]['nickname'] = $item;
                    continue;
                }

                if($key == 'Birthday'){
                    $data_result[$k]['birthday'] = $item;
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

                if($key == "Address {$data_params['cnt_address_key']} - Country" || $key == "Address {$data_params['cnt_address_key']} - Postal Code" || $key == "Address {$data_params['cnt_address_key']} - Region" || $key == "Address {$data_params['cnt_address_key']} - City" || $key == "Address {$data_params['cnt_address_key']} - Street" || $key == "Address {$data_params['cnt_address_key']} - Extended Address" || $key == "Address {$data_params['cnt_address_key']} - PO Box"){
                    if($key == "Address {$data_params['cnt_address_key']} - Country"){
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

        dump($data_result);
        die('END');
    }

    public function parse($spreadsheet)
    {
        $objWorksheet = $spreadsheet->getActiveSheet();
        $worksheet = $spreadsheet->setActiveSheetIndex(0); // Выбираем первый лист

        $i = 0;
        $arrLevel = [];

        foreach ($worksheet->getRowDimensions() as $rowDimension) {
            $i++;
            // Determine the nesting level
            $arrLevel[$i]['level'] = $rowDimension->getOutlineLevel();
        }
        $worksheet->getHighestColumn();

        foreach ($objWorksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                // Combining nesting depth and data from columns and rows
                $arrLevel[$row->getRowIndex()][$cell->getColumn()] = $cell->getValue();
            }
        }
        return $this->parseByLetter($arrLevel);
    }

    /**
     *   Parses and converts a string to an array
     *
     * @param string $file_data
     * @return array $csv_arr
     */
    /*public function readData($file_data)
    {
        $file_mimes = array('application/x-csv', 'text/x-csv', 'text/csv', 'application/csv');



        $reader = new Csv();
        $spreadsheet = $reader->load($file_data);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        dd($spreadsheet);
        return true;
    }*/



    /**
     *   Parses and converts a string to an array
     *
     * @param string $file_data
     * @return array $csv_arr
     */
    /*public function readDataTmp($file_data)
    {
        $csv_data = [];
        $lines = explode(PHP_EOL, $file_data);
        foreach ($lines as $line) {
            $csv_arr[] = str_getcsv($line);
        }
        dump($file_data);
        dump($csv_arr);
        dd('END');
        return $csv_arr;
    }*/
}
