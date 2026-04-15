<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SwitchOrganizationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'org.active'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Organisation switch
    Route::post('/organizations/switch', SwitchOrganizationController::class)->name('organizations.switch');

    // Organisation CRUD
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization:slug}/settings', [OrganizationController::class, 'settings'])->name('organizations.settings');
    Route::patch('/organizations/{organization:slug}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{organization:slug}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

    // Members
    Route::get('/organizations/{organization:slug}/members', [OrganizationMemberController::class, 'index'])->name('organizations.members.index');
    Route::post('/organizations/{organization:slug}/members/invite', [OrganizationMemberController::class, 'invite'])->name('organizations.members.invite');
    Route::patch('/organizations/{organization:slug}/members/{user}/role', [OrganizationMemberController::class, 'updateRole'])->name('organizations.members.updateRole');
    Route::delete('/organizations/{organization:slug}/members/{user}', [OrganizationMemberController::class, 'remove'])->name('organizations.members.remove');

    // Invitations (public accept link)
    Route::get('/invitations/{token}/accept', [OrganizationMemberController::class, 'acceptInvitation'])->name('invitations.accept');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');

    // Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

// Stripe webhook (no auth)
Route::post('/stripe/webhook', [BillingController::class, 'handleWebhook'])->name('cashier.webhook');

require __DIR__ . '/auth.php';
