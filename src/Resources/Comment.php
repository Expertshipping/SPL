<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SWG\Definition(
 *      definition="Address",
 *      required={
 *          "id",
 *          "uuid",
 *          "name",
 *      },
 *      @SWG\Property(property="id", type="integer", description="the shipping unique system id"),
 *      @SWG\Property(property="uuid", type="uuid", description="the shipping uuid"),
 * )
 */
class Comment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'commentable_type' => $this->commentable_type,
            'commentable_id' => $this->commentable_id,
            'comment' => $this->comment,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
            'humain_created_at' => $this->created_at->diffForHumans(),
            'user' => $this->whenLoaded('user'),
            'image' => optional($this->getFirstMedia('shipments-images'))->getUrl(),
            'platform' => $this->platform,
        ];
    }
}
