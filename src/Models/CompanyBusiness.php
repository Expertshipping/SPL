<?php

namespace ExpertShipping\Spl\Models;

class CompanyBusiness extends Company
{
    protected $table = 'companies';
    public string $route = 'customer-service';
    public array $condition = ['account_type' => 'business'];
}
