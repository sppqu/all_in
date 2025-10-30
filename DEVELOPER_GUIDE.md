# 👨‍💻 Developer Guide - SPPQU

Panduan lengkap untuk developer yang akan maintain, develop, atau customize aplikasi SPPQU.

---

## 📋 Daftar Isi

- [Development Environment Setup](#development-environment-setup)
- [Project Structure](#project-structure)
- [Coding Standards](#coding-standards)
- [Database Schema](#database-schema)
- [API Development](#api-development)
- [Payment Gateway Integration](#payment-gateway-integration)
- [Adding New Features](#adding-new-features)
- [Testing](#testing)
- [Debugging](#debugging)
- [Performance Optimization](#performance-optimization)
- [Security Best Practices](#security-best-practices)

---

## 🛠️ Development Environment Setup

### **1. Prerequisites**

```bash
# Check versions
php --version      # Should be 8.2+
composer --version # Should be 2.x
mysql --version    # Should be 8.0+
node --version     # Should be 18.x+
npm --version      # Should be 9.x+
```

### **2. Local Development Stack**

**Option A: Laragon (Windows)**
- Download: https://laragon.org
- Includes: Apache, PHP 8.2, MySQL, phpMyAdmin
- Auto-configure virtual hosts

**Option B: Docker**
```bash
# Using Laravel Sail
composer require laravel/sail --dev
php artisan sail:install
./vendor/bin/sail up
```

**Option C: Manual (Linux/Mac)**
```bash
# Install LAMP/LEMP stack manually
# PHP 8.2, MySQL 8.0, Nginx/Apache
```

### **3. Clone & Setup**

```bash
# Clone repository
git clone https://github.com/sppqu/all_in.git
cd all_in

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create database
mysql -u root -p
```

```sql
CREATE DATABASE sppqu_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

```bash
# Configure .env
nano .env
# Update DB_* values

# Run migrations with seeders
php artisan migrate --seed

# Link storage
php artisan storage:link

# Build assets
npm run dev

# Start dev server
php artisan serve
```

### **4. IDE Setup**

**Recommended: VS Code**

Extensions:
- Laravel Extension Pack
- PHP Intelephense
- Laravel Blade Snippets
- Laravel goto view
- Laravel Extra Intellisense
- GitLens
- Better Comments

**Or: PHPStorm**
- Laravel plugin
- Laravel Idea plugin
- Database Tools

---

## 📁 Project Structure

```
sppqu/
├── app/
│   ├── Console/
│   │   └── Commands/          # Artisan commands
│   ├── Exceptions/
│   │   └── Handler.php        # Global exception handler
│   ├── Exports/               # Excel exports
│   ├── Helpers/               # Helper functions
│   │   ├── ActivityLogger.php
│   │   ├── PaymentGatewayHelper.php
│   │   └── ...
│   ├── Http/
│   │   ├── Controllers/       # All controllers
│   │   │   ├── AdminController.php
│   │   │   ├── SPMBController.php
│   │   │   ├── StudentAuthController.php
│   │   │   └── ...
│   │   └── Middleware/        # Custom middleware
│   ├── Imports/               # Excel imports
│   ├── Models/                # Eloquent models
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── Payment.php
│   │   └── ...
│   ├── Observers/             # Model observers
│   ├── Providers/             # Service providers
│   └── Services/              # Business logic services
│       ├── IpaymuService.php
│       └── ...
│
├── bootstrap/
│   ├── app.php                # App bootstrap
│   └── cache/                 # Framework cache
│
├── config/                    # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── ipaymu.php
│   ├── midtrans.php
│   └── ...
│
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
│
├── public/                    # Public assets
│   ├── assets/
│   ├── css/
│   ├── images/
│   └── index.php              # Entry point
│
├── resources/
│   ├── css/                   # CSS source
│   ├── js/                    # JS source
│   └── views/                 # Blade templates
│       ├── layouts/
│       ├── admin/
│       ├── spmb/
│       ├── student/
│       └── ...
│
├── routes/
│   ├── web.php                # Web routes
│   ├── api.php                # API routes
│   ├── spmb.php               # SPMB routes
│   ├── callback.php           # Payment callbacks
│   └── ...
│
├── storage/
│   ├── app/                   # App storage
│   ├── framework/             # Framework files
│   └── logs/                  # Log files
│
├── tests/                     # Tests
│   ├── Feature/
│   └── Unit/
│
├── vendor/                    # Composer dependencies
│
├── .env                       # Environment variables
├── artisan                    # Artisan CLI
├── composer.json              # PHP dependencies
├── package.json               # Node dependencies
└── vite.config.js             # Vite configuration
```

---

## 📏 Coding Standards

### **1. PHP Coding Style**

Follow **PSR-12** coding standard.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $students = Student::with('class')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(20);

        return view('admin.students.index', compact('students'));
    }
}
```

**Key Points:**
- Use type hints
- Add PHPDoc comments
- Use descriptive variable names
- Keep methods small & focused
- Follow SRP (Single Responsibility Principle)

### **2. Naming Conventions**

```php
// Classes: PascalCase
class StudentController {}

// Methods: camelCase
public function showProfile() {}

// Variables: camelCase
$totalAmount = 100000;

// Constants: UPPER_SNAKE_CASE
const MAX_UPLOAD_SIZE = 2048;

// Database tables: snake_case, plural
students, spmb_registrations, subscription_invoices

// Database columns: snake_case
first_name, created_at, payment_status
```

### **3. Blade Templates**

```blade
@extends('layouts.app')

@section('title', 'Student List')

@section('content')
<div class="container">
    <h1>Students</h1>
    
    @if($students->count() > 0)
        <table class="table">
            @foreach($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->class->name }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p>No students found.</p>
    @endif
</div>
@endsection
```

### **4. JavaScript**

```javascript
// Use ES6+ syntax
const fetchData = async (url) => {
    try {
        const response = await fetch(url);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
};

// Use descriptive names
const paymentButton = document.getElementById('btnPayment');

// Add comments for complex logic
// Calculate total with 10% tax
const totalWithTax = amount * 1.1;
```

---

## 🗄️ Database Schema

### **Key Tables**

#### **users**
```sql
- id
- name
- email
- password
- role (enum: superadmin, admin, operator, guru, siswa)
- phone
- remember_token
- timestamps
```

#### **students**
```sql
- id
- user_id (FK)
- nisn
- class_id (FK)
- major_id (FK)
- academic_year
- parent_name
- parent_phone
- timestamps
```

#### **payments**
```sql
- id
- student_id (FK)
- amount
- type (enum: monthly, free)
- status (enum: pending, paid, failed)
- payment_date
- payment_method
- reference_id
- timestamps
```

#### **spmb_registrations**
```sql
- id
- name
- phone
- email
- password
- step (1-5)
- gelombang_id (FK)
- major_id (FK)
- status (enum: pending, approved, rejected)
- timestamps
```

#### **spmb_payments**
```sql
- id
- registration_id (FK)
- amount
- type (enum: registration_fee, monthly_fee, free_payment)
- status (enum: pending, paid, failed)
- payment_method
- tripay_reference
- qr_code
- payment_url
- expired_at
- timestamps
```

### **Relationships**

```php
// User → Student (One to One)
User::hasOne(Student::class);
Student::belongsTo(User::class);

// Class → Students (One to Many)
ClassModel::hasMany(Student::class);
Student::belongsTo(ClassModel::class);

// Student → Payments (One to Many)
Student::hasMany(Payment::class);
Payment::belongsTo(Student::class);

// SPMBRegistration → Payments (One to Many)
SPMBRegistration::hasMany(SPMBPayment::class);
SPMBPayment::belongsTo(SPMBRegistration::class);
```

---

## 🔌 API Development

### **1. Create API Endpoint**

**Step 1: Define Route** (`routes/api.php`)

```php
use App\Http\Controllers\Api\StudentApiController;

Route::prefix('students')->group(function () {
    Route::get('/', [StudentApiController::class, 'index']);
    Route::get('/{id}', [StudentApiController::class, 'show']);
    Route::post('/', [StudentApiController::class, 'store']);
    Route::put('/{id}', [StudentApiController::class, 'update']);
    Route::delete('/{id}', [StudentApiController::class, 'destroy']);
});
```

**Step 2: Create Controller**

```bash
php artisan make:controller Api/StudentApiController --api
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentApiController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(): JsonResponse
    {
        $students = Student::with('class')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    /**
     * Display the specified student.
     */
    public function show(int $id): JsonResponse
    {
        $student = Student::with('class', 'payments')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nisn' => 'required|unique:students',
            'name' => 'required|string',
            'class_id' => 'required|exists:classes,id',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => $student
        ], 201);
    }
}
```

### **2. API Authentication**

**Using Sanctum:**

```php
// Install Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

// config/sanctum.php - configure

// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}

// Create token
$token = $user->createToken('api-token')->plainTextToken;

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

### **3. API Response Format**

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe"
  },
  "message": "Operation successful"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
```

---

## 💳 Payment Gateway Integration

### **iPaymu Integration**

**1. Configuration** (`config/ipaymu.php`)

```php
return [
    'api_key' => env('IPAYMU_API_KEY'),
    'va' => env('IPAYMU_VA'),
    'sandbox' => env('IPAYMU_SANDBOX', true),
    'production_url' => 'https://my.ipaymu.com/api/v2',
    'sandbox_url' => 'https://sandbox.ipaymu.com/api/v2',
];
```

**2. Service Class** (`app/Services/IpaymuService.php`)

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpaymuService
{
    protected $apiKey;
    protected $va;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ipaymu.api_key');
        $this->va = config('ipaymu.va');
        $this->baseUrl = config('ipaymu.sandbox') 
            ? config('ipaymu.sandbox_url')
            : config('ipaymu.production_url');
    }

    /**
     * Create payment request
     */
    public function createPayment(array $data)
    {
        $body = [
            'product' => $data['product'] ?? [],
            'qty' => $data['qty'] ?? [],
            'price' => $data['price'] ?? [],
            'returnUrl' => $data['returnUrl'] ?? url('/'),
            'cancelUrl' => $data['cancelUrl'] ?? url('/'),
            'notifyUrl' => route('ipaymu.callback'),
            'referenceId' => $data['referenceId'],
            'buyerName' => $data['buyerName'],
            'buyerEmail' => $data['buyerEmail'],
            'buyerPhone' => $data['buyerPhone'],
            'paymentMethod' => $data['paymentMethod'] ?? 'QRIS',
        ];

        $signature = $this->generateSignature($body);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'va' => $this->va,
            'signature' => $signature,
            'timestamp' => now()->timestamp
        ])->post($this->baseUrl . '/payment/direct', $body);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('iPaymu API Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception('iPaymu payment creation failed');
    }

    /**
     * Check payment status
     */
    public function checkStatus(string $transactionId)
    {
        $body = ['transactionId' => $transactionId];
        $signature = $this->generateSignature($body);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'va' => $this->va,
            'signature' => $signature,
            'timestamp' => now()->timestamp
        ])->post($this->baseUrl . '/transaction', $body);

        return $response->json();
    }

    /**
     * Generate signature for request
     */
    protected function generateSignature(array $body)
    {
        $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $jsonBody, $this->apiKey);
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback(array $data, string $receivedSignature)
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);
        $expectedSignature = hash_hmac('sha256', $jsonData, $this->apiKey);
        
        return hash_equals($expectedSignature, $receivedSignature);
    }
}
```

**3. Callback Handler** (`routes/callback.php`)

```php
use App\Services\IpaymuService;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

Route::post('/ipaymu/callback', function (Request $request) {
    $ipaymu = new IpaymuService();
    
    // Verify signature
    $signature = $request->header('signature');
    if (!$ipaymu->verifyCallback($request->all(), $signature)) {
        Log::error('iPaymu callback signature mismatch');
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Process payment
    $data = $request->all();
    $payment = Payment::where('reference_id', $data['reference_id'])->first();

    if ($payment && $data['status'] == 'success') {
        $payment->update([
            'status' => 'paid',
            'payment_date' => now(),
            'transaction_id' => $data['trx_id']
        ]);

        // Send notification
        // Activate addon/subscription
        
        Log::info('Payment successful', ['reference' => $data['reference_id']]);
    }

    return response()->json(['success' => true]);
});
```

---

## ➕ Adding New Features

### **Example: Add Book Borrowing Feature**

**1. Create Migration**

```bash
php artisan make:migration create_book_loans_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('borrowed_at');
            $table->date('due_date');
            $table->date('returned_at')->nullable();
            $table->enum('status', ['borrowed', 'returned', 'overdue'])->default('borrowed');
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
    }
};
```

**2. Create Model**

```bash
php artisan make:model BookLoan
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookLoan extends Model
{
    protected $fillable = [
        'book_id',
        'student_id',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
        'fine_amount'
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'due_date' => 'date',
        'returned_at' => 'date',
        'fine_amount' => 'decimal:2'
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' 
            && $this->due_date->isPast();
    }

    /**
     * Calculate fine amount
     */
    public function calculateFine(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $daysOverdue = now()->diffInDays($this->due_date);
        $finePerDay = 1000; // Rp 1,000 per day
        
        return $daysOverdue * $finePerDay;
    }
}
```

**3. Create Controller**

```bash
php artisan make:controller BookLoanController --resource
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\BookLoan;
use App\Models\Book;
use App\Models\Student;
use Illuminate\Http\Request;

class BookLoanController extends Controller
{
    public function index()
    {
        $loans = BookLoan::with(['book', 'student'])
            ->latest()
            ->paginate(20);

        return view('library.loans.index', compact('loans'));
    }

    public function create()
    {
        $books = Book::where('available_copies', '>', 0)->get();
        $students = Student::all();

        return view('library.loans.create', compact('books', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
            'borrowed_at' => 'required|date',
            'due_date' => 'required|date|after:borrowed_at'
        ]);

        // Check book availability
        $book = Book::findOrFail($validated['book_id']);
        if ($book->available_copies <= 0) {
            return back()->withErrors(['book_id' => 'Book not available']);
        }

        // Create loan
        $loan = BookLoan::create($validated);

        // Decrease available copies
        $book->decrement('available_copies');

        return redirect()->route('library.loans.index')
            ->with('success', 'Book borrowed successfully');
    }

    public function return(BookLoan $loan)
    {
        $loan->update([
            'returned_at' => now(),
            'status' => 'returned',
            'fine_amount' => $loan->calculateFine()
        ]);

        // Increase available copies
        $loan->book->increment('available_copies');

        return back()->with('success', 'Book returned successfully');
    }
}
```

**4. Create Routes**

```php
// routes/web.php
use App\Http\Controllers\BookLoanController;

Route::prefix('library')->middleware('auth')->group(function () {
    Route::resource('loans', BookLoanController::class);
    Route::post('loans/{loan}/return', [BookLoanController::class, 'return'])
        ->name('loans.return');
});
```

**5. Create Views**

```blade
{{-- resources/views/library/loans/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Book Loans</h1>
    
    <a href="{{ route('loans.create') }}" class="btn btn-primary mb-3">
        New Loan
    </a>

    <table class="table">
        <thead>
            <tr>
                <th>Book</th>
                <th>Student</th>
                <th>Borrowed</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->book->title }}</td>
                <td>{{ $loan->student->name }}</td>
                <td>{{ $loan->borrowed_at->format('d/m/Y') }}</td>
                <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                <td>
                    <span class="badge bg-{{ $loan->status === 'returned' ? 'success' : ($loan->isOverdue() ? 'danger' : 'warning') }}">
                        {{ $loan->status }}
                    </span>
                </td>
                <td>
                    @if($loan->status === 'borrowed')
                        <form action="{{ route('loans.return', $loan) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-success">Return</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $loans->links() }}
</div>
@endsection
```

---

## 🧪 Testing

### **1. Unit Testing**

```bash
# Create test
php artisan make:test BookLoanTest --unit
```

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BookLoan;
use App\Models\Book;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookLoanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_calculate_fine_for_overdue_loan()
    {
        $loan = BookLoan::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => 'borrowed'
        ]);

        $fine = $loan->calculateFine();

        $this->assertEquals(5000, $fine); // 5 days * Rp 1,000
    }

    /** @test */
    public function it_returns_zero_fine_for_on_time_loan()
    {
        $loan = BookLoan::factory()->create([
            'due_date' => now()->addDays(7),
            'status' => 'borrowed'
        ]);

        $fine = $loan->calculateFine();

        $this->assertEquals(0, $fine);
    }
}
```

### **2. Feature Testing**

```bash
php artisan make:test BookLoanFeatureTest
```

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookLoanFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_borrow_a_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 5]);
        $student = Student::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('loans.store'), [
                'book_id' => $book->id,
                'student_id' => $student->id,
                'borrowed_at' => now(),
                'due_date' => now()->addDays(7)
            ]);

        $response->assertRedirect(route('library.loans.index'));
        $this->assertDatabaseHas('book_loans', [
            'book_id' => $book->id,
            'student_id' => $student->id
        ]);
        
        // Check available copies decreased
        $this->assertEquals(4, $book->fresh()->available_copies);
    }
}
```

### **3. Run Tests**

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter BookLoanTest

# Run with coverage
php artisan test --coverage

# Run parallel
php artisan test --parallel
```

---

## 🐛 Debugging

### **1. Laravel Debugbar**

```bash
composer require barryvdh/laravel-debugbar --dev
```

### **2. Laravel Telescope**

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access: `/telescope`

### **3. Logging**

```php
// Log messages
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', ['error' => $e->getMessage()]);
Log::debug('Debug info', ['data' => $data]);

// Log channels (config/logging.php)
Log::channel('slack')->error('Critical error');
Log::stack(['single', 'slack'])->info('Info message');
```

### **4. dd() and dump()**

```php
// Dump and die
dd($variable);

// Dump (continue execution)
dump($variable);

// Ray (optional tool)
ray($variable);
```

---

## ⚡ Performance Optimization

### **1. Query Optimization**

```php
// Bad: N+1 query problem
$students = Student::all();
foreach ($students as $student) {
    echo $student->class->name; // Extra query for each student
}

// Good: Eager loading
$students = Student::with('class')->get();
foreach ($students as $student) {
    echo $student->class->name; // No extra queries
}

// Use Query Builder for large datasets
$payments = DB::table('payments')
    ->select('student_id', DB::raw('SUM(amount) as total'))
    ->groupBy('student_id')
    ->get();
```

### **2. Caching**

```php
// Cache data
Cache::put('students', $students, now()->addHours(1));

// Retrieve from cache
$students = Cache::get('students');

// Cache with callback
$students = Cache::remember('students', 3600, function () {
    return Student::all();
});

// Clear cache
Cache::forget('students');
Cache::flush();
```

### **3. Queue Jobs**

```bash
# Create job
php artisan make:job SendPaymentNotification
```

```php
<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function handle(): void
    {
        // Send notification via WhatsApp, Email, etc.
    }
}

// Dispatch job
SendPaymentNotification::dispatch($payment);
```

### **4. Asset Optimization**

```bash
# Minify & combine assets
npm run build

# Optimize images
composer require intervention/image

# Enable browser caching (nginx)
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
}
```

---

## 🔒 Security Best Practices

### **1. Input Validation**

```php
// Always validate user input
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'amount' => 'required|numeric|min:0'
]);
```

### **2. Mass Assignment Protection**

```php
// Model
protected $fillable = ['name', 'email']; // Allowed fields
protected $guarded = ['id', 'password']; // Protected fields
```

### **3. SQL Injection Prevention**

```php
// Bad
DB::select("SELECT * FROM users WHERE id = " . $id);

// Good
DB::select("SELECT * FROM users WHERE id = ?", [$id]);

// Eloquent (automatically protected)
User::where('id', $id)->get();
```

### **4. XSS Prevention**

```blade
{{-- Blade automatically escapes --}}
<p>{{ $userInput }}</p>

{{-- Raw output (dangerous!) --}}
<p>{!! $trustedHtml !!}</p>
```

### **5. CSRF Protection**

```blade
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

### **6. Authorization**

```php
// Gates (app/Providers/AuthServiceProvider.php)
Gate::define('update-post', function (User $user, Post $post) {
    return $user->id === $post->user_id;
});

// Policies
php artisan make:policy PostPolicy --model=Post

// Check authorization
$this->authorize('update', $post);

// In blade
@can('update', $post)
    <!-- show edit button -->
@endcan
```

---

## 📚 Additional Resources

**Laravel Documentation:**
- https://laravel.com/docs

**Laracasts:**
- https://laracasts.com

**Laravel News:**
- https://laravel-news.com

**Laravel Daily:**
- https://laraveldaily.com

**Stack Overflow:**
- https://stackoverflow.com/questions/tagged/laravel

---

## 🤝 Contributing Guidelines

1. **Fork & Clone**
2. **Create feature branch**: `git checkout -b feature/AmazingFeature`
3. **Write tests** for new features
4. **Follow coding standards** (PSR-12)
5. **Commit**: `git commit -m 'Add some AmazingFeature'`
6. **Push**: `git push origin feature/AmazingFeature`
7. **Create Pull Request**

**PR Checklist:**
- [ ] Code follows PSR-12
- [ ] Tests pass
- [ ] Documentation updated
- [ ] No merge conflicts
- [ ] Reviewed by at least 1 developer

---

**Happy Coding! 👨‍💻**

**Version:** 2.0.0  
**Last Updated:** October 2024

