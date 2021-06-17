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
        $this->test2($path_file);
    }

    public function parse($data_arr)
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
        dump($data_result);
        die('END');
    }

    public function test2($file)
    {
        $reader = new Csv();
        $spreadsheet = $reader->load($file);
        $objWorksheet = $spreadsheet->getActiveSheet();
        $worksheet = $spreadsheet->setActiveSheetIndex(0); // Выбираем первый лист

        $i = 0;
        $arrLevel = [];

        foreach ($worksheet->getRowDimensions() as $rowDimension) {
            $i++;
            // Determine the nesting level
            $arrLevel[$i]['level'] = $rowDimension->getOutlineLevel();
        }
        $highestColumn = $worksheet->getHighestColumn();

        foreach ($objWorksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                // Combining nesting depth and data from columns and rows
                $arrLevel[$row->getRowIndex()][$cell->getColumn()] = $cell->getValue();
            }
        }
//        dd($arrLevel);
        return $this->parse($arrLevel);
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
