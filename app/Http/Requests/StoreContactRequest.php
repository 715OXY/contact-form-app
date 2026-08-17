<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|integer|in:1,2,3',
            'email' => 'required|email|unique:contacts',
            'tel' => 'required|string|max:11',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'detail' => 'required|string|max:120',
        ];
    }

    /**
     * バリデーションメッセージ
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'カテゴリは必須です。',
            'category_id.exists' => '選択されたカテゴリは存在しません。',
            'first_name.required' => '名は必須です。',
            'first_name.max' => '名は255文字以内で入力してください。',
            'last_name.required' => '姓は必須です。',
            'last_name.max' => '姓は255文字以内で入力してください。',
            'gender.required' => '性別は必須です。',
            'gender.in' => '性別は男性、女性、その他のいずれかを選択してください。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'tel.required' => '電話番号は必須です。',
            'tel.max' => '電話番号は11文字以内で入力してください。',
            'address.required' => '住所は必須です。',
            'address.max' => '住所は255文字以内で入力してください。',
            'building.max' => '建物名は255文字以内で入力してください。',
            'detail.required' => 'お問い合わせ内容は必須です。',
            'detail.max' => '詳細情報は120文字以内で入力してください。',
        ];
    }
}
