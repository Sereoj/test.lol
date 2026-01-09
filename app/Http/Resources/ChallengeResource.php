<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ChallengeResource",
 *     type="object",
 *     title="Challenge Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description"
 *     ),
 *     @OA\Property(
 *         property="cover",
 *         type="string",
 *         description="Cover"
 *     ),
 *     @OA\Property(
 *         property="prize_pool",
 *         type="string",
 *         description="Prize pool"
 *     ),
 *     @OA\Property(
 *         property="participants_count",
 *         type="string",
 *         description="Participants count"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         description="Start date"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         description="End date"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status"
 *     ),
 *     @OA\Property(
 *         property="is_participating",
 *         type="string",
 *         description="Is participating"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         description="Created at"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         description="Updated at"
 *     )
 * )
 */
class ChallengeResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'cover' => $this->url,
            'organizer_id' => $this->organizer_id,
            'organizer' => $this->whenLoaded('organizer', function () {
                return [
                    'id' => $this->organizer->id,
                    'name' => $this->organizer->name,
                    'username' => $this->organizer->username,
                ];
            }),
            'type' => $this->type,
            'winner_selection_method' => $this->winner_selection_method,
            'prize_amount' => $this->prize_amount,
            'prize_currency' => $this->prize_currency,
            'platform_fee_percentage' => $this->platform_fee_percentage,
            'platform_fee_amount' => $this->platform_fee_amount,
            'net_prize_amount' => $this->net_prize_amount,
            'prize_pool' => $this->prize_pool,
            'participants_count' => $this->participants_count,
            'submissions_count' => $this->submissions_count,
            'votes_count' => $this->votes_count,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'voting_end_date' => $this->voting_end_date?->format('Y-m-d H:i:s'),
            'results_announced_at' => $this->results_announced_at?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'is_participating' => $this->is_participating,
            'prizes' => ChallengePrizeResource::collection($this->whenLoaded('prizes')),
            'winners' => ChallengeWinnerResource::collection($this->whenLoaded('winners')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
