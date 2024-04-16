<p class="mt-5">
    <small class="small">
        {{ Illuminate\Mail\Markdown::parse($invoice->company->ticket_footer) }}
    </small>
</p>

<br><br>
<svg id="barcode"></svg>
<script>
    JsBarcode("#barcode", "{{$invoice->id}}", {
        // format: "pharmacode",
        lineColor: "#000",
        // width: 4,
        // height: 50,
    });
    window.print();
    window.addEventListener("afterprint", function(event) {
        window.close();
    });
</script>
