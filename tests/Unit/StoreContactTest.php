<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\TagsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactTest extends TestCase
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
     * 正常なお問い合わせデータを作成する
     */
    private function validContactData(): array
    {
        return [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストマンション101',
            'category_id' => Category::first()->id,
            'detail' => '商品のお届けについて確認したいです。',
            'tag_ids' => Tag::take(2)->pluck('id')->toArray(),
        ];
    }

    /**
     * 全ての必須項目とタグ入力を受け付けること
     */
    public function test_contact_confirm_accepts_valid_input(): void
    {
        $response = $this->post(
            '/contacts/confirm',
            $this->validContactData()
        );

        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);
    }

    /**
     * タグを入力しなくても受け付けること
     */
    public function test_contact_confirm_accepts_input_without_tags(): void
    {
        $data = $this->validContactData();

        unset($data['tag_ids']);

        $response = $this->post('/contacts/confirm', $data);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);
    }

    /**
     * 不正な電話番号形式は拒否すること
     */
    public function test_contact_confirm_rejects_invalid_tel(): void
    {
        $data = $this->validContactData();

        $data['tel'] = 'abcdefghijk';

        $response = $this->post('/contacts/confirm', $data);

        $response->assertSessionHasErrors('tel');
    }

    /**
     * 必須項目が1つでも欠けている場合は拒否すること
     */
    public function test_contact_confirm_rejects_missing_required_fields(): void
    {
        $requiredFields = [
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ];

        foreach ($requiredFields as $field) {
            $data = $this->validContactData();

            unset($data[$field]);

            $response = $this->post('/contacts/confirm', $data);

            $response->assertSessionHasErrors($field);
        }
    }
}
