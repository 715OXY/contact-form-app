<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Category $category1;

    private Category $category2;

    private Tag $tag1;

    private Tag $tag2;

    private Tag $tag3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->category2 = Category::create([
            'content' => '商品の交換について',
        ]);

        $this->tag1 = Tag::create([
            'name' => '重要',
        ]);

        $this->tag2 = Tag::create([
            'name' => '確認',
        ]);

        $this->tag3 = Tag::create([
            'name' => '対応済み',
        ]);
    }

    /**
     * 正常な入力でお問い合わせが更新され、
     * 200 OK が返ること
     */
    public function test_contact_can_be_updated_and_returns_200(): void
    {
        $contact = $this->createContact();

        $contact->tags()->attach([
            $this->tag1->id,
            $this->tag2->id,
        ]);

        $data = [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'updated@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '更新ビル202',
            'category_id' => $this->category2->id,
            'detail' => '更新後のお問い合わせ内容です。',
            'tag_ids' => [
                $this->tag2->id,
                $this->tag3->id,
            ],
        ];

        $response = $this->putJson(
            route('api.v1.contacts.update', $contact),
            $data
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'updated@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '更新ビル202',
            'category_id' => $this->category2->id,
            'detail' => '更新後のお問い合わせ内容です。',
        ]);

        // tag1 は解除される
        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $this->tag1->id,
        ]);

        // tag2 は残る
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $this->tag2->id,
        ]);

        // tag3 は新たに追加される
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $this->tag3->id,
        ]);

        $response->assertJsonPath(
            'data.id',
            $contact->id
        );

        $response->assertJsonPath(
            'data.first_name',
            '花子'
        );

        $response->assertJsonPath(
            'data.email',
            'updated@example.com'
        );

        $response->assertJsonPath(
            'data.category.id',
            $this->category2->id
        );

        $response->assertJsonCount(
            2,
            'data.tags'
        );
    }

    /**
     * 存在しないお問い合わせIDでは
     * 404エラーJSONが返ること
     */
    public function test_nonexistent_contact_returns_404_json(): void
    {
        $data = [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'updated@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => null,
            'category_id' => $this->category1->id,
            'detail' => '更新テストです。',
            'tag_ids' => [
                $this->tag1->id,
            ],
        ];

        $response = $this->putJson(
            route('api.v1.contacts.update', 999999),
            $data
        );

        $response->assertStatus(404);

        $response->assertExactJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    /**
     * 不正な入力では422が返り、
     * お問い合わせが更新されないこと
     */
    public function test_invalid_input_returns_422_and_contact_is_not_updated(): void
    {
        $contact = $this->createContact();

        $contact->tags()->attach([
            $this->tag1->id,
        ]);

        $data = [
            'first_name' => '',
            'last_name' => '佐藤',
            'gender' => 9,
            'email' => 'invalid-email',
            'tel' => '080-1234-5678',
            'address' => '',
            'category_id' => 999999,
            'detail' => '',
            'tag_ids' => [
                999999,
            ],
        ];

        $response = $this->putJson(
            route('api.v1.contacts.update', $contact),
            $data
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
            'tag_ids.0',
        ]);

        // 元データが変更されていないこと
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'original@example.com',
            'category_id' => $this->category1->id,
        ]);

        // 元のタグ関連も維持されていること
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $this->tag1->id,
        ]);
    }

    /**
     * テスト用Contactを作成
     */
    private function createContact(array $attributes = []): Contact
    {
        return Contact::forceCreate(array_merge([
            'category_id' => $this->category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'original@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => '旧ビル101',
            'detail' => '更新前のお問い合わせ内容です。',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ], $attributes));
    }
}
