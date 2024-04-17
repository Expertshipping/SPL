<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>ExpertShipping</title>
        <style>
            * {
                font-size: 12px;
                font-family: sans-serif;
                line-height: 1.3;
            }
            td,
            th,
            tr,
            table {
                border-top: 1px solid black;
                border-collapse: collapse;
            }

            .centered {
                text-align: center;
                align-content: center;
            }

            .ticket {
                width: 100%;
                max-width: 100%;
                text-align: center;
            }
            img {
                max-width: inherit;
                width: inherit;
            }

            @media print {
                .hidden-print,
                .hidden-print * {
                    display: none !important;
                }
            }

            .small{
                font-size: 8px;
                line-height: 8px;
                text-align: center;
                display: block;
            }

            .img{
                width: 50mm;
                max-width: 50mm;
            }
            h1{
                font-size: 6mm;
            }

            table.no-border, table.no-border tr, table.no-border td{
                border-top: 0 !important;
            }

            .no-border{
                border-top: 0 !important;
            }

            .total-price{
                font-size: 20px;
                font-weight: bold;
            }

            .table-al, .table-al td,.table-al tr{
                text-align: left;
                border: 0;
            }

            .text-left{
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <img src="{{asset('assets/invoices/logo-black.png')}}" class="img" alt="Logo">
            <p class="centered">
                {{ $company->name }} <br>
                {{ $company->addr1 }} <br>
                @if ($company->addr2)
                    {{ $company->addr2 }} <br>
                @endif
                {{ $company->city }}, {{ $company->country }} <br>
                {{ $company->zip_code }} <br>
                www.expertshipping.ca
                @if ($company->email)
                    <br>
                    {{ $company->email }}
                @endif
            </p>
            <br><br>
            {!! nl2br(str_replace(' ', '&nbsp;&nbsp;', $receiptC)) !!}
        </div>

        <script>
            window.print();
            window.addEventListener("afterprint", function(event) {
                window.close();
            });
        </script>
    </body>
</html>

