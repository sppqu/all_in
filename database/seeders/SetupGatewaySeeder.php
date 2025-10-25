<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SetupGateway;

class SetupGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SetupGateway::updateOrCreate(
            ['setup_id' => 1],
            [
                'url_duitku' => 'https://sandbox.duitku.com',
                'apikey_duitku' => 'your_duitku_api_key_here',
                'merchantcode_duitku' => 'your_duitku_merchant_code',
                'duitku_sandbox' => 'true',
                'url_tripay' => 'https://tripay.co.id',
                'apikey_tripay' => 'TRIPAY_API_KEY',
                'privatekey_tripay' => 'TRIPAY_PRIVATE_KEY',
                'merchantcode_tripay' => 'TRIPAY_MERCHANT',
                'payment_gateway' => 'duitku',
                'url_wagateway' => 'https://wagateway.com',
                'apikey_wagateway' => 'your_wagateway_api_key',
                'sender_wagateway' => 'your_sender_number',
                'wa_gateway' => 'wagateway',
                'norek_bank' => '1234-5678-9012-3456',
                'nama_bank' => 'Bank Central Asia (BCA)',
                'nama_rekening' => 'NAMA SEKOLAH',
                'midtrans_mode' => 'sandbox',
                'midtrans_is_active' => true,
                'midtrans_server_key_sandbox' => 'SB-Mid-server-kPdWjzufT77jNCgM7EQTYIz5',
                'midtrans_client_key_sandbox' => 'SB-Mid-client-lJRoDoWDFqA6NzlJ',
                'midtrans_merchant_id_sandbox' => 'G409110172',
                'midtrans_server_key_production' => '',
                'midtrans_client_key_production' => '',
                'midtrans_merchant_id_production' => '',
                'enable_wa_notification' => true,
            ]
        );
    }
} 