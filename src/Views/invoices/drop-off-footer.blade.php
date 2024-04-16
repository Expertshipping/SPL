<p class="mt-5 text-justify">
    Thank you for choosing
    @if($dropOff->store->is_retail_reseller ?? false)
        {{ $dropOff->store->name }}
    @else
        {{env('APP_NAME')}}
    @endif
    to drop off your packages. Please note that we are not responsible for the package once it has been picked up by the carrier. We are also not responsible for delivery delays, loss, damage, or theft during transit.
    <br><br>
    If you have any issues with your package, please contact the carrier directly. The carrier's phone numbers are as follows:
    <br><br>
        •	DHL: <a href="tel:+1 800 225 5345">+1 800 225 5345</a> <br>
        •	FedEx: <a href="tel:+1 800 463 3339">+1 800 463 3339</a> <br>
        •	UPS: <a href="tel:+1 800 742 5877">+1 800 742 5877</a> <br>
        •	Purolator: <a href="tel:+1 888 744 7123">+1 888 744 7123</a> <br>
    <br>
    If you have any further questions or concerns, please do not hesitate to contact us at
    @if($dropOff->store->is_retail_reseller ?? false)
        {{ $dropOff->store->email }}
    @else
        {{ env('APP_EMAIL') }}.
    @endif
    <br><br>
    Thank you again for your trust in
    @if($dropOff->store->is_retail_reseller ?? false)
        {{ $dropOff->store->name }}
    @else
        {{env('APP_NAME')}}
    @endif
    for your shipping needs.
</p>
