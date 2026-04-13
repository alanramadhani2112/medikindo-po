<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? url()->previous());
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count()
        ]);
    }
}
