<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category1;

    private Category $category2;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理画面へアクセスするためログイン状態を作る
        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        // 検索確認用カテゴリー
        $this->category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->category2 = Category::create([
            'content' => '商品の交換について',
        ]);
    }

    /**
     * テスト用お問い合わせを作成する
     */
    private function createContact(array $attributes = []): Contact
    {
        return Contact::forceCreate(array_merge([
            'category_id' => $this->category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => uniqid().'@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => null,
            'detail' => '通常のお問い合わせです。',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ], $attributes));
    }

    /**
     * キーワードで検索できること
     */
    public function test_admin_can_filter_contacts_by_keyword(): void
    {
        $this->createContact([
            'first_name' => '検索対象',
            'last_name' => '山田',
            'email' => 'target@example.com',
        ]);

        $this->createContact([
            'first_name' => '太郎',
            'last_name' => '佐藤',
            'email' => 'other@example.com',
        ]);

        $response = $this->get('/admin?keyword=検索対象');

        $response->assertStatus(200);

        $response->assertSee('検索対象');
        $response->assertDontSee('佐藤');
    }

    /**
     * 性別で検索できること
     */
    public function test_admin_can_filter_contacts_by_gender(): void
    {
        $this->createContact([
            'first_name' => '男性ユーザー',
            'gender' => 1,
        ]);

        $this->createContact([
            'first_name' => '女性ユーザー',
            'gender' => 2,
        ]);

        $response = $this->get('/admin?gender=2');

        $response->assertStatus(200);

        $response->assertSee('女性ユーザー');
        $response->assertDontSee('男性ユーザー');
    }

    /**
     * カテゴリで検索できること
     */
    public function test_admin_can_filter_contacts_by_category(): void
    {
        $this->createContact([
            'first_name' => '配送問い合わせ',
            'category_id' => $this->category1->id,
        ]);

        $this->createContact([
            'first_name' => '交換問い合わせ',
            'category_id' => $this->category2->id,
        ]);

        $response = $this->get(
            '/admin?category_id='.$this->category2->id
        );

        $response->assertStatus(200);

        $response->assertSee('交換問い合わせ');
        $response->assertDontSee('配送問い合わせ');
    }

    /**
     * 日付で検索できること
     */
    public function test_admin_can_filter_contacts_by_date(): void
    {
        $this->createContact([
            'first_name' => '対象日ユーザー',
            'created_at' => '2026-08-17 10:00:00',
            'updated_at' => '2026-08-17 10:00:00',
        ]);

        $this->createContact([
            'first_name' => '別日ユーザー',
            'created_at' => '2026-08-18 10:00:00',
            'updated_at' => '2026-08-18 10:00:00',
        ]);

        $response = $this->get('/admin?date=2026-08-17');

        $response->assertStatus(200);

        $response->assertSee('対象日ユーザー');
        $response->assertDontSee('別日ユーザー');
    }

    /**
     * 検索条件を組み合わせて絞り込めること
     */
    public function test_admin_can_filter_contacts_with_multiple_conditions(): void
    {
        $this->createContact([
            'first_name' => '完全一致対象',
            'gender' => 2,
            'category_id' => $this->category2->id,
            'created_at' => '2026-08-17 10:00:00',
            'updated_at' => '2026-08-17 10:00:00',
        ]);

        $this->createContact([
            'first_name' => '条件不一致',
            'gender' => 1,
            'category_id' => $this->category1->id,
            'created_at' => '2026-08-18 10:00:00',
            'updated_at' => '2026-08-18 10:00:00',
        ]);

        $response = $this->get('/admin?'.http_build_query([
            'keyword' => '完全一致対象',
            'gender' => 2,
            'category_id' => $this->category2->id,
            'date' => '2026-08-17',
        ]));

        $response->assertStatus(200);

        $response->assertSee('完全一致対象');
        $response->assertDontSee('条件不一致');
    }

    /**
     * お問い合わせ一覧が7件ごとにページネーションされること
     */
    public function test_admin_contacts_are_paginated_seven_per_page(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->createContact([
                'first_name' => 'テスト'.$i,
                'email' => 'test'.$i.'@example.com',
            ]);
        }

        $response = $this->get('/admin');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 7
                && $contacts->perPage() === 7
                && $contacts->total() === 8;
        });

        // 2ページ目には残り1件だけ表示されること
        $secondPage = $this->get('/admin?page=2');

        $secondPage->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1
                && $contacts->total() === 8;
        });
    }
}
