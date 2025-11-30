<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ShortUserBalanceResource",
 *     type="object",
 *     title="ShortUserBalance Resource",
 *     @OA\Property(
 *         property="balance",
 *         type="string",
 *         description="Balance"
 *     ),
 *     @OA\Property(
 *         property="pending_balance",
 *         type="string",
 *         description="Pending balance"
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency"
 *     )
 * )
 */
class ShortUserBalance extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "balance" => $this->balance,
            "pending_balance" => $this->pending_balance,
            "currency" => $this->currency
        ];
    }
}
