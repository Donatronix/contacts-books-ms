<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $list = [
            'Email Platforms' => [
                'Gmail',
                'HubSpot',
                'Sendinblue',
                'ProtonMail',
                'Outlook',
                'Yahoo! Mail',
                'Zoho Mail',
                'AOL Mail',
                'Mail.com',
                'GMX Mail',
                'iCloud Mail',
                'Yandex. Mail',
                'Tutanota'
            ],
            'Social Media platforms' => [
                'Facebook',
                'YouTube',
                'WhatsApp',
                'Weixin/WeChat',
                'Instagram',
                'Tencent QQ',
                'Tumblr',
                'Qzone',
                'Tik Tok',
                'Twitter',
                'Reddit',
                'Baidu Tieba',
                'LinkedIn',
                'Pinterest',
                'Medium',
                'Vimeo',
                'Twitch'
            ],
            'Messenger platforms' => [
                'Facebook Messenger',
                'Viber',
                'Telegram',
                'Snapchat',
                'Line',
                'Skype',
                'Discord',
                'Signal'
            ]
        ];

        foreach ($list as $name => $children){
            $category = Category::factory()->create([
                'name' => $name
            ]);

            foreach($children as $child){
                Category::factory()->create([
                    'name' => $child,
                    'parent_id' => $category->id
                ]);
            }
        }
    }
}
