<?php

namespace ExpertShipping\Spl\Models;

class CompanyRetail extends Company
{
    protected $table = 'companies';
    public string $route = 'retail';
    public static array $condition = ['is_retail_reseller' => 0, 'account_type' => 'retail'];
}
