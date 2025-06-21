<?php

namespace ExpertShipping\Spl\Commandes;

use ExpertShipping\Spl\Jobs\GenerateStoreLocatorJob;
use ExpertShipping\Spl\Models\Carrier;
use ExpertShipping\Spl\Models\CompanyRetail;
use ExpertShipping\Spl\Models\GoogleBusiness;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;


class GenerateStoreLocator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store-locator:generate {company_id?} {slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate store locator for a company by carriers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        $carrierSlug = $this->argument('slug');

        if ($companyId) {
            $companies = [$companyId];
        } else {
            $companies = CompanyRetail::pluck('id')->toArray();
        }

        if($carrierSlug) {
            $carriers = [$carrierSlug];
        }else{
            $carriers = Carrier::pluck('slug')->toArray();
        }

        // Get business without taking in consideration language only one business for same company and carrier
        $businesses = GoogleBusiness::query()
            ->whereIn('company_id', $companies)
            ->whereHas('carrier', fn($query) => $query->whereIn('slug', $carriers))
            ->get()
            ->unique('company_id')
            ->unique('carrier_id')
            ->all();

        Bus::batch(collect($businesses)->map(fn($business) => new GenerateStoreLocatorJob($business)))->dispatch();

        $this->info('Store locator generation dispatched.');
    }
}
