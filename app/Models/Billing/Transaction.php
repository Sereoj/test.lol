<?php

namespace App\Models\Billing;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\TransactionFactory::new();
    }

    protected $fillable = ['user_id', 'type', 'amount', 'currency', 'status', 'metadata', 'external_transaction_id'];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
