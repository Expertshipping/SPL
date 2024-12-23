<table width="100%">
    <tr>
        <td width="45%">
            <div class="text-open-sans-11 text-secondary">
                {{ __('Sender')  }} :
            </div>
        </td>

        <td width="10%"></td>

        <td width="45%">
            <div class="text-open-sans-11 text-secondary">
                {{ __('Receiver')  }} :
            </div>
        </td>
    </tr>

    <tr>
        <td width="45%">
            <div class="text-open-sans-11 text-default text-uppercase">
                {{ $shipment->from_city  }},
                {{ $shipment->from_zip_code  }},
                {{ $shipment->from_province  }},
                {{ $shipment->from_country  }}
            </div>
        </td>

        <td width="10%"></td>

        <td width="45%">
            <div class="text-open-sans-11 text-default text-uppercase">
                {{ $shipment->to_city  }},
                {{ $shipment->to_zip_code  }},
                {{ $shipment->to_province  }},
                {{ $shipment->to_country  }}
            </div>
        </td>
    </tr>

    {{--   spacer  --}}
    <tr><td colspan="3" height="10px"></td></tr>

    <tr>
        <td width="45%">
            <div class="text-open-sans-11 text-default">
                <strong>{{ __('Shipment Information')  }}</strong>
            </div>
        </td>

        <td width="10%"></td>

        <td width="45%">
            <div class="text-open-sans-11 text-default">
                <strong>{{ __('Billed Charges')  }}</strong>
            </div>
        </td>
    </tr>

    <tr>
        <td width="45%">
            <hr>
        </td>

        <td width="10%"></td>

        <td width="45%">
            <hr>
        </td>
    </tr>

    <tr>
        <td width="45%">
            <table width="100%">
                <tr>
                    <td class="text-open-sans-8 text-default">
                        {{ __('Ship Date')  }}
                    </td>

                    <td class="text-open-sans-8 text-default text-right">
                        {{ $shipment->start_date->format('d/m/Y') }}
                    </td>
                </tr>

                <tr>
                    <td class="text-open-sans-8 text-default">
                        {{ __('Service Name')  }}
                    </td>

                    <td class="text-open-sans-8 text-default text-right">
                        {{ $shipment->getService()->name }}
                    </td>
                </tr>

                <tr>
                    <td class="text-open-sans-8 text-default">
                        {{ __('Tracking #')  }}
                    </td>

                    <td class="text-open-sans-8 text-default text-right">
                        <a href="{{ $shipment->tracking_link  }}" target="_blank">
                            {{ $shipment->tracking_number }}
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="text-open-sans-8 text-default">
                        {{ __('Payment Method')  }}
                    </td>

                    <td class="text-open-sans-8 text-default text-right">
                        {{ $detail->meta_data['payment_method'] ?? 'N/A' }}
                    </td>
                </tr>

                <tr>
                    <td class="text-open-sans-8 text-default">
                        {{ __('Payment Transaction Number')  }}
                    </td>

                    <td class="text-open-sans-8 text-default text-right">
                        {{ $detail->meta_data['payment_transaction_number'] ?? 'N/A' }}
                    </td>
                </tr>

                <tr>
                    <td class="text-open-sans-8 text-default">
                        {{ __('Payment Date')  }}
                    </td>

                    <td class="text-open-sans-8 text-default text-right">
                        {{ $detail->meta_data['payment_date'] ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </td>

        <td width="10%"></td>

        <td width="45%">
            @if(isset($surcharge))
                <table width="100%">
                    <tr>
                        <td class="text-open-sans-8 text-default">
                            Surcharge
                        </td>

                        <td class="text-open-sans-8 text-default text-right">
                            {{ $surcharge->name }} : {{ $surcharge->description }}
                        </td>
                    </tr>
                </table>
                <hr>
            @else
                @php
                    $arrayDetails = $shipment->{$rateDetailsAttribute}['rateDetails'] ?? $shipment->{$rateDetailsAttribute} ?? null;
                    $sumArray = collect($arrayDetails)->sum('amount');
                @endphp
                @if(isset($arrayDetails))
                    <table width="100%">
                        @foreach($arrayDetails as $rateDetail)
                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ $rateDetail['type'] }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    {{ SplMoney::format($rateDetail['amount'], $rateDetail['currency']) }}
                                </td>
                            </tr>
                        @endforeach
                        @if($shipment->company->is_retail_reseller && $shipment->company->billing_system === 'periodic' && $sumArray < $detail->total)
                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Expert Shipping Marge') }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    {{ SplMoney::format($detail->total - $sumArray, $shipment->company->currency) }}
                                </td>
                            </tr>
                        @endif
                    </table>
                    <hr>
                @endif
            @endif

            <table width="100%">
                <tr>
                    <td class="text-open-sans-11 text-default">
                        <strong>{{ __('Total') }} :</strong>
                    </td>

                    <td class="text-open-sans-11 text-default text-right">
                        <strong>{{ SplMoney::format($detail->total) }}</strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<hr/>
<br>
