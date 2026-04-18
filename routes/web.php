<?php
// routes/web.php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemandeAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuiviController;
use Illuminate\Support\Facades\Route;

// ─── Page d'accueil ───────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('home');

// ─── Suivi public par code de demande ────────────────────────────
Route::get('/suivi',  [SuiviController::class, 'index'])->name('suivi');
Route::post('/suivi', [SuiviController::class, 'chercher'])->name('suivi.chercher');

// ─── Auth (Breeze) ────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ─── Zone Utilisateur (authentifié + email vérifié) ───────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard utilisateur
    Route::get('/dashboard', fn() => view('user.dashboard'))->name('dashboard');

    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // Demandes passeport
    Route::resource('demandes', DemandeController::class);
    Route::post('/demandes/{demande}/paiement',   [DemandeController::class, 'initierPaiement'])->name('demandes.paiement');
    Route::post('/demandes/{demande}/documents',  [DemandeController::class, 'uploadDocument'])->name('demandes.document.upload');
    Route::get('/demandes/{demande}/recipisse',   [DemandeController::class, 'recipisse'])->name('demandes.recipisse');
    Route::get('/documents/{document}/download',  [DemandeController::class, 'telechargerDocument'])->name('demandes.document.download');

    // Paiement
    Route::get('/paiement/simulation/{token}',  [PaiementController::class, 'pageSimulation'])->name('paiement.simulation');
    Route::post('/paiement/simulation/confirmer', [PaiementController::class, 'confirmerSimulation'])->name('paiement.simulation.confirmer');
    Route::get('/paiement/succes/{reference}',  [PaiementController::class, 'succes'])->name('paiement.succes');
    Route::get('/paiement/annuler/{reference}', [PaiementController::class, 'annuler'])->name('paiement.annuler');

    // Notifications
    Route::get('/notifications',  [ProfileController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/lire', [ProfileController::class, 'marquerNotificationLue'])->name('notifications.lire');
    Route::post('/notifications/lire-tout', [ProfileController::class, 'marquerToutesLues'])->name('notifications.lire-tout');
});

// ─── Webhook PayTech (pas d'auth) ────────────────────────────────
Route::post('/paiement/ipn', [PaiementController::class, 'ipn'])
     ->name('paiement.ipn')
     ->withoutMiddleware(['web']);

// ─── Zone Agent (tableau de bord agent) ──────────────────────────
Route::middleware(['auth', 'staff'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/', [AgentDashboardController::class, 'index'])->name('dashboard');
});

// ─── Routes staff: traitement des demandes (admin + agent) ────────
Route::middleware(['auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {

    // Demandes
    Route::get('/demandes',                    [DemandeAdminController::class, 'index'])->name('demandes.index');
    Route::get('/demandes/{demande}',          [DemandeAdminController::class, 'show'])->name('demandes.show');
    Route::post('/demandes/{demande}/statut',  [DemandeAdminController::class, 'changerStatut'])->name('demandes.statut');
    Route::get('/demandes/{demande}/pdf',      [DemandeAdminController::class, 'exporterPDF'])->name('demandes.pdf');

    // Documents
    Route::post('/documents/{document}/valider', [DemandeAdminController::class, 'validerDocument'])->name('documents.valider');
    Route::post('/documents/{document}/rejeter', [DemandeAdminController::class, 'rejeterDocument'])->name('documents.rejeter');
    Route::get('/documents/{document}/download', [DemandeAdminController::class, 'telechargerDocument'])->name('documents.download');
});

// ─── Zone Admin (admin + super_admin seulement) ───────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Utilisateurs
    Route::get('/users',                   [UserAdminController::class, 'index'])->name('users.index');
    Route::get('/users/{user}',            [UserAdminController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle',    [UserAdminController::class, 'toggleActif'])->name('users.toggle');

    // Agents (admin only)
    Route::get('/agents',                         [AgentController::class, 'index'])->name('agents.index');
    Route::post('/agents',                        [AgentController::class, 'store'])->name('agents.store');
    Route::post('/agents/{agent}/toggle',         [AgentController::class, 'toggleActif'])->name('agents.toggle');
    Route::post('/agents/{agent}/reset-password', [AgentController::class, 'resetPassword'])->name('agents.reset-password');
    Route::delete('/agents/{agent}',              [AgentController::class, 'destroy'])->name('agents.destroy');
});
