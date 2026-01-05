<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictReportAccess
{
    public function handle(Request $request, Closure $next)
    {
        $restrictedEmails = config('admin.restricted_report_emails', []);
        
        if (Auth::check() && in_array(Auth::user()->email, $restrictedEmails)) {
            return redirect()->route('admin.attendance.index')
                ->with('error', 'Sizda hisobotlar bo\'limiga kirish huquqi yo\'q.');
        }

        return $next($request);
    }
}