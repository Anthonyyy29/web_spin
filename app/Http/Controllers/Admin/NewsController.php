<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsController extends ImageCrudController
{
    protected string $uploadFolder = 'news';

    protected int $maxImageKb = 5012;

    protected function modelClass(): string
    {
        return News::class;
    }

    protected function auditName(): string
    {
        return 'News';
    }

    protected function fieldRules(): array
    {
        return [
            'title' => ['required', 'max:255'],
            'category' => ['required', Rule::in(News::CATEGORIES)],
            'description' => ['required'],
            'video' => ['nullable'],
        ];
    }

    protected function prepare(array $data): array
    {
        $data['video'] = News::extractInstagramReelId($data['video'] ?? null);

        return $data;
    }

    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'All');

        $news = News::query()
            ->when($filter !== '' && $filter !== 'All', fn ($query) => $query->where('category', $filter))
            ->latest('updated_at')
            ->paginate(8, pageName: 'newspage')
            ->withQueryString();

        return view('admin.news', [
            'navmenu' => 'News',
            'categoryList' => News::CATEGORIES,
            'news' => $news->through(fn (News $article): array => [...$article->toArray(), 'created_at' => $article->created_at->format('d-M-Y')]),
            'pager' => $news,
            'activefilter' => $filter === '' ? 'All' : $filter,
        ]);
    }

    public function filter(string $category): RedirectResponse
    {
        return redirect('/dashboard/news?filter='.urlencode($category));
    }
}
