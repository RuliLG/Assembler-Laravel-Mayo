<?php

namespace App\Models;

use App\Services\WordCounterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'word_count',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function wordCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->content ? (new WordCounterService())->count($this->content) : 0,
        );
    }

    public function scopePublished(Builder $query)
    {
        $query->where('published_at', '<=', now());
    }
}
