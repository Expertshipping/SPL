<div class="page-3">
    @foreach($invoice->chargeable_details as $key => $detail)
        @if($detail->invoiceable instanceof \App\Shipment)
            <table width="100%">
                <tr>
                    <td width="40%">
                        <div class="text-open-sans-11 text-secondary">
                            {{ __('Sender')  }} :
                        </div>
                    </td>

                    <td width="20%"></td>

                    <td width="40%">
                        <div class="text-open-sans-11 text-secondary">
                            {{ __('Receiver')  }} :
                        </div>
                    </td>
                </tr>

                <tr>
                    <td width="40%">
                        <div class="text-open-sans-9 text-default text-uppercase">
                            {{ $detail->invoiceable->from_name  }} <br>
                            {{ $detail->invoiceable->from_address_1  }} <br>
                            {{ $detail->invoiceable->from_city  }},
                            {{ $detail->invoiceable->from_zip_code  }},
                            {{ $detail->invoiceable->from_province  }},
                            {{ $detail->invoiceable->from_country  }}
                        </div>
                    </td>

                    <td width="20%"></td>

                    <td width="40%">
                        <div class="text-open-sans-9 text-default text-uppercase">
                            {{ $detail->invoiceable->to_name  }} <br>
                            {{ $detail->invoiceable->to_address_1  }} <br>
                            {{ $detail->invoiceable->to_city  }},
                            {{ $detail->invoiceable->to_zip_code  }},
                            {{ $detail->invoiceable->to_province  }},
                            {{ $detail->invoiceable->to_country  }}
                        </div>
                    </td>
                </tr>

            </table>

            <br>

            <table width="50%">
                <tr>
                    <td width="100%">
                        <div class="text-open-sans-11 text-default">
                            <strong>{{ __('Shipment Information')  }}</strong>
                        </div>
                    </td>
                </tr>
            </table>

            <table width="100%">
                <tr>
                    <td width="100%">
                        <hr>
                    </td>
                </tr>
            </table>

            <table width="50%">
                <tr>
                    <td width="100%">
                        <table width="100%">
                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Ship Date')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    {{ $detail->invoiceable->start_date }}
                                </td>
                            </tr>

                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Service Name')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    {{ $detail->invoiceable->getService()->name }}
                                </td>
                            </tr>

                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Tracking #')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    <a href="{{ $detail->invoiceable->tracking_link  }}" target="_blank">
                                        {{ $detail->invoiceable->tracking_number }}
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
                                    {{ $invoice->meta_data['payment_date'] ?? 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>

            <table width="100%" class="table-1">
                <tr>
                    <th></th>
                    <th>{{ __('Quoted Charges')  }}</th>
                    <th>{{ __('Billed Charges')  }}</th>
                    <th>{{ __('Comment')  }}</th>
                </tr>
                @foreach($detail->invoiceable->rate_details as $rateDetail)
                    <tr>
                        <td class="text-open-sans-8 text-default">
                            {{ $rateDetail['type'] }}
                        </td>

                        <td class="text-open-sans-8 text-default text-left">
                            {{ SplMoney::format($rateDetail['amount'], $rateDetail['currency']) }}
                        </td>

                        <td class="text-open-sans-8 text-default text-left">
                            {{ SplMoney::format($rateDetail['amount'], $rateDetail['currency']) }}
                        </td>

                        <td class="text-open-sans-8 text-default text-left">
                            N/A
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th>{{ __('Total')  }}</th>
                    <th>{{ SplMoney::format($detail->price)  }}</th>
                    <th>{{ SplMoney::format($detail->price)  }}</th>
                    <th></th>
                </tr>
            </table>
            <br>

            <table width="100%">
                <tr>
                    <td width="40%">
                        <div class="text-open-sans-11 text-default">
                            <strong>{{ __('Original Dimensions & Weight')  }}</strong>
                        </div>
                        <hr>
                        <table width="100%">
                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Total # of Packages')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    @if(in_array($detail->invoiceable->package->packaging_type, ['envelope', 'pack']))
                                        1
                                    @else
                                        {{ count($detail->invoiceable->package->meta_data) }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Total Shipment Weight')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    @if(in_array($detail->invoiceable->package->packaging_type, ['envelope', 'pack']))
                                        {{ $detail->invoiceable->package->meta_data[0]->weight ?? 'N/A' }}
                                        {{ $detail->invoiceable->package->weight_unit }}
                                    @else
                                        {{ collect($detail->invoiceable->package->meta_data)->sum('weight') }}
                                        {{ $detail->invoiceable->package->weight_unit }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="20%"></td>
                    <td width="40%">
                        <div class="text-open-sans-11 text-default">
                            <strong>{{ __('Billed Dimensions & Weight')  }}</strong>
                        </div>
                        <hr>
                        <table width="100%">
                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Total # of Packages')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    @if(in_array($detail->invoiceable->package->packaging_type, ['envelope', 'pack']))
                                        1
                                    @else
                                        {{ count($detail->invoiceable->package->meta_data) }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="text-open-sans-8 text-default">
                                    {{ __('Total Shipment Weight')  }}
                                </td>

                                <td class="text-open-sans-8 text-default text-right">
                                    @if(in_array($detail->invoiceable->package->packaging_type, ['envelope', 'pack']))
                                        {{ $detail->invoiceable->package->meta_data[0]->weight ?? 'N/A' }}
                                        {{ $detail->invoiceable->package->weight_unit }}
                                    @else
                                        {{ $detail->invoiceable->total_weight }}
                                        {{ $detail->invoiceable->package->weight_unit }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <hr>

            <div class="footer-3">
                <hr>

                <div class="text-open-sans-6">
                    {{ __('If the carrier conducted an audit of the dimensions or weight of your shipment after they pick up your shipment, and they discover a discrepancy between the dimensions or weight that you originally entered into the Freightcom website upon booking and the carrier’s remeasurement, your Billed Charges for Freight may differ from the original quote. Please note that all charges are based on the greater of the dimensional weight or the actual weight. See the FAQ section on your Freightcom account for a definition of dimensional weight.') }}
                </div>
            </div>

            @if($key < count($invoice->chargeable_details) - 1)
                <div class="page-break"></div>
            @endif
        @endif
    @endforeach
</div>
