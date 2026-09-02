<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\Api\V1\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    /**
     * お問い合わせ一覧を取得
     */
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);

        // キーワード検索
        // 姓・名・メールアドレスの部分一致
        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        // 性別検索
        if (isset($validated['gender'])) {
            $query->where('gender', $validated['gender']);
        }

        // カテゴリ検索
        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        // 日付検索
        if (!empty($validated['date'])) {
            $query->whereDate('created_at', $validated['date']);
        }

        // 1ページあたりの件数
        $perPage = $validated['per_page'] ?? 20;

        // 新着順
        $contacts = $query
            ->latest()
            ->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * お問い合わせ詳細を取得
     */
    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * お問い合わせを新規作成
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // tag_ids は contacts テーブルのカラムではないため分離
        $tagIds = $validated['tag_ids'] ?? [];

        unset($validated['tag_ids']);

        // Contactを作成
        $contact = Contact::create($validated);

        // タグを中間テーブルへ登録
        if (!empty($tagIds)) {
            $contact->tags()->attach($tagIds);
        }

        // Resourceで使用するリレーションを読み込む
        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * お問い合わせを更新
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): ContactResource {
        $validated = $request->validated();

        // tag_ids を分離
        $tagIds = $validated['tag_ids'] ?? [];

        unset($validated['tag_ids']);

        // Contact本体を更新
        $contact->update($validated);

        // タグを同期
        $contact->tags()->sync($tagIds);

        // 更新後のリレーションを再読み込み
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * お問い合わせを削除
     */
    public function destroy(Contact $contact): Response
    {
        $contact->delete();

        return response()->noContent();
    }
}