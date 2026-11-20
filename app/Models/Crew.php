<?php

namespace App\Models;

use Database\Factories\CrewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Crew extends Model
{
    /** @use HasFactory<CrewFactory> */
    use HasFactory;

    /**
     * Daftar jabatan, urut dari yang tampil paling depan, beserta warna grafiknya.
     *
     * @var array<string, string>
     */
    public const array ROLES = [
        'General Manager' => '#2E3A59',
        'Vice General Manager' => '#4B6587',
        'Sekretaris' => '#E74C3C',
        'Bendahara' => '#F39C12',
        'Head of Socmed' => '#8E44AD',
        'Head of Podcast' => '#3498DB',
        'Human Resource' => '#16A085',
        'Public Relation' => '#1ABC9C',
        'Partnership' => '#2980B9',
        'Media Relation' => '#E67E22',
        'Editor Video' => '#C0392B',
        'Design & Publishing' => '#F1C40F',
        'Creative' => '#34495E',
        'Web Development' => '#27AE60',
        'Sound Operator' => '#D35400',
        'Announcer' => '#7F8C8D',
    ];

    /**
     * Jabatan yang boleh ditugaskan ke sesi siaran.
     *
     * @var list<string>
     */
    public const array SESSION_ROLES = ['Announcer', 'Sound Operator', 'Human Resource'];

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'image', 'role', 'extra_role', 'about', 'linkedin_link', 'instagram_link'];

    /**
     * Crew diurutkan menurut urutan jabatan; jabatan di luar daftar tidak ikut.
     *
     * @return Collection<int, self>
     */
    public static function sortedByRole(int $limit = 0): Collection
    {
        $roles = array_keys(self::ROLES);

        return static::query()
            ->when($limit > 0, fn ($query) => $query->limit($limit))
            ->get()
            ->filter(fn (self $crew): bool => in_array($crew->role, $roles, true))
            ->sortBy(fn (self $crew): int => array_search($crew->role, $roles, true))
            ->values();
    }

    /**
     * Crew yang jabatan utama atau tambahannya boleh ditugaskan ke sesi siaran.
     *
     * @param  Builder<self>  $query
     */
    public function scopeCanJoinSession(Builder $query): void
    {
        $query->where(fn (Builder $query) => $query
            ->whereIn('role', self::SESSION_ROLES)
            ->orWhereIn('extra_role', self::SESSION_ROLES));
    }

    /**
     * @return BelongsToMany<BroadcastSession, $this>
     */
    public function broadcastSessions(): BelongsToMany
    {
        return $this->belongsToMany(BroadcastSession::class, 'broadcast_session_crew');
    }

    /**
     * @return BelongsToMany<Content, $this>
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_crew');
    }
}
