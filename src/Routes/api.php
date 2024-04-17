<?php

use ExpertShipping\Spl\Controllers\InsuranceController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('spl.middleware'))->prefix('spl')->name('spl.')->group(function () {

    Route::post('insurance/send-claim-link/{id}', [InsuranceController::class, 'sendClaimLink'])
        ->name('insurance.send-claim-link');

    Route::delete('insurance/delete-claim/{id}', [InsuranceController::class, 'deleteClaim'])
        ->name('insurance.delete-claim');

});
