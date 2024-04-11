<?php

namespace ExpertShipping\Spl\Enum;


enum CompanyStatusEnum: string
{
    case ACTIVE = 'ACTIVE';
    case PENDING = 'PENDING';
    case SUSPENDED = 'SUSPENDED';
    case DECLINED = 'DECLINED';
    case BLOCKED = 'BLOCKED';
    case SIGNUP = 'SIGNUP'; //INFO: (hs) user signup in second step
    case EMAIL_VERIFICATION = 'EMAIL_VERIFICATION'; //INFO: (hs) user signup but email not verified

    public static function toSelectArray()
    {
        return collect([
            ['id' => self::ACTIVE, 'name' => 'Active'],
            ['id' => self::PENDING, 'name' => 'Suspended'],
            ['id' => self::SUSPENDED, 'name' => 'Suspended'],
            ['id' => self::DECLINED, 'name' => 'Suspended'],
            ['id' => self::BLOCKED, 'name' => 'Suspended'],
        ])->pluck('name', 'id');
    }
}
