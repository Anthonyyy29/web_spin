<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PodcastController extends ImageCrudController
{
    protected string $uploadFolder = 'program';

    protected function modelClass(): string
    {
        return Content::class;
    }

    protected function auditName(): string
    {
        return 'Podcast';
    }

    protected function fieldRules(): array
    {
        return [
            'title' => ['required', 'max:255'],
            'description' => ['required'],
            'jam_mulai_default' => ['nullable', 'date_format:H:i', 'required_with:jam_selesai_default'],
            'jam_selesai_default' => ['nullable', 'date_format:H:i', 'required_with:jam_mulai_default', 'after:jam_mulai_default'],
            'crew_ids' => ['array'],
            'crew_ids.*' => ['integer', Rule::exists('crews', 'id')->where(
                fn ($query) => $query->whereIn('role', Crew::SESSION_ROLES)->orWhereIn('extra_role', Crew::SESSION_ROLES)
            )],
        ];
    }

    protected function prepare(array $data): array
    {
        return Arr::except($data, 'crew_ids');
    }

    /**
     * Sinkronkan crew yang layak program (jabatan utama atau tambahan termasuk tiga jabatan program);
     * crew yang tidak layak tetap dipertahankan. Id ganda dari dua dropdown disimpan sekali.
     */
    protected function afterSave(Model $model, Request $request): void
    {
        $dipertahankan = $model->crews()->whereNotIn('crews.id', Crew::canJoinSession()->select('crews.id'))->pluck('crews.id');

        $crewIds = array_map('intval', array_values((array) $request->input('crew_ids', [])));

        $model->crews()->sync($dipertahankan->merge($crewIds)->unique()->all());
    }

    /**
     * Tolak hapus program yang masih punya sesi, karena sesi ikut terhapus dan absensinya kehilangan rujukan.
     */
    public function delete(int $id): RedirectResponse
    {
        if (BroadcastSession::where('content_id', $id)->exists()) {
            return back()->with('error', 'Program masih punya sesi siaran. Hapus sesinya dulu di menu Sesi.');
        }

        return parent::delete($id);
    }

    public function index(): View
    {
        $contents = Content::with('crews')->latest()->paginate(8, pageName: 'podcastpage');

        $crewProgram = Crew::canJoinSession()->orderBy('name')->get();
        $crewByRole = collect(Crew::SESSION_ROLES)->mapWithKeys(fn (string $role) => [
            $role => $crewProgram->filter(fn (Crew $crew) => $crew->role === $role || $crew->extra_role === $role)->values(),
        ]);

        return view('admin.podcast', [
            'navmenu' => 'Podcast',
            'contents' => $contents,
            'pager' => $contents,
            'crewByRole' => $crewByRole,
        ]);
    }

    public function openPodcast(int $contentId): RedirectResponse
    {
        return redirect('/dashboard/podcast')->with('openContentId', $contentId);
    }

    public function openEpisode(int $contentId): View
    {
        $episodes = Episode::where('id_content', $contentId)->latest()->paginate(3, pageName: 'episodepage');

        return view('admin.podcast-episode', [
            'navmenu' => 'Episode',
            'episodes' => $episodes,
            'pager' => $episodes,
            'id_content' => $contentId,
            'podcast' => Content::findOrFail($contentId),
        ]);
    }

    /**
     * Simpan episode dari dashboard, atau dari API (bila ada content_name) yang mencari segmen menurut nama.
     */
    public function saveEpisode(Request $request): RedirectResponse|JsonResponse
    {
        $isApi = $request->has('content_name');

        if ($isApi) {
            $segment = Content::whereRaw('LOWER(title) like ?', ['%'.strtolower((string) $request->input('content_name')).'%'])->first();

            if ($segment === null) {
                return response()->json(['status' => false, 'message' => 'Konten tidak ditemukan.'], 404);
            }

            $request->merge(['id_content' => $segment->id]);
        }

        $validator = Validator::make($request->all(), [
            'id_content' => ['required', 'exists:contents,id'],
            'title' => ['required', 'max:255'],
            'description' => ['required'],
            'audio' => ['required'],
        ]);

        if ($validator->fails()) {
            return $isApi
                ? response()->json(['status' => false, 'message' => $validator->errors()->first()], 422)
                : back()->with('error', $validator->errors()->first());
        }

        $data = $validator->validated();
        $data['audio'] = 'https://open.spotify.com/embed/episode/'.Episode::extractSpotifyEpisodeId($data['audio']);

        $id = $request->input('id');
        $episode = $id ? Episode::find($id) : new Episode;

        if ($episode === null) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $episode->fill($data)->save();
        AuditLog::record('Podcast', $id ? 'UPDATE' : 'CREATE');

        return $isApi
            ? response()->json(['status' => true, 'message' => 'Upload sukses'])
            : back()->with('success', 'Data berhasil disimpan.');
    }

    public function deleteEpisode(int $id): RedirectResponse
    {
        if (Episode::find($id)?->delete()) {
            AuditLog::record('Podcast', 'DELETE');
        }

        return back()->with('success', 'Data berhasil dihapus.');
    }

    /**
     * API: apakah segmen sudah punya episode yang dibuat hari ini.
     */
    public function checkLatestPodcast(string $segmentName): JsonResponse
    {
        $segment = Content::where('title', $segmentName)->first();

        if ($segment === null) {
            return response()->json(['status' => false, 'message' => 'Segment tidak ditemukan.']);
        }

        $latest = Episode::where('id_content', $segment->id)->latest()->first();

        return $latest?->created_at->isToday()
            ? response()->json(['status' => true, 'message' => 'Ada podcast terbaru hari ini.', 'data' => $latest])
            : response()->json(['status' => false, 'message' => 'Tidak ada podcast terbaru hari ini.']);
    }
}
