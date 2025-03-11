<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'user_token' => Str::uuid(),
                'association' => 'TTPU',
                'uname' => '1',
                'password' => Hash::make('1'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'ОЛИЙ ТАЪЛИМ',
                'uname' => '2',
                'password' => Hash::make('2'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'КАСБИЙ ТАЪЛИМ',
                'uname' => '3',
                'password' => Hash::make('3'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'АКАДЕМИК ЛИЦЕЙ',
                'uname' => '4',
                'password' => Hash::make('4'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'ИЛМ, ФАН ВА ИННОВАЦИЯЛАР',
                'uname' => '5',
                'password' => Hash::make('5'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'ИНФРАТУЗИЛМА',
                'uname' => '6',
                'password' => Hash::make('6'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'user_token' => Str::uuid(),
                'association' => 'ЎҚУВ ЖАРАЁНИНИ ВА ТАЪЛИМ СИФАТИ',
                'uname' => '7',
                'password' => Hash::make('7'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'user_token' => Str::uuid(),
                'association' => 'ИЛМИЙ-ТАДҚИҚОТ ФАОЛИЯТИ ',
                'uname' => '8',
                'password' => Hash::make('8'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'user_token' => Str::uuid(),
                'association' => 'ХАЛҚАРО ИЛМИЙ-ТЕХНИК ҲАМКОРЛИК',
                'uname' => '9',
                'password' => Hash::make('9'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'МАЪНАВИЙ-МАЪРИФИЙ ИШЛАР',
                'uname' => '10',
                'password' => Hash::make('10'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'МАЛАКА ОШИРИШ',
                'uname' => '11',
                'password' => Hash::make('11'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_token' => Str::uuid(),
                'association' => 'ИЖРО ИНТИЗОМИ',
                'uname' => '12',
                'password' => Hash::make('12'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
