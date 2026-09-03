<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1つのカテゴリから紐づく複数のお問い合わせを取得できること
     */
    public function test_category_has_many_contacts(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $otherCategory = Category::create([
            'content' => '商品の交換について',
        ]);

        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => null,
            'detail' => '1件目のお問い合わせです。',
        ]);

        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区渋谷1-1-1',
            'building' => null,
            'detail' => '2件目のお問い合わせです。',
        ]);

        // 別カテゴリのお問い合わせ
        $otherContact = Contact::create([
            'category_id' => $otherCategory->id,
            'first_name' => '一郎',
            'last_name' => '鈴木',
            'gender' => 1,
            'email' => 'ichiro@example.com',
            'tel' => '07012345678',
            'address' => '東京都港区芝1-1-1',
            'building' => null,
            'detail' => '別カテゴリのお問い合わせです。',
        ]);

        $contacts = $category->contacts;

        // このカテゴリには2件だけ紐づいていること
        $this->assertCount(2, $contacts);

        // 自カテゴリのContactを取得できる
        $this->assertTrue($contacts->contains($contact1));
        $this->assertTrue($contacts->contains($contact2));

        // 他カテゴリのContactは取得されない
        $this->assertFalse($contacts->contains($otherContact));
    }
}
