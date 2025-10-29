<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetupGateway extends Model
{
    use HasFactory;
    protected $primaryKey = 'setup_id';
    protected $fillable = [
        'url_duitku', 'apikey_duitku', 'merchantcode_duitku', 'duitku_sandbox',
        'url_tripay', 'apikey_tripay', 'privatekey_tripay', 'merchantcode_tripay',
        'payment_gateway',
        'url_wagateway', 'apikey_wagateway', 'sender_wagateway', 'wa_gateway',
        'norek_bank', 'nama_bank', 'nama_rekening',
        // iPaymu fields
        'ipaymu_va', 'ipaymu_api_key', 'ipaymu_mode', 'ipaymu_is_active',
        // Midtrans fields (deprecated - will be removed)
        'midtrans_mode', 'midtrans_is_active',
        'midtrans_server_key_sandbox', 'midtrans_client_key_sandbox', 'midtrans_merchant_id_sandbox',
        'midtrans_server_key_production', 'midtrans_client_key_production', 'midtrans_merchant_id_production',
    ];
}
