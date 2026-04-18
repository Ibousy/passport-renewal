<?php
// routes/api.php
// API REST pour application mobile (authentification via Sanctum)

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DemandeApiController;
use App\Http\Controllers\Api\NotificationApiController;
use Illuminate\Support\Facades\Route;

// ─── Auth API ────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/login',    [AuthApiController::class, 'login']);

    // Zone protégée
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthApiController::class, 'logout']);
        Route::get('/auth/me',      [AuthApiController::class, 'me']);

        // Demandes
        Route::get('/demandes',               [DemandeApiController::class, 'index']);
        Route::post('/demandes',              [DemandeApiController::class, 'store']);
        Route::get('/demandes/{demande}',     [DemandeApiController::class, 'show']);
        Route::get('/demandes/{demande}/statut', [DemandeApiController::class, 'statut']);

        // Notifications
        Route::get('/notifications',          [NotificationApiController::class, 'index']);
        Route::get('/notifications/count',    [NotificationApiController::class, 'count']);
        Route::post('/notifications/{id}/lu', [NotificationApiController::class, 'marquerLu']);
    });
});

/*
EXEMPLE RÉPONSES API :

GET /api/v1/demandes
{
  "data": [
    {
      "id": 1,
      "numero_demande": "REF-2024-00001",
      "statut": "en_cours_traitement",
      "statut_label": "En cours de traitement",
      "statut_color": "primary",
      "type_passeport": "ordinaire",
      "montant_total": "25 000 XOF",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "total": 25
  }
}

POST /api/v1/auth/login
Body: { "email": "user@example.com", "password": "secret" }
Response: { "token": "1|abc...", "user": { ... } }
*/
