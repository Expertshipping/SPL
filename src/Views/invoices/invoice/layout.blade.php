
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        @page{
            margin-top: 100px;
        }

        .pagenum:before {
            content: counter(page);
        }

        .page-break {
            page-break-after: always;
        }

        .invoice-number {
            color: #172B4D;
            font-family: "Open Sans", sans-serif;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }
        .invoice-number strong {
            font-weight: 700;
        }

        .bill-to-title{
            color: #8392AB;
            font-family: Inter, sans-serif;
            font-size: 10px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .bill-to-details{
            color: #172B4D;
            font-family: "Open Sans", sans-serif;
            font-size: 10px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .invoice-date-title{
            color: #172B4D;
            text-align: right;
            font-family: "Open Sans", sans-serif;
            font-size: 10px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .invoice-date-details{
            color: #172B4D;
            text-align: right;
            font-family: "Open Sans", sans-serif;
            font-size: 10px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .billing-information-title{
            color: #172B4D;
            font-family: "Open Sans", sans-serif;
            font-size: 10px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .table-1 {
            border: 0;
            border-collapse: collapse;
        }

        .table-1 th{
            color: #FFFFFF;
            font-family: "Open Sans", sans-serif;
            font-size: 8px;
            font-style: normal;
            font-weight: 700;
            line-height: normal;
            background:#8392AB;
            padding: 10px;
            border: 0;
            text-align: left;
        }

        .table-1 td{
            color: #172B4D;
            font-family: "Open Sans", sans-serif;
            font-size: 8px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
            padding: 10px;
            border: 0;
        }

        /* Globals */
        hr{
            border: 0;
            border-top: 1px solid #DEE2E6;
        }

        .text-white{
            color: #FFFFFF;
        }

        .text-right{
            text-align: right;
        }

        .text-center{
            text-align: center;
        }

        .terms{
            color: #172B4D;
            text-align: justify;
            font-family: "Open Sans", sans-serif;
            font-size: 6px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .header{
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            margin-top: -60px;
            width: 100%;
        }

        .page-1 .footer-1{
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        .page-3 .footer-3{
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        .text-open-sans-6{
            font-family: "Open Sans", sans-serif;
            font-size: 6px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .text-open-sans-9{
            font-family: "Open Sans", sans-serif;
            font-size: 9px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .text-open-sans-11{
            font-family: "Open Sans", sans-serif;
            font-size: 11px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .text-open-sans-8{
            font-family: "Open Sans", sans-serif;
            font-size: 8px;
            font-style: normal;
            font-weight: 400;
            line-height: normal;
        }

        .text-secondary{
            color: #8392AB;
        }

        .text-default{
            color: #172B4D;
        }

        .text-uppercase{
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="header">
    <table width="100%">
        <tr>
            <td>
                <img src="{{ asset('domains/'. request()->getHost() .'/logo-color.png') }}" alt="" width="147px">
            </td>
            <td align="right">
                <div class="invoice-number">
                    {{ __('Invoice')  }} : <strong>{{ $invoice->id }}</strong>
                </div>
            </td>
        </tr>
    </table>
</div>
@yield('content')
</body>
</html>
