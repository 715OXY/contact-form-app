<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category1;

    private Category $category2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->category2 = Category::create([
            'content' => '商品の交換について',
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
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ], $attributes));
    }

    /**
     * ログイン済みユーザーが
     * フィルタ条件付きでCSVをダウンロードできること
     */
    public function test_authenticated_user_can_export_filtered_contacts_as_csv(): void
    {
        $target = $this->createContact([
            'first_name' => '検索対象',
            'last_name' => '山田',
            'gender' => 2,
            'email' => 'target@example.com',
            'category_id' => $this->category2->id,
            'created_at' => '2026-08-17 10:00:00',
            'updated_at' => '2026-08-17 10:00:00',
        ]);

        $this->createContact([
            'first_name' => '対象外',
            'last_name' => '佐藤',
            'gender' => 1,
            'email' => 'other@example.com',
            'category_id' => $this->category1->id,
            'created_at' => '2026-08-18 10:00:00',
            'updated_at' => '2026-08-18 10:00:00',
        ]);

        $response = $this->get(route('contacts.export', [
            'keyword' => '検索対象',
            'gender' => 2,
            'category_id' => $this->category2->id,
            'date' => '2026-08-17',
        ]));

        $response->assertStatus(200);

        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8'
        );

        $content = $response->streamedContent();

        // BOM付きであること
        $this->assertStringStartsWith(
            "\xEF\xBB\xBF",
            $content
        );

        // ヘッダーが含まれること
        $this->assertStringContainsString(
            'ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時',
            $content
        );

        // 条件一致データが含まれること
        $this->assertStringContainsString(
            '検索対象',
            $content
        );

        $this->assertStringContainsString(
            'target@example.com',
            $content
        );

        $this->assertStringContainsString(
            '商品の交換について',
            $content
        );

        // 性別が数値ではなく文字列で出力されること
        $this->assertStringContainsString(
            '女性',
            $content
        );

        // 条件不一致データは含まれないこと
        $this->assertStringNotContainsString(
            '対象外',
            $content
        );

        $this->assertStringNotContainsString(
            'other@example.com',
            $content
        );
    }

    /**
     * フィルタ未指定時は
     * 全件が新着順でCSV出力されること
     */
    public function test_contacts_are_exported_in_latest_order_without_filters(): void
    {
        $oldContact = $this->createContact([
            'first_name' => '古い問い合わせ',
            'email' => 'old@example.com',
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ]);

        $middleContact = $this->createContact([
            'first_name' => '中間問い合わせ',
            'email' => 'middle@example.com',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);

        $newContact = $this->createContact([
            'first_name' => '新しい問い合わせ',
            'email' => 'new@example.com',
            'created_at' => '2026-08-25 10:00:00',
            'updated_at' => '2026-08-25 10:00:00',
        ]);

        $response = $this->get(
            route('contacts.export')
        );

        $response->assertStatus(200);

        $content = $response->streamedContent();

        // 全件が含まれていること
        $this->assertStringContainsString(
            'old@example.com',
            $content
        );

        $this->assertStringContainsString(
            'middle@example.com',
            $content
        );

        $this->assertStringContainsString(
            'new@example.com',
            $content
        );

        // CSV内で新しい順に並んでいること
        $newPosition = strpos(
            $content,
            'new@example.com'
        );

        $middlePosition = strpos(
            $content,
            'middle@example.com'
        );

        $oldPosition = strpos(
            $content,
            'old@example.com'
        );

        $this->assertTrue(
            $newPosition < $middlePosition
        );

        $this->assertTrue(
            $middlePosition < $oldPosition
        );
    }
}