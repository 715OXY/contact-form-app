<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーはタグ編集画面を表示できること
     */
    public function test_authenticated_user_can_view_tag_edit_page(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '重要',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.tags.edit', $tag));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');

        $response->assertViewHas('tag', function ($viewTag) use ($tag) {
            return $viewTag->id === $tag->id;
        });

        $response->assertSee('重要');
    }

    /**
     * 認証済みユーザーはタグを新規作成でき、
     * /admin へリダイレクトされること
     */
    public function test_authenticated_user_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.tags.store', [
                'name' => '新しいタグ',
            ]));

        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);

        $response->assertRedirect(route('admin.index'));
    }

    /**
     * 認証済みユーザーはタグを更新でき、
     * /admin へリダイレクトされること
     */
    public function test_authenticated_user_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '変更前',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.tags.update', $tag), [
                'name' => '変更後',
            ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '変更後',
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
            'name' => '変更前',
        ]);

        $response->assertRedirect(route('admin.index'));
    }

    /**
     * 認証済みユーザーはタグを削除でき、
     * /admin へリダイレクトされること
     */
    public function test_authenticated_user_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '削除対象',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('admin.tags.destroy', $tag));

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $response->assertRedirect(route('admin.index'));
    }

    /**
     * 未認証ユーザーはタグ編集画面へアクセスできないこと
     */
    public function test_guest_cannot_view_tag_edit_page(): void
    {
        $tag = Tag::create([
            'name' => '重要',
        ]);

        $response = $this->get(
            route('admin.tags.edit', $tag)
        );

        $response->assertRedirect(route('login'));
    }

    /**
     * 未認証ユーザーはタグを新規作成できないこと
     */
    public function test_guest_cannot_create_tag(): void
    {
        $response = $this->post(
            route('admin.tags.store'),
            ['name' => '不正作成タグ']
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('tags', [
            'name' => '不正作成タグ',
        ]);
    }

    /**
     * 未認証ユーザーはタグを更新できないこと
     */
    public function test_guest_cannot_update_tag(): void
    {
        $tag = Tag::create([
            'name' => '変更前',
        ]);

        $response = $this->put(
            route('admin.tags.update', $tag),
            [
                'name' => '変更後',
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '変更前',
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
            'name' => '変更後',
        ]);
    }

    /**
     * 未認証ユーザーはタグを削除できないこと
     */
    public function test_guest_cannot_delete_tag(): void
    {
        $tag = Tag::create([
            'name' => '削除禁止タグ',
        ]);

        $response = $this->delete(
            route('admin.tags.destroy', $tag)
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '削除禁止タグ',
        ]);
    }
}
