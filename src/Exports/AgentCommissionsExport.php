<?php

namespace ExpertShipping\Spl\Exports;

use ExpertShipping\Spl\Services\PayPeriodsService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class AgentCommissionsExport implements  FromView, WithCustomCsvSettings
{
    private $commissionable;

    public function __construct($commissionable)
    {
        $this->commissionable = $commissionable;
    }

    public function view(): View
    {
        $data = PayPeriodsService::getPayPeriodFromRequest();
        $start = $data['start'];
        $end = $data['end'];
        $paymentPeriod = $data['paymentPeriod'];

        $agentCommissions = $this->commissionable
            ->agentCommissions()
            ->where(function($q) use ($start, $end){
                $q->where(function($q) use ($start, $end){
                    $q->whereDate('created_at', '>=', $start)
                        ->whereDate('created_at', '<=', $end)
                        ->whereNotIn('status', ['pending', 'cancelled', 'paid']);
                })
                ->orWhere(function($q) use ($start){
                    $q->whereNotIn('status', ['pending', 'cancelled', 'paid'])
                        ->whereDate('created_at', '<', $start);
                });
            })
            ->when(request('agent_type') === 'employed', function($q){
                $q->whereHas('agent', function($q){
                    $q->where('agent_type', 'employed');
                });
            })
            ->when(request('agent_type') === 'self-employed', function($q){
                $q->whereHas('agent', function($q){
                    $q->where('agent_type', 'self-employed');
                })
                ->when(request('all_or_selected') === 'selected', function($q){
                    $q->whereIn('user_id', request('agents'));
                });
            })
            ->get();

        $agentCommissions2 = collect();

        foreach ($agentCommissions as $agentCommission) {
            $hasWarning = $agentCommission->agent
                ->agentWarnings()
                ->whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->exists();

            if($hasWarning){
                $amount = $agentCommission->palier_1_amount;
            }else{
                $amount = $agentCommission->palier_2_amount;
            }

            if($agentCommission->paid_amount < 0){
                $amount = $agentCommission->paid_amount;
            }

            $array = [
                'created_at' => $agentCommission->created_at->format('Y-m-d'),
                'name' => $agentCommission->agent->name  ?? '',
                'amount' => round($amount, 2),
                'commission' => $this->commissionable->name,
            ];

            if(request('reason') == 'pay'){
                $agentCommission->update([
                    'status' => 'paid',
                    'paid_amount' => $amount,
                    'paid_at' => now(),
                    'payment_period' => $paymentPeriod,
                ]);
            }

            $agentCommissions2->push($array);
        }

        return view('exports/agent-commissions', ['agentCommissions'=> $agentCommissions2]);
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => '	'
        ];
    }
}
