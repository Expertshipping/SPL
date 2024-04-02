<?php

namespace ExpertShipping\Spl\Enum;


enum CompanyStatusEnum: string
{
    case ACTIVE = 'ACTIVE';
    case PENDING = 'PENDING';
    case SUSPENDED = 'SUSPENDED';
    case DECLINED = 'DECLINED';
    case BLOCKED = 'BLOCKED';
}
