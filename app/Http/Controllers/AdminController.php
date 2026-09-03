<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * お問合せ一覧を表示
     */
    public function index(AdminRequest $request)
    {
        // dd($request->all());
        // お問合せ一覧を取得（カテゴリーとタグのリレーションをロード）
        $query = Contact::with(['category', 'tags']);

        // キーワード検索
        // 氏名またはメールアドレスを部分一致で検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            });
        }

        // 性別検索
        if ($request->filled('gender') && $request->input('gender') !== '0') {
            $query->where('gender', $request->input('gender'));
        }

        // カテゴリー検索
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 作成日検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // 検索実行
        // 1ページ7件表示
        // 検索条件をページ遷移後も維持
        $contacts = $query->paginate(7)->withQueryString();

        // 検索フォームのカテゴリー選択肢
        $categories = Category::all();

        // タグ（一覧表示用）
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.index');
    }
}
