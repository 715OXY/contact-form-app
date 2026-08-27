<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->actingAs($user);
    }

    /**
     * 正常なタグ名を登録できること
     */
    public function test_tag_can_be_stored_with_valid_name(): void
    {
        $response = $this->post('/admin/tags', [
            'name' => '新しいタグ',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);
    }

    /**
     * タグ名は必須であること
     */
    public function test_tag_name_is_required(): void
    {
        $response = $this->post('/admin/tags', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tags', 0);
    }

    /**
     * タグ名の文字数制限を超えると拒否されること
     */
    public function test_tag_name_cannot_exceed_max_length(): void
    {
        $response = $this->post('/admin/tags', [
            'name' => str_repeat('あ', 51), // 51文字のタグ名
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tags', 0);
    }

    /**
     * 同じタグ名を重複して登録できないこと
     */
    public function test_tag_name_must_be_unique(): void
    {
        Tag::create([
            'name' => '重要',
        ]);

        $response = $this->post('/admin/tags', [
            'name' => '重要',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tags', 1);
    }
}