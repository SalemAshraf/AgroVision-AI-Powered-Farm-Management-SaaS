<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class OrderSeeder extends Seeder
{
    public function run()
    {
        DB::table('orders')->insert([
            [
                'user_id' => 1,
                'due_date' => '2020-06-01',
                'client_name' => 'Higspeed Studios',
                'client_contact' => 'highspeed@mail.com',
                'amount' => 650036.34,
                'status' => 'Completed',
            ],
            [
                'user_id' => 1,
                'due_date' => '2020-06-01',
                'client_name' => 'Wedepeloper',
                'client_contact' => 'wedepeloper@mail.com',
                'amount' => 1672.45,
                'status' => 'Completed',
            ],
            [
                'user_id' => 2,
                'due_date' => '2020-06-01',
                'client_name' => 'Jean Graphic Inc.',
                'client_contact' => 'jeangraphic@mail.com',
                'amount' => 2456221.55,
                'status' => 'Pending',
            ],
            // أضف باقي البيانات هنا...
        ]);
    }
}
