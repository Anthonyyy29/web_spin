<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news', [
            'categoryList' => News::CATEGORIES,
            'news' => $this->present(News::all()),
            'top_news' => $this->present(News::latest()->limit(5)->get()),
            'categorized_news' => collect(News::CATEGORIES)->mapWithKeys(
                fn (string $category): array => [$category => $this->present(News::where('category', $category)->latest()->limit(3)->get())],
            )->all(),
        ]);
    }

    public function openCategory(string $category): View
    {
        return view('news-category', [
            'categoryList' => News::CATEGORIES,
            'activeCategory' => $category,
            'news' => $this->present(News::where('category', $category)->latest()->get(), 20),
        ]);
    }

    public function openDetails(News $news): View
    {
        return view('news-details', [
            'categoryList' => News::CATEGORIES,
            'news' => $this->present(collect([$news]))->first(),
        ]);
    }

    /**
     * Bentuk data berita untuk tampilan publik: tanggal diformat dan deskripsi dipecah per baris.
     *
     * @param  Collection<int, News>  $newsList
     * @return Collection<int, array<string, mixed>>
     */
    private function present(Collection $newsList, int $maxWords = 0): Collection
    {
        return $newsList->map(fn (News $news): array => [
            ...$news->toArray(),
            'created_at' => $news->created_at->format('d-M-Y'),
            'description_row' => $news->descriptionRows($maxWords),
        ])->values();
    }
}
