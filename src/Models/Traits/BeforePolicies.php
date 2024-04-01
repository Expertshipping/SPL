<?php

namespace ExpertShipping\Spl\Models\Traits;

use App\

trait BeforePolicies
{
    /**
     * @param User $user
     * @param $ability
     * @return bool
     */
    public function before(User $user)
    {
        if (in_array($user->email, [
            'm.alaoui@expertshipping.ca',
            'c.mallette@expertshipping.ca',
            'mouadaarab@gmail.com',
            'accounting@expertshipping.ca',
            'm.alaoui@shippayless.com',
            'm.alaoui@awsel.ma',
            'h.alaoui@awsel.ma',
        ])) {
            return true;
        }
    }
}
