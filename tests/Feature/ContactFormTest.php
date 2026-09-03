<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\TagsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            CategoriesTableSeeder::class,
            TagsTableSeeder::class,
        ]);
    }

    /**
     * お問い合わせフォーム入力ページが正常に表示され、
     * categories・tags がビューに渡されること
     */
    public function test_contact_form_page_is_displayed_with_categories_and_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    /**
     * カテゴリ名がページに表示されること
     */
    public function test_contact_form_page_displays_category_names(): void
    {
        $response = $this->get('/');

        $categories = Category::all();

        foreach ($categories as $category) {
            $response->assertSee($category->content);
        }
    }

    /**
     * タグ名がページに表示されること
     */
    public function test_contact_form_page_displays_tag_names(): void
    {
        $response = $this->get('/');

        $tags = Tag::all();

        foreach ($tags as $tag) {
            $response->assertSee($tag->name);
        }
    }

    /**
     * サンクスページが正常に表示されること
     */
    public function test_thanks_page_is_displayed_after_contact_is_stored(): void
    {
        $category = Category::first();

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストマンション101',
            'category_id' => $category->id,
            'detail' => '商品についてのお問い合わせです。',
        ];

        $response = $this->post('/contacts', $data);

        // お問い合わせがDBに保存されたこと
        $this->assertDatabaseHas('contacts', [
            'email' => 'taro@example.com',
            'category_id' => $category->id,
        ]);

        // 登録後に /thanks へリダイレクトされること
        $response->assertRedirect('/thanks');

        // /thanks が正常に表示されること
        $thanksResponse = $this->get('/thanks');

        $thanksResponse->assertStatus(200);
        $thanksResponse->assertViewIs('contact.thanks');
    }
}
