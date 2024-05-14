<!-- Each sheet element should have the class "sheet" -->
<!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
<article style="margin: 10px">
    <table style="width:100%">
        <caption style="text-align:right">
            @if (in_array($shipment->customInvoice->exportation_reason, ['Gift', 'Cadeau', 'Personal effects', 'Personal use']))
                {{__('PROFORMA INVOICE', [], 'en')}}
            @else
                {{__('COMMERCIAL INVOICE', [], 'en')}}
            @endif
        </caption>

        <tbody>
        <tr>
            <td colspan="9">
                <span>({{__('AWB (tracking) number', [], 'en')}})#: {{$shipment->tracking_number}}</span>
                <span style="float:right">
                            {{__('Reference number', [], 'en')}}#: {{$shipment->reference_value ?? 'NONE'}}
                        </span> <br>
                <span>{{__('Date', [], 'en')}} {{$shipment->created_at->format('m/d/Y')}}</span> <br>
                <span>{{__('Carrier', [], 'en')}} {{$shipment->carrier->name}}</span> <br>
            </td>
        </tr>

        <tr>
            <td colspan="4" valign="top">
                <strong><span>{{__('Sender', [], 'en')}}</span> </strong><br><br>
                <span>
                            {{\Illuminate\Support\Str::upper($shipment->from_company)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->from_name)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->from_address_1)}} <br>
                            @if($shipment->from_address_2)
                        {{\Illuminate\Support\Str::upper($shipment->from_address_2)}} <br>
                    @endif
                    @if($shipment->from_address_3)
                        {{\Illuminate\Support\Str::upper($shipment->from_address_3)}} <br>
                    @endif
                    {{\Illuminate\Support\Str::upper($shipment->from_city)}}, {{\Illuminate\Support\Str::upper($shipment->from_province)}}, {{\Illuminate\Support\Str::upper($shipment->from_zip_code)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->from_country)}}
                        </span><br><br>
                <span>{{__('Phone', [], 'en')}}: {{$shipment->from_phone}}</span> <br>
                <span>{{__('Email', [], 'en')}}: {{$shipment->from_email}}</span><br>
            </td>

            <td colspan="5" valign="top">
                <strong><span>{{__('Consignee', [], 'en')}}</span> </strong><br><br>
                <span>
                            {{\Illuminate\Support\Str::upper($shipment->to_company)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->to_name)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->to_address_1)}} <br>
                            @if($shipment->to_address_2)
                        {{\Illuminate\Support\Str::upper($shipment->to_address_2)}} <br>
                    @endif
                    @if($shipment->to_address_3)
                        {{\Illuminate\Support\Str::upper($shipment->to_address_3)}} <br>
                    @endif
                    {{\Illuminate\Support\Str::upper($shipment->to_city)}}, {{\Illuminate\Support\Str::upper($shipment->to_province)}}, {{\Illuminate\Support\Str::upper($shipment->to_zip_code)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->to_country)}}
                        </span><br><br>
                <span>{{__('Phone', [], 'en')}}: {{$shipment->to_phone}}</span> <br>
                <span>{{__('Tax ID/ VAT No', [], 'en')}}: {{$shipment->customInvoice->vat_tax_id}}</span><br>
                <span>{{__('Email', [], 'en')}}: {{$shipment->to_email}}</span><br>

            </td>
        </tr>

        <tr>
            <td colspan="4">
                <strong><span>{{__('Reason for export', [], 'en')}}</span></strong><br>
                <span>{{$shipment->customInvoice->exportation_reason}}</span>
            </td>

            <td rowspan="5" colspan="5">
                <strong><span>{{__('Billed to', [], 'en')}}</span></strong><br><br>
                <span>
                            {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_company)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_name)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_address)}} <br>
                            @if($shipment->customInvoice->bill_to_address_2)
                        {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_address_2)}} <br>
                    @endif
                    @if($shipment->customInvoice->bill_to_address_3)
                        {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_address_3)}} <br>
                    @endif
                    {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_city)}}, {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_province)}}, {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_zip_code)}} <br>
                            {{\Illuminate\Support\Str::upper($shipment->customInvoice->bill_to_country)}}
                        </span><br><br>
                <span>{{__('Phone', [], 'en')}}: {{$shipment->customInvoice->bill_to_tel}}</span> <br>
                <span>{{__('Tax ID/ VAT No', [], 'en')}}: {{$shipment->customInvoice->vat_tax_id}}</span><br>
                <span>{{__('Email', [], 'en')}}: {{$shipment->customInvoice->bill_to_email}}</span><br>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong><span>{{__('Type of export', [], 'en')}}</span></strong><br>
                <span>{{$shipment->customInvoice->exportation_type}}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong><span>{{__('Terms of sale', [], 'en')}}</span></strong><br>
                <span>{{$shipment->customInvoice->terms_of_sale}}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong><span>{{__('Duty/taxes acct', [], 'en')}}</span></strong><br>
                <span>{{__('Receiver will pay', [], 'en')}}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">

                <strong><span>{{__('Shipment Weight', [], 'en')}}</span></strong><br>
                <span>{{$shipment->package->total_weight}} {{$shipment->package->weight_unit}}</span>
            </td>
        </tr>

        <tr style="background:#f1f1f1;">
            <td colspan="4">
                <strong>{{__('Full Description of Goods', [], 'en')}}</strong>
            </td>
            <td>
                <strong>{{__('Country of origin', [], 'en')}}</strong>
            </td>
            <td>
                <strong>{{__('HS Code', [], 'en')}}</strong>
            </td>
            <td>
                <strong>{{__('Qty', [], 'en')}}</strong>
            </td>
            <td>
                <strong>{{__('Unit Value', [], 'en')}}</strong>
            </td>
            <td>
                <strong>{{__('Total Value', [], 'en')}}</strong>
            </td>
        </tr>
        @php
            $total = 0;
        @endphp
        @if (count($shipment->customInvoice->items) > 0)
            @foreach ($shipment->customInvoice->items as $item)

                <tr>
                    <td colspan="4">
                        <span>{{$item->description}}</span>
                    </td>
                    <td>
                        <span>{{$item->manufacturing_country}}</span>
                    </td>
                    <td>
                        <span>{{$item->hs_no}}</span>
                    </td>
                    <td>
                        <span>{{$item->quantity}}</span>
                    </td>
                    <td>
                        <span>{{$item->value}}</span>
                    </td>
                    <td>
                        <span>{{$item->value * $item->quantity}}</span>
                    </td>
                </tr>
                @php
                    $total += ($item->value * $item->quantity);
                @endphp
            @endforeach
        @endif
        <tr>
            <td colspan="8" style="border-left:none">
                        <span style="float:right">
                            <strong>{{__('Total', [], 'en')}}  ({{$shipment->customInvoice->currency}})</strong>
                        </span> <br>
            </td>
            <td style="border:1px solid black">{{$total}}</td>
        </tr>
        </tbody>

    </table>
    <p></p>
    <p>{{__('I/We hereby certify that the information contained in the invoice is true and correct and that the contents of this shipment are as stated above', [], 'en')}}.</p>

    <strong><p>{{__('Name', [], 'en')}} : {{$shipment->from_name}}</p></strong>
    <br>
    <strong><span>{{__('Signature', [], 'en')}}</span></strong>
    <br>
    <p style="font-family: 'HandScript', sans-serif;">
        {{$shipment->from_name}}
    </p>
{{--    @if ($shipment->company->account_type==='business')--}}
{{--        <img src="{{$signature}}" width="120" alt="">--}}
{{--    @else--}}
{{--        <p style="font-family: 'HandScript', sans-serif;">--}}
{{--            {{$shipment->from_name}}--}}
{{--        </p>--}}
{{--    @endif--}}
</article>
