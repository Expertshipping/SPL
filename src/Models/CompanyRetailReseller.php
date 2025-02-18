<?php

namespace ExpertShipping\Spl\Models;

class CompanyRetailReseller extends Company
{
    protected $table = 'companies';
    public string $route = 'retail-reseller';
    public static array $condition = ['is_retail_reseller' => 1, 'account_type' => 'retail'];
}
