<?php

namespace ExpertShipping\Spl\Controllers;

use ExpertShipping\Spl\Exports\AgentCommissionsExcelExport;
use ExpertShipping\Spl\Jobs\CreateAgentCommissionsExportJob;
use ExpertShipping\Spl\Models\Retail\AgentCommission;
use ExpertShipping\Spl\Services\PayPeriodsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel;

class CommissionController extends Controller
{
    public function export(Request $request){
        $payPeriodRequiredIf = ($request->reason === 'pay' && $request->reason === 'reset') ||
            (
                $request->reason === 'preview' &&
                $request->agent_type === 'employed' &&
                !$request->pay_range_from &&
                !$request->pay_range_to
            );

        $request->validate([
            'agent_type' => 'required|in:employed,self-employed',
            'pay_period' => Rule::requiredIf($payPeriodRequiredIf) . '|nullable',
            'reason' => 'required',
            'pay_range_from' => 'nullable|'.
                Rule::requiredIf($request->agent_type === 'self-employed' && $request->all_or_selected==='selected') .
                '|date',
            'pay_range_to' => 'nullable|'.
                Rule::requiredIf($request->agent_type === 'self-employed' && $request->all_or_selected==='selected')
                .'|date',
            'agents' => 'nullable|'.
                Rule::requiredIf($request->agent_type === 'self-employed' && $request->all_or_selected==='selected') .
                '|array',
            'all_or_selected' => 'nullable|'.
                Rule::requiredIf($request->agent_type === 'self-employed') .
                '|in:all,selected',
        ]);

        if($request->reason === 'reset'){
            $data = PayPeriodsService::getPayPeriodFromRequest();
            $start = $data['start'];
            $end = $data['end'];
            $paymentPeriod = $data['paymentPeriod'];

            AgentCommission::query()
                ->where(function($q) use ($start, $end, $paymentPeriod){
                    $q->where(function($q) use ($start, $end){
                        $q->whereDate('created_at', '>=', $start)
                            ->whereDate('created_at', '<=', $end);
                    })
                        ->orWhere(function($q) use ($paymentPeriod){
                            $q->where('payment_period', $paymentPeriod);
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
                ->whereIn('status', ['paid'])
                ->update([
                    'status' => 'pending_payment',
                    'paid_amount' => 0,
                    'paid_at' => null,
                    'payment_period' => null,
                ]);
        }else{
            try {
                dispatch_sync(new CreateAgentCommissionsExportJob());
                return response()->download(
                    config('filesystems.disks.tmp.root') ."/agent-commissions.zip",
                    'agent-commissions.zip',
                    ['X-Vapor-Base64-Encode' => 'True']
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function excelDownload()
    {
        return Excel::download(
            new AgentCommissionsExcelExport(),
            'agent-commissions.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            ['X-Vapor-Base64-Encode' => 'True']
        );
    }
}
