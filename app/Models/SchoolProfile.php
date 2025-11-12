<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Use School model instead
 * This model is kept for backward compatibility
 * The table 'school_profiles' has been renamed to 'schools'
 */
class SchoolProfile extends Model
{
    use HasFactory;

    protected $table = 'schools'; // Use the new table name

    protected $fillable = [
        'foundation_id',
        'jenjang',
        'nama_sekolah',
        'alamat',
        'no_telp',
        'logo_sekolah',
        'status',
    ];

    /**
     * Get the first school (for backward compatibility)
     * This replaces SchoolProfile::first() calls
     */
    public static function first()
    {
        // If user is logged in, try to get current school
        if (auth()->check() && function_exists('currentSchool')) {
            $currentSchool = currentSchool();
            if ($currentSchool) {
                return $currentSchool;
            }
        }

        // Fallback to first active school
        return School::where('status', 'active')->first() ?? School::first();
    }
}
