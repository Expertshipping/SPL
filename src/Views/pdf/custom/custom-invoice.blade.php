<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>A4</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @font-face {
            font-family: 'HandScript';
            src: url("../../../public/fonts/HandScript-gxen4.otf") format("opentype");
        }
        /* @page { size: A4 } */
        table, th, td {
            border: 1px solid black;
            border-bottom: none;
            border-left: none;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px;
            text-align: left;
            border-left: 1px solid black;

        }
        td[rowspan] {
            vertical-align: top;
            text-align: left;
        }
        tr {
            border: 1px solid black;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->
<body>
    @include('spl::pdf.custom.page')
    <div class="page-break"></div>
    @include('spl::pdf.custom.page')
    <div class="page-break"></div>
    @include('spl::pdf.custom.page')
</body>

</html>
