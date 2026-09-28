 <?php

use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminWebsiteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DinasAppearanceController;
use App\Http\Controllers\DinasMediaController;
use App\Http\Controllers\DinasPageController;
use App\Http\Controllers\DinasPostController;
use App\Http\Controllers\DinasSettingController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\TemplateBuilderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('throttle:public-site');

// Website Publik (Read-Only) — Render dinamis berdasarkan tenant dengan proteksi throttle
Route::middleware('throttle:public-site')->group(function () {
    Route::get('/site/{identifier}', [PublicWebsiteController::class, 'show'])->name('site.show');
    Route::get('/site/{identifier}/page/{slug}', [PublicWebsiteController::class, 'showPage'])->name('site.page.show');
    Route::get('/site/{identifier}/post/{slug}', [PublicWebsiteController::class, 'showPost'])->name('site.post.show');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('throttle:public-site');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
});

Route::middleware(['auth', 'throttle:cms-read'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/admin/dashboard', function () {
        if (auth()->user()?->role === 'admin_dinas') {
            return view('dinas.dashboard');
        }

        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/dinas/dashboard', function () {
        return view('dinas.dashboard');
    })->name('dinas.dashboard');

    // Posts Management — Admin Kedinasan only (PRD.md 4.4, ROLES_RBAC Rule 05)
    Route::get('/dinas/posts', [DinasPostController::class, 'index'])
        ->name('dinas.posts.index');
    Route::get('/dinas/posts/create', [DinasPostController::class, 'create'])
        ->name('dinas.posts.create');
    Route::post('/dinas/posts', [DinasPostController::class, 'store'])
        ->name('dinas.posts.store')->middleware('throttle:cms-write');
    Route::get('/dinas/posts/{post}/edit', [DinasPostController::class, 'edit'])
        ->name('dinas.posts.edit');
    Route::put('/dinas/posts/{post}', [DinasPostController::class, 'update'])
        ->name('dinas.posts.update')->middleware('throttle:cms-write');
    Route::delete('/dinas/posts/{post}', [DinasPostController::class, 'destroy'])
        ->name('dinas.posts.destroy')->middleware('throttle:cms-write');

    // Media Management (Dokumen & Gambar) — Admin Kedinasan only (PRD.md 4.4, ROLES_RBAC Rule 05)
    Route::get('/dinas/media', [DinasMediaController::class, 'index'])
        ->name('dinas.media.index');
    Route::post('/dinas/media', [DinasMediaController::class, 'store'])
        ->name('dinas.media.store')->middleware('throttle:cms-write');
    Route::delete('/dinas/media/{media}', [DinasMediaController::class, 'destroy'])
        ->name('dinas.media.destroy')->middleware('throttle:cms-write');

    // Pages Management (Static Content) — Admin Kedinasan only (PRD.md 4.5, ROLES_RBAC Rule 05)
    Route::get('/dinas/pages', [DinasPageController::class, 'index'])
        ->name('dinas.pages.index');
    Route::get('/dinas/pages/create', [DinasPageController::class, 'create'])
        ->name('dinas.pages.create');
    Route::post('/dinas/pages', [DinasPageController::class, 'store'])
        ->name('dinas.pages.store')->middleware('throttle:cms-write');
    Route::get('/dinas/pages/{page}/edit', [DinasPageController::class, 'edit'])
        ->name('dinas.pages.edit');
    Route::put('/dinas/pages/{page}', [DinasPageController::class, 'update'])
        ->name('dinas.pages.update')->middleware('throttle:cms-write');
    Route::delete('/dinas/pages/{page}', [DinasPageController::class, 'destroy'])
        ->name('dinas.pages.destroy')->middleware('throttle:cms-write');

    // Appearance Slot Management — Admin Kedinasan only (PRD.md 4.6 & ROLES_RBAC 5.6)
    Route::get('/dinas/appearance', [DinasAppearanceController::class, 'edit'])
        ->name('dinas.appearance.edit');
    Route::put('/dinas/appearance', [DinasAppearanceController::class, 'update'])
        ->name('dinas.appearance.update')->middleware('throttle:cms-write');

    // Website Settings — Admin Kedinasan only (PRD.md 4.2 & ROLES_RBAC 5.2)
    Route::get('/dinas/settings', [DinasSettingController::class, 'edit'])
        ->name('dinas.settings.edit');
    Route::put('/dinas/settings', [DinasSettingController::class, 'update'])
        ->name('dinas.settings.update')->middleware('throttle:cms-write');

    // Super Admin Routes (PRD 4.2 & ROLES_RBAC 4.3)
    Route::middleware('super_admin')->group(function () {
        Route::get('/admin/templates/{template}/builder', [TemplateBuilderController::class, 'show'])
            ->name('admin.templates.builder');
        Route::get('/admin/templates/{template}/preview', [TemplateBuilderController::class, 'preview'])
            ->name('admin.templates.preview');
        Route::put('/admin/templates/{template}/builder', [TemplateBuilderController::class, 'update'])
            ->name('admin.templates.builder.update')->middleware('throttle:cms-write');

        // User Management
        Route::get('/admin/users', [AdminUserController::class, 'index'])
            ->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])
            ->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])
            ->name('admin.users.store')->middleware('throttle:cms-write');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])
            ->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])
            ->name('admin.users.update')->middleware('throttle:cms-write');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])
            ->name('admin.users.destroy')->middleware('throttle:cms-write');

        // Website Dinas Management (Super Admin only)
        Route::get('/admin/websites', [AdminWebsiteController::class, 'index'])
            ->name('admin.websites.index');
        Route::get('/admin/websites/create', [AdminWebsiteController::class, 'create'])
            ->name('admin.websites.create');
        Route::post('/admin/websites', [AdminWebsiteController::class, 'store'])
            ->name('admin.websites.store')->middleware('throttle:cms-write');
        Route::get('/admin/websites/{website}/edit', [AdminWebsiteController::class, 'edit'])
            ->name('admin.websites.edit');
        Route::put('/admin/websites/{website}', [AdminWebsiteController::class, 'update'])
            ->name('admin.websites.update')->middleware('throttle:cms-write');
        Route::delete('/admin/websites/{website}', [AdminWebsiteController::class, 'destroy'])
            ->name('admin.websites.destroy')->middleware('throttle:cms-write');

        // System Configuration Settings
        Route::get('/admin/settings', [AdminSettingController::class, 'edit'])
            ->name('admin.settings.edit');
        Route::put('/admin/settings', [AdminSettingController::class, 'update'])
            ->name('admin.settings.update')->middleware('throttle:cms-write');

        // Platform Activity Logs (Super Admin only)
        Route::get('/admin/activities', [AdminActivityController::class, 'index'])
            ->name('admin.activities.index');
    });
});
