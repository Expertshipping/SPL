<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\LoginActivity;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
        $last_login = $this->loginActivities->sortByDesc('created_at')->pluck('created_at')->first();
        if ($last_login != null) {
            $last_login = Carbon::parse($last_login)->format('M j, Y');
        }
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'first_name'            => $this->first_name,
            'last_name'             => $this->last_name,
            'email'                 => strtolower($this->email),
            'phone'                 => $this->phone,
            'company'               => $this->company,
            'app_role'              => $this->companies->first()?->pivot?->appRole,
            'business_roles'        => $this->businessRoles,
            'photo_url'             => $this->photo_url,
            'creation_date'         => $this->created_at->format('M j, Y'),
            'last_login'            => $last_login,
            'preferred_language'    => $this->preferred_language,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
