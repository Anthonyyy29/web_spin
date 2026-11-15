<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\BroadcastSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastSession extends Model
{
    /** @use HasFactory<BroadcastSessionFactory> */
    use HasFactory;

    /**
     * Tap boleh dilakukan sekian menit sebelum jam mulai sampai jam selesai.
     */
    public const int TAP_WINDOW_MINUTES = 60;

    /**
     * @var list<string>
     */
    protected $fillable = ['content_id', 'tanggal', 'jam_mulai', 'jam_selesai'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->tanggal->toDateString().' '.$this->jam_mulai);
    }

    public function endsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->tanggal->toDateString().' '.$this->jam_selesai);
    }

    public function statusAt(CarbonInterface $at): string
    {
        return $at->greaterThan($this->startsAt()) ? 'Terlambat' : 'Hadir';
    }

    /**
     * Sesi hari ini milik program crew yang jendela tap-nya mencakup $at;
     * bila ada beberapa, dipilih yang jam mulainya terdekat. Null berarti tamu.
     */
    public static function findForTap(?Crew $crew, CarbonInterface $at): ?self
    {
        if ($crew === null) {
            return null;
        }

        return static::query()
            ->whereDate('tanggal', $at->toDateString())
            ->whereIn('content_id', $crew->contents()->pluck('contents.id'))
            ->get()
            ->filter(fn (self $session): bool => $at->betweenIncluded(
                $session->startsAt()->subMinutes(self::TAP_WINDOW_MINUTES),
                $session->endsAt(),
            ))
            ->sortBy(fn (self $session): float => abs($session->startsAt()->diffInSeconds($at)))
            ->first();
    }
}
