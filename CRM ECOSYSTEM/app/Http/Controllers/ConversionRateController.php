<?php

namespace App\Http\Controllers;

use App\Models\ConversionRate;
use Illuminate\Http\Request;

class ConversionRateController extends Controller
{
    function show() {
        return ConversionRate::find(1);
    }

    function update(Request $request, ConversionRate $conversionRate) {
        $conversionRate->update([
            'rate' => $request->post('rate')
        ]);
    }
}
