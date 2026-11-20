<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\BroadcastSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BroadcastSession extends Model
{
    /** @use HasFactory<BroadcastSessionFactory> */
    use HasFactory;

    /**
     * Tap masih dihitung ke sesi sampai sekian menit setelah jam selesai (status Terlambat).
     */
    public const int LATE_WINDOW_MINUTES = 60;

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

    /**
     * @return BelongsToMany<Crew, $this>
     */
    public function crews(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class, 'broadcast_session_crew');
    }

    /**
     * Menyimpan crew terpilih (id ganda disatukan). Crew yang sudah ditugaskan
     * tetapi tak lagi berhak (canJoinSession) dipertahankan.
     *
     * @param  array<int, int|string>  $crewIds
     */
    public function syncCrews(array $crewIds): void
    {
        $dipertahankan = $this->crews()
            ->whereNotIn('crews.id', Crew::canJoinSession()->select('crews.id'))
            ->pluck('crews.id');

        $this->crews()->sync(
            collect($crewIds)->map(fn ($id): int => (int) $id)->merge($dipertahankan)->unique()->values()->all()
        );
    }

    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->tanggal->toDateString().' '.$this->jam_mulai);
    }

    public function endsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->tanggal->toDateString().' '.$this->jam_selesai);
    }

    /**
     * Hadir bila tap paling lambat di jam selesai; sesudahnya Terlambat.
     */
    public function statusAt(CarbonInterface $at): string
    {
        return $at->greaterThan($this->endsAt()) ? 'Terlambat' : 'Hadir';
    }

    /**
     * Sesi hari ini yang memuat crew dan yang jendela tap-nya mencakup $at:
     * dari jam mulai sampai LATE_WINDOW_MINUTES setelah jam selesai. Sebelum jam
     * mulai berarti tamu. Bila ada beberapa, sesi yang sedang berjalan (Hadir)
     * didahulukan atas yang akan Terlambat, lalu dipilih jam mulai terdekat.
     * Null berarti tamu.
     */
    public static function findForTap(?Crew $crew, CarbonInterface $at): ?self
    {
        if ($crew === null) {
            return null;
        }

        return static::query()
            ->whereDate('tanggal', $at->toDateString())
            ->whereHas('crews', fn ($query) => $query->whereKey($crew->getKey()))
            ->get()
            ->filter(fn (self $session): bool => $at->betweenIncluded(
                $session->startsAt(),
                $session->endsAt()->addMinutes(self::LATE_WINDOW_MINUTES),
            ))
            ->sortBy([
                fn (self $a, self $b): int => $at->greaterThan($a->endsAt()) <=> $at->greaterThan($b->endsAt()),
                fn (self $a, self $b): int => abs($a->startsAt()->diffInSeconds($at)) <=> abs($b->startsAt()->diffInSeconds($at)),
            ])
            ->first();
    }
}
