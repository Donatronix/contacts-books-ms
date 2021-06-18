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
        return view("tests.import1");
    }

    public function test(Request $request, $file_data_array)
    {
        /*$request->validate([
            'contacts' => 'file'
        ]);*/

        /*$file = $request->file('contacts');
        $file_data = file_get_contents($file->getPathname());
        $data_object = new Vcard($file_data);
        $file_data_array = (new Vcard())->readData($file_data);*/
        $data = [];

        foreach ($file_data_array as $k => $item)
        {
            // field: FN (Full name)
            $data[$k]['full_name'] = $data_object->getFullname($item);

            // field: N (array of name parameters)
            $data[$k]['name_param'] = $data_object->getParamsName($item);

            // field: NICKNAME (pseudonym)
            $data[$k]['nickname'] = $data_object->getNickname($item);

            // field: EMAIL
            $data[$k]['email'] = $data_object->getEmail($item);

            // field: TEL (phone)
            $data[$k]['phone'] = $data_object->getPhone($item);

            // field: ADR (address)
            $data[$k]['address'] = $data_object->getAddress($item);

            // field: ORG (company, department) + TITLE (post)
            $data[$k]['company_info'] = $data_object->getCompanyInfo($item);

            // field: BDAY (birthday)
            $data[$k]['birthday'] = $data_object->getBirthday($item);

            // field: URL (sites)
            $data[$k]['sites'] = $data_object->getSites($item);

            // field: X-ABRELATEDNAMES (relation)
            $data[$k]['relation'] = $data_object->getRelationInfo($item);

            // fields: X-GTALK + X-AIM + X-YAHOO + X-SKYPE + X-QQ + X-MSN + X-ICQ + X-JABBER
            $data[$k]['chats'] = $data_object->getChat($item);

            // field: NOTE
            $data[$k]['note'] = $data_object->getNote($item); // доработать

            // field: PHOTO
            $data[$k]['photo'] = $data_object->getAvatar($item);

            // field: CATEGORIES
            $data[$k]['categories'] = $data_object->getCategories($item);
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
