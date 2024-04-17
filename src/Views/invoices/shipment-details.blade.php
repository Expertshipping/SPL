<small>
    @if ($invoice->surcharge_details && $invoice->surcharge_details->count()==0)
        {{__('Date')}} <strong>{{ $shipment->created_at->toFormattedDateString() }}</strong> <br>
    @endif

    {{__("Tracking")}} <strong>{{$shipment->tracking_number}}</strong> <br>

    @if ($invoice->surcharge_details && $invoice->surcharge_details->count()==0)
        @if($shipment->package)
            {{__("Weight")}}: <span>{{$shipment->package->total_weight}} {{$shipment->package->weight_unit}}</span>
        @endif
        <br>
        @if ($shipment->carrier && $shipment->service)
            {{__('Service')}}: {{$shipment->carrier->name}} - {{$shipment->service->name}}
            <br>
        @endif
        {{__('From')}}: {{$shipment->from_country}}, {{$shipment->from_zip_code}}, {{$shipment->from_city}}
        <br>
        {{__('To')}}: {{$shipment->to_country}}, {{$shipment->to_zip_code}}, {{$shipment->to_city}}
        @if($shipment->reference_value)
            <br>
            {{__("Refrence")}}: {{$shipment->reference_value}}
        @endif

        @if(!! $shipment->package)
            <br>
            {{__('Package type')}}:
            <span>{{ucfirst($shipment->package->packaging_type)}}</span>
        @endif

        @if(!! $shipment->package && $shipment->package->packaging_type === 'box')
            <br>
            {{__('Dimensions')}}:
            @foreach($shipment->package->meta_data as $meta)
                {{$meta->length}} x {{$meta->width}} x {{$meta->height}} {{$meta->weight_unit}}
            @endforeach
        @endif
    @endif
</small>
@if ($invoice->surcharge_details && $invoice->surcharge_details->count()>0)
    <div>
        <p style="font-weight:bold;">{{__('Surcharge details:')}}</p>
        @foreach ($invoice->surcharge_details as $detail)
            <small class="font-weight-bold" v-for="detail in invoice.surcharge_details">
                {{__('Reason')}}: {{  $detail->reason }} <br>
                {{__('Amount')}}: {{  $detail->surcharge_amount }}
                @if(str_replace(' ','-', \Illuminate\Support\Str::lower($detail->reason)) === "audited-dimensions")
                    <br>
                    <span>{{__('Length:')}} {{$detail->length}} {{$detail->unit}}</span> <br>
                    <span>{{__("Width")}}: {{$detail->width }} {{$detail->unit}}</span> <br>
                    <span>{{__("Height")}}: {{$detail->height}} {{$detail->unit}}</span> <br>
                @endif
                @if(str_replace(' ','-', \Illuminate\Support\Str::lower($detail->reason)) === "audited-weight")
                    <span>{{__("Weight")}}: {{$detail->weight}} {{$detail->unit}}</span> <br>
                @endif
                @if($detail->comment)
                    {{$detail->comment}}
                @endif
                <hr style="border: 0;border-bottom:1px solid #ddd;" />
            </small>
        @endforeach
    </div>
@endif
