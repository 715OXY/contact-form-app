<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 日本語ロケールのFakerインスタンスを作成
        $faker = Faker::create('ja_JP');

        // 既存カテゴリーとタグのIDを取得
        $categoryIds = Category::pluck('id');
        $tagIds = Tag::pluck('id');

        // 20件のダミーデータを作成
        for ($i = 0; $i < 20; $i++) {
            $contact = Contact::create([
                'category_id' => $categoryIds->random(),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'gender' => $faker->numberBetween(1, 3),
                'email' => $faker->unique()->safeEmail(),
                'tel' => '0'.$faker->numerify('##########'),
                'address' => $faker->prefecture()
                    .$faker->city()
                    .$faker->streetAddress(),
                'building' => $faker->optional()->secondaryAddress(),
                'detail' => $faker->realText(100),
            ]);
            // 1〜3件のタグをランダムに選択
            $randomTagIds = $tagIds
                ->shuffle()
                ->take($faker->numberBetween(1, 3))
                ->values()
                ->all();

            // contact_tag 中間テーブルに登録
            $contact->tags()->attach($randomTagIds);
        }
    }
}
