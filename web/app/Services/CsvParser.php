<?php

namespace App\Services;

use App\Services\Imports\CSVGoogle;
use App\Services\Imports\CSVOutlook;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class CsvParser
{
    /**
     * Loads a file, gets and returns its path.
     * We get the parsed array from Excel after we load the file of the required structure.
     *
     * From the Excel file format, we get an array, where the keys are the letters of the column.
     *
     * @param $file
     *
     * @return array|false
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function parse($file)
    {
        $path_file = $file->getPathname();

        $reader = new Csv();
        $spreadsheet = $reader->load($path_file);

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

    /**
     *  Get the array by letter, define the file structure and parse the results
     *
     * @param array $data_arr
     *
     * @return array $data_result | false
     */
    public function parseByLetter(array $data_arr): array
    {
        $array_letter = array_shift($data_arr);
        $data_arr = array_values($data_arr);

        $data_result = [];

        foreach ($array_letter as $key => $item) {
            for ($i = 0; $i < count($data_arr); $i++) {
                if (isset($data_arr[$i][$key])) {
                    $data_result[$i][$item] = $data_arr[$i][$key];
                } else {
                    continue;
                }
            }
        }

        $data_google = new CSVGoogle();
        $data_result_google = $data_google->define($data_result);

        $data_outlook = new CSVOutlook();
        $data_result_outlook = $data_outlook->define($data_result);

        if ($data_result_google) {
            $data_result = $data_google->getTransformation($data_result);
        }

        if ($data_result_outlook) {
            $data_result = $data_outlook->getTransformation($data_result);
        }

        return $data_result;
    }
}
