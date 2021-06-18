<?php

namespace App\Services\Imports;

class CSVOutlook
{
    public $data = [];
    public $file_format = 'csv';

    public function __construct($file_data)
    {
        $this->data = $this->readData($file_data);
        return $this->data;
    }

    public function readData($file_data)
    {
        return false;
    }

    public function parse()
    {
        return false;
    }
}
