<?php

namespace App\Services;


class CsvParser
{
    public function readData($file_data)
    {
        $csv_data = [];
        $lines = explode(PHP_EOL, $file_data);
        foreach ($lines as $line) {
            $csv_data[] = str_getcsv($line);
        }
        return $csv_data;
    }


}
