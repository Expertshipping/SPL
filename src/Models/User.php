<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\LocalInvoice as AppLocalInvoice;
use ExpertShipping\Spl\Models\Notifications\EmailConfirmation;
use ExpertShipping\Spl\Models\Notifications\PasswordReset;
use ExpertShipping\Spl\Models\Retail\AgentCommission;
use ExpertShipping\Spl\Models\Retail\AgentWarning;
use ExpertShipping\Spl\Services\TimesheetService;
use Dompdf\Dompdf;
use Dompdf\Options;
use ExpertShipping\Spl\Services\UserIpAddress;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Exceptions\CustomerAlreadyCreated;
use Ramsey\Uuid\Uuid;

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Symfony\Component\HttpFoundation\Response;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements HasMedia, HasLocalePreference
{

    const POS_START_VERIFICATION_DATE = '2023-08-16';

    const AVATAR_PATH = 'storage/avatars';

    protected $connection = 'mysql';

    use HasRoles;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasApiTokens;
    use Notifiable;
    use Billable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company', 'first_name', 'last_name', 'tel', 'email', 'country', 'password', 'addr1',
        'addr2', 'city', 'zip_code', 'province', 'instant_payment', 'phone', 'blocked_at',
        'name', 'discount', 'email', 'uuid', 'signup_completed_at', 'first_name', 'last_name', 'account_type', 'custom_branding', 'company_id', 'activate_account_token', 'active', 'color', 'options',

        'ip_address', 'montly_salary', 'hourly_salary', 'moneris_id', 'card_last_four', 'referral_code', 'referral_id', 'referral_percentage',

        'availability', 'is_spark_user',
        'hidden_at',

        'firebase_token', 'firebase_uid',

        'consumer_card_code', 'consumer_card', 'photo_url',

        'mobile_platform',
        'mobile_platform_version',

        'dashboard_order',
        'ip_restrictions',
        'agent_type',
        'api_token',

        'has_right_to_commission',
        'preferred_language',

        'hide_from_timesheet',
        'availability_notified_at',
        'in_training'
    ];

    protected $attributes = [
        'account_type' => 'business',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'country_code',
        'two_factor_reset_code',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at'                     => 'datetime',
        'uses_two_factor_auth'              => 'boolean',
        'active'                            => 'boolean',
        'options'                           =>  'array',
        'custom_branding'                   =>  'array',
        'blocked_at'                        => 'datetime',
        'availability'                      => 'array',
        'is_spark_user'                     => 'boolean',
        'hidden_at'                         => 'datetime',
        'dashboard_order'                   => 'array',
        'hide_from_timesheet'               => 'boolean',
        'availability_notified_at'          => 'datetime',
        'in_training'                       => 'boolean',
    ];

    public static $packs = [
        'blue' => [
            'color' => '',
            'name' => 'Blue',
            'discount' => 10,
        ],
        'silver' => [
            'color' => '',
            'name' => 'Silver',
            'discount' => 15,
        ],
        'gold' => [
            'color' => '',
            'name' => 'Gold',
            'discount' => 20,
        ],
        'platinum' => [
            'color' => '',
            'name' => 'Platinum',
            'discount' => 25,
        ]
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
            $model->name = $model->full_name;
            if ($model->account_type === 'retail') {
                $model->signup_completed_at = now();
            }
        });
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $data = [
            'id' => $array['id'],
            'first_name' => $array['first_name'],
            'last_name' => $array['last_name'],
            //'phone' => $array['phone'],
            'email' => $array['email'],
            'name' => $array['name']
        ];

        return $data;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getFullNameAttribute()
    {
        return !!$this->first_name && !!$this->last_name
            ? "{$this->first_name} {$this->last_name}"
            : $this->name;
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function companyDiscounts()
    {
        return $this->hasManyThrough(CompanyDiscount::class, Company::class, 'user_id', 'company_id');
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    public function ownerCompany()
    {
        return $this->hasOne(Company::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Get the user carriers.
     */
    public function carriers()
    {
        return $this->hasMany(CompanyCarrier::class);
    }

    public function carrierAccounts()
    {
        $activeCarriers = collect($this->availableCarriers())
            ->map(function ($slug) {
                return Str::slug($slug);
            })
            ->toArray();
        if ($this->company) {
            return $this->company->carriers()
                ->whereHas('carrier', fn ($query) => $query->whereIn('slug', $activeCarriers))
                ->where('is_active', 1)
                ->get()
                ->map(function ($carrierAccount) {
                    $account = new \stdClass();
                    $account->id = $carrierAccount->id;
                    $account->type =  'App\CompanyCarrier';
                    $account->carrier = Str::upper($carrierAccount->carrier->slug);
                    $account->is_active = $carrierAccount->is_active;
                    $account->api_credentials = $carrierAccount->options;
                    return (array) $account;
                })->toArray();
        }

        return null;
    }

    /**
     * Get all of un-paid local invoices to add for a bulk.
     */
    public function bulkAvailableInvoices()
    {
        $ids = array_merge(
            LocalInvoice::distinct('bulk_id')->select('bulk_id')
                ->whereNotNull('bulk_id')
                ->withTrashed()
                ->get()->pluck('bulk_id')->all(),
            Claim::distinct('invoice_id')
                ->select('invoice_id')
                ->whereNotNull('invoice_id')
                ->get()->pluck('invoice_id')->all()
        );

        return $this->hasMany(LocalInvoice::class, 'user_id')
            ->whereNull('paid_at')
            ->whereNull('bulk_id')
            ->whereDoesntHave('bulkInvoices')
            ->whereNull('refunded_at')
            ->whereDoesntHave('bulkInvoices')
            ->whereNotIn('id', $ids);
    }


    public function addShipment(Shipment $shipment)
    {
        return $this->shipments()->save($shipment);
    }

    public function rateVisibility()
    {
        $user = $this->loadMissing('company');
        if ($user->company) {
            return  $user->company->rate_visibility;
        }
        return null;
    }

    public function addInvoiceFor(Shipment $shipment)
    {
        return $this->localInvoices()->create([
            'total' =>  Money::normalizeAmount($shipment->rate),
            'shipment_id' => $shipment->id,
            'company_id' => $shipment->user()->company_id
        ]);
    }

    public function createInvoiceForUser(Model $relation, $charge = null, $companyId = null)
    {
        $total = 0;
        if ($charge) {
            $total = $charge;
        } else {
            if (get_class($relation) === 'App\Insurance') {
                if ($relation->company->is_retail_reseller) {
                    $total = $relation->reseller_charged;
                } else {
                    $total = $relation->price;
                }
            }

            if (get_class($relation) === 'App\Shipment') {
                if ($relation->company->is_retail_reseller && is_numeric($relation->reseller_charged)) {
                    $total = $relation->reseller_charged;
                } else {
                    $total = $relation->rate;
                }
            }
        }

        // remove , and space from total
        $total = str_replace(',', '', $total);
        $total = str_replace(' ', '', $total);

        $invoice = $this->localInvoices()
            ->whereDoesntHave('posDetails')
            ->whereNotNull('company_id')
            ->orderByDesc('id')
            //->whereNull('paid_at')
            ->first();

        if(!$invoice){
            $newInvoice = true;
        }else{
            $billingPeriod = $this->company->billing_period;
            match ($billingPeriod) {
                'monthly' => $newInvoice = $invoice->created_at->year !== now()->year || $invoice->created_at->month !== now()->month,
                'every_two_weeks' => $newInvoice = $invoice->created_at->year !== now()->year || (now()->day >= 15 && $invoice->created_at->day < 15) || (now()->day < 15 && $invoice->created_at->day >= 15),
                'weekly' => $newInvoice = $invoice->created_at->year !== now()->year || $invoice->created_at->week !== now()->week,
                'daily' => $newInvoice = $invoice->created_at->year !== now()->year || $invoice->created_at->day !== now()->day,
                default => $newInvoice = true,
            };
        }

        if ($newInvoice) {
            $invoice = $this->localInvoices()->create([
                'total' => Money::normalizeAmount($total),
                'company_id' => $companyId ?? $this->company_id,
                'closed_at' => null,
            ]);
        }else{
            $invoice->update([
                'created_at' => now(),
            ]);
        }

        // add details
        $detail = $invoice->details()->create([
            'invoiceable_type' => get_class($relation),
            'invoiceable_id' => $relation->id,
            'price' => $total,
            'quantity' => 1,
        ]);

        $invoice->updateTotal(false);

        return $detail;
    }

    public function hasCompany()
    {
        return !!$this->company;
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     *
     * @return string
     */
    public function verificationUrl()
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->getKey()]
        );
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\PasswordReset($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailConfirmation);
    }

    public function allowedToCreateShipment($rate)
    {
        return !$this->isReachedLimit($rate);
        // if ($this->hasBillingProvider()) {
        //     return true;
        // }

        // if ($this->company) {
        //     if ($this->company->instant_payment) {
        //         return true;
        //     }
        //     return ! $this->isReachedLimit($rate);
        // }

        // return false;
    }

    public function isReachedLimit($rate)
    {
        return $this->company->isReachedLimit($rate, $this);
    }

    public function canDisplayPaymentNotification()
    {
        return $this->hasBillingProvider()
            || ($this->company && (float) $this->company->notPaidInvoices($this) < (float) $this->company->invoice_limit);
    }


    /**
     * Create an invoice download Response.
     *
     * @param  LocalInvoice  $invoice
     * @param  array  $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadLocalInvoice(LocalInvoice $invoice, array $data)
    {
        return $invoice->download($data);
    }

    public function generateSingleInvoice(Collection $collection, array $data)
    {
        if ($collection->first()->bulk_id && ($invoice = LocalInvoice::query()->find($collection->first()->bulk_id))) {
            $data = ['invoices' => $collection] + ['bulkInvoice' => $invoice] + $data;
        } else {
            $data = ['invoices' => $collection] + $data;
        }
        return $this->download($data);
    }

    /**
     * Create an invoice download response.
     *
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(array $data)
    {
        $filename = $data['product'] . '_' . now()->month . '_' . now()->year;

        return $this->downloadAs($filename, $data);
    }

    /**
     * Create an invoice download response with a specific filename.
     *
     * @param  string  $filename
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAs($filename, array $data)
    {
        //        return $this->view($data);
        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
            'X-Vapor-Base64-Encode' => 'True',
        ]);
    }

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\View\View
     */
    public function view(array $data)
    {
        return View::make('invoices.bulk', $data);
    }

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     *
     * @param  array  $data
     * @return string
     */
    public function pdf(array $data)
    {
        if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $dompdf->setHttpContext($context);

        $dompdf->setPaper('a4');
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages()
    {
        return $this->hasMany(PackageUser::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(ProductUser::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class, 'user_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function fulfillmentLocations()
    {
        return $this->hasMany(FulfillmentLocation::class);
    }

    public function locationToUse()
    {
        if ($location = $this->fulfillmentLocations->first()) {
            return $location;
        }

        return (object) [
            "name"          => $this->name,
            "country"       => $this->country,
            "province"      => $this->province,
            'zip'           => $this->zip_code,
            'city'          => $this->city,
            'company'       => $this->name,
            'address1'      => $this->addr1,
            'address2'      => $this->addr2,
            'address3'      => '',
            'phone'         => $this->phone,
            'email'         => $this->email
        ];
    }

    public function locationByIntegration(Integration $integration)
    {
        if ($integration->location) {
            return $integration->location;
        }

        return (object) [
            "name"          => $this->company->name,
            "country"       => $this->company->country,
            "province"      => $this->company->state,
            'zip'           => $this->company->zip_code,
            'city'          => $this->company->city,
            'company'       => $this->company->name,
            'address1'      => $this->company->addr1,
            'address2'      => $this->company->addr2,
            'address3'      => $this->company->addr3,
            'phone'         => $this->company->phone,
            'email'         => $this->company->email
        ];
    }

    public function getNeedTodoSomeConfigurationsAttribute()
    {
        if ($this->company) {
            $haveProductsNeedInformations   = $this->company->products->whereNull('dimensions')->count() > 0;
            $haveNoPackage                  = $this->company->packages->count() == 0;
            $haveAtLeastOneShopWithAutoShip = $this->integrations->where('checkout', 1)->count() > 0;
        } else {
            $haveProductsNeedInformations   = null;
            $haveNoPackage                  = null;
            $haveAtLeastOneShopWithAutoShip = null;
        }

        return [
            'checkout' => $haveAtLeastOneShopWithAutoShip,
            'products' => $haveProductsNeedInformations,
            'packages' => $haveNoPackage
        ];
    }

    public function block()
    {
        $this->blocked_at = now();
        $this->save();
    }

    public function unBlock()
    {
        $this->blocked_at = null;
        $this->save();
    }

    public function isBlocked()
    {
        return !is_null($this->blocked_at);
    }

    /**
     * getCustomLogoAttribute
     *
     * @return void
     */
    public function getCustomLogoAttribute()
    {
        if (!!$this->custom_branding && !!$this->custom_branding['active'] && !!$this->custom_branding['header'] && !!$this->getFirstMedia('logo')) {
            return $this->getFirstMediaUrl('logo');
        }

        if (!!$this->custom_branding && !!$this->custom_branding['active'] && (!!!$this->getFirstMedia('logo') || !$this->custom_branding['header'])) {
            return 'company_name';
        }

        return 'default';
    }

    public function socialNetworks()
    {
        return $this->belongsToMany(SocialNetwork::class)
            ->using(SocialNetworkUser::class)
            ->withPivot(['url']);
    }

    public function getUserSocialNetworksAttribute()
    {
        return $this->socialNetworks->mapWithKeys(function ($sn) {
            return [
                Str::lower($sn->name) => $sn->pivot->url
            ];
        })->toArray();
    }

    public function scopeDateLimitReached($query)
    {
        $query->join('companies', function ($join) {
            $join->on('users.id', '=', 'companies.user_id')
                ->where('companies.instant_payment', 0)
                ->whereNotNull('companies.date_limit')
                ->where('companies.date_limit', '<>', 'null')
                ->where('companies.date_limit', '<>', 'NO_LIMIT');
        })
            ->join('invoices', function ($join) {
                $join->on('users.id', '=', 'invoices.user_id')
                    ->whereNull('invoices.deleted_at')
                    ->whereNull('paid_at')
                    ->whereNull('refunded_at')
                    ->whereNull('bulk_id')
                    ->whereNotIn(
                        'invoices.id',
                        LocalInvoice::distinct('bulk_id')->select('bulk_id')
                            ->whereNotNull('bulk_id')
                            ->withTrashed()
                            ->get()->pluck('bulk_id')->all()
                    );
            })
            ->groupBy(['users.id', 'invoices.created_at', 'date_limit'])
            ->havingRaw('invoices.created_at <= NOW() - INTERVAL date_limit WEEK');
    }

    public function scopeAmountLimitReached($query, $margin = 100)
    {
        $query->join('companies', function ($join) {
            $join->on('users.id', '=', 'companies.user_id')
                ->where('companies.instant_payment', 0);
        })
            ->join('invoices', function ($join) {
                $join->on('users.id', '=', 'invoices.user_id')
                    ->whereNull('invoices.deleted_at')
                    ->whereNull('paid_at')
                    ->whereNull('bulk_id')
                    ->whereNull('refunded_at')
                    ->whereNotIn(
                        'invoices.id',
                        LocalInvoice::distinct('bulk_id')->select('bulk_id')
                            ->whereNotNull('bulk_id')
                            ->withTrashed()
                            ->get()->pluck('bulk_id')->all()
                    );
            })
            ->groupBy(['companies.invoice_limit', 'users.id'])
            ->havingRaw('sum(invoices.total) >= companies.invoice_limit - ? ', [$margin]);
    }

    public static function reachedLimit()
    {
        return self::distinct('users.id')
            ->select('users.id')
            ->amountlimitReached()->union(
                self::distinct('users.id')
                    ->select('users.id')
                    ->dateLimitReached()
            )->with('bulkAvailableInvoices:invoices.id,user_id,total');
    }

    public function scopeDailyBulkMatched($query)
    {
        $query->whereHas('company', function ($query) {
            $query->where('instant_payment', 1);
        })
            ->whereHas('shipments', function ($query) {
                $query->whereNotIn('type', ['draft', 'cancelled', 'returned', 'in_progress', 'delivered']);
            })->with(['localInvoices' => function ($query) {
                $query->whereNull('provider_id')
                    ->whereNull('paid_at')
                    ->whereNull('refunded_at')
                    ->whereNull('canceled_at')
                    ->whereNull('bulk_id')
                    ->whereNotIn(
                        'id',
                        LocalInvoice::select('bulk_id')
                            ->distinct('bulk_id')
                            ->whereNotNull('bulk_id')
                            ->get()->pluck('bulk_id')->all()
                    );
            }]);
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }

    public function activeCarriers()
    {
        return $this->belongsToMany(Carrier::class, 'active_carrier_users')
            ->using(ActiveCarrierUser::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    public function availableCarriers()
    {
        if ($this->company) {
            $carriersGivenByES = isset($this->company->discountPackage) ? $this->company
                ->discountPackage
                ->discountPackageDetails()
                ->with(['service.carrier'])
                ->get()
                ->pluck('service.carrier.slug')
                ->unique()
                ->filter(function ($value) {
                    return !is_null($value);
                })
                ->values() : collect([]);

            $carriersWithCompanyAccount = $this->company
                ->carriers()
                ->with('carrier')
                ->get()
                ->pluck('carrier.slug')
                ->unique()
                ->filter(function ($value) {
                    return !is_null($value);
                })
                ->values();

            $allCarriersWithAccount = $carriersGivenByES->merge($carriersWithCompanyAccount)->unique();

            $carriersChoosenByUser = $this->company->activeCarriers->count() > 0 ?
                $this->company->activeCarriers->pluck('slug') :
                Carrier::active()->pluck('slug');

            $carriers = $allCarriersWithAccount->filter(function ($carrier) use ($carriersChoosenByUser) {
                return $carriersChoosenByUser->contains($carrier);
            });
        } else {
            $carriers = Carrier::active()->pluck('slug');
        }

        return $carriers->map(function ($carrier) {
            return Str::upper($carrier);
        })
            ->values()
            ->toArray();
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id')
            ->using(CompanyUser::class)
            ->withPivot('app_role_id', 'can_view_cost')
            ->withTimestamps();
    }

    public function getUserCompaniesAttribute()
    {
        $appRoles = Cache::rememberForever('appRoles', function () {
            return AppRole::all();
        });

        return $this->companies->map(function ($company) use ($appRoles){
            $appRole = $appRoles->where('id', $company->pivot->app_role_id)->first();

            return [
                'company_id' => $company->id,
                'company_role' => $appRole?->slug,
                'company_name' => $company->name,
                'cash_registers' => $company->cashRegisters
            ];
        });
    }

    public function getUserRoleAttribute()
    {
        $company = $this->loadMissing('companies')->companies->where('id', $this->company_id)->first();

        if($this->account_type === 'business' && !$company && !!$this->company){
            return 'manager';
        }

        if (!$company) {
            return null;
        }

        return $company->pivot->appRole->slug;
    }


    // public function tokens(){
    //     return $this->hasMany(ApiToken::class);
    // }

    public function cashRegisterSessions()
    {
        return $this->hasMany(CashRegisterSession::class, 'user_id');
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function shipmentsWithoutInvoice()
    {
        return $this->shipments()
            ->where('company_id', $this->company_id)
            ->where('bulk', false)
            ->whereDoesntHave('receiptDetail')
            ->whereNotNull('tracking_number')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereDate('created_at', '>=', self::POS_START_VERIFICATION_DATE)
                        ->whereNotIn('type', ['cancelled']);
                })
                    ->orWhere(function ($q) {
                        $q->where('type', 'in_progress');
                    });
            })
            ->with('carrier', 'service')
            ->withService()
            ->get();
    }

    public function insurancesWithoutInvoice()
    {
        return $this->insurances()
            ->where('company_id', $this->company_id)
            ->where('status', 'completed')
            // ->whereDoesntHave('invoice')
            ->whereDoesntHave('invoiceDetail')
            ->with('carrier')
            ->get();
    }

    public function refundsWithoutInvoice()
    {
        return $this->company->refunds()
            ->where('company_id', $this->company_id)
            ->whereDoesntHave('invoiceDetail')
            ->with('refundable.invoiceDetail.invoice')
            ->get();
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        return $this->company ? $this->company->local : 'fr';
    }

    public function getCurrentCashRegisterSessionAttribute()
    {
        return $this->cashRegisterSessions()
            ->where('status', 'opening')
            ->with('invoices', 'cashRegister')
            ->first();
    }

    public function sales()
    {
        return LocalInvoice::where('user_id', $this->id)
            ->where('company_id', $this->company_id);
    }

    public function POSProducts()
    {
        return $this->hasMany(Product::class);
    }

    public function codes()
    {
        return $this->hasMany(FreeLabelCode::class);
    }

    public function toggleActive()
    {
        if ($this->active) {
            $this->timesheets()->where('scheduled_start_date', '>', today())->delete();
        }

        $this->update([
            'active' => !$this->active
        ]);
    }

    public function timesheetLogs()
    {
        return $this->hasMany(TimesheetLog::class, 'user_id');
    }

    public function getHasOpenSessionAttribute()
    {
        if ($this->account_type === 'business' || request()->session()->has('spark:impersonator') || $this->company->is_retail_reseller) {
            return true;
        }

        return $this->timesheetLogs()
            ->whereDate('check_in', today())
            ->whereNull('check_out')
            ->exists();
    }

    public function ipAddresses()
    {
        return $this->hasMany(IpAddress::class);
    }

    public function userInvoices()
    {
        return $this->hasMany(AppLocalInvoice::class);
    }

    public function getHasInvoicesAttribute()
    {
        return $this->localInvoices()->count() > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function vacations()
    {
        return $this->hasMany(Vacation::class);
    }

    public function referral()
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    public function getIpAllowedAttribute()
    {
        if (!$this->ip_restrictions) {
            return true;
        }

        if ($this->company->is_retail_reseller && isset($this->company->theme_setting['use_ip_restriction']) && $this->company->theme_setting['use_ip_restriction'] == false) {
            return true;
        }

        if (request()->session()->has('spark:impersonator')) {
            return true;
        }

        if (env('WHITE_LABEL_COUNTRY', 'CA') === 'MA') {
            return true;
        }

        $ip = $this->ipAddresses()->where('ip', UserIpAddress::getUserIp())->first();

        if (($ip && $ip->allowed) || UserIpAddress::getUserIp() === $this->company->ip_address || $this->companies()->where('ip_address', UserIpAddress::getUserIp())->exists()) {
            return true;
        }

        return false;
    }

    public function getAvailabilitySortedAttribute()
    {
        return collect($this->availability)->sortBy('index')->toArray();
    }

    public function unavailabilities()
    {
        return $this->hasMany(AgentAvailability::class);
    }

    public function prepaidCards()
    {
        return $this->hasMany(PrepaidCard::class, 'agent_id');
    }

    public function inventoryReports()
    {
        return $this->hasMany(InventoryReport::class);
    }

    public function chatGroups()
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_members', 'user_id', 'group_id')
            ->using(ChatGroupMember::class)
            ->withTimestamps();
    }

    public function getPhotoUrlAttribute($value)
    {
        return empty($value) ? self::defaultPhoto() : $value;
    }

    public static function defaultPhoto()
    {
        return asset('assets/images/user.png');
    }

    public function todoListResponses()
    {
        return $this->belongsToMany(TodoList::class, 'company_todo_list_response')
            ->using(CompanyTodoListResponse::class)
            ->withPivot([
                'company_id',
                'tasks',
                'confirmed',
                'created_at',
                'updated_at'
            ]);
    }

    public function hasHalfTimeTodoList()
    {
        return ($todoList = $this->company->halfTimeTodoList()) &&

            $this->timesheets()
                ->whereDate('scheduled_start_date', today())
                ->whereTime('scheduled_end_date', '>=', now()->subMinutes(30)->format("H:i"))
                ->whereTime('scheduled_end_date', '<=', now()->addMinutes(30)->format("H:i"))
                ->exists() &&

            !$this->hasTodoResponse($todoList->id, true);
    }

    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'to_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'from_id');
    }

    public function getUnreadMessagesAttribute()
    {
        $u2u = $this->receivedMessages
            ->where('seen', false)
            ->where('type', 'user')
            ->count();

        $group = ChatMessage::whereIn('to_id', $this->chatGroups->pluck('id')->toArray())
            ->where('from_id', '!=', auth()->id())
            ->where('type', 'group')
            ->get()
            ->sum(function ($msg) {
                return collect($msg->group_seen_details)->where('id', $this->id)->count() === 0 ? 1 : 0;
            });

        return $u2u + $group;
    }

    public function scopeRetailUser($query)
    {
        return $query
            ->where('account_type', 'retail')
            ->active()
            ->whereNull('hidden_at');
    }

    public function hasTodoResponse($id, $confirmed = false)
    {
        return CompanyTodoListResponse::where('user_id', $this->id)
            ->where('company_id', $this->company_id)
            ->where('todo_list_id', $id)
            ->whereDate('created_at', today()->format("Y-m-d"))
            ->when($confirmed, function ($q) {
                $q->where('confirmed', true);
            })
            ->first();
    }

    public function getIsAdminAttribute()
    {
        return in_array($this->email, Spark::$adminDevelopers);
    }

    public static function generateUniqueConsumerCardCode(): int
    {
        do {
            $code = rand(11111, 99999);
        } while (User::where('consumer_card_code', $code)->exists());

        return $code;
    }

    public function consumerShipments()
    {
        return $this->hasMany(Shipment::class, 'consumer_id');
    }

    public function consumerInvoices()
    {
        return $this->hasMany(LocalInvoice::class, 'consumer_id');
    }

    public function consumerDropoffs()
    {
        return $this->hasMany(DropOff::class, 'consumer_id');
    }

    public function consumerFavoriteStores()
    {
        return $this->belongsToMany(Company::class, 'consumer_favorite_stores')
            ->using(ConsumerFavoriteStore::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    public function getTrainingNameAttribute()
    {
        if (!$this->in_training) {
            return $this->name;
        }

        $totalHours = TimesheetService::getTotalHoursForUserAllTime(null, $this->id);
        if ($totalHours <= 80) {
            return $this->name . " (In Training)";
        }

        $this->update(['in_training' => false]);

        return $this->name;
    }

    public function getLastLoginAttribute()
    {
        return $this->loginActivities->sortByDesc('created_at')->pluck('created_at')->first();
    }

    public function refundCodes()
    {
        return $this->hasMany(RefundCode::class);
    }

    public function managerRefundCodes()
    {
        return $this->hasMany(RefundCode::class, 'manager_id');
    }

    public function shipmentsWithoutPhotosQuery($date = null)
    {
        if (!$date) {
            $date = now();
        }

        $date = $date->format('Y-m-d H:i:s');

        return $this->shipments()
            ->whereDate('created_at', '>=', Shipment::START_DATE_WHEN_COMMENTS_ARE_REQUIRED)
            ->where('created_at', '<=', $date)
            ->whereDoesntHave('comments', function ($q) {
                $q->where('comment', 'Image');
            })
            ->whereHas('receiptDetail')
            ->exportShipments();
    }

    public function getToken()
    {
        if ($this->api_token) {
            return $this->api_token;
        }

        $token = $this->createToken('API TOKEN')->plainTextToken;
        $this->api_token = $token;
        $this->save();

        return $token;
    }

    public function agentCommissions()
    {
        return $this->hasMany(AgentCommission::class, 'user_id');
    }

    public function giveCommission($commissionAmount, $commissionType, $commissionValue, $detail, $status = 'pending', $commissionable)
    {
        return $this->agentCommissions()->create([
            'company_id' => $detail->invoice->company_id,
            'invoice_detail_id' => $detail->id,
            'commission_amount' => round($commissionAmount, 2),
            'commission_value' => $commissionValue,
            'commission_type' => $commissionType,
            'commissionable_type' => $detail->invoiceable_type,
            'commissionable_id' => $detail->invoiceable_id,
            'status' => $status,
            'commission_id' => $commissionable->id,
        ]);
    }

    public function agentWarnings()
    {
        return $this->hasMany(AgentWarning::class);
    }

    public function agentWarningsByDates()
    {
        $from = today();
        $to = today();

        if (request('analysis_period') === 'yesterday') {
            $from = today()->subDay();
            $to = today()->subDay();
        }

        if (request('analysis_period') === 'this_week') {
            $from = today()->startOfWeek();
            $to = today();
        }

        if (request('analysis_period') === 'this_month') {
            $from = today()->startOfMonth();
            $to = today();
        }

        if (request('analysis_period') === 'last_month') {
            $from = today()->subMonth()->startOfMonth();
            $to = today()->subMonth()->endOfMonth();
        }

        return $this->hasMany(AgentWarning::class)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
    }

    /**
     * Get all of the local invoices.
     */
    public function localInvoices()
    {
        return $this->hasMany(LocalInvoice::class)->orderBy('id', 'desc');
    }

    /**
     * Get all business to business roles.
     */
    public function businessRoles(): BelongsToMany
    {
        return $this->belongsToMany(BusinessToBusinessRole::class, 'business_to_business_user_roles');
    }

    /**
     * Get all activities login.
     */
    public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class);
    }

    /**
     * @throws ApiErrorException
     * @throws CustomerAlreadyCreated
     * @throws ValidationException
     */
    public function addPaymentCreditCard($details)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        try {
            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => $details['card_number'],
                    'exp_month' => $details['card_expiry_month'],
                    'exp_year' => $details['card_expiry_year'],
                    'cvc' => $details['card_cvc'],
                ],
                'billing_details' => [
                    'name' => $details['card_holder'],
                ]
            ]);
        } catch (CardException $ex) {
            throw ValidationException::withMessages([
                $ex->getStripeParam() => [$ex->getError()->message],
            ]);
        }

        if ($this->stripe_id) {
            $this->addPaymentMethod($paymentMethod);
        } else {
            $this->createAsStripeCustomer();
            $this->addPaymentMethod($paymentMethod);
        }

        $this->updateDefaultPaymentMethod($paymentMethod);
    }

    public function workingShifts()
    {
        return $this->hasMany(WorkingShift::class);
    }

    public function getCanViewCostAttribute()
    {
        $company = $this->loadMissing('companies')->companies->where('id', $this->company_id)->first();
        if(!$company)
            return false;

        return $company->pivot->can_view_cost;
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'user_coupons')->withTimestamps();
    }
}
