<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssignmentNotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        
        // O'qilmagan bildirishnomalar soni
        $unreadCount = auth()->user()->unreadNotifications()->count();
        
        return view('admin.assignments.natification', compact('notifications', 'unreadCount'));
    }
    
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        
        return redirect()->back()->with('success', 'Barcha bildirishnomalar o\'qilgan deb belgilandi');
    }
    
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Bildirishnoma o\'qilgan deb belgilandi');
    }
    
    public function showUnread()
    {
        $notifications = auth()->user()->unreadNotifications()->latest()->paginate(20);
        $unreadCount = auth()->user()->unreadNotifications()->count();
        
        return view('admin.assignments.natification', compact('notifications', 'unreadCount'));
    }
}