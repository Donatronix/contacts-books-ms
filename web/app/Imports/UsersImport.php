<?php


namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Contact;


class UsersImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            dump($row);
            /*Contact::create([
                'name' => $row[0],
            ]);*/
        }
    }
}
