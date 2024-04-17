<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Mailbox\MailboxConversation;
use ExpertShipping\Spl\Models\Mailbox\MailboxEmail;
use ExpertShipping\Spl\Models\Mailbox\MailboxFolder;
use ExpertShipping\Spl\Models\Mailbox\Services\MailboxImapConnection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ExpertShipping\Spl\Enum\CompanyStatusEnum;
use ExpertShipping\Spl\Enum\PlanSubscriptionStatusEnum;
use ExpertShipping\Spl\Models\LocalInvoice as ModelsLocalInvoice;
use Illuminate\Support\Str;

class Company extends Model
{

    const ACCOUNT_TYPE_RETAIL = 'retail';
    const ACCOUNT_TYPE_BUSINESS = 'business';
    const ACCOUNT_TYPE_RETAIL_RESELLER = 'retail_reseller';
    const ACCOUNT_TYPE_CONSUMER = 'consumer';


    protected $appends = ['reseller', 'valid_subscription'];
    protected $connection = 'mysql';

    //    use Searchable;

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $data = [
            'id' => $array['id'],
            //            'from_name' => $array['from_name'],
            'from_company' => $array['name'],
            //            'from_email' => $array['from_email'],
            //            'from_phone' => $array['phone'],
            //            'to_name' => $array['to_name'],
            //            'to_company' => $array['to_company'],
            //            'to_email' => $array['to_email'],
            //            'to_phone' => $array['to_phone'],
        ];

        return $data;
    }

    protected $guarded = [];

    protected $casts = [
        'rate_visibility'               => 'array',
        'instant_payment'               => 'boolean',
        'sales_targets'                 => 'array',
        'moneris'                       => 'boolean',
        'pos_order'                     => 'array',
        'categories_order'              => 'array',
        'is_retail_reseller'            => 'boolean',
        'theme_setting'                 => 'array',
        'lat_lng'                       => 'array',
        'show_on_mobile'                => 'boolean',
        'daily_opening_check'           => 'boolean',
        'daily_opening_check_active'    => 'boolean',
        'active_schedule'               => 'boolean',
        'has_fix_from_address'          => 'boolean',
        'purolator_arpc_auto'           => 'boolean',
        'legal_details'                 => 'array',
        'mailbox_alias'                 => 'array',
        'status'                        =>  CompanyStatusEnum::class,
        'update_form'                   =>  'array'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
            $model->rate_visibility = [
                "full_rate" => false,
                "discount_rate" => true
            ];
        });

        self::updating(function (self $model) {
            if ($model->isDirty('mailbox_username')) {
                $model->mailboxFolders()->delete();
                $model->mailboxEmails()->delete();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discounts()
    {
        return $this->hasMany(CompanyDiscount::class);
    }

    public function notPaidInvoices($user = null)
    {
        $user = $user ?: auth()->user();
        return $user->localInvoices()
            ->whereNull('paid_at')
            ->whereNull('refunded_at')
            ->whereNull('canceled_at')
            ->get()->sum('total');
    }

    public function limitDateExceeded($user = null)
    {
        if (!$this->date_limit || $this->date_limit === "NO_LIMIT") {
            return false;
        }

        if ($this->date_limit === 'null') {
            $dateLimit = now()->subWeeks(1);
        } else {
            $dateLimit = now()->subWeeks($this->date_limit ?? 1);
        }

        $user = $user ?: auth()->user();

        return $user->localInvoices()
            ->whereNull('paid_at')
            ->whereNull('refunded_at')
            ->whereDate('created_at', '<', $dateLimit)
            ->whereNull('canceled_at')
            ->count() > 0;
    }

    public function isReachedLimit($rate, $user = null)
    {

        if (!$this->invoice_limit) {
            return false;
        }

        $user = $user ?: auth()->user();
        return ((float) $this->notPaidInvoices($user ?: auth()->user()) + (float) $rate > (float) $this->invoice_limit)  || $this->limitDateExceeded($user ?: auth()->user());
    }

    public function getCompanyGivenPackagesAttribute()
    {
        return $this->discounts()
            ->join('discount_packages', 'company_discounts.discount_package_id', '=', 'discount_packages.id')
            ->select('discount_packages.name')
            ->distinct()
            ->get();
    }

    public function googleBusinesses()
    {
        return $this->hasMany(GoogleBusiness::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(CompanyUser::class)
            ->withPivot(['app_role_id', 'activate_notification'])
            ->withTimestamps();
    }

    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function timesheetLogs()
    {
        return $this->hasMany(TimesheetLog::class);
    }

    public function usersWithoutManagers()
    {
        return $this->users()
            ->where('active', true)
            ->wherePivot('app_role_id', AppRole::where('slug', 'user')->first()->id)
            ->get();
    }

    public function getManagersAttribute()
    {
        return $this->users()
            ->where('active', true)
            ->wherePivot('app_role_id', AppRole::where('slug', 'manager')->first()->id)
            ->get();
    }

    /**
     * Get the company carriers.
     */
    public function carriers()
    {
        return $this->hasMany(CompanyCarrier::class);
    }

    public function activeCarriers()
    {
        return $this->belongsToMany(Carrier::class, 'active_carrier_companies')
            ->using(ActiveCarrierCompany::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    public function sales()
    {
        return ModelsLocalInvoice::where('company_id', $this->id);
    }

    public function monthlyTargets()
    {
        return $this->hasMany(MonthlyTarget::class);
    }

    public function target($date = null)
    {
        if ($date) {
            $date = Carbon::create($date);
        } else {
            $date = now();
        }

        $month = $date->month;
        $year = $date->year;

        $targetRow = $this->monthlyTargets->where('year', $year)->first();

        if ($targetRow) {
            return $targetRow->details[str_pad($month, 2, '0', STR_PAD_LEFT)];
        }

        return 0;
    }

    public static function targetSum($date = null)
    {
        if ($date) {
            $date = Carbon::create($date);
        } else {
            $date = now();
        }

        $month = $date->month;
        $year = $date->year;

        $targets = MonthlyTarget::where('year', $year)->get();

        return $targets->sum(function ($target) use ($month) {
            return $target->details[str_pad($month, 2, '0', STR_PAD_LEFT)];
        });
    }

    public function taxNumbers()
    {
        return $this->hasMany(TaxNumber::class);
    }

    public function getTaxNumber($slug)
    {
        $taxNumber = $this->taxNumbers()->where('tax_slug', $slug)->first();

        if ($taxNumber) {
            return $taxNumber->number;
        }
        return null;
    }

    public function freeCodes()
    {
        return $this->hasMany(FreeLabelCode::class);
    }

    public function inventoryMouvements()
    {
        return $this->hasMany(InventoryMouvement::class);
    }

    public function localInvoices()
    {
        return $this->hasMany(LocalInvoice::class)->orderBy('id', 'desc');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class)->orderBy('id', 'desc');
    }

    public function addSolde($amount)
    {
        $this->solde = $this->solde + $amount;
        $this->save();
    }

    public function products()
    {
        return $this->hasMany(CompanyProduct::class);
    }

    public function packages()
    {
        return $this->hasMany(CompanyPackage::class);
    }

    public function agents()
    {
        return $this->belongsToMany(User::class)
            ->using(CompanyUser::class)
            ->withPivot('app_role_id')
            ->withTimestamps();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'company_services')
            ->using(CompanyService::class)
            ->withPivot(['user_id']);
    }

    public function hiddenProducts()
    {
        return $this->belongsToMany(Product::class, 'hidden_products')
            ->using(HiddenProduct::class)
            ->withPivot(['manager_id', 'created_at', 'updated_at']);
    }

    public function emailPreferences()
    {
        return $this->belongsToMany(EmailPreference::class)
            ->using(CompanyEmailPreference::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    public function getCarrierAccount($carrierSlug, $action)
    {
        $companyCarrier = $this->carriers()->whereHas('carrier', fn ($q) => $q->where('slug', $carrierSlug))->first();
        $accountCarrier = null;
        if ($companyCarrier) {
            $selectedAccount = [
                'id' => $companyCarrier->pivot->id,
                'type' => CompanyCarrier::class,
                'carrier' => Str::upper($companyCarrier->slug),
                'api_credentials' => $companyCarrier->pivot->options
            ];
        } else {
            $accountCarrier = AccountCarrier::whereHas('carrier', function ($q) use ($carrierSlug) {
                $q->where('slug', $carrierSlug);
            })
                ->where($action, true)
                ->first();
            if ($accountCarrier) {
                $selectedAccount = [
                    'id' => $accountCarrier->id,
                    'type' => AccountCarrier::class,
                    'carrier' => Str::upper($accountCarrier->carrier->slug),
                    'api_credentials' => $accountCarrier->api_credentials
                ];
            } else {
                $selectedAccount = null;
            }
        }

        return $selectedAccount;
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function getResellerAttribute()
    {
        if ($this->is_retail_reseller) {
            return [
                'has_pos' => $this->theme_setting['pos'] ?? false,
                // 'has_pos' => isset($this->theme_setting['pos']) && $this->user->subscriptions()->whereNull('ends_at')->count()>0 ? $this->theme_setting['pos'] : false,
            ];
        }

        if ($this->account_type === 'retail') {
            return [
                'has_pos' => true,
            ];
        }

        return false;
    }

    public function categories()
    {
        return $this->hasMany(CategoryProduct::class);
    }

    public function hiddenCategories()
    {
        return $this->belongsToMany(CategoryProduct::class, 'hidden_categories')
            ->using(HiddenCategories::class)
            ->withPivot(['manager_id', 'created_at', 'updated_at']);
    }

    public function todoListResponses()
    {
        return $this->belongsToMany(TodoList::class, 'company_todo_list_response')
            ->using(CompanyTodoListResponse::class)
            ->withPivot([
                'user_id',
                'tasks',
                'created_at',
                'updated_at'
            ]);
    }

    public function todoLists()
    {
        return $this->belongsToMany(TodoList::class, 'todo_list_company')
            ->using(TodoListCompany::class)
            ->withPivot([
                'created_at',
                'updated_at'
            ]);
    }

    public function halfTimeTodoList()
    {
        return TodoList::where(function ($q) {
            $q->whereHas('stores', function ($query) {
                $query->where('companies.id', $this->id);
            })
                ->orWhere('for_all_stores', true);
        })
            ->where('for_half_time_sessions', true)
            ->with(['tasks'])
            ->first();
    }

    public function moringTodoList()
    {
        return TodoList::where(function ($q) {
            $q->whereHas('stores', function ($query) {
                $query->where('companies.id', $this->id);
            })
                ->orWhere('for_all_stores', true);
        })
            ->whereTime('from', '<', "12:00")
            ->with(['tasks'])
            ->first();
    }

    public function afternoonTodoList()
    {
        return TodoList::where(function ($q) {
            $q->whereHas('stores', function ($query) {
                $query->where('companies.id', $this->id);
            })
                ->orWhere('for_all_stores', true);
        })
            ->whereTime('to', '>=', "18:00")
            ->with(['tasks'])
            ->first();
    }

    public function allTodoLists()
    {
        $todoListsForAllStores = TodoList::where('for_all_stores', true)
            ->whereTime('to', '>=', date('H:i'))
            ->whereTime('from', '<=', date('H:i'))
            ->get();

        return $this->todoLists()
            ->whereTime('to', '>=', date('H:i'))
            ->whereTime('from', '<=', date('H:i'))
            ->get()
            ->merge($todoListsForAllStores);
    }

    public function carrierPickups()
    {
        return $this->hasMany(CarrierPickup::class);
    }

    // public function getOpenAtAttribute(){
    //     return Carbon::createFromFormat('H:i:s', $this->attributes['open_at'] ?? '00:00:00')->format('H:i');
    // }

    // public function getCloseAtAttribute(){
    //     return Carbon::createFromFormat('H:i:s', $this->attributes['close_at'] ?? '00:00:00')->format('H:i');
    // }

    public function carriersDropoffPrices()
    {
        return $this->belongsToMany(Carrier::class, 'carrier_company_dropoff_price')
            ->using(CarrierCompanyDropoffPrice::class)
            ->withPivot(['id', 'price', 'created_at', 'updated_at']);
    }

    public function discountPackages()
    {
        return $this->belongsToMany(DiscountPackage::class)
            ->using(CompanyDiscountPackage::class)
            ->withTimestamps();
    }


    public function getPrimaryColorAttribute()
    {
        if ($this->account_type === 'business' || $this->activate_custom_brand) {
            return $this->attributes['primary_color'];
        }
        return "#20C4F4";
    }

    public function getSecondaryColorAttribute()
    {
        if ($this->account_type === 'business' || $this->activate_custom_brand) {
            return $this->attributes['secondary_color'];
        }
        return "#20C4F4";
    }

    public function getValidSubscriptionAttribute()
    {
        return $this->planSubscriptions()->with(['plan_package', 'plan_package.plan'])->where("status", PlanSubscriptionStatusEnum::IN_USE->value)->first();
    }

    public static function timesheetQuery()
    {
        return self::query()
            ->whereHas('users', function ($query) {
                $query->where('users.account_type', 'retail')
                    ->when(request()->has('agent') && request('agent') !== "null", function ($query) {
                        $query->where('users.id', request('agent'));
                    })
                    ->when(request()->has('agent_type') && request('agent_type') !== "null", function ($query) {
                        $query->where('users.agent_type', request('agent_type'));
                    });
            })
            ->where('account_type', 'retail')
            ->where('is_retail_reseller', false)
            ->when(request()->has('store') && request('store') !== "null", function ($query) {
                $query->where('id', request('store'));
            });
    }

    public function discountPackage()
    {
        return $this->belongsTo(DiscountPackage::class);
    }

    public function carrierEvents()
    {
        return $this->hasMany(CarrierEvent::class);
    }

    public function dailyCarrierEventsCheck()
    {
        return $this->carrierEvents()
            ->whereDate('created_at', today())
            ->whereIn('meta_data->update', ['ARPC_1', 'ARPC_3', 'ARPC_4', 'ARPC_6', 'ARPC_5', 'REFUSED'])
            ->exists();
    }

    public function dailyARPCCheck()
    {
        return $this->carrierEvents()
            ->whereDate('created_at', today())
            ->where('meta_data->update', 'ARPC_1')
            ->exists();
    }

    public function store()
    {
        return $this->belongsTo(Company::class, 'store_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class)->orderBy('id', 'desc');
    }

    public function mailboxEmails()
    {
        return $this->hasMany(MailboxEmail::class, 'company_id');
    }

    public function mailboxFolders()
    {
        return $this->hasMany(MailboxFolder::class, 'company_id');
    }

    public function mailboxConvesations()
    {
        return $this->hasMany(MailboxConversation::class, 'company_id');
    }

    /**
     * Get the mailbox connection for the company.
     *
     * @return Mailbox
     */
    public function getMailboxConnection()
    {
        $mailboxImapConnection = new MailboxImapConnection(
            $this->mailbox_imap_host,
            $this->mailbox_imap_port,
            $this->mailbox_username,
            $this->mailbox_password
        );

        return $mailboxImapConnection->mailbox;
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function soldeTransactions()
    {
        return $this->hasMany(SoldeTransaction::class);
    }

    public function referrals()
    {
        return $this->hasMany(Company::class, 'referral_id');
    }

    public function getEarningByCompanyAttribute()
    {
        // (Rate - cost)*2%
        return $this->referrals->map(function ($company) {
            $rates = $company->shipments
                ->whereNull('referral_payout_id')
                ->whereNotIn('type', ['cancelled', 'draft'])
                ->sum(function ($shipment) {
                    return $shipment->rate - $shipment->cost_rate;
                });

            if ($this->referral_percentage > 0) {
                $earning = $rates * ($this->referral_percentage / 100);
            } else {
                $earning = 0;
            }

            $ratesPayable = $company->shipments()
                ->whereNull('referral_payout_id')
                ->whereNotIn('type', ['cancelled', 'draft'])
                ->whereHas('carrierInvoices', function ($q) {
                    $q->where('carrier_invoices.status', 'verified');
                })
                ->sum(DB::raw('((rate/100) - cost_rate)'));

            if ($this->referral_percentage > 0) {
                $earningPayable = $ratesPayable * ($this->referral_percentage / 100);
            } else {
                $earningPayable = 0;
            }

            return [
                'name' => $company->user->name,
                'company' => $company->name ?? null,
                'signup_date' => $company->created_at->format('d/m/Y'),
                'active_account' => $company->user->active,
                'count_shipments' => $company->shipments->whereNotIn('type', ['cancelled', 'draft'])->count(),
                'earning' => round($earning, 2),
                'payable' => round($earningPayable, 2),
            ];
        });
    }

    public function referralPayouts()
    {
        return $this->hasMany(ReferralPayout::class);
    }

    public function planSubscriptions(): HasMany
    {
        return $this->hasMany(PlanSubscription::class);
    }

    public function getStatusColorAttribute()
    {
        $statusColors = [
            CompanyStatusEnum::ACTIVE->value => 'success',
            CompanyStatusEnum::SUSPENDED->value => 'warning',
        ];

        return $statusColors[$this->status?->value] ?? 'error';
    }

    public function getStatusTextAttribute()
    {
        $statusTexts = [
            CompanyStatusEnum::ACTIVE->value => 'Active',
            CompanyStatusEnum::SUSPENDED->value => 'Suspended',
            CompanyStatusEnum::PENDING->value => 'Waiting for validation',
            CompanyStatusEnum::SIGNUP->value => 'Signup',
            CompanyStatusEnum::EMAIL_VERIFICATION->value => 'Email verification',
        ];

        return $statusTexts[$this->status?->value] ?? 'Refused';
    }

    public function scopeFilterBySearch($query, $term)
    {
        $query->where(function ($query) use ($term) {
            $query->when($term, function ($query, $term) {
                collect(str_getcsv($term, ' ', '"'))->filter()->each(function ($term) use ($query) {
                    $term = $term . "%";
                    $query->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('users', function ($query) use ($term) {
                            $query->where('name', 'like', '%' . $term . '%');
                        });;
                });
            });
        });
    }

    public function scopeFilterByBillingOption($query, $billingOption)
    {
        $query->when($billingOption, function ($query, $billingOption) {
            $query->where('instant_payment', $billingOption);
        });
    }

    public function scopeFilterByDiscountPackage($query, $discountPackage)
    {
        $query->when($discountPackage, function ($query, $discountPackage) {
            $query->whereHas('discountPackage', function ($query) use ($discountPackage) {
                $query->where('id', $discountPackage);
            });
        });
    }
    public function scopeFilterByStatus($query, $status)
    {
        $query->when($status, function ($query, $status) {
            $query->where('status', $status);
        });
    }

    public function unblockedRequests()
    {
        return $this->hasMany(unblockedRequest::class);
    }
}
