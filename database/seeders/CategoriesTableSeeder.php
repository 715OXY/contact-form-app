<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'content' => '商品のお届けについて',
            ],
            [
                'content' => '商品の交換について',
            ],
            [
                'content' => '商品トラブル',
            ],
            [
                'content' => 'ショップへのお問い合わせ',
            ],
            [
                'content' => 'その他',
            ],
        ]);
    }
}
