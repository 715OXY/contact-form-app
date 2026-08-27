<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 指定したお問い合わせがカテゴリ情報付きで
     * 詳細ページに表示されること
     */
    public function test_admin_can_view_contact_detail_with_category(): void
    {
        // 管理画面へアクセスするため認証済みユーザーを作成
        $user = User::factory()->create();

        $this->actingAs($user);

        // カテゴリを作成
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        // 詳細表示対象のお問い合わせを作成
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストマンション101',
            'detail' => '商品の配送状況について確認したいです。',
        ]);

        // 詳細ページへアクセス
        $response = $this->get("/admin/contacts/{$contact->id}");

        // 正常表示されること
        $response->assertStatus(200);

        // 指定したBladeが使用されること
        $response->assertViewIs('admin.show');

        // Contactがビュー変数として渡されること
        $response->assertViewHas('contact', function ($viewContact) use ($contact) {
            return $viewContact->id === $contact->id;
        });

        // お問い合わせ内容が表示されること
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('商品の配送状況について確認したいです。');

        // カテゴリ名が表示されること
        $response->assertSee('商品のお届けについて');
    }
}
