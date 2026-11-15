<?php

namespace App\Models;

use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['title', 'description', 'image'];

    /**
     * @return HasMany<Episode, $this>
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'id_content');
    }

    /**
     * @return BelongsToMany<Crew, $this>
     */
    public function crews(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class, 'content_crew');
    }
}
