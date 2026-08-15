<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
    ];

    /**
     * このカテゴリーに属するお問い合せを取得
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
