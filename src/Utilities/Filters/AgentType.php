<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Models\AgentTip;
use ExpertShipping\Spl\Utilities\FilterContract;

class AgentType implements FilterContract
{
    public static function apply($query, $value)
    {
        if(in_array(get_class($query->getModel()),[AgentTip::class, 'App\AgentTip'])){
            return $query->whereHas('user', function($query) use ($value){
                $query->where('agent_type', $value);
            });
        }

        return $query;
    }
}
