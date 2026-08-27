<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * お問い合わせを削除でき、
     * 削除後に /admin へリダイレクトされること
     */
    public function test_admin_can_delete_contact_and_redirect_to_admin(): void
    {
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        $this->actingAs($user);

        // カテゴリを作成
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        // 削除対象のお問い合わせを作成
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => null,
            'detail' => '削除テスト用のお問い合わせです。',
        ]);

        // 削除前はDBに存在すること
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ]);

        // DELETEリクエスト
        $response = $this->delete(
            "/admin/contacts/{$contact->id}"
        );

        // DBから削除されていること
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin');
    }
}
