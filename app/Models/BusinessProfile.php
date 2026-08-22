<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'logo',
        'address',
        'phone',
        'email',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
