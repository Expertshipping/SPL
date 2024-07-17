<div class="page-3">
    @foreach($invoice->chargeable_details as $detail)
        @if($detail->invoiceable instanceof \App\Shipment)
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
                            {{ $detail->invoiceable->from_city  }},
                            {{ $detail->invoiceable->from_zip_code  }},
                            {{ $detail->invoiceable->from_province  }},
                            {{ $detail->invoiceable->from_country  }}
                        </div>
                    </td>

                    <td width="10%"></td>

                    <td width="45%">
                        <div class="text-open-sans-11 text-default text-uppercase">
                            {{ $detail->invoiceable->to_city  }},
                            {{ $detail->invoiceable->to_zip_code  }},
                            {{ $detail->invoiceable->to_province  }},
                            {{ $detail->invoiceable->to_country  }}
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
                                    {{ $detail->meta_data['payment_date'] ?? 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td width="10%"></td>

                    <td width="45%">
                        @if(isset($detail->invoiceable->{$rateDetailsAttribute}))
                        <table width="100%">
                            @foreach($detail->invoiceable->{$rateDetailsAttribute} as $rateDetail)
                                <tr>
                                    <td class="text-open-sans-8 text-default">
                                        {{ $rateDetail['type'] }}
                                    </td>

                                    <td class="text-open-sans-8 text-default text-right">
                                        {{ SplMoney::format($rateDetail['amount'], $rateDetail['currency']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <hr>
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
        @endif
    @endforeach
</div>
