@if($invoice->status === 'paid')
    <span style="background: #4caf50;display: inline-block;padding: 5px 10px;color: #fff;margin-bottom: 5px;border-radius: 3px;">
        {{__('PAID')}} {{$invoice->paymentGateway ? __('with') .' '. $invoice->paymentGateway->name:''}}
    </span> <br>
    @if($invoice->paymentGateway)
        @if ($invoice->paymentGateway->slug==="stripe")
            {{__('Credit card:')}} {{$chargedUser->card_last_four}} <br>
            {{__('Credit card reference:')}} {{$chargedUser->stripe_id}}
        @elseif($invoice->paymentGateway->slug==="wire-transfer")
            {{__('Wire Transfer Confirmation :')}} {{$invoice->metadata['transaction']}}
        @endif
    @endif

    @isset($invoice->metadata['payment_details'])
        @foreach ($invoice->metadata['payment_details'] as $detail => $value)
            <span>{{$detail==='stripe'?'Card':'Solde'}} : ${{$value}}</span><br>
        @endforeach
    @endisset
@else
    <span style="background: #ff9800;display: inline-block;padding: 5px 10px;color: #fff;margin-bottom: 5px;border-radius: 3px;">
        {{__('unpaid')}}
    </span>
@endif
