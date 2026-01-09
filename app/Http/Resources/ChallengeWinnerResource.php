<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeWinnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'challenge_id' => $this->challenge_id,
            'user_id' => $this->user_id,
            'post_id' => $this->post_id,
            'place' => $this->place,
            'prize_amount' => $this->prize_amount,
            'prize_currency' => $this->prize_currency,
            'payout_status' => $this->payout_status,
            'transaction_id' => $this->transaction_id,
            'payout_completed_at' => $this->payout_completed_at?->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                ];
            }),
            'post' => $this->whenLoaded('post'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
