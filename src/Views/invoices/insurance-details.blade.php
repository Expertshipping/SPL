<small>
    {{__('Date:')}} <strong>{{ $insurance->created_at->toFormattedDateString() }}</strong> <br>
    {{__("Transaction:")}} <strong>{{$insurance->transaction_number}}</strong> <br>
    {{__("From :")}} <strong>{{$insurance->ship_from}}</strong> <br>
    {{__("To:")}} <strong>{{$insurance->ship_to}}</strong> <br>
    {{__("Insured Value:")}} <strong>{{$insurance->declared_value}}</strong> <br>
    {{__("Carrier/ Service:")}} <strong>{{$insurance->service->name}}</strong> <br>
    {{__("Ship date:")}} <strong>{{$insurance->ship_date->format('d/m/Y')}}</strong> <br>
</small>
