<?php

namespace ExpertShipping\Spl\Models;

class CompanyBusiness extends Company
{
    protected $table = 'companies';
    public string $route = 'customer_service';
    public static array $condition = ['account_type' => 'business'];
}
