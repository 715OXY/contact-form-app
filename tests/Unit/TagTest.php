<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1つのタグが中間テーブルを介して
     * 複数のお問い合わせに紐づいていること
     */
    public function test_tag_belongs_to_many_contacts(): void
    {
        // カテゴリを作成
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        // タグを作成
        $tag = Tag::create([
            'name' => '重要',
        ]);

        // お問い合わせを2件作成
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

        // 中間テーブルを介して同じタグを2件のContactに紐づける
        $tag->contacts()->attach([
            $contact1->id,
            $contact2->id,
        ]);

        // リレーションを再読み込み
        $tag->load('contacts');

        // 2件のお問い合わせが取得できること
        $this->assertCount(2, $tag->contacts);

        // 作成した2件が含まれていること
        $this->assertTrue(
            $tag->contacts->contains($contact1)
        );

        $this->assertTrue(
            $tag->contacts->contains($contact2)
        );

        // 中間テーブルにもレコードが存在すること
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact1->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact2->id,
            'tag_id' => $tag->id,
        ]);
    }
}
