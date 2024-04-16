<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facture hkjghfdkjghdf</title>

    <style type="text/css">
        * {
            font-family: Verdana, Arial, sans-serif;
        }

        table {
            font-size: x-small;
            border-collapse: collapse;

        }

        td, th {
            padding: 15px;

        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }

        .gray {
            background-color: white
        }
    </style>

</head>
<body>

<div id="app">

    <table width="100%" border="">
        <tr>
            <td width="33%" align="left" valign="top">
                {{--Doc No.jhfgjkfdhg--}}
            </td>
            <td width="33%" align="center" valign="bottom"><h3 style="text-transform: uppercase">Facture
                    commerciale</h3>
                COMMERCIAL INVOICE
            </td>
            <td width="33%" align="right">
                {{--<pre>Client/Cust Num: some number</pre>--}}
                <pre>
               Date: {{\Carbon\Carbon::parse($shipment->created_at)->format('d/m/Y')}}
            </pre>
            </td>
        </tr>

    </table>

    <table width="100%" border="1">
        <tr>
            <td>De/From: <br>
                {{$shipment->ship_name}} <br>
                {{$shipment->ship_addr1}} {{$shipment->ship_addr2}}
                {{$shipment->ship_city}}, {{$ship_state}} {{$shipment->ship_code}}<br>
                Canada <br>
                Tel.:{{$shipment->ship_phone}} <br>
                @if($shipment->to_country == 'BR')
                    CPF/CNPJ: {{$shipment->cpf_cnpj}}
                @endif()
            </td>
            <td>A/To: <br>
                {{$shipment->to_name}} <br>
                {{$shipment->to_addr1}} {{$shipment->to_addr2}}
                {{$shipment->to_city}}, {{$to_state}} {{$shipment->to_code}}<br>
                {{$to_country}} <br>
                Tel.:{{$shipment->to_phone}} <br>
                {{--Tax ID:--}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Shipping Purpose/Raison d'envoi: {{$shipment->shipping_purpose}}
            </td>
        </tr>
    </table>

    <br/>

    <table width="100%" border="1">
        <thead style="background-color: white;">
        <tr>
            <th>Description</th>
            <th>HS No</th>
            <th>Pays/country origin</th>
            <th>Qte/Qty</th>
            <th>Prix/Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody style="height: 200px">

        <tr>
            <td>{{$shipment->commodity_description}}</td>
            <td>{{$shipment->hs_no}}</td>
            <td>{{$country_of_manufacture}}</td>
            <td align="right">{{count($packages)}}</td>
            <td align="right">{{$shipment->currency}} {{$shipment->customs_value}}</td>
            <td align="right"
                style="white-space: nowrap;">{{$shipment->currency}} {{$shipment->customs_value}}</td>
        </tr>
        <tr>
            <td style="height: 350px"></td>
            <td style="height: 350px"></td>
            <td style="height: 350px"></td>
            <td style="height: 350px"></td>
            <td style="height: 350px"></td>
            <td style="height: 350px"></td>
        </tr>
        {{--@foreach($shipments as $key =>  $shipment)--}}
        {{--<tr>--}}
        {{--<td scope="row">{{$key + 1}}.</td>--}}
        {{--<td>Expédition du colis {{$shipment->tracking_number}}</td>--}}
        {{--<td> {{$shipment->tracking_number}}</td>--}}
        {{--<td> {{$shipment->ship_state}} {{$shipment->ship_city}}</td>--}}
        {{--<td align="right">1</td>--}}
        {{--<td align="right">{{$shipment->rate}}</td>--}}
        {{--<td align="right">{{$shipment->rate}}</td>--}}
        {{--</tr>--}}
        {{--@endforeach--}}

        <tfoot>
        <tr>
            <td colspan="3">
                <small style="font-size: 8px">
                    <b>Terms of sales:</b>
                    Ces marchandises, technologies ou logiciels ont ete exportes du Canada
                    conformement aux reglements adminstratifs sur l'exportation aux Etats-Unis. Tout agissement
                    contraire a
                    la loi canadienne est strictement interdit. Je certifie par la presente que les prix indiques sur
                    cette
                    facture sont exacts, qu'aucune autre facture commerciale n'a ete produite et que tous les
                    renseignements
                    fournis sont veridiques.
                    These commodities, technology or software were exported from Canada in accordance with the Export
                    Administration Regulations. Diversion contrary to Canadian law prohibited. It is hereby certified
                    that
                    this invoice shows the actual price of the goods described, that that
                    no other invoice has been issued, and that all particulars are true and correct.
                </small>
            </td>
            <td colspan="2" align="right">
                <div>Total Items: {{count($packages)}}</div>
            </td>
            <td align="right"
                style="white-space: nowrap;">{{$shipment->currency}} {{$shipment->customs_value}}</td>
        </tr>
        {{--<tr>--}}
        {{--<td colspan="3"></td>--}}
        {{--<td align="right">Tax $</td>--}}
        {{--<td align="right">294.3</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
        {{--<td colspan="3"></td>--}}
        {{--<td align="right">Total $</td>--}}
        {{--<td align="right" class="gray">$ 1929.3</td>--}}
        {{--</tr>--}}
        <tr>
            <td colspan="2">Signature:______________________</td>
            <td colspan="4">Fonction/Title__________________</td>
        </tr>
        </tfoot>
    </table>
</div>


</body>
</html>