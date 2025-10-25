<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first class
        $class = ClassModel::first();
        
        if ($class) {
            $students = [
                [
                    'student_nis' => '12345',
                    'student_nisn' => '1234567890',
                    'student_full_name' => 'Ahmad Fadillah',
                    'student_gender' => 'L',
                    'student_born_place' => 'Jakarta',
                    'student_born_date' => '2005-01-15',
                    'student_phone' => '08123456789',
                    'student_hobby' => 'Membaca',
                    'student_address' => 'Jl. Contoh No. 123, Jakarta',
                    'student_name_of_mother' => 'Siti Aminah',
                    'student_name_of_father' => 'Ahmad Hidayat',
                    'student_parent_phone' => '08123456788',
                    'class_class_id' => $class->class_id,
                    'student_status' => 1,
                ],
                [
                    'student_nis' => '12346',
                    'student_nisn' => '1234567891',
                    'student_full_name' => 'Siti Nurhaliza',
                    'student_gender' => 'P',
                    'student_born_place' => 'Surabaya',
                    'student_born_date' => '2005-02-10',
                    'student_phone' => '08123456790',
                    'student_hobby' => 'Menyanyi',
                    'student_address' => 'Jl. Contoh No. 234, Surabaya',
                    'student_name_of_mother' => 'Dewi Sartika',
                    'student_name_of_father' => 'Bambang Sutejo',
                    'student_parent_phone' => '08123456791',
                    'class_class_id' => $class->class_id,
                    'student_status' => 1,
                ],
                [
                    'student_nis' => '12347',
                    'student_nisn' => '1234567892',
                    'student_full_name' => 'Budi Santoso',
                    'student_gender' => 'L',
                    'student_born_place' => 'Bandung',
                    'student_born_date' => '2005-03-20',
                    'student_phone' => '08123456792',
                    'student_hobby' => 'Olahraga',
                    'student_address' => 'Jl. Contoh No. 456, Bandung',
                    'student_name_of_mother' => 'Rina Sari',
                    'student_name_of_father' => 'Budi Prasetyo',
                    'student_parent_phone' => '08123456793',
                    'class_class_id' => $class->class_id,
                    'student_status' => 1,
                ],
                [
                    'student_nis' => '12348',
                    'student_nisn' => '1234567893',
                    'student_full_name' => 'Dewi Sartika',
                    'student_gender' => 'P',
                    'student_born_place' => 'Semarang',
                    'student_born_date' => '2005-04-25',
                    'student_phone' => '08123456794',
                    'student_hobby' => 'Menari',
                    'student_address' => 'Jl. Contoh No. 567, Semarang',
                    'student_name_of_mother' => 'Sri Wahyuni',
                    'student_name_of_father' => 'Joko Widodo',
                    'student_parent_phone' => '08123456795',
                    'class_class_id' => $class->class_id,
                    'student_status' => 1,
                ],
                [
                    'student_nis' => '12349',
                    'student_nisn' => '1234567894',
                    'student_full_name' => 'Eko Prasetyo',
                    'student_gender' => 'L',
                    'student_born_place' => 'Yogyakarta',
                    'student_born_date' => '2005-05-30',
                    'student_phone' => '08123456796',
                    'student_hobby' => 'Menggambar',
                    'student_address' => 'Jl. Contoh No. 678, Yogyakarta',
                    'student_name_of_mother' => 'Siti Fatimah',
                    'student_name_of_father' => 'Eko Susilo',
                    'student_parent_phone' => '08123456797',
                    'class_class_id' => $class->class_id,
                    'student_status' => 1,
                ],
            ];

            foreach ($students as $student) {
                $student['student_password'] = Hash::make('password123');
                Student::create($student);
            }
        }
    }
} 