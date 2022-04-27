<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        DB::table('managers')->insert(
            [
                [
                    'name'     => '爱着雄雄的小华华',
                    'phone'    => '12345678',
                    'password' => Hash::make('12345678'),
                    'department' => '华哥yyds',
                    'level'   => 0,
                    'api_token' => Str::random(60),
                ]
            ]);

    }
}
