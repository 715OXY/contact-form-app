<?php

namespace Tests\Unit;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiStoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Tag $tag1;

    private Tag $tag2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->tag1 = Tag::create([
            'name' => '重要',
        ]);

        $this->tag2 = Tag::create([
            'name' => '確認',
        ]);
    }

    /**
     * 全必須項目とタグを含む正常な入力を受け付けること
     */
    public function test_store_contact_request_accepts_valid_input_with_tags(): void
    {
        $data = $this->validData();

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * タグを省略しても正常であること
     */
    public function test_store_contact_request_accepts_input_without_tags(): void
    {
        $data = $this->validData();

        unset($data['tag_ids']);

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * 必須項目が欠落している場合は拒否すること
     */
    public function test_store_contact_request_rejects_missing_required_fields(): void
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
            $data = $this->validData();

            unset($data[$field]);

            $validator = Validator::make(
                $data,
                (new StoreContactRequest)->rules()
            );

            $this->assertTrue(
                $validator->fails(),
                "{$field} should be required."
            );

            $this->assertArrayHasKey(
                $field,
                $validator->errors()->toArray()
            );
        }
    }

    /**
     * 不正な性別を拒否すること
     */
    public function test_store_contact_request_rejects_invalid_gender(): void
    {
        $data = $this->validData();
        $data['gender'] = 9;

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    /**
     * 不正なメールアドレスを拒否すること
     */
    public function test_store_contact_request_rejects_invalid_email(): void
    {
        $data = $this->validData();
        $data['email'] = 'invalid-email';

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'email',
            $validator->errors()->toArray()
        );
    }

    /**
     * ハイフンを含む電話番号を拒否すること
     */
    public function test_store_contact_request_rejects_invalid_tel(): void
    {
        $data = $this->validData();
        $data['tel'] = '090-1234-5678';

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }

    /**
     * 存在しないカテゴリを拒否すること
     */
    public function test_store_contact_request_rejects_nonexistent_category(): void
    {
        $data = $this->validData();
        $data['category_id'] = 999999;

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    /**
     * 存在しないタグを拒否すること
     */
    public function test_store_contact_request_rejects_nonexistent_tag(): void
    {
        $data = $this->validData();
        $data['tag_ids'] = [
            $this->tag1->id,
            999999,
        ];

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'tag_ids.1',
            $validator->errors()->toArray()
        );
    }

    /**
     * お問い合わせ内容が120文字を超える場合は拒否すること
     */
    public function test_store_contact_request_rejects_detail_over_120_characters(): void
    {
        $data = $this->validData();
        $data['detail'] = str_repeat('あ', 121);

        $validator = Validator::make(
            $data,
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'detail',
            $validator->errors()->toArray()
        );
    }

    /**
     * 正常系データを生成
     */
    private function validData(): array
    {
        return [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区西新宿1-1-1',
            'building' => 'テストビル101',
            'category_id' => $this->category->id,
            'detail' => 'API作成バリデーションのテストです。',
            'tag_ids' => [
                $this->tag1->id,
                $this->tag2->id,
            ],
        ];
    }
}
