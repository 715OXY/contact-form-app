<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTagTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->actingAs($user);
    }

    /**
     * タグ更新時、自身の現在の名前は使用できること
     */
    public function test_tag_can_be_updated_with_its_own_name(): void
    {
        $tag = Tag::create([
            'name' => '重要',
        ]);

        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => '重要',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '重要',
        ]);
    }

    /**
     * タグ更新時、他のタグで使用されている名前には変更できないこと
     */
    public function test_tag_cannot_be_updated_with_another_tags_name(): void
    {
        $tag = Tag::create([
            'name' => '重要',
        ]);

        $otherTag = Tag::create([
            'name' => '至急',
        ]);

        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => '至急',
        ]);

        $response->assertSessionHasErrors('name');

        // 元のタグ名が変更されていないこと
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '重要',
        ]);

        // 既存の「至急」もそのまま存在すること
        $this->assertDatabaseHas('tags', [
            'id' => $otherTag->id,
            'name' => '至急',
        ]);

        // 「至急」が重複して作成されていないこと
        $this->assertDatabaseCount('tags', 2);
    }
}