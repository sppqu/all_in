<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SPMBFormSettings;

class SPMBFormSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFields = [
            // Data Pribadi
            [
                'field_name' => 'birth_date',
                'field_label' => 'Tanggal Lahir',
                'field_type' => 'date',
                'field_section' => 'personal',
                'is_required' => true,
                'is_active' => true,
                'show_in_print' => true,
                'field_placeholder' => 'Pilih tanggal lahir',
                'field_help_text' => 'Masukkan tanggal lahir sesuai dengan dokumen resmi',
                'field_order' => 1
            ],
            [
                'field_name' => 'birth_place',
                'field_label' => 'Tempat Lahir',
                'field_type' => 'text',
                'field_section' => 'personal',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Masukkan tempat lahir',
                'field_help_text' => 'Masukkan tempat lahir sesuai dengan dokumen resmi',
                'field_order' => 2
            ],
            [
                'field_name' => 'gender',
                'field_label' => 'Jenis Kelamin',
                'field_type' => 'select',
                'field_section' => 'personal',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Pilih jenis kelamin',
                'field_help_text' => 'Pilih jenis kelamin sesuai dengan dokumen resmi',
                'field_order' => 3,
                'field_options' => [
                    ['value' => 'male', 'label' => 'Laki-laki'],
                    ['value' => 'female', 'label' => 'Perempuan']
                ]
            ],
            [
                'field_name' => 'address',
                'field_label' => 'Alamat Lengkap',
                'field_type' => 'textarea',
                'field_section' => 'personal',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Masukkan alamat lengkap',
                'field_help_text' => 'Masukkan alamat lengkap tempat tinggal',
                'field_order' => 4
            ],
            [
                'field_name' => 'religion',
                'field_label' => 'Agama',
                'field_type' => 'select',
                'field_section' => 'personal',
                'is_required' => false,
                'is_active' => true,
                'field_placeholder' => 'Pilih agama',
                'field_help_text' => 'Pilih agama sesuai dengan dokumen resmi',
                'field_order' => 5,
                'field_options' => [
                    ['value' => 'islam', 'label' => 'Islam'],
                    ['value' => 'kristen', 'label' => 'Kristen'],
                    ['value' => 'katolik', 'label' => 'Katolik'],
                    ['value' => 'hindu', 'label' => 'Hindu'],
                    ['value' => 'buddha', 'label' => 'Buddha'],
                    ['value' => 'khonghucu', 'label' => 'Khonghucu']
                ]
            ],
            [
                'field_name' => 'nationality',
                'field_label' => 'Kewarganegaraan',
                'field_type' => 'text',
                'field_section' => 'personal',
                'is_required' => false,
                'is_active' => true,
                'field_placeholder' => 'Masukkan kewarganegaraan',
                'field_help_text' => 'Masukkan kewarganegaraan sesuai dengan dokumen resmi',
                'field_order' => 6
            ],

            // Data Orang Tua
            [
                'field_name' => 'parent_name',
                'field_label' => 'Nama Orang Tua',
                'field_type' => 'text',
                'field_section' => 'parent',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Masukkan nama orang tua',
                'field_help_text' => 'Masukkan nama lengkap orang tua',
                'field_order' => 1
            ],
            [
                'field_name' => 'parent_phone',
                'field_label' => 'No. HP Orang Tua',
                'field_type' => 'tel',
                'field_section' => 'parent',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Masukkan nomor HP orang tua',
                'field_help_text' => 'Masukkan nomor HP yang dapat dihubungi',
                'field_order' => 2
            ],
            [
                'field_name' => 'parent_occupation',
                'field_label' => 'Pekerjaan Orang Tua',
                'field_type' => 'text',
                'field_section' => 'parent',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Masukkan pekerjaan orang tua',
                'field_help_text' => 'Masukkan pekerjaan orang tua saat ini',
                'field_order' => 3
            ],
            [
                'field_name' => 'parent_address',
                'field_label' => 'Alamat Orang Tua',
                'field_type' => 'textarea',
                'field_section' => 'parent',
                'is_required' => false,
                'is_active' => true,
                'field_placeholder' => 'Masukkan alamat orang tua',
                'field_help_text' => 'Masukkan alamat orang tua jika berbeda dengan alamat pendaftar',
                'field_order' => 4
            ],

            // Data Akademik
            [
                'field_name' => 'school_origin',
                'field_label' => 'Asal Sekolah',
                'field_type' => 'text',
                'field_section' => 'academic',
                'is_required' => true,
                'is_active' => true,
                'field_placeholder' => 'Masukkan asal sekolah',
                'field_help_text' => 'Masukkan nama sekolah asal',
                'field_order' => 1
            ],
            [
                'field_name' => 'graduation_year',
                'field_label' => 'Tahun Lulus',
                'field_type' => 'number',
                'field_section' => 'academic',
                'is_required' => false,
                'is_active' => true,
                'field_placeholder' => 'Masukkan tahun lulus',
                'field_help_text' => 'Masukkan tahun lulus dari sekolah asal',
                'field_order' => 2
            ],
            [
                'field_name' => 'academic_achievement',
                'field_label' => 'Prestasi Akademik',
                'field_type' => 'textarea',
                'field_section' => 'academic',
                'is_required' => false,
                'is_active' => true,
                'field_placeholder' => 'Masukkan prestasi akademik',
                'field_help_text' => 'Masukkan prestasi akademik yang pernah diraih',
                'field_order' => 3
            ]
        ];

        foreach ($defaultFields as $field) {
            SPMBFormSettings::create($field);
        }
    }
}
