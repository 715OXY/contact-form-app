<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーは管理ダッシュボードを表示できること
     */
    public function test_authenticated_user_can_view_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    /**
     * 未認証ユーザーはログイン画面へリダイレクトされること
     */
    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}
