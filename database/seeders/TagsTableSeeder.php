<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tags')->insert([
            [
                'name' => '質問',
            ],
            [
                'name' => '要望',
            ],
            [
                'name' => '不具合報告',
            ],
            [
                'name' => 'ご意見',
            ],
            [
                'name' => 'その他',
            ],
        ]);
    }
}
