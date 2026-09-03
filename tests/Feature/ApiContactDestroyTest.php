<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactDestroyTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->tag = Tag::create([
            'name' => '重要',
        ]);
    }

    /**
     * 実在するお問い合わせを削除でき、
     * 204 No Content が返ること
     */
    public function test_contact_can_be_deleted_and_returns_204(): void
    {
        $contact = $this->createContact();

        $contact->tags()->attach(
            $this->tag->id
        );

        $response = $this->deleteJson(
            route('api.v1.contacts.destroy', $contact)
        );

        $response->assertStatus(204);

        $response->assertNoContent();

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $this->tag->id,
        ]);
    }

    /**
     * 存在しないお問い合わせIDでは
     * 404エラーJSONが返ること
     */
    public function test_nonexistent_contact_returns_404_json(): void
    {
        $response = $this->deleteJson(
            route('api.v1.contacts.destroy', 999999)
        );

        $response->assertStatus(404);

        $response->assertExactJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    /**
     * テスト用Contactを作成
     */
    private function createContact(array $attributes = []): Contact
    {
        return Contact::forceCreate(array_merge([
            'category_id' => $this->category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'delete-test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => null,
            'detail' => '削除APIのテストです。',
            'created_at' => '2026-08-30 10:00:00',
            'updated_at' => '2026-08-30 10:00:00',
        ], $attributes));
    }
}
