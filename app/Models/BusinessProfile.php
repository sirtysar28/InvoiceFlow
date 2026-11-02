<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Profil bisnis perusahaan user login (staff seperusahaan lihat profil yang sama).
     * Super Admin / tanpa perusahaan → profil platform (company_id NULL).
     */
    public static function current(): ?self
    {
        $cid = auth()->user()?->company_id;
        if ($cid !== null) {
            return self::where('company_id', $cid)->first()
                ?? self::where('user_id', auth()->id())->first();
        }
        return self::whereNull('company_id')->orderBy('id')->first()
            ?? self::orderBy('id')->first();
    }
}
