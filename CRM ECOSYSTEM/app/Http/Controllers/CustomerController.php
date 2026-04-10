<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function show(Request $request) {
        $user = $request->user();
        $customer = $user->customer;
        $lead = $customer->lead;

        $retainer = $lead->status == LeadStatus::FILE_OPENING
            ? $lead->retainers()->whereIn('visa_id', [10, 11, 12, 13])->first()  // File Opening
            : $lead->retainers()->whereNotIn('visa_id', [10, 11, 12, 13])->first();

        return [
            'title' => "#$lead->id - $user->firstname $user->lastname",
            'results' => $lead->results,
            'product' => $lead->visa ? $lead->visa->name : null,
            'docs' => $lead->backoffice ?? [],
            'status' => $lead->status->value,
            'loa' => [
                'retainer' => $retainer ? [
                    'signed_at' => $retainer->signed_at ? date('M d, Y H:i', strtotime($retainer->signed_at)) : null,
                    'retainer' => $retainer->file,
                ] : null,
            ],
        ];
    }

    public function doc(Request $request, $slug) {
        $user = $request->user();
        $customer = $user->customer;
        $lead = $customer->lead;

        if($file = $request->file('file')) {
            $name = uniqid();
            $extension = $file->getClientOriginalExtension();
            $fileName =  "$name.$extension";
            $file->move(public_path('pdf/uploads'), $fileName);

            $backoffice = $lead->backoffice ?? [];
            $item = $backoffice[$slug] ?? [
                'status' => 'review',
            ];
            $item['doc'] = config('app.url') . "/pdf/uploads/$name.$extension";
            $item['name'] = $request->post('name');
            $backoffice[$slug] = $item;
            $lead->update(['backoffice' => $backoffice]);

            return $item['doc'];
        } else {
            return response('No file found', 400);
        }
    }
}
