<?php

namespace ExpertShipping\Spl\Models\Retail;

use ExpertShipping\Spl\Models\CategoryProduct;
use ExpertShipping\Spl\Models\Product;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commissionable extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_value',
        'commission_type',
        'commissionable_type',
        'commissionable_id',
        'admin_id',
        'commission_value_palier_2',
    ];

    public function agentCommissions(){
        return $this->hasMany(AgentCommission::class, 'commission_id');
    }

    public function commissionable(){
        return $this->morphTo();
    }

    public function getNameAttribute(){
        $productName = '-';
        if($this->commissionable_type == Product::class){
            $productName = 'Product ' . $this->commissionable->name;
        }

        if($this->commissionable_type == CategoryProduct::class){
            $productName = 'Category ' . $this->commissionable->name;
        }

        if($this->commissionable_type == 'InsuranceDropOffLessThen400'){
            $productName = 'Insurnace Drop Off Less Then 400';
        }

        if($this->commissionable_type == 'InsuranceDropOffMoreThen400'){
            $productName = 'Insurnace Drop Off More Then 400';
        }

        if($this->commissionable_type == 'InsuranceShipmentMoreThen300'){
            $productName = 'Insurnace Shipment More Then 300';
        }

        if($this->commissionable_type == 'InsuranceShipmentLessThen300'){
            $productName = 'Insurnace Shipment Less Then 300';
        }

        if($this->commissionable_type == Shipment::class){
            $productName = 'Shipment';
        }

        return $productName;
    }
}
