<div class="page-1">
    <br>
    <br>

    <table width="100%">
        <tr>
            <td>
                <div class="bill-to-title">
                    {{ __('Bill To') }}
                </div>

                <div class="bill-to-details">
                    {{ $invoice->company->name }}<br>
                    {{ $invoice->company->addr1 }}, {{ $invoice->company->city }}, {{ $invoice->company->country }}<br>
                    {{ $invoice->company->phone }}<br>
                </div>
            </td>
            <td align="right">
                <table width="100%">
                    <tr>
                        <td align="right">
                            <div class="invoice-date-title">
                                {{ __('Invoice Date') }} :
                            </div>
                        </td>
                        <td width="120px" align="right">
                            <div class="invoice-date-details">
                                {{ $invoice->created_at->format('d M Y') }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" align="right">
                            <div class="invoice-date-title">
                                {{ __('Total') }} :
                            </div>
                        </td>
                        <td align="right">
                            <div class="invoice-date-details">
                                <strong>{{ SplMoney::format($invoice->total) }}</strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" align="right">
                            <div class="invoice-date-title">
                                {{ __('Paid Amount') }} :
                            </div>
                        </td>
                        <td align="right">
                            <div class="invoice-date-details">
                                <strong>{{ SplMoney::format($invoice->total_paid_amount) }}</strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" align="right">
                            <div class="invoice-date-title">
                                {{ __('Due Amount') }} :
                            </div>
                        </td>
                        <td align="right">
                            <div class="invoice-date-details">
                                <strong>{{ SplMoney::format($invoice->total_due_amount) }}</strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            <div class="invoice-date-title">
                                {{ __('Invoice Status') }} :
                            </div>
                        </td>
                        <td align="right">
                            <div class="invoice-date-details">
                                {{ str()->headline(__($invoice->status)) }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <br>
    <br>

    <table width="100%">
        <tr>
            <td>
                <div class="billing-information-title">
                    <strong>{{ __('Billing Information') }}</strong>
                </div>
            </td>

            <td align="right">
                <div class="billing-information-title">
                    {{ __('Merchant Name:') }} <strong> {{ env('APP_NAME') }} </strong>
                </div>
            </td>
        </tr>
    </table>

    <hr>

    {{--    <table width="100%">--}}
    {{--        <tr>--}}
    {{--            <td width="130px">--}}
    {{--                <div class="billing-information-title">--}}
    {{--                    {{ __('Credit Card *****1234') }} :--}}
    {{--                </div>--}}
    {{--            </td>--}}
    {{--            <td>--}}
    {{--                <div class="billing-information-title">--}}
    {{--                    <strong>12.0$ USD</strong>--}}
    {{--                </div>--}}
    {{--            </td>--}}
    {{--        </tr>--}}
    {{--        <tr>--}}
    {{--            <td width="130px">--}}
    {{--                <div class="billing-information-title">--}}
    {{--                    {{ __('Solde') }} :--}}
    {{--                </div>--}}
    {{--            </td>--}}
    {{--            <td>--}}
    {{--                <div class="billing-information-title">--}}
    {{--                    <strong>100.0$ USD</strong>--}}
    {{--                </div>--}}
    {{--            </td>--}}
    {{--        </tr>--}}
    {{--    </table>--}}
    {{--    <br>--}}

    <table width="100%" class="table-1">
        <tr>
            <th><div class="text-white">{{ __('# of Shipments') }}</div></th>
            <th><div class="text-white">{{ __('Freight Charges') }}</div></th>
            <th><div class="text-white">{{ __('Fuel Charges') }}</div></th>
            <th><div class="text-white">{{ __('Accessorials') }}</div></th>
            <th><div class="text-white">{{ __('Surcharges') }}</div></th>
            <th><div class="text-white">{{ __('Taxes') }}</div></th>
        </tr>

        <tr>
            <td>{{ $invoice->chargeable_details->count() }}</td>
            <td>{{ SplMoney::format($invoice->total_freight_charges) }}</td>
            <td>{{ SplMoney::format($invoice->total_fuel_charges) }}</td>
            <td>{{ SplMoney::format($invoice->total_other_charges) }}</td>
            <td>{{ SplMoney::format($invoice->total_surcharges) }}</td>
            <td>{{ SplMoney::format($invoice->total_taxes_charges) }}</td>
        </tr>
    </table>

    <hr/>

    <table width="100%">
        <tr>
            <td></td>
            <td align="right" width="220px">
                <table>
                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Subtotal') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($detailsTaxes->preTax) }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Tax Breakdown') }} :
                            </div>
                        </td>
                        <td align="right" width="120px"></td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Taxes') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($invoice->total - $detailsTaxes->preTax) }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Total GST') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($detailsTaxes->taxes['GST']) }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Total PST') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($detailsTaxes->taxes['PST']) }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Total QST') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($detailsTaxes->taxes['QST']) }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Total HST') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($detailsTaxes->taxes['HST']) }}</strong>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Paid Amount') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($invoice->total_paid_amount) }}</strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr/>

    <table width="100%">
        <tr>
            <td></td>
            <td align="right" width="220px">
                <table>
                    <tr>
                        <td width="100px">
                            <div class="billing-information-title">
                                {{ __('Total Amount Due') }} :
                            </div>
                        </td>
                        <td align="right" width="120px">
                            <div class="billing-information-title text-right">
                                <strong>{{ SplMoney::format($invoice->total_due_amount) }}</strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-1">
        <hr>

        <div class="billing-information-title">
            {{ __('Email for General Inquiries:') }} <strong> {{ env('APP_EMAIL') }} </strong>
        </div>

        <hr>

        <table width="100%">
            <tr>
                <td>
                    <div class="billing-information-title">
                        {{ __('GST Number:') }} <strong>  1220736560 TQ0001 </strong>
                    </div>
                </td>
                <td align="right">
                    <div class="billing-information-title">
                        {{ __('QST Number:') }} <strong>  1220736560 TQ0001 </strong>
                    </div>
                </td>
            </tr>
        </table>

        <hr>

        <div class="terms">
            <strong>{{ __('Terms & Conditions') }}</strong> <br>
            <strong>1. </strong> {{ __('You have thirty (30) days from the Invoice Date above to dispute charges on this invoice after which date you will be deemed to have waived your right to dispute these charges.') }} <br>
            <strong>2. </strong> {{ __('Please pay the Amount Due in full by the Payment Due date stated above before initiating any invoice dispute or any claim with Shippayless. to avoid service interruptions. In the event your invoice dispute or claim is approved of by Shippayless., such approved amounts will be credited or remitted to you.') }} <br>
            <strong>3. </strong> {{ __('For further details regarding payment terms, please refer to the Terms of Service on your account details dashboard.') }} <br>
        </div>
    </div>
</div>
