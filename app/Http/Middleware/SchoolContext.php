<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;
use App\Models\Foundation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SchoolContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSchoolId = session('current_school_id');
            $foundationId = session('foundation_id');

            // Superadmin & Admin Yayasan: Set foundation if not set
            if (in_array($user->role, ['superadmin', 'admin_yayasan']) && !$foundationId) {
                $firstFoundation = Foundation::first();
                if ($firstFoundation) {
                    session(['foundation_id' => $firstFoundation->id]);
                    $foundationId = $firstFoundation->id;
                }
            }

            // Set school context if not set
            if (!$currentSchoolId) {
                $schoolToSet = null;

                if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                    // Superadmin/Admin Yayasan: get first active school in current foundation
                    if ($foundationId) {
                        $schoolToSet = School::where('foundation_id', $foundationId)
                            ->where('status', 'active')
                            ->first();
                    }
                } else {
                    // Regular users: get their first accessible school
                    if ($foundationId) {
                        $userSchool = DB::table('user_schools')
                            ->where('user_id', $user->id)
                            ->join('schools', 'user_schools.school_id', '=', 'schools.id')
                            ->where('schools.foundation_id', $foundationId)
                            ->where('schools.status', 'active')
                            ->select('schools.id')
                            ->first();
                        
                        if ($userSchool) {
                            $schoolToSet = School::find($userSchool->id);
                        }
                    }
                }

                if ($schoolToSet) {
                    session(['current_school_id' => $schoolToSet->id]);
                }
            }
        }

        return $next($request);
    }
}





