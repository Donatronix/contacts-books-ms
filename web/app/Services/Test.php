<?php


namespace App\Services;

use PubSub;
use Exception;
use App\Services\Imports\Vcard;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class Test
{
    public function run(Request $request)
    {
        return view("tests.import");
    }

    public function test(Request $request)
    {
        /*$request->validate([
            'contacts' => 'file'
        ]);*/

        $file = $request->file('contacts');
        $file_data = file_get_contents($file->getPathname());
        $file_data_array = (new Vcard())->readData($file_data);
        $data = [];

        //dump($file_data_array[0]["FN"][0]["value"][0][0]);

        foreach ($file_data_array as $k => $item)
        {
            $data[$k]['full_name'] = $item["FN"][0]["value"][0][0];

            for($i=0; $i < count($item["N"][0]['value']); $i++){
                $data[$k]['N'][$i] = $item['N'][0]['value'][$i][0];
            }

            $data[$k]['NICKNAME'] = $item['NICKNAME'][0]['value'][0][0];
            $data[$k]['X-PHONETIC-FIRST-NAME'] = $item['X-PHONETIC-FIRST-NAME'][0]['value'][0][0];
            $data[$k]['X-PHONETIC-MIDDLE-NAME'] = $item['X-PHONETIC-MIDDLE-NAME'][0]['value'][0][0];
            $data[$k]['X-PHONETIC-LAST-NAME'] = $item['X-PHONETIC-LAST-NAME'][0]['value'][0][0];

            for($i=0; $i < count($item['EMAIL']); $i++){
                $data[$k]['EMAIL'][$i] = $item['EMAIL'][$i]['value'][0][0];
            }

            //$data[$k]['X-AIM'] = $item['X-AIM'];
//            $data[$k]['X-AIM'] = $item['X-AIM'][0]['value'][0][0];

//            $data[$k]['X-SKYPE'] = $item['X-SKYPE'][0]['value'][0][0];


            for($i=0; $i < count($item['TEL']); $i++){
                $data[$k]['TEL'][$i] = $item['TEL'][$i]['value'][0][0];
            }

            for($i=0; $i < count($item['ADR']); $i++){
                for($j=0; $j < count($item['ADR'][$i]['value']);$j++){
                    $data[$k]['ADR'][$i][$j] = $item['ADR'][$i]['value'][$j][0];
                }
            }

            for($i=0; $i < count($item['ORG'][0]['value']); $i++){
                $data[$k]['ORG'][$i] = $item['ORG'][0]['value'][$i][0];
            }

            $data[$k]['TITLE'] = $item['TITLE'][0]['value'][0][0];
            $data[$k]['BDAY'] = $item['BDAY'][0]['value'][0][0];
            $data[$k]['URL'] = $item['URL'][0]['value'][0][0];
            $data[$k]['X-ABDATE'] = $item['X-ABDATE'][0]['value'][0][0];
            $data[$k]['X-ABRELATEDNAMES'] = $item['X-ABRELATEDNAMES'][0]['value'][0][0];
            $data[$k]['NOTE'] = $item['NOTE'][0]['value'][0][0];
            $data[$k]['PHOTO'] = $item['PHOTO'][0]['value'][0][0];

            for($i=0; $i < count($item['CATEGORIES'][0]['value'][0]); $i++){
                $data[$k]['CATEGORIES'][$i] = $item['CATEGORIES'][0]['value'][0][$i];
            }

        }

        dd($data);
//        dd($file_data_array);

    }

    private function checkParam($param)
    {
        if($param){
            return $param;
        }
        return false;
    }
}
