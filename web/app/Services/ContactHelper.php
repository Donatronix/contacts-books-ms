<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Email;
use App\Models\Group;
use App\Models\Phone;
use App\Models\Relation;
use App\Models\Site;
use App\Models\Work;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PubSub;

class ContactHelper
{
    /**
     * Save Contact Data
     *
     * @param array $inputData
     *
     * @throws \Exception
     */
    public static function save(array $inputData): array
    {
        // Validate input
        Validator::make($inputData, Contact::rules());

//           dd($inputData);

        // Read selected custom group
        $selectedGroup = request()->get('group_id', null);

        try {
            // First, Create contact
            $contact = new Contact();
            $contact->fill($inputData);
            $contact->write_as_name = $inputData['display_name'] ?? '';

            if (isset($inputData['birthday']) && !empty($inputData['birthday'])) {
                $contact->birthday = Carbon::parse($inputData['birthday']);
            }

            $contact->user_id = Auth::user()->getAuthIdentifier();
            $contact->save();

            // Save contact's phones
            if (isset($inputData['phones'])) {
                $count = sizeof($inputData['phones']);

                foreach ($inputData['phones'] as $key => $phone) {
                    if (empty($phone)) {
                        continue;
                    }

                    if (is_string($phone)) {
                        $phone = [
                            'value' => $phone
                        ];
                    }

                    $phone['value'] = str_replace(['(', ')', ' ', '-', '+'], '', $phone['value']);
                    $phone['type'] = $phone['type'] ?? 'other';

                    $row = new Phone();
                    $row->fill($phone);
                    $row->is_default = $count == 1 || $key == 0;
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's emails
            if (isset($inputData['emails'])) {
                $count = sizeof($inputData['emails']);

                foreach ($inputData['emails'] as $key => $email) {
                    if (empty($email)) {
                        continue;
                    }

                    if (is_string($email)) {
                        $email = [
                            'value' => $email
                        ];
                    }

                    $email['type'] = $email['type'] ?? 'other';

                    $row = new Email();
                    $row->fill($email);
                    $row->is_default = $count == 1 || $key == 0;
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's chats if exist
            if (isset($inputData['chats'])) {
                $count = sizeof($inputData['chats']);

                foreach ($inputData['chats'] as $key => $chat) {
                    if (empty($chat)) {
                        continue;
                    }

                    if (is_string($chat)) {
                        $chat = [
                            'value' => $chat
                        ];
                    }

                    $chat['type'] = $chat['type'] ?? 'other';

                    $row = new Chat();
                    $row->fill($chat);
                    $row->is_default = $count == 1 || $key == 0;
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's addresses if exist
            if (isset($inputData['addresses'])) {
                foreach ($inputData['addresses'] as $address) {
                    if (empty($address)) {
                        continue;
                    }

                    if (is_string($address)) {
                        $address = [
                            'value' => $address
                        ];
                    }

                    $site['type'] = $site['type'] ?? 'other';

                    $row = new Address();
                    $row->fill($address);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's sites if exist
            if (isset($inputData['sites'])) {
                foreach ($inputData['sites'] as $site) {
                    if (empty($site)) {
                        continue;
                    }

                    if (is_string($site)) {
                        $site = [
                            'value' => $site
                        ];
                    }

                    $site['type'] = $site['type'] ?? 'other';

                    $row = new Site();
                    $row->fill($site);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's works if exist
            if (isset($inputData['works'])) {
                foreach ($inputData['works'] as $work) {
                    if (empty($work)) {
                        continue;
                    }

                    $row = new Work();

                    if (is_array($work)) {
                        $row->fill($work);
                    }

                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Save contact's works if exist
            if (isset($inputData['company_info'])) {
                $row = new Work();

                foreach ($inputData['company_info'] as $key => $value) {
                    $row->{$key} = $value;
                }

                $row->contact()->associate($contact);
                $row->save();
            }

            // Save contact's relations if exist
            if (isset($inputData['relations'])) {
                foreach ($inputData['relations'] as $relation) {
                    if (empty($relation)) {
                        continue;
                    }

                    if (is_string($relation)) {
                        $relation = [
                            'value' => $relation
                        ];
                    }

                    $relation['type'] = $relation['type'] ?? 'other';

                    // Save data
                    $row = new Relation();
                    $row->fill($relation);
                    $row->contact()->associate($contact);
                    $row->save();
                }
            }

            // Add contact to group
            // If user select custom group
            if ($selectedGroup) {
                $group = Group::find($selectedGroup);
                if ($group) {
                    $contact->groups()->attach($group);
                }
            }

            // If user not select custom group and has groups in file
            if (isset($inputData['groups']) && !$selectedGroup) {
                foreach ($inputData['groups'] as $name) {
                    if (Str::endsWith($name, 'starred')) {
                        $contact->is_favorite = true;
                        $contact->save();

                        continue;
                    }

                    $group = Group::byOwner()->where('name', $name)->first();
                    if (!$group) {
                        $group = Group::create([
                            'name' => $name,
                            'user_id' => (string)Auth::user()->getAuthIdentifier()
                        ]);
                    }

                    $contact->groups()->attach($group);
                }
            }

            // Save
            $avatar = null;
            if (isset($inputData['photo'])) {
                $file_check_data = ContactHelper::checkFileFormat($inputData['photo']);

                if ($file_check_data) {
                    $avatar = [
                        'entity_id' => $contact->id,
                        'url' => preg_replace('/[^[:print:]]+/', '', $inputData['photo'])
                    ];
                }
            }

//            dd($contact->load([
//                'phones',
//                'emails',
//                'groups',
//                'works',
//                'addresses',
//                'sites',
//                'chats',
//                'relations'
//            ]));

            // Return response
            return [
                'contact' => $contact,
                'avatar' => $avatar
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public static function checkFileFormat($file): bool
    {
        $avatar = strtolower(substr($file, -3));
        $result_file = false;

        if ($avatar == 'jpg' || $avatar == 'gif' || $avatar == 'png' || $avatar == 'bmp' || $avatar == 'jpeg' || $avatar == 'tiff' || $avatar == 'webp') {
            $result_file = true;
        }

        return $result_file;
    }

    public static function saveAvatars(array $avatars): bool
    {
        if (empty($avatars)) {
            return false;
        }

        // Send to batch process contact;s avatars
        $info_send_rabbitmq = [
            'entity' => 'contact',
            'user_id' => (string)Auth::user()->getAuthIdentifier(),
            'avatars' => $avatars
        ];

        return PubSub::publish('SaveAvatars', $info_send_rabbitmq, config('settings.exchange_queue.files'));
    }
}
