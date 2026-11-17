<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SharedDatabaseController;
use App\Http\Controllers\ProjectUnitLocatorController;
use App\Http\Controllers\FrontendController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// FRONTEND ROUTES (Dành cho Người dùng & Khách)
// ==========================================
Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/api/projects/{projectId}/units', [FrontendController::class, 'getProjectUnits'])->name('frontend.project.units');
Route::post('/api/consultation', [FrontendController::class, 'submitConsultation'])->name('frontend.consultation');

// Cập nhật thông tin cá nhân & Đổi mật khẩu (Người dùng đăng nhập)
Route::middleware(['auth'])->group(function () {
    Route::post('/profile/update', [FrontendController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [FrontendController::class, 'changePassword'])->name('profile.password');
});

// ==========================================
// AUTHENTICATION ROUTES
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// PUBLIC MEDIA & IMAGE PROXY
// Cho phép cả khách & người dùng xem ảnh chỉ căn, mặt bằng, thumbnail
// ==========================================
Route::get('/unit-locators/project-image/{filename}', [ProjectUnitLocatorController::class, 'projectImageProxy'])->name('admin.unit-locators.project-image');
Route::get('/unit-locators/media/{path}', [ProjectUnitLocatorController::class, 'serveMedia'])->where('path', '.*')->name('admin.unit-locators.media');

// ==========================================
// ADMIN PROTECTED ROUTES
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management & Permissions
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/data', [AdminController::class, 'getUserData'])->name('users.data');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Settings & Utilities
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');

    // Shared Databases Configuration
    Route::get('/databases', [SharedDatabaseController::class, 'index'])->name('databases.index');
    Route::post('/databases', [SharedDatabaseController::class, 'store'])->name('databases.store');
    Route::put('/databases/{id}', [SharedDatabaseController::class, 'update'])->name('databases.update');
    Route::delete('/databases/{id}', [SharedDatabaseController::class, 'destroy'])->name('databases.destroy');
    Route::post('/databases/{id}/toggle', [SharedDatabaseController::class, 'toggleStatus'])->name('databases.toggle');
    Route::post('/databases/test-connection', [SharedDatabaseController::class, 'testConnection'])->name('databases.test');
    Route::post('/databases/fetch-tables', [SharedDatabaseController::class, 'fetchTables'])->name('databases.fetch-tables');

    // Unit Locators (Chỉ căn Bất động sản)
    Route::get('/unit-locators', [ProjectUnitLocatorController::class, 'index'])->name('unit-locators.index');
    Route::get('/unit-locators/project/{projectId}', [ProjectUnitLocatorController::class, 'projectUnits'])->name('unit-locators.project');
    Route::get('/unit-locators/project/{projectId}/create', [ProjectUnitLocatorController::class, 'create'])->name('unit-locators.create');
    Route::post('/unit-locators/project/{projectId}/import-excel', [ProjectUnitLocatorController::class, 'importExcel'])->name('unit-locators.import-excel');
    Route::post('/unit-locators/project/{projectId}/bulk-update', [ProjectUnitLocatorController::class, 'bulkUpdate'])->name('unit-locators.bulk-update');
    Route::post('/unit-locators/project/{projectId}/bulk-delete', [ProjectUnitLocatorController::class, 'bulkDelete'])->name('unit-locators.bulk-delete');
    Route::post('/unit-locators', [ProjectUnitLocatorController::class, 'store'])->name('unit-locators.store');
    Route::post('/unit-locators/floorplans', [ProjectUnitLocatorController::class, 'storeFloorplan'])->name('unit-locators.floorplan.store');
    Route::delete('/unit-locators/floorplans/{id}', [ProjectUnitLocatorController::class, 'destroyFloorplan'])->name('unit-locators.floorplan.destroy');
    Route::post('/unit-locators/templates', [ProjectUnitLocatorController::class, 'storeTemplate'])->name('unit-locators.templates.store');
    Route::put('/unit-locators/templates/{id}', [ProjectUnitLocatorController::class, 'updateTemplate'])->name('unit-locators.templates.update');
    Route::delete('/unit-locators/templates/{id}', [ProjectUnitLocatorController::class, 'destroyTemplate'])->name('unit-locators.templates.destroy');
    Route::post('/unit-locators/perspectives', [ProjectUnitLocatorController::class, 'storePerspective'])->name('unit-locators.perspectives.store');
    Route::delete('/unit-locators/perspectives/{id}', [ProjectUnitLocatorController::class, 'destroyPerspective'])->name('unit-locators.perspectives.destroy');
    Route::get('/unit-locators/{id}', [ProjectUnitLocatorController::class, 'show'])->name('unit-locators.show');
    Route::put('/unit-locators/{id}', [ProjectUnitLocatorController::class, 'update'])->name('unit-locators.update');
    Route::delete('/unit-locators/{id}', [ProjectUnitLocatorController::class, 'destroy'])->name('unit-locators.destroy');
    Route::post('/unit-locators/{id}/status', [ProjectUnitLocatorController::class, 'updateStatus'])->name('unit-locators.status');
});
