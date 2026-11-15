<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\Content;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        return ['title' => ['required', 'max:255'], 'description' => ['required']];
    }

    public function index(): View
    {
        $contents = Content::latest()->paginate(8, pageName: 'podcastpage');

        return view('admin.podcast', ['navmenu' => 'Podcast', 'contents' => $contents, 'pager' => $contents]);
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
