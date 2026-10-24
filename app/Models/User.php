<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Area;
use App\Enums\CrewPosition;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['username', 'password', 'role_id', 'photo_path'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The crew position this user may be assigned to, based on their role.
     */
    public function crewPosition(): ?CrewPosition
    {
        return CrewPosition::forRoleName($this->role->name);
    }

    /**
     * The area this user may enter, based on their role.
     */
    public function area(): ?Area
    {
        return Area::forRoleName($this->role->name);
    }

    /**
     * Scope a query to users whose role name is one of the given names.
     *
     * @param  Builder<User>  $query
     * @param  list<string>  $names
     */
    #[Scope]
    protected function withRoles(Builder $query, array $names): void
    {
        $query->whereHas('role', fn (Builder $roleQuery) => $roleQuery->whereIn('name', $names));
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_crew')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function broadcastSessions(): BelongsToMany
    {
        return $this->belongsToMany(BroadcastSession::class, 'session_crew', 'user_id', 'broadcast_session_id')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
