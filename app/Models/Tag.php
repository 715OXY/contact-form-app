<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * リレーションの定義（このタグは複数のお問い合せを持つ）
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class);
    }
}
