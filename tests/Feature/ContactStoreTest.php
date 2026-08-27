<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常な入力でお問い合わせとタグが保存され、
     * /thanks へリダイレクトされること
     */
    public function test_contact_is_stored_with_tags_and_redirected_to_thanks(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag1 = Tag::create([
            'name' => '重要',
        ]);

        $tag2 = Tag::create([
            'name' => '確認',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストマンション101',
            'category_id' => $category->id,
            'detail' => '商品のお届けについて確認したいです。',
            'tag_ids' => [
                $tag1->id,
                $tag2->id,
            ],
        ];

        $response = $this->post('/contacts', $data);

        // contacts テーブルに保存されていること
        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
            'category_id' => $category->id,
            'detail' => '商品のお届けについて確認したいです。',
        ]);

        // 保存されたContactを取得
        $contact = Contact::where('email', 'taro@example.com')->firstOrFail();

        // contact_tag にタグ1が登録されていること
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);

        // contact_tag にタグ2が登録されていること
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);

        // /thanks へリダイレクトされること
        $response->assertRedirect('/thanks');
    }

    /**
     * バリデーションエラー時は保存されず、
     * 入力画面へリダイレクトされエラーが返ること
     */
    public function test_contact_is_not_stored_when_validation_fails(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $data = [
            // first_name を意図的に未入力
            'first_name' => '',
            'last_name' => '山田',
            'gender' => 1,

            // 不正なメールアドレス
            'email' => 'invalid-email',

            // 不正な電話番号
            'tel' => 'abcdefghijk',

            'address' => '東京都新宿区西新宿1-1-1',
            'building' => '',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
        ];

        $response = $this
            ->from('/')
            ->post('/contacts', $data);

        // バリデーションエラーが返ること
        $response->assertSessionHasErrors([
            'first_name',
            'email',
            'tel',
        ]);

        // 入力ページへ戻ること
        $response->assertRedirect('/');

        // contacts テーブルに保存されていないこと
        $this->assertDatabaseCount('contacts', 0);

        // 中間テーブルにも保存されていないこと
        $this->assertDatabaseCount('contact_tag', 0);
    }
}
