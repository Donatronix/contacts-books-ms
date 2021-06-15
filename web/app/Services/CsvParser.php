<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;


class CsvParser
{
    /**
     *   Parses and converts a string to an array
     *
     * @param string $file_data
     * @return array $csv_arr
     */
    public function readData($file_data)
    {
        Excel::import(new UsersImport, $file_data);
//        Excel::import(new UsersImport, 'users.xlsx');
    }
    /**
     *   Parses and converts a string to an array
     *
     * @param string $file_data
     * @return array $csv_arr
     */
    public function readDataTmp($file_data)
    {
        $csv_data = [];
        //$file_data = str_replace(',', "", $file_data);
        $lines = explode(PHP_EOL, $file_data);
        foreach ($lines as $line) {
            $csv_arr[] = str_getcsv($line);
        }
//        $csv_clean_data = $this->cleanCsv($csv_arr);
        dump($file_data);
        dump($csv_arr);
        dd('END');
        return $csv_arr;
    }
}
