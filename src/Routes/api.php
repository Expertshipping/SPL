<?php

use ExpertShipping\Spl\Controllers\CustomsInvoiceController;
use ExpertShipping\Spl\Controllers\InsuranceController;
use ExpertShipping\Spl\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
//config('spl.middleware')
Route::prefix('spl')->name('spl.')->group(function () {

    Route::post('insurance/send-claim-link/{id}', [InsuranceController::class, 'sendClaimLink'])
        ->name('insurance.send-claim-link');

    Route::delete('insurance/delete-claim/{id}', [InsuranceController::class, 'deleteClaim'])
        ->name('insurance.delete-claim');

    // invoices
    Route::get('invoice/{id}/download', [InvoiceController::class, 'download'])
        ->name('invoice.download');

    // customs invoice
    Route::get('customs-invoice/{uuid}/download', [CustomsInvoiceController::class, 'download'])
        ->name('invoice.download');
});
