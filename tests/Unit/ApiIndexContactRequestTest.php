<?php

namespace Tests\Unit;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiIndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    /**
     * A basic unit test example.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'content' => '商品のお届けについて',
        ]);
    }

    /**
     * 正常な検索条件を受け付けること
     */
    public function test_index_contact_request_accepts_valid_filters(): void
    {
        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $this->category->id,
            'date' => '2026-08-29',
            'per_page' => 20,
        ];

        $validator = Validator::make(
            $data,
            (new IndexContactRequest())->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * 検索条件をすべて省略できること
     */
    public function test_index_contact_request_accepts_empty_filters(): void
    {
        $validator = Validator::make(
            [],
            (new IndexContactRequest())->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * 性別は1,2,3のみ受け付けること
     */
    public function test_index_contact_request_rejects_invalid_gender(): void
    {
        foreach ([0, 4, 99] as $gender) {
            $validator = Validator::make(
                ['gender' => $gender],
                (new IndexContactRequest())->rules()
            );

            $this->assertTrue(
                $validator->fails(),
                "gender={$gender} should be rejected."
            );

            $this->assertArrayHasKey(
                'gender',
                $validator->errors()->toArray()
            );
        }
    }

    /**
     * 存在しないカテゴリIDを拒否すること
     */
    public function test_index_contact_request_rejects_nonexistent_category(): void
    {
        $validator = Validator::make(
            ['category_id' => 999999],
            (new IndexContactRequest())->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    /**
     * 不正な日付を拒否すること
     */
    public function test_index_contact_request_rejects_invalid_date(): void
    {
        $validator = Validator::make(
            ['date' => 'not-a-date'],
            (new IndexContactRequest())->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'date',
            $validator->errors()->toArray()
        );
    }

    /**
     * per_pageが範囲外の場合は拒否すること
     */
    public function test_index_contact_request_rejects_invalid_per_page(): void
    {
        foreach ([0, 101] as $perPage) {
            $validator = Validator::make(
                ['per_page' => $perPage],
                (new IndexContactRequest())->rules()
            );

            $this->assertTrue(
                $validator->fails(),
                "per_page={$perPage} should be rejected."
            );

            $this->assertArrayHasKey(
                'per_page',
                $validator->errors()->toArray()
            );
        }
    }

    /**
     * キーワードが文字列でない場合は拒否すること
     */
    public function test_index_contact_request_rejects_invalid_keyword(): void
    {
        $validator = Validator::make(
            ['keyword' => ['山田']],
            (new IndexContactRequest())->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'keyword',
            $validator->errors()->toArray()
        );
    }
}
