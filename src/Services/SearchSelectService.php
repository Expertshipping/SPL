<?php

namespace ExpertShipping\Spl\Services;

class SearchSelectService
{
    public function getBillingsPeriods(): array
    {
        return [
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'every_two_weeks', 'label' => __('Every two weeks')],
            ['value' => 'weekly', 'label' => __('Weekly')],
            ['value' => 'daily', 'label' => __('Daily')]
        ];
    }
}
