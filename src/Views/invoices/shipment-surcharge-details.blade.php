<small>
    {{__('Date:')}} <strong>{{ $shipmentSurcharge->created_at->toFormattedDateString() }}</strong> <br>
    {{__("Title :")}} <strong>{{$shipmentSurcharge->name}}</strong> <br>
    {{__("Description :")}} <strong>{{$shipmentSurcharge->description}}</strong> <br>
    {{__("Tracking :")}} <strong>{{$shipmentSurcharge->shipment->tracking_number}}</strong> <br>
</small>
