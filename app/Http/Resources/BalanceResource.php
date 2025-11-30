<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="BalanceResource",
 *     type="object",
 *     title="Balance Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
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
class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'balance' => $this->balance,
            'pending_balance' => $this->pending_balance,
            'currency' => $this->currency,
        ];
    }
}
