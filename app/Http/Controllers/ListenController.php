<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ListenController extends Controller
{
    public function index(): View
    {
        return $this->listing(Content::all(), Episode::latest()->limit(3)->get(), '');
    }

    /**
     * Cari episode menurut judul; bila tidak ada hasil kembali ke /listen.
     */
    public function searchEpisode(Request $request): View|RedirectResponse
    {
        $query = (string) $request->query('query');

        if ($query === '') {
            return redirect('/listen');
        }

        $episodes = Episode::where('title', 'like', '%'.$query.'%')->get();

        if ($episodes->isEmpty()) {
            return redirect('/listen')->with('error', 'Podcast not found');
        }

        return $this->listing(collect(), $episodes, 'podcast');
    }

    public function openContent(Content $content): View
    {
        return view('listen-segment', [
            'content' => $content,
            'episodes' => $content->episodes()->orderByDesc('id')->get(),
        ]);
    }

    /**
     * Daftar judul episode per segmen, untuk saran pencarian.
     */
    public function podcastList(): JsonResponse
    {
        $grouped = [];

        foreach (Episode::all() as $episode) {
            $segment = Content::find($episode->id_content);

            $grouped[$segment?->title ?? ''][] = [
                'label' => $episode->title,
                'url' => 'listen/search?query='.$episode->title,
            ];
        }

        return response()->json($grouped);
    }

    /**
     * @param  Collection<int, Content>  $contents
     * @param  Collection<int, Episode>  $episodes
     */
    private function listing($contents, $episodes, string $searchMode): View
    {
        return view('listen', [
            'contents' => $contents,
            'last_episodes' => $episodes,
            'search_mode' => $searchMode,
        ]);
    }
}
