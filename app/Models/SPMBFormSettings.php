<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPMBFormSettings extends Model
{
    protected $table = 's_p_m_b_form_settings';
    
    protected $fillable = [
        'field_name',
        'field_label',
        'field_type',
        'is_required',
        'is_active',
        'show_in_print',
        'field_options',
        'field_placeholder',
        'field_help_text',
        'field_order',
        'field_section'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'show_in_print' => 'boolean',
        'field_options' => 'array'
    ];

    /**
     * Get active form fields by section
     */
    public static function getActiveFieldsBySection($section = null)
    {
        $query = self::where('is_active', true);
        
        if ($section) {
            $query->where('field_section', $section);
        }
        
        $fields = $query->orderBy('field_section')->orderBy('field_order')->get();
        return $fields->groupBy('field_section');
    }

    /**
     * Get all active form fields
     */
    public static function getActiveFields()
    {
        return self::where('is_active', true)->orderBy('field_section')->orderBy('field_order')->get();
    }

    /**
     * Get form fields grouped by section
     */
    public static function getFieldsBySection()
    {
        $fields = self::getActiveFields();
        return $fields->groupBy('field_section');
    }

    /**
     * Get fields that should be shown in print
     */
    public static function getPrintFields()
    {
        return self::where('is_active', true)
                   ->where('show_in_print', true)
                   ->orderBy('field_section')
                   ->orderBy('field_order')
                   ->get();
    }

    /**
     * Get print fields grouped by section
     */
    public static function getPrintFieldsBySection()
    {
        $fields = self::getPrintFields();
        return $fields->groupBy('field_section');
    }

    /**
     * Check if field is required
     */
    public function isRequired()
    {
        return $this->is_required;
    }

    /**
     * Get field options for select
     */
    public function getOptions()
    {
        return $this->field_options ?? [];
    }

    /**
     * Get section label
     */
    public function getSectionLabel()
    {
        $sections = [
            'personal' => 'Data Pribadi',
            'parent' => 'Data Orang Tua',
            'academic' => 'Data Akademik'
        ];

        return $sections[$this->field_section] ?? ucfirst($this->field_section);
    }
}
