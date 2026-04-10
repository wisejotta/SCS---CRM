<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class RetainerController extends Controller
{
    public function show($uuid)
    {
        $retainer = Retainer::where('uuid', $uuid)->select('id', 'file', 'signed_at')->firstOrFail();
        return $retainer;
    }

    public function send(Request $request, Lead $lead)
    {
        $user = $lead->customer->user;
        $agentUser = $lead->agent->user;
        $sg = new \SendGrid(config('sendgrid.apiKey'));

        $resultsFiles = $lead->results
            ? array_map(function($result) {
                $results = last(explode('/', $result['file']));
                return [
                    'filename' => "$result[name].pdf",
                    'content' => base64_encode(file_get_contents(public_path("pdf/uploads/$results"))),
                    'type' => 'application/pdf',
                    'disposition' => 'attachment',
                ];
            }, $lead->results)
            : [];

        $retainer = $lead->status == LeadStatus::FILE_OPENING
            ? $lead->retainers()->whereIn('visa_id', [10, 11, 12, 13])->first()  // File Opening
            : $lead->retainers()->whereNotIn('visa_id', [10, 11, 12, 13])->first();

        $file = last(explode('/', $retainer->file));
        $fileContent = base64_encode(file_get_contents(public_path("pdf/uploads/$file")));
        $visaName = $retainer->visa->name;
        $resultsFiles[] = [
            'content' => $fileContent,
            'filename' => "$visaName Retainer.pdf",
            'type' => 'application/pdf',
            'disposition' => 'attachment',
        ];

        $response = $sg->client->mail()->send()->post([
            'template_id' => config('sendgrid.conreactTemplateId'), // 
            'from' => [
                'email' => 'hello@visascanada.org',
                'name' => 'Visas Canada',
            ],
            'personalizations' => [[
                'to' => [[
                    'email' => $request->post('email'),
                    'name' => "$user->firstname $user->lastname",
                ]],
                'cc' => [[
                    'email' => $agentUser->email,
                    'name' => 'Visas Canada',
                ]],
                'dynamic_template_data' => [
                    'name' => "$user->firstname $user->lastname",
                    'language' => 'en',
                    'link' => config('app.url') . "/retainer/$retainer->uuid",
                ],
            ]],
            'attachments' => $resultsFiles,
        ]);

        $controller = new LeadController;
        return response()->json(
            $controller->show($request, Lead::find($lead->id)),
            $response->statusCode()
        );
    }

    public function sign(Request $request, $uuid)
    {
        $retainer = Retainer::where('uuid', $uuid)
            ->has('lead.customer.user')
            ->with('lead.customer.user')
            ->firstOrFail();

        $uniqid = uniqid();
        $image = $request->image;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        \File::put(storage_path('signatures/uploads') . "/$uniqid.png", base64_decode($image));

        $filename = public_path("pdf/uploads/$uniqid.pdf");
        $pdf = $this->prep($retainer);

        switch($retainer->visa_id) {
            // File opening
            case 10:
            case 11:
                $pdf->Image(storage_path('signatures/uploads') . "/$uniqid.png", 22, 190, 58, 22);
                $pdf->SetFont('arial', 'B', 11);
                $pdf->SetXY(140, 211.5);
                break;

            case 4:
                $pdf->Image(storage_path('signatures/uploads') . "/$uniqid.png", 50, 235, 58, 22);
                $pdf->SetFont('arial', 'B', 11);
                $pdf->SetXY(140, 252);
                break;

            case 14:
                $pdf->Image(storage_path('signatures/uploads') . "/$uniqid.png", 24, 193, 58, 22);
                $pdf->SetFont('arial', 'B', 10);
                $pdf->SetXY(125, 254);
                break;

            default:
                $pdf->Image(storage_path('signatures/uploads') . "/$uniqid.png", 22, 198, 58, 22);
                $pdf->SetFont('arial', '', 10);
                $pdf->SetXY(39, 233);
                break;
        }
        
        $pdf->Write(0, date('d/m/Y'));
        $pdf->Output($filename, 'F');

        $retainer->update([
            'file' => config('app.url') . "/pdf/uploads/$uniqid.pdf",
            'signed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function results(Request $request, Lead $lead)
    {
        $name = $request->post('name');
        $file = $request->file('file');
        $uniqid = uniqid();
        $file->move(public_path("pdf/uploads/"), "$uniqid.pdf");
        $result = [
            'name' => $name,
            'file' => config('app.url') . "/pdf/uploads/$uniqid.pdf",
        ];

        $lead->results = array_merge(
            $lead->results ?? [],
            [$result],
        );
        $lead->save();
        return $result;
    }

    public function deleteResult(Request $request, Lead $lead)
    {
        $new = [];
        foreach($lead->results as $result) {
            if($result['file'] != $request->post('file')) {
                $new[] = $result;
            }
        }
        $lead->update(['results' => $new]);
        return $new;
    }

    public function updateVisa(Lead $lead, $id, $partial = null)
    {
        $uniqid = uniqid();
        $file = config('app.url') . "/pdf/uploads/$uniqid.pdf";

        $retainer = in_array($id, [10, 11, 12, 13, 16, 17, 18])
            ? $lead->retainers()->whereIn('visa_id', [10, 11, 12, 13, 16, 17, 18])->first()  // File Opening
            : $lead->retainers()->whereNotIn('visa_id', [10, 11, 12, 13, 16, 17, 18])->first();

        if($retainer) {
            $retainer->update([
                'file' => $file,
                'visa_id' => $id,
            ]);
            $id = $retainer->id;
        } else {
            $id = Retainer::create([
                'uuid' => str()->uuid(),
                'file' => $file,
                'lead_id' => $lead->id,
                'visa_id' => $id,
            ])->id;
        }

        $retainer = Retainer::find($id);
        $filename = public_path("pdf/uploads/$uniqid.pdf");
        $pdf = $this->prep($retainer, $partial);
        if($pdf) $pdf->Output($filename, 'F');

        return $lead->retainers()->get()->map(function(Retainer $retainer) {
            return [
                'visa_id' => $retainer->visa_id,
                'name' => $retainer->visa->name,
                'signed_at' => $retainer->signed_at
                    ? date('M d, Y H:i', strtotime($retainer->signed_at))
                    : null,
                'retainer' => $retainer->file,
            ];
        });
    }

    public function prep(
        Retainer $retainer,
        $partial = null
    ): FPDI | null
    {
        $lead = $retainer->lead;
        $customer = $lead->customer;
        $user = $customer->user;

        // initiate FPDI
        $pdf = new FPDI();
        switch($retainer->visa_id) {
            // File opening
            case 10:
            case 11:
            case 12:
            case 13:
            case 16:
            case 17:
            case 18:
                return $this->prepFileOpening(
                    $retainer,
                    $lead,
                    $customer,
                    $user,
                    $partial,
                );
            case 1: // Student Visa
                $pdf->setSourceFile(storage_path('/study-visa-new.pdf'));
                break;
            case 2: // Express Entry
                $pdf->setSourceFile(storage_path('/express-entry-new.pdf'));
                break;
            case 4: // Tourist Visa
                return $this->prepTouristVisa(
                    $retainer,
                    $lead,
                    $customer,
                    $user,
                    $partial,
                );
                break;
            case 14: // Work Visa
                return $this->prepWorkVisa(
                    $retainer,
                    $lead,
                    $customer,
                    $user,
                    $partial,
                );
                break;
        }

        // ====== Page 1 ======
        try {
            $templateId = $pdf->importPage(1);
        } catch (\Exception $e) {
            return null;
        }
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        
        $pdf->useTemplate($templateId);

        $pdf->SetFont('arial', '', 10);
        $pdf->SetXY(40, 93.4);
        $pdf->Write(0, "$user->firstname $user->lastname");

        $pdf->SetFont('arial', '', 11);
        $pdf->SetXY(43, 102.5);
        $pdf->Write(0, $customer->residence ?? $customer->country);

        $pdf->SetFont('arial', 'B', 10);
        $pdf->SetXY(40, 97.8);
        $pdf->Write(0, "CA$lead->id");
        // ====== End Page 1 ======

        // ====== Page 2 ======
        $templateId = $pdf->importPage(2);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);

        $amount = $retainer->visa->price;
        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(178, 245.8);
        $pdf->Write(0, '$' . number_format($amount, 2));

        if($partial) {
            $pdf->SetFont('arial', 'BIU');
            $pdf->SetXY(150, 253);
            $pdf->Write(0, 'Partial payment $'.number_format($partial, 2));
        }
        // ====== End Page 2 ======

        // ====== Page 3 ======
        $templateId = $pdf->importPage(3);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);
        // ====== End Page 3 ======

        // ====== Page 4 ======
        $templateId = $pdf->importPage(4);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);
        // ====== End Page 4 ======

        // ====== Page 5 ======
        $templateId = $pdf->importPage(5);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);

        $pdf->SetFont('arial', 'B', 10);
        $pdf->SetXY(41, 280.5);
        $pdf->Write(0, "CA$lead->id");
        // ====== End Page 5 ======
        return $pdf;
    }

    public function prepWorkVisa(
        Retainer $retainer,
        Lead $lead,
        Customer $customer,
        User $user,
        $partial = null,
    ) {
        $pdf = new FPDI();
        $pdf->setSourceFile(storage_path('/work-visa-retainer.pdf'));

        // ====== Page 1 ======
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        
        $pdf->useTemplate($templateId);

        $pdf->SetFont('arial', '', 10);
        $pdf->SetXY(40, 86.5);
        $pdf->Write(0, "$user->firstname $user->lastname");

        $pdf->SetFont('arial', 'B', 10);
        $pdf->SetXY(41, 91.5);
        $pdf->Write(0, "CA$lead->id");

        $pdf->SetFont('arial', '', 11);
        $pdf->SetXY(45, 96.5);
        $pdf->Write(0, $customer->residence ?? $customer->country);
        // ====== End Page 1 ======

        // ====== Page 2 ======
        $templateId = $pdf->importPage(2);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);

        $amount = $retainer->visa->price;
        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(177, 200);
        $pdf->Write(0, '$' . number_format($amount));

        if($partial) {
            $pdf->SetFont('arial', 'BIU');
            $pdf->SetXY(150, 205);
            $pdf->Write(0, 'Partial payment $'.number_format($partial));
        }
        // ====== End Page 2 ======

        // ====== Page 3 ======
        $templateId = $pdf->importPage(3);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);
        // ====== End Page 3 ======

        // ====== Page 4 ======
        $templateId = $pdf->importPage(4);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);
        // ====== End Page 4 ======

        // ====== Page 5 ======
        $templateId = $pdf->importPage(5);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);

        $pdf->SetFont('arial', 'B', 10);
        $pdf->SetXY(38, 259.5);
        $pdf->Write(0, "CA$lead->id");
        // ====== End Page 5 ======

        return $pdf;
    }

    public function prepTouristVisa(
        Retainer $retainer,
        Lead $lead,
        Customer $customer,
        User $user,
        $partial = null,
    ) {
        $pdf = new FPDI();
        $pdf->setSourceFile(storage_path('/tourism-visa-retainer.pdf'));

        // ====== Page 1 ======
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(14, 48);
        $pdf->Write(0, "$user->firstname $user->lastname - CA$lead->id");

        $pdf->SetXY(14, 54);
        $pdf->Write(0, $customer->residence ?? $customer->country);

        $pdf->SetFont('arial', '', 11);
        $pdf->SetXY(24, 122.5);
        $pdf->Write(0, "$user->firstname $user->lastname,");

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(67, 217);
        $pdf->Write(0, '$' . number_format($retainer->visa->price));

        if($partial) {
            $pdf->SetFont('arial', 'BIU');
            $pdf->SetXY(67, 211);
            $pdf->Write(0, 'Partial payment $'.number_format($partial));
        }
        // ====== End Page 1 ======

        // ====== Page 2 ======
        $templateId = $pdf->importPage(2);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
        $pdf->useTemplate($templateId);
        // ====== End Page 2 ======

        return $pdf;
    }

    public function prepFileOpening(
        Retainer $retainer,
        Lead $lead,
        Customer $customer,
        User $user,
        $partial = null,
    ): FPDI {
        $pdf = new FPDI();
        $pdf->setSourceFile(storage_path('/file-opening-retainer.pdf')); 
        
        // ====== Page 1 ======
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));

        $pdf->useTemplate($templateId);

        $fullname = "$user->firstname $user->lastname";
        $stringCount = strlen($fullname);
        $fontSize = 210/$stringCount;

        $pdf->SetFont('arial', 'B', $fontSize > 11 ? 11: $fontSize);
        $pdf->SetXY(78, 67.5);
        $pdf->Write(0, "$fullname");

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(23, 222);
        $pdf->Write(0, "$fullname");

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(120, 73);
        $pdf->Write(0, "CA$lead->id");

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(137, 231.5);
        $pdf->Write(0, "CA$lead->id");

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(83, 78);
        $pdf->Write(0, $customer->residence ?? $customer->country);

        $pdf->SetFont('arial', 'B', 11);
        $pdf->SetXY(109.5, 114);
        $pdf->Write(0, '$'.number_format($retainer->visa->price));

        if($partial) {
            $pdf->SetFont('arial', 'BIU');
            $pdf->SetXY(109.5, 108);
            $pdf->Write(0, 'Partial payment $'.number_format($partial));
        }

        return $pdf;
    }
}
