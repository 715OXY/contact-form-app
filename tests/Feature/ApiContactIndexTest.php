<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactIndexTest extends TestCase
{
    use RefreshDatabase;

    private Category $category1;
    private Category $category2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->category2 = Category::create([
            'content' => '商品の交換について',
        ]);
    }

    /**
     * GET /api/v1/contacts でJSON形式の一覧が返ること
     */
    public function test_contacts_index_returns_json_list(): void
    {
        $contact1 = $this->createContact([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        $contact2 = $this->createContact([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'hanako@example.com',
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.index')
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $response->assertJsonFragment([
            'id' => $contact1->id,
            'email' => 'taro@example.com',
        ]);

        $response->assertJsonFragment([
            'id' => $contact2->id,
            'email' => 'hanako@example.com',
        ]);
    }

    /**
     * keywordで姓・名・メールアドレスを部分一致検索できること
     */
    public function test_contacts_can_be_filtered_by_keyword(): void
    {
        $target = $this->createContact([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'target@example.com',
        ]);

        $other = $this->createContact([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'other@example.com',
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'keyword' => '山田',
            ])
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.id',
            $target->id
        );

        $response->assertJsonPath(
            'data.0.email',
            'target@example.com'
        );
    }

    /**
     * genderで絞り込みできること
     */
    public function test_contacts_can_be_filtered_by_gender(): void
    {
        $male = $this->createContact([
            'gender' => 1,
            'email' => 'male@example.com',
        ]);

        $female = $this->createContact([
            'gender' => 2,
            'email' => 'female@example.com',
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'gender' => 2,
            ])
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.id',
            $female->id
        );

        $response->assertJsonPath(
            'data.0.gender',
            2
        );
    }

    /**
     * category_idで絞り込みできること
     */
    public function test_contacts_can_be_filtered_by_category(): void
    {
        $target = $this->createContact([
            'category_id' => $this->category2->id,
            'email' => 'category2@example.com',
        ]);

        $other = $this->createContact([
            'category_id' => $this->category1->id,
            'email' => 'category1@example.com',
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'category_id' => $this->category2->id,
            ])
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.id',
            $target->id
        );

        $response->assertJsonPath(
            'data.0.category.id',
            $this->category2->id
        );
    }

    /**
     * dateでcreated_atの日付を絞り込みできること
     */
    public function test_contacts_can_be_filtered_by_date(): void
    {
        $target = $this->createContact([
            'email' => 'target-date@example.com',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);

        $other = $this->createContact([
            'email' => 'other-date@example.com',
            'created_at' => '2026-08-21 10:00:00',
            'updated_at' => '2026-08-21 10:00:00',
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'date' => '2026-08-20',
            ])
        );

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $target->id,
        ]);

        $response->assertJsonMissing([
            'id' => $other->id,
        ]);
    }

    /**
     * 複数条件をAND条件で絞り込みできること
     */
    public function test_contacts_can_be_filtered_by_multiple_conditions(): void
    {
        $target = $this->createContact([
            'first_name' => '検索対象',
            'last_name' => '山田',
            'gender' => 2,
            'category_id' => $this->category2->id,
            'email' => 'multi-target@example.com',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);

        $other = $this->createContact([
            'first_name' => '検索対象',
            'last_name' => '山田',
            'gender' => 1,
            'category_id' => $this->category2->id,
            'email' => 'multi-other@example.com',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);

        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'keyword' => '検索対象',
                'gender' => 2,
                'category_id' => $this->category2->id,
                'date' => '2026-08-20',
            ])
        );

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath(
            'data.0.id',
            $target->id
        );

        $response->assertJsonPath(
            'data.0.gender',
            2
        );

        $response->assertJsonPath(
            'data.0.category.id',
            $this->category2->id
        );

        $response->assertJsonPath(
            'data.0.email',
            'multi-target@example.com'
        );
    }

    /**
     * per_pageに応じてページネーションされること
     */
    public function test_contacts_are_paginated_by_per_page(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->createContact([
                'email' => "user{$i}@example.com",
                'created_at' => sprintf(
                    '2026-08-%02d 10:00:00',
                    $i
                ),
                'updated_at' => sprintf(
                    '2026-08-%02d 10:00:00',
                    $i
                ),
            ]);
        }

        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'per_page' => 3,
            ])
        );

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');

        $response->assertJsonPath(
            'meta.per_page',
            3
        );

        $response->assertJsonPath(
            'meta.total',
            8
        );

        $response->assertJsonPath(
            'meta.last_page',
            3
        );
    }

    /**
     * 不正な検索条件では422が返ること
     */
    public function test_invalid_filters_return_422(): void
    {
        $response = $this->getJson(
            route('api.v1.contacts.index', [
                'gender' => 9,
                'category_id' => 999999,
                'date' => 'invalid-date',
                'per_page' => 101,
            ])
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
            'category_id',
            'date',
            'per_page',
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
            'email' => uniqid() . '@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => null,
            'detail' => 'お問い合わせ内容です。',
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ], $attributes));
    }
}
