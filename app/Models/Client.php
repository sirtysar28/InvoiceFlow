<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'linked_user_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Hitung total invoice per status
     */
    public function invoiceCount(string $status = null): int
    {
        $query = $this->invoices();

        if ($status === 'paid') {
            return $query->paid()->count();
        } elseif ($status === 'unpaid') {
            return $query->unpaid()->count();
        } elseif ($status === 'overdue') {
            return $query->overdue()->count();
        }

        return $query->count();
    }

    /**
     * Total nominal semua invoice
     */
    public function totalInvoiceAmount(): float
    {
        return $this->invoices()->sum('total');
    }
}
