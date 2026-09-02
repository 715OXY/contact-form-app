<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
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
     * CSV形式でダウンロード
     */
    public function export(ExportContactRequest $request)
    {

        $validated = $request->validated();

        $query = Contact::with('category');

        // キーワード検索
        // 氏名またはメールアドレスを部分一致で検索
        if ($request->filled('keyword')) {
            $keyword = $validated['keyword'];

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            });
        }

        // 性別検索
        // 0 は「全て」なので絞り込まない
        if (
            isset($validated['gender'])
            && (int) $validated['gender'] !== 0
        ) {
            $query->where('gender', $validated['gender']);
        }

        // カテゴリー検索
        if ($request->filled('category_id')) {
            $query->where('category_id', $validated['category_id']);
        }

        // 作成日検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $validated['date']);
        }

        // 検索実行
        // 新着順で全件取得
        $contacts = $query
            ->orderByDesc('created_at')
            ->get();

        // CSVファイルの生成
        $fileName = 'contacts.csv';

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ]);

            foreach ($contacts as $contact) {
                $gender = match ((int) $contact->gender) {
                    1 => '男性',
                    2 => '女性',
                    3 => 'その他',
                    default => '',
                };

                fputcsv($handle, [
                    $contact->id,
                    $contact->last_name.' '.$contact->first_name,
                    $gender,
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category?->content ?? '',
                    $contact->detail,
                    $contact->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
