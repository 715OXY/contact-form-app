<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
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
     * 正しいフィルタ条件を受け付けること
     */
    public function test_export_contact_request_accepts_valid_filters(): void
    {
        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $this->category->id,
            'date' => '2026-08-30',
        ];

        $validator = Validator::make(
            $data,
            (new ExportContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * フィルタ条件を指定しなくても受け付けること
     */
    public function test_export_contact_request_accepts_empty_filters(): void
    {
        $validator = Validator::make(
            [],
            (new ExportContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * gender=0は「指定なし」として受け付けること
     */
    public function test_export_contact_request_accepts_gender_zero(): void
    {
        $validator = Validator::make(
            [
                'gender' => 0,
            ],
            (new ExportContactRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * 不正な性別を拒否すること
     */
    public function test_export_contact_request_rejects_invalid_gender(): void
    {
        foreach ([4, 99, -1] as $gender) {
            $validator = Validator::make(
                [
                    'gender' => $gender,
                ],
                (new ExportContactRequest)->rules()
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
    public function test_export_contact_request_rejects_nonexistent_category(): void
    {
        $validator = Validator::make(
            [
                'category_id' => 999999,
            ],
            (new ExportContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }
}
