@extends('spl::invoices.invoice.layout')

@section('content')
    @include('spl::invoices.invoice.pages.page-1')
    <div class="page-break"></div>
    @include('spl::invoices.invoice.pages.page-2')
    <div class="page-break"></div>
    @include('spl::invoices.invoice.pages.page-3')
@endsection
