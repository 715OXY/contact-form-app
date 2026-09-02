<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactStoreTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Tag $tag1;

    private Tag $tag2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->tag1 = Tag::create([
            'name' => '重要',
        ]);

        $this->tag2 = Tag::create([
            'name' => '確認',
        ]);
    }

    /**
     * 正常な入力でお問い合わせが作成され、
     * 201 Created が返ること
     */
    public function test_contact_can_be_created_and_returns_201(): void
    {
        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro.api@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストビル101',
            'category_id' => $this->category->id,
            'detail' => 'APIから作成したお問い合わせです。',
            'tag_ids' => [
                $this->tag1->id,
                $this->tag2->id,
            ],
        ];

        $response = $this->postJson(
            route('api.v1.contacts.store'),
            $data
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro.api@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストビル101',
            'category_id' => $this->category->id,
            'detail' => 'APIから作成したお問い合わせです。',
        ]);

        $contactId = $response->json('data.id');

        $this->assertNotNull($contactId);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactId,
            'tag_id' => $this->tag1->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactId,
            'tag_id' => $this->tag2->id,
        ]);

        $response->assertJsonPath(
            'data.first_name',
            '太郎'
        );

        $response->assertJsonPath(
            'data.last_name',
            '山田'
        );

        $response->assertJsonPath(
            'data.email',
            'taro.api@example.com'
        );

        $response->assertJsonPath(
            'data.category.id',
            $this->category->id
        );

        $response->assertJsonCount(
            2,
            'data.tags'
        );
    }

    /**
     * タグを指定しなくてもお問い合わせを作成できること
     */
    public function test_contact_can_be_created_without_tags(): void
    {
        $data = [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako.api@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => null,
            'category_id' => $this->category->id,
            'detail' => 'タグなしのお問い合わせです。',
        ];

        $response = $this->postJson(
            route('api.v1.contacts.store'),
            $data
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'hanako.api@example.com',
        ]);

        $contactId = $response->json('data.id');

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contactId,
        ]);

        $response->assertJsonCount(
            0,
            'data.tags'
        );
    }

    /**
     * 不正な入力では422が返り、
     * お問い合わせが登録されないこと
     */
    public function test_invalid_input_returns_422_and_contact_is_not_created(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '山田',
            'gender' => 9,
            'email' => 'invalid-email',
            'tel' => '090-1234-5678',
            'address' => '',
            'category_id' => 999999,
            'detail' => '',
            'tag_ids' => [
                999999,
            ],
        ];

        $response = $this->postJson(
            route('api.v1.contacts.store'),
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

        $this->assertDatabaseCount(
            'contacts',
            0
        );
    }
}