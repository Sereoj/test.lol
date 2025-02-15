<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'gateway',
        'fixed_amount',
        'percentage',
    ];

    // Метод для получения комиссии по типу
    public static function getFeeByType(string $type, ?string $gateway = null)
    {
        $query = self::where('type', $type);

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        return $query->first();
    }
}
