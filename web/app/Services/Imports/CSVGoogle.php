<?php

namespace App\Services\Imports;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CsvParser;

class CSVGoogle
{
    public $data = [];

    public function __construct($file_data=false)
    {
        $csv_object = new CsvParser();
        $this->data = $csv_object->readData($file_data);

        dd($this->data);
        return $this->data ?? false;
    }

    public function readData($file_data)
    {
        /*$googles = $this->parse_csv($file_data);
        dd($googles);*/
        /*$delimiter = ';';
        $rows = explode(PHP_EOL, $file_data);
        $data = [];
        foreach ($rows as $row)
        {
            $data[] = explode($delimiter, $row);
        }*/
        $csv_data = [];
        $lines = explode(PHP_EOL, $file_data);
        foreach ($lines as $line) {
            $csv_data[] = str_getcsv($line);
        }
        dd($csv_data);
//        print_r($data);
    }

    public function parse()
    {
        return false;
    }

    function parse_csv($str)
    {
        $str = preg_replace_callback('/([^"]*)("((""|[^"])*)"|$)/s',
            function ($matches) {
                $str = str_replace("\r", "\rR", $matches[3]);
                //$str = str_replace("\n", "\rN", $str);
                //$str = str_replace('""', "\rQ", $str);
                //$str = str_replace(',', "\rC", $str);

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
//        $googlecsv = $request['googleexport'];

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
