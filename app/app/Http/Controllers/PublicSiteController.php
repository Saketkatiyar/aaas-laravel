<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'slides' => DB::table('oc_slider')->orderBy('sort')->get(),
            'news' => DB::table('oc_news')->latest('id')->limit(8)->get(),
        ]);
    }

    public function page(string $page): View
    {
        // Awards/elections remain in the legacy database but are dormant for this release.
        $pages = ['aboutus' => 79, 'executive_council' => 80, 'membership' => 81, 'conference' => 82, 'contactus' => 83, 'publication' => 84];

        abort_unless(isset($pages[$page]), 404);

        $content = DB::table('oc_information_description')->where('information_id', $pages[$page])->where('language_id', 1)->first();
        return view('public.page', ['title' => $content?->title ?? ucwords(str_replace('_', ' ', $page)), 'content' => $content?->description ?? 'Content will be available soon.', 'pageKey' => $page]);
    }

    public function news(): View
    {
        return view('public.news', ['items' => DB::table('oc_news')->latest('id')->get()]);
    }

    public function showNews(int $id): View
    {
        return view('public.news-item', ['item' => DB::table('oc_news')->where('id', $id)->firstOrFail()]);
    }

    public function downloads(): View
    {
        return view('public.downloads', ['downloads' => DB::table('oc_downloads')->latest('id')->get()]);
    }

    public function books(): View { return view('public.collection', ['title' => 'Books', 'items' => DB::table('oc_books')->latest('id')->get(), 'kind' => 'books']); }
    public function reports(): View { return view('public.collection', ['title' => 'Reports', 'items' => DB::table('oc_report')->latest('id')->get(), 'kind' => 'reports']); }
    public function publicationAbstracts(): View { return view('public.collection', ['title' => 'Publication Abstracts', 'items' => DB::table('oc_abstract')->latest('id')->get(), 'kind' => 'abstracts']); }
    public function book(int $id): View { return view('public.book', ['book' => DB::table('oc_books')->where('id', $id)->firstOrFail()]); }
    public function galleries(): View { return view('public.galleries', ['galleries' => DB::table('oc_gallery')->latest('id')->get()]); }
    public function gallery(int $id): View { return view('public.gallery', ['gallery' => DB::table('oc_gallery')->where('id', $id)->firstOrFail(), 'images' => DB::table('oc_gallery_images')->where('gallery_id', $id)->orderBy('sort')->get()]); }
    public function videos(): View { return view('public.videos', ['videos' => DB::table('oc_videos')->orderBy('sort')->get()]); }
    public function search(): View
    {
        $term = trim((string) request('q'));
        $results = collect();
        if ($term !== '') {
            $results = DB::table('oc_information_description')->where('language_id', 1)->where('information_id', '!=', 85)->where(fn ($query) => $query->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))->get(['information_id as id', 'title', 'description'])->map(fn ($row) => (object) ['title' => $row->title, 'excerpt' => \App\Support\ContentHtml::preview($row->description), 'url' => match ((int) $row->id) {79 => '/aboutus',80 => '/executive_council',81 => '/membership',82 => '/conference',83 => '/contactus',84 => '/publication',default => '/'}]);
        }
        return view('public.search', compact('term', 'results'));
    }
}
