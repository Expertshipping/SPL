<?php

namespace ExpertShipping\Spl\Jobs;

use ExpertShipping\Spl\Facades\OpenAIText;
use ExpertShipping\Spl\Models\GoogleBusiness;
use ExpertShipping\Spl\Models\StoreLocator;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateStoreLocatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(public GoogleBusiness $business)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $keyWord = $this->business->seo_title;
        $meta['faq']['fr'] = OpenAIText::generateCompanyFAQs($keyWord, 'fr');
        $meta['faq']['en'] = OpenAIText::generateCompanyFAQs($keyWord);

        $meta['content']['fr'] = OpenAIText::generateCarrierService($keyWord, 'fr');
        $meta['content']['en'] = OpenAIText::generateCarrierService($keyWord, 'en');

        StoreLocator::query()->updateOrCreate(
            [
                'company_id' => $this->business->company_id,
                'carrier_id' => $this->business->carrier_id,
                'google_business_id' => $this->business->id,
            ],
            [
                'slug' => Str::slug(str()->slug($keyWord)),
                'company_id' => $this->business->company_id,
                'carrier_id' => $this->business->carrier_id,
                'google_business_id' => $this->business->id,
                'meta' => $meta,
            ]
        );

        Log::info("Store locator generated successfully for Company ID: {$this->business->company->name} and Carrier: {$this->business->carrier->name}");
    }
}
