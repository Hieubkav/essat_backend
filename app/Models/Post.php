<?php

namespace App\Models;

use App\Casts\LexicalToHtmlCast;
use App\Models\Concerns\HasRichEditorMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    use HasRichEditorMedia;

    protected array $richEditorFields = ['content'];

    protected string $richEditorContentDirectory = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'active',
        'thumbnail',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
            'content' => LexicalToHtmlCast::class,
        ];
    }
}
