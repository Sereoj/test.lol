<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="TaskResource",
 *     type="object",
 *     title="Task Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description"
 *     ),
 *     @OA\Property(
 *         property="target",
 *         type="string",
 *         description="Target"
 *     ),
 *     @OA\Property(
 *         property="period",
 *         type="string",
 *         description="Period"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type"
 *     ),
 *     @OA\Property(
 *         property="experience_reward",
 *         type="string",
 *         description="Experience reward"
 *     ),
 *     @OA\Property(
 *         property="virtual_balance_reward",
 *         type="string",
 *         description="Virtual balance reward"
 *     )
 * )
 */
class TaskResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'target' => $this->target,
            'period' => $this->period,
            'type' => $this->type,
            'experience_reward' => $this->experience_reward,
            'virtual_balance_reward' => $this->virtual_balance_reward,
        ];
    }
}
