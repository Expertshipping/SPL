<?php

namespace ExpertShipping\Spl\Controllers;

use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\User;
use ExpertShipping\Spl\Notifications\SendClaimLink;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{

    public function sendClaimLink($id, Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $insurance = Insurance::findOrFail($id);

        if(!$insurance->token){
            $insurance->update([
                'token' => Str::random(40)
            ]);
        }

        (new AnonymousNotifiable)
            ->route('mail', $request->email)
            ->notify(new SendClaimLink($insurance));

        return response()->json([
            'message' => __('Insurance claim link has been successfully sent')
        ], 201);
    }

    public function deleteClaim($id)
    {

        $insurance = Insurance::findOrFail($id);

        $insuranceService = app('insurance');

        if($insuranceService->voidTransactionForInsurance($insurance)){
            if($insurance->invoice){
                $insurance->invoice->delete();
            }

            return response()->json([
                'message' => __('Your Insurance transaction has been successfully voided')
            ], 201);
        }

        return response()->json([
            'message' => __('Your demand cannot be completed!'),
        ], 400);
    }
}
