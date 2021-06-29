<?php

namespace App\Services;

use App\Services\Imports\CSVGoogle;
use App\Services\Imports\CSVOutlook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class CsvParser
{
    /**
     *  Loads a file, gets and returns its path.
     *
     * @param $request
     * @return array $path_file
     */
    public function run($request)
    {
        $file = $request->file('contacts');
        $path_file = $file->getPathname();
        $this->load($path_file);
    }

    /**
     *  We get the parsed array from Excel after we load the file of the required structure.
     *
     * @param $path_file
     * @return array|false
     */
    public function load($path_file)
    {
        $reader = new Csv();
        $spreadsheet = $reader->load($path_file);
        return $this->parse($spreadsheet);
    }

    /**
     *  Get the array by letter, define the file structure and parse the results
     *
     * @param array $data_arr
     * @return array $data_result | false
     */
    public function parseByLetter($data_arr)
    {
        $array_letter = array_shift($data_arr);
        $data_arr = array_values($data_arr);
        $data_result = [];
        $import = new Import();

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
        $data_google = new CSVGoogle();
        $data_result_google = $data_google->define($data_result);
        $data_outlook = new CSVOutlook();
        $data_result_outlook = $data_outlook->define($data_result);

        if($data_result_google){

            $data_result = $data_google->getTransformation($data_result);
            $import->insertContactToBb($data_result);
        }

        if($data_result_outlook){
            $data_result = $data_outlook->getTransformation($data_result);
            $data_outlook->insertContactToBb($data_result);
        }
    }

    /**
     *  From the Excel file format, we get an array, where the keys are the letters of the column.
     *
     * @param array | false
     */
    public function parse($spreadsheet)
    {
        $objWorksheet = $spreadsheet->getActiveSheet();
        // Selecting the first sheet
        $worksheet = $spreadsheet->setActiveSheetIndex(0);

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
        return $this->parseByLetter($arrLevel) ?? false;
    }
}
