<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carrier_platform_country', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carrier_id');
            $table->unsignedBigInteger('platform_country_id');
            $table->double('residential')->nullable();
            $table->double('saturday_delivery')->nullable();
            $table->double('signature_on_delivery')->nullable();
            $table->string('transit_time_source')->nullable();
            $table->double('weight_limit')->nullable();
            $table->string('full_rate_source')->nullable();
            $table->string('discount_rate_source')->nullable();
            $table->string('pickup_api_or_email')->default('api');
            $table->string('pickup_email_address')->nullable();
            $table->longText('pickup_email_content')->nullable();

            $table->boolean('has_fixed_ship_from_location')->default(false);
            $table->string('ship_from_addr1')->nullable();
            $table->string('ship_from_addr2')->nullable();
            $table->string('ship_from_addr3')->nullable();
            $table->string('ship_from_city')->nullable();
            $table->string('ship_from_state')->nullable();
            $table->string('ship_from_zip_code')->nullable();
            $table->string('ship_from_country')->nullable();

            $table->boolean('support_po_box')->default(false);
            $table->boolean('active_for_inventory')->default(true);
            $table->string('claim_email')->nullable();
            $table->string('claim_language')->nullable();
            $table->json('reseller_marge_details')->nullable();
            $table->boolean('has_ground_service')->default(false);
            $table->float('special_handling_price')->nullable();

            $table->timestamps();
        });

        Schema::table('carriers', function (Blueprint $table) {
            if(Schema::hasColumn('carriers', 'residential')) $table->dropColumn('residential');
            if(Schema::hasColumn('carriers', 'saturday_delivery')) $table->dropColumn('saturday_delivery');
            if(Schema::hasColumn('carriers', 'signature_on_delivery')) $table->dropColumn('signature_on_delivery');
            if(Schema::hasColumn('carriers', 'transit_time_source')) $table->dropColumn('transit_time_source');
            if(Schema::hasColumn('carriers', 'weight_limit')) $table->dropColumn('weight_limit');
            if(Schema::hasColumn('carriers', 'full_rate_source')) $table->dropColumn('full_rate_source');
            if(Schema::hasColumn('carriers', 'discount_rate_source')) $table->dropColumn('discount_rate_source');
            if(Schema::hasColumn('carriers', 'pickup_api_or_email')) $table->dropColumn('pickup_api_or_email');
            if(Schema::hasColumn('carriers', 'pickup_email_address')) $table->dropColumn('pickup_email_address');
            if(Schema::hasColumn('carriers', 'pickup_email_content')) $table->dropColumn('pickup_email_content');
            if(Schema::hasColumn('carriers', 'has_fixed_ship_from_location')) $table->dropColumn('has_fixed_ship_from_location');
            if(Schema::hasColumn('carriers', 'ship_from_addr1')) $table->dropColumn('ship_from_addr1');
            if(Schema::hasColumn('carriers', 'ship_from_addr2')) $table->dropColumn('ship_from_addr2');
            if(Schema::hasColumn('carriers', 'ship_from_addr3')) $table->dropColumn('ship_from_addr3');
            if(Schema::hasColumn('carriers', 'ship_from_city')) $table->dropColumn('ship_from_city');
            if(Schema::hasColumn('carriers', 'ship_from_state')) $table->dropColumn('ship_from_state');
            if(Schema::hasColumn('carriers', 'ship_from_zip_code')) $table->dropColumn('ship_from_zip_code');
            if(Schema::hasColumn('carriers', 'ship_from_country')) $table->dropColumn('ship_from_country');
            if(Schema::hasColumn('carriers', 'support_po_box')) $table->dropColumn('support_po_box');
            if(Schema::hasColumn('carriers', 'active_for_inventory')) $table->dropColumn('active_for_inventory');
            if(Schema::hasColumn('carriers', 'claim_email')) $table->dropColumn('claim_email');
            if(Schema::hasColumn('carriers', 'claim_language')) $table->dropColumn('claim_language');
            if(Schema::hasColumn('carriers', 'reseller_marge_details')) $table->dropColumn('reseller_marge_details');
            if(Schema::hasColumn('carriers', 'has_ground_service')) $table->dropColumn('has_ground_service');
            if(Schema::hasColumn('carriers', 'special_handling_price')) $table->dropColumn('special_handling_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_platform_country');
    }
};
