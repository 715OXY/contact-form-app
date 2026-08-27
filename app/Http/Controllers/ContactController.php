<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class ContactController extends Controller
{
    /**
     * お問い合わせフォーム作成ページを表示
     */
    public function index()
    {
        $categories = Category::all();

        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * お問い合わせフォーム確認ページを表示
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        // 選択されたCategoryを1件取得
        $category = Category::findOrFail(
            $validated['category_id']
        );

        // 選択されたTagを複数件取得
        $tag = Tag::all();

        return view('contact.confirm', compact('validated', 'category', 'tag'));
    }

    /**
     * お問い合わせ作成フォームを表示
     */
    public function create()
    {
        //
    }

    /**
     * お問い合わせを新規作成
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        // タグIDを先に退避
        $tagIds = $validated['tag_ids'] ?? [];

        // contactsテーブルには存在しないので除外
        unset($validated['tag_ids']);

        // Contactを保存
        $contact = Contact::create($validated);

        // 中間テーブルへ保存
        $contact->tags()->attach($tagIds);

        return redirect()->route('contacts.thanks');
    }

    /**
     * お問い合わせ作成完了ページを表示
     */
    public function thanks()
    {
        return view('contact.thanks');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
