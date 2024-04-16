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
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.4/dist/JsBarcode.all.min.js"></script>
    </head>
    <body>
        <div class="ticket">
            @if ($signature)
                @include('invoices.ticket.ticket')
                @include('invoices.ticket.customer-copy')
                @include('invoices.ticket.footer')
                <br><br>
                <hr>
                <br><br>
                @include('invoices.ticket.ticket')
                @include('invoices.ticket.merchant-copy')
                @include('invoices.ticket.footer')
            @else
                @include('invoices.ticket.ticket')
                @include('invoices.ticket.customer-copy')
                @include('invoices.ticket.footer')
            @endif
        </div>

        <script>
            JsBarcode("#barcode", "{{$invoice->id}}", {
                // format: "pharmacode",
                lineColor: "#000",
                // width: 4,
                // height: 50,
            });
            window.print();
            // window.addEventListener("afterprint", function(event) {
            //     window.close();
            // });
        </script>
    </body>
</html>
