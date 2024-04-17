{{-- <br><br>
<div class="text-left">
    {{ __("SIGNATURE") }}
    <br><br>
    <div style="width: 75%">
        <hr>
        {{ __("CARDHOLDER WILL PAY CARD ISSUER ABOVE AMOUNT PURSUANT TO CARDHOLDER AGREEMENT") }}
    </div>
    <br><br>
</div>

<div>
    {{ __('*** MERCHANT COPY ***') }}
</div>

<br><br> --}}

{!! nl2br(str_replace(' ', '&nbsp;&nbsp;', $invoice->receipt_merchant)) !!}
