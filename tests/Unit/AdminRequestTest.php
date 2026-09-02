<?php

namespace Tests\Unit;

use App\Http\Requests\AdminRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminRequestTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'content' => '商品のお届けについて',
        ]);
    }

    /**
     * 正しい検索条件を受け付けること
     */
    public function test_admin_request_accepts_valid_filters(): void
    {
        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $this->category->id,
            'date' => '2026-09-01',
        ];

        $validator = Validator::make(
            $data,
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    /**
     * 検索条件をすべて省略できること
     */
    public function test_admin_request_accepts_empty_filters(): void
    {
        $validator = Validator::make(
            [],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    /**
     * gender=0は「指定なし」として受け付けること
     */
    public function test_admin_request_accepts_gender_zero(): void
    {
        $validator = Validator::make(
            [
                'gender' => 0,
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    /**
     * gender=1,2,3を受け付けること
     */
    public function test_admin_request_accepts_valid_gender_values(): void
    {
        foreach ([1, 2, 3] as $gender) {
            $validator = Validator::make(
                [
                    'gender' => $gender,
                ],
                (new AdminRequest())->rules()
            );

            $this->assertTrue(
                $validator->passes(),
                "gender={$gender} should be accepted."
            );
        }
    }

    /**
     * 不正な性別値を拒否すること
     */
    public function test_admin_request_rejects_invalid_gender(): void
    {
        foreach ([-1, 4, 99] as $gender) {
            $validator = Validator::make(
                [
                    'gender' => $gender,
                ],
                (new AdminRequest())->rules()
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
     * 存在するカテゴリIDを受け付けること
     */
    public function test_admin_request_accepts_existing_category(): void
    {
        $validator = Validator::make(
            [
                'category_id' => $this->category->id,
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    /**
     * 存在しないカテゴリIDを拒否すること
     */
    public function test_admin_request_rejects_nonexistent_category(): void
    {
        $validator = Validator::make(
            [
                'category_id' => 999999,
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->fails()
        );

        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    /**
     * 正しい日付を受け付けること
     */
    public function test_admin_request_accepts_valid_date(): void
    {
        $validator = Validator::make(
            [
                'date' => '2026-09-01',
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    /**
     * 不正な日付を拒否すること
     */
    public function test_admin_request_rejects_invalid_date(): void
    {
        $validator = Validator::make(
            [
                'date' => 'invalid-date',
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->fails()
        );

        $this->assertArrayHasKey(
            'date',
            $validator->errors()->toArray()
        );
    }

    /**
     * 文字列のキーワードを受け付けること
     */
    public function test_admin_request_accepts_valid_keyword(): void
    {
        $validator = Validator::make(
            [
                'keyword' => '山田',
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->passes()
        );
    }

    /**
     * 文字列でないキーワードを拒否すること
     */
    public function test_admin_request_rejects_invalid_keyword(): void
    {
        $validator = Validator::make(
            [
                'keyword' => ['山田'],
            ],
            (new AdminRequest())->rules()
        );

        $this->assertTrue(
            $validator->fails()
        );

        $this->assertArrayHasKey(
            'keyword',
            $validator->errors()->toArray()
        );
    }
}