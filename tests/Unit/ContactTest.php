<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1つのお問い合わせが特定のカテゴリに属し、
     * 複数のタグとsyncできること
     */
    public function test_contact_belongs_to_category_and_can_sync_multiple_tags(): void
    {
        // カテゴリを作成
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        // タグを3件作成
        $tag1 = Tag::create([
            'name' => '重要',
        ]);

        $tag2 = Tag::create([
            'name' => '至急',
        ]);

        $tag3 = Tag::create([
            'name' => '確認',
        ]);

        // お問い合わせを作成
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストマンション101',
            'detail' => '商品のお届けについて確認したいです。',
        ]);

        // Contactが指定したCategoryに属していることを確認
        $this->assertTrue(
            $contact->category->is($category)
        );

        // 複数のタグをsync
        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
            $tag3->id,
        ]);

        // リレーションを再読み込み
        $contact->load('tags');

        // 3件のタグが紐付いていることを確認
        $this->assertCount(3, $contact->tags);

        // 指定したタグがすべて含まれていることを確認
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
        $this->assertTrue($contact->tags->contains($tag3));

        // 中間テーブルにもレコードが作成されていることを確認
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag3->id,
        ]);
    }
}
