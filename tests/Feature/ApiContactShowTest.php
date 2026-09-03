<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactShowTest extends TestCase
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
     * 実在するお問い合わせIDで
     * JSON形式の詳細が返ること
     */
    public function test_contact_show_returns_contact_detail_as_json(): void
    {
        $contact = Contact::forceCreate([
            'category_id' => $this->category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストビル101',
            'detail' => 'お問い合わせ詳細APIのテストです。',
            'created_at' => '2026-08-30 10:00:00',
            'updated_at' => '2026-08-30 10:00:00',
        ]);

        $contact->tags()->attach([
            $this->tag1->id,
            $this->tag2->id,
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.show', $contact)
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'building',
                'detail',
                'category' => [
                    'id',
                    'content',
                ],
                'tags' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath(
            'data.id',
            $contact->id
        );

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
            'taro@example.com'
        );

        $response->assertJsonPath(
            'data.category.id',
            $this->category->id
        );

        $response->assertJsonPath(
            'data.category.content',
            '商品のお届けについて'
        );

        $response->assertJsonCount(
            2,
            'data.tags'
        );

        $response->assertJsonFragment([
            'name' => '重要',
        ]);

        $response->assertJsonFragment([
            'name' => '確認',
        ]);
    }

    /**
     * 存在しないお問い合わせIDでは
     * 404エラーJSONが返ること
     */
    public function test_nonexistent_contact_returns_404_json(): void
    {
        $response = $this->getJson(
            route('api.v1.contacts.show', 999999)
        );

        $response->assertStatus(404);

        $response->assertExactJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }
}
