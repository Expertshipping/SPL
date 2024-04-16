@if ($invoice->surcharge_details->count()>0)
    <div>
        <p style="font-weight:bold;">{{__('Surcharge details:')}}</p>
        @foreach ($invoice->surcharge_details as $detail)
            <small class="font-weight-bold" v-for="detail in invoice.surcharge_details">
                {{__('Reason')}}: {{  $detail->reason }} <br>
                {{__('Amount')}}: {{  $detail->surcharge_amount }}
                @if(str_replace(' ','-', \Illuminate\Support\Str::lower($detail->reason)) === "audited-dimensions")
                    <br>
                    <span>{{__('Length:')}} {{$detail->length}} {{$detail->unit}}</span> <br>
                    <span>Width : {{$detail->width }} {{$detail->unit}}</span> <br>
                    <span>Height: {{$detail->height}} {{$detail->unit}}</span> <br>
                @endif
                @if(str_replace(' ','-', \Illuminate\Support\Str::lower($detail->reason)) === "audited-weight")
                    <span>Weight: {{$detail->weight}} {{$detail->unit}}</span> <br>
                @endif
                @if($detail->comment)
                    {{$detail->comment}}
                @endif
                <hr style="border: 0;border-bottom:1px solid #ddd;" />
            </small>
        @endforeach
    </div>
@endif
