<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications_app()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->map(fn($n) => [
                'id'      => $n->id,
                'type'    => $n->type,
                'titre'   => $n->titre,
                'message' => $n->message,
                'lu'      => $n->lu,
                'date'    => $n->created_at->diffForHumans(),
            ]),
            'non_lues' => $request->user()->notificationsNonLues()->count(),
        ]);
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json([
            'non_lues' => $request->user()->notificationsNonLues()->count(),
        ]);
    }

    public function marquerLu(Request $request, int $id): JsonResponse
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notif->marquerLu();
        return response()->json(['message' => 'Notification marquée comme lue.']);
    }
}

