<?php

use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home']);
Route::get('/home', [PublicSiteController::class, 'home']);
Route::get('/news', [PublicSiteController::class, 'news']);
Route::get('/news/{id}', [PublicSiteController::class, 'showNews'])->whereNumber('id');
Route::get('/downloads', [PublicSiteController::class, 'downloads']);
Route::get('/publications/books', [PublicSiteController::class, 'books']);
Route::get('/publications/books/{id}', [PublicSiteController::class, 'book'])->whereNumber('id');
Route::get('/publications/reports', [PublicSiteController::class, 'reports']);
Route::get('/publications/abstracts', [PublicSiteController::class, 'publicationAbstracts']);
Route::get('/gallery', [PublicSiteController::class, 'galleries']);
Route::get('/gallery/{id}', [PublicSiteController::class, 'gallery'])->whereNumber('id');
Route::get('/videos', [PublicSiteController::class, 'videos']);
Route::get('/search', [PublicSiteController::class, 'search']);
Route::get('/login', [MemberController::class, 'loginForm']);
Route::post('/login', [MemberController::class, 'login']);
Route::get('/register', [MemberController::class, 'registerForm']);
Route::post('/register', [MemberController::class, 'register']);
Route::get('/account', [MemberController::class, 'account']);
Route::get('/account/profile', [MemberController::class, 'profile']);
Route::post('/account/profile', [MemberController::class, 'updateProfile']);
Route::get('/account/password', [MemberController::class, 'passwordForm']);
Route::post('/account/password', [MemberController::class, 'updatePassword']);
Route::post('/logout', [MemberController::class, 'logout']);
Route::get('/feedback', [MemberController::class, 'feedbackForm']);
Route::post('/feedback', [MemberController::class, 'feedback']);
Route::get('/index.php', function () {
    $id = request()->integer('news_id');

    abort_unless($id > 0 && request('route') === 'common/home/viewnews', 404);

    return app(PublicSiteController::class)->showNews($id);
});

foreach (['aboutus', 'executive_council', 'membership', 'publication', 'conference', 'aaasawards', 'contactus'] as $page) {
    Route::get("/{$page}", fn () => app(PublicSiteController::class)->page($page));
}

Route::get('/admin', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/conferences/{id}/registrations', [AdminController::class, 'conferenceRegistrations'])->name('admin.conference.registrations');
Route::get('/admin/conferences/{id}/abstracts', [AdminController::class, 'conferenceAbstracts'])->name('admin.conference.abstracts');
Route::get('/admin/conferences/{id}/download', [AdminController::class, 'conferenceDownload'])->name('admin.conference.download');
Route::get('/admin/conference-users/{id}', [AdminController::class, 'conferenceRegistrant'])->name('admin.conference.registrant');
Route::get('/admin/conference-users/{id}/abstracts', [AdminController::class, 'conferenceAbstractPreview'])->name('admin.conference.abstract.preview');
Route::get('/admin/{resource}', [AdminController::class, 'index'])->name('admin.resource.index');
Route::get('/admin/{resource}/create', [AdminController::class, 'create'])->name('admin.resource.create');
Route::post('/admin/{resource}', [AdminController::class, 'store'])->name('admin.resource.store');
Route::get('/admin/{resource}/{id}/edit', [AdminController::class, 'edit'])->name('admin.resource.edit');
Route::put('/admin/{resource}/{id}', [AdminController::class, 'update'])->name('admin.resource.update');
Route::delete('/admin/{resource}/{id}', [AdminController::class, 'destroy'])->name('admin.resource.destroy');
