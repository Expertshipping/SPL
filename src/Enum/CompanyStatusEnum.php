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
    case BUSINESS_CLOSED = 'BUSINESS_CLOSED';
    case NO_LONGER_SHIPPING = 'NO_LONGER_SHIPPING';
    case TEMPORARILY_CLOSED = 'TEMPORARILY_CLOSED';
    case NOT_INTERESTED = 'NOT_INTERESTED';

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

    public static function toList()
    {
        return collect([
            ['value' => self::ACTIVE, 'label' => __('Active')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::SUSPENDED, 'label' => __('Suspended')],
            ['value' => self::DECLINED, 'label' => __('Rejected')],
            ['value' => self::SIGNUP, 'label' => __('Signup')],
            ['value' => self::EMAIL_VERIFICATION, 'label' => __('Email verification')],
            ['value' => self::BUSINESS_CLOSED, 'label' => __('Business Closed')],
            ['value' => self::NO_LONGER_SHIPPING, 'label' => __('No Longer Shipping')],
            ['value' => self::TEMPORARILY_CLOSED, 'label' => __('Temporarily Closed')],
            ['value' => self::NOT_INTERESTED, 'label' => __('Not Interested')],
            ['value' => self::BLOCKED, 'label' => __('Blocked')],
        ])->pluck('name', 'id');
    }
}
