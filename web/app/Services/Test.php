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

            // field: ORG (company, department) + TITLE (post)
            $data[$k]['company_info'] = $vcard->getCompanyInfo($item);

            // field: BDAY (birthday)
            $data[$k]['birthday'] = $vcard->getBirthday($item);

            // field: URL (sites)
            $data[$k]['sites'] = $vcard->getSites($item);

            // field: X-ABRELATEDNAMES (relation)
            $data[$k]['relation'] = $vcard->getRelationInfo($item);

            // fields: X-GTALK + X-AIM + X-YAHOO + X-SKYPE + X-QQ + X-MSN + X-ICQ + X-JABBER
            $data[$k]['chats'] = $vcard->getChat($item);

            // field: NOTE
            $data[$k]['note'] = $vcard->getNote($item); // доработать

            // field: PHOTO
            $data[$k]['photo'] = $vcard->getAvatar($item);

            // field: CATEGORIES
            $data[$k]['categories'] = $vcard->getCategories($item);

            /*$data[$k]['X-PHONETIC-FIRST-NAME'] = $this->checkParam($item['X-PHONETIC-FIRST-NAME'][0]['value'][0][0]);
            $data[$k]['X-PHONETIC-MIDDLE-NAME'] = $this->checkParam($item['X-PHONETIC-MIDDLE-NAME'][0]['value'][0][0]);
            $data[$k]['X-PHONETIC-LAST-NAME'] = $this->checkParam($item['X-PHONETIC-LAST-NAME'][0]['value'][0][0]);*/
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
