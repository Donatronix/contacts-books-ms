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
        $vcard = new Vcard();
        $file_data_array = (new Vcard())->readData($file_data);
        $data = [];

        foreach ($file_data_array as $k => $item)
        {
            // field: FN (Full name)
            $data[$k]['full_name'] = $vcard->getFullname($item);

            // field: N (array of name parameters)
            $data[$k]['name_param'] = $vcard->getParamsName($item);

            // field: NICKNAME (pseudonym)
            $data[$k]['nickname'] = $vcard->getNickname($item);

            // field: EMAIL
            $data[$k]['email'] = $vcard->getEmail($item);

            // field: TEL (phone)
            $data[$k]['phone'] = $vcard->getPhone($item);

            // field: ADR (address)
            $data[$k]['address'] = $vcard->getAddress($item);

            // field: ORG (company_info)
            $data[$k]['company_info'] = $vcard->getCompanyInfo($item);

            $data[$k]['X-PHONETIC-FIRST-NAME'] = $this->checkParam($item['X-PHONETIC-FIRST-NAME'][0]['value'][0][0]);
            $data[$k]['X-PHONETIC-MIDDLE-NAME'] = $this->checkParam($item['X-PHONETIC-MIDDLE-NAME'][0]['value'][0][0]);
            $data[$k]['X-PHONETIC-LAST-NAME'] = $this->checkParam($item['X-PHONETIC-LAST-NAME'][0]['value'][0][0]);

//            $data[$k]['PHOTO'] = $this->checkParam($item['PHOTO'][0]['value'][0][0]);

//            $this->checkParam($item['X-AIM'][0]['value'][0][0]);
//            $data[$k]['X-AIM'] = $this->checkParam($item['X-AIM'][0]['value'][0][0]);

//            $data[$k]['X-SKYPE'] = $this->checkParam($item['X-SKYPE'][0]['value'][0][0]);

            $data[$k]['TITLE'] = $this->checkParam($item['TITLE'][0]['value'][0][0]);
            $data[$k]['BDAY'] = $this->checkParam($item['BDAY'][0]['value'][0][0]);
            $data[$k]['URL'] = $this->checkParam($item['URL'][0]['value'][0][0]);
//            $data[$k]['X-ABDATE'] = $this->checkParam($item['X-ABDATE'][0]['value'][0][0]);
            $data[$k]['X-ABRELATEDNAMES'] = $this->checkParam($item['X-ABRELATEDNAMES'][0]['value'][0][0]);
            $data[$k]['NOTE'] = $this->checkParam($item['NOTE'][0]['value'][0][0]);

            if($item['CATEGORIES'][0]['value'][0]){
                for($i=0; $i < count($item['CATEGORIES'][0]['value'][0]); $i++){
                    $data[$k]['CATEGORIES'][$i] = $item['CATEGORIES'][0]['value'][0][$i];
                }
            }

        }

        dump($data);
        dd($file_data_array);

    }

    private function checkParam($param)
    {
        if($param){
            return $param;
        }
        return false;
    }
}
