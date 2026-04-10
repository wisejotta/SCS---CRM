<?php

namespace App\Http\Controllers;

use App\Console\Commands\FixLanguage;
use App\Enums\AgentType;
use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use App\Models\Agent;
use App\Models\CSVUpload;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\PaymentResponse;
use App\Models\User;
use App\Models\Visa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AdminController extends Controller
{
    public function leads(Request $request, $type, $first = false)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('q');
        $selectedAgents = $request->post('selectedAgents');
        $reassign = $request->post('reassign');

        // query
        $leadsQ = Lead::with('customer.user')->with('agent.user')->with('boAgent.user');
        if($search) {
            $leadsQ->whereHas('customer', function ($query) use($search) {
                return $query->where('phone_number', 'LIKE', "%$search%")
                    ->orWhere('country', 'LIKE', "%$search%")
                    ->orWhereHas('user', function($query) use($search) {
                        $query->where('firstname', 'LIKE', "%$search%")
                            ->orWhere('lastname', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    })->orWhereHas('lead', function($query) use($search) {
                        $query->where('id', $search);
                    });
            });
        }
        $leadsQ = $leadsQ->whereNull('reason');

        if($request->post('type')) {
            $leadsQ->where('status', LeadStatus::fromName(strtoupper($request->post('type'))));
        }

        $type = explode('-', $type);
        switch($type[0]) {
            case 'unassigned':
                $leadsQ->whereNull('reassigned_at')->whereNotNull('unassigned_at');
                break;

            case 'english':
                $leadsQ->where('language', 'en');
                break;

            case 'spanish':
                $leadsQ->where('language', 'es');
                break;
        }

        if(isset($type[1])) {
            switch($type[1]) {
                case 'fresh':
                    $leadsQ->whereNull('reassigned_at')->whereNull('unassigned_at');
                    break;
                
                case 'reassign':
                    $leadsQ->whereNotNull('reassigned_at');
                    break;
                
                case 'collection':
                    $leadsQ->whereHas('payments', function($q) {
                        $q->where('complete', false)->where('status', PaymentStatus::SUCCESSFUL);
                    });
                    break;
            }
        }
        
        if(count($selectedAgents)) {
            $leadsQ->whereIn('agent_id', $selectedAgents);
        }

        if($reassign) {
            $leadsQ->whereNotNull('assigned_at');
        }

        // managing outputs
        $count = $leadsQ->count();
        if($first) return $count;
        $leads = $leadsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('sort_date', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($leads as $lead) {
            $array[] = [
                'id' => $lead->id,
                'product' => $lead->visa ? $lead->visa->name : '-',
                'agent' => $lead->agent ? $lead->agent->user->firstname.' '.$lead->agent->user->lastname : '-',
                'boAgent' => $lead->boAgent ? $lead->boAgent->user->firstname.' '.$lead->boAgent->user->lastname : '-',
                'csAgent' => $lead->csAgent ? $lead->csAgent->user->firstname.' '.$lead->csAgent->user->lastname : '-',
                'name' => $lead->customer->user->firstname.' '.$lead->customer->user->lastname,
                'email' => $lead->email ?? $lead->customer->user->email,
                'phone_number' => $lead->phone_number ?? $lead->customer->phone_number,
                'country' => $lead->customer->country,
                'profession' => $lead->customer->profession ?? '-',
                'created_at' => date('M d, Y H:i', strtotime($lead->created_at)),
            ];
        }
        
        return [
            'leads' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalLeads' => $count
        ];
    }

    public function closed(Request $request)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');

        // query
        $leadsQ = Lead::with('customer.user')
            ->with('agent.user')
            ->has('visa')
            ->with('visa')
            ->whereHas('payments', function($query) {
                $query->where('complete', false)->where('status', PaymentStatus::SUCCESSFUL);
            })
            ->withSum(['payments' => function($q) {
                $q->where('complete', false)->where('status', PaymentStatus::SUCCESSFUL);
            }], 'amount')->get()->filter(function(Lead $lead, int $index) {
                return $lead->payments_sum_amount && $lead->payments_sum_amount >= $lead->visa->price;
            });

        // managing outputs
        $count = $leadsQ->count();
        $leads = $leadsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('created_at', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($leads as $lead) {
            $array[] = [
                'id' => $lead->id,
                'product' => $lead->visa ? $lead->visa->name : '-',
                'agent' => $lead->agent ? $lead->agent->user->firstname.' '.$lead->agent->user->lastname : '-',
                'name' => $lead->customer->user->firstname.' '.$lead->customer->user->lastname,
                'email' => $lead->email ?? $lead->customer->user->email,
                'phone_number' => $lead->phone_number ?? $lead->customer->phone_number,
            ];
        }
        
        return [
            'leads' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalLeads' => $count
        ];
    }

    public function disqualified(Request $request, $type)
    {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('search');

        // query
        $leadsQ = Lead::where('reason->status', $type)->with('customer.user')->with('agent.user');
        if($search) {
            $leadsQ->whereHas('customer', function ($query) use($search) {
                return $query->where('phone_number', 'LIKE', "%$search%")
                    ->orWhere('country', 'LIKE', "%$search%")
                    ->orWhereHas('user', function($query) use($search) {
                        $query->where('firstname', 'LIKE', "%$search%")
                            ->orWhere('lastname', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    })->orWhereHas('lead', function($query) use($search) {
                        $query->where('id', $search);
                    });
            })->where('reason->status', $type);
        }
        // managing outputs
        $count = $leadsQ->count();
        $leads = $leadsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('reason->date', 'desc')->get();

        // processing data
        $array = [];
        foreach($leads as $lead) {
            $array[] = [
                'id' => $lead->id,
                'product' => $lead->visa ? $lead->visa->name : '-',
                'agent' => $lead->agent ? $lead->agent->user->firstname.' '.$lead->agent->user->lastname : '-',
                'name' => $lead->customer->user->firstname.' '.$lead->customer->user->lastname,
                'email' => $lead->email ?? $lead->customer->user->email,
                'date' => date('M d, Y H:i', strtotime($lead->reason['date'])),
            ];
        }
        
        return [
            'leads' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalLeads' => $count
        ];
    }

    public function assigned(Request $request) {
        // post fields
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('q');
        $selectedAgents = $request->post('selectedAgents');

        // query
        $leadsQ = Lead::with('customer.user')->with('agent.user')->with('boAgent.user');
        if($search) {
            $leadsQ->whereHas('customer', function ($query) use($search) {
                return $query->where('phone_number', 'LIKE', "%$search%")
                    ->orWhere('country', 'LIKE', "%$search%")
                    ->orWhereHas('user', function($query) use($search) {
                        $query->where('firstname', 'LIKE', "%$search%")
                            ->orWhere('lastname', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    })->orWhereHas('lead', function($query) use($search) {
                        $query->where('id', $search);
                    });
            });
        }
        $leadsQ = $leadsQ->whereNull('reason')->has('agent');
        
        if(count($selectedAgents)) {
            $leadsQ->whereIn('agent_id', $selectedAgents);
        }
        
        if($request->post('recallsOnly')) {
            $leadsQ->where('recall', true);
        }

        // managing outputs
        $count = $leadsQ->count();
        $leads = $leadsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('created_at', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($leads as $lead) {
            $array[] = [
                'id' => $lead->id,
                'product' => $lead->visa ? $lead->visa->name : '-',
                'agent' => $lead->agent ? $lead->agent->user->firstname.' '.$lead->agent->user->lastname : '-',
                'boAgent' => $lead->boAgent ? $lead->boAgent->user->firstname.' '.$lead->boAgent->user->lastname : '-',
                'name' => $lead->customer->user->firstname.' '.$lead->customer->user->lastname,
                'email' => $lead->email ?? $lead->customer->user->email,
                'phone_number' => $lead->phone_number ?? $lead->customer->phone_number,
                'country' => $lead->customer->country,
                'created_at' => date('M d, Y H:i', strtotime($lead->created_at)),
            ];
        }
        
        return [
            'leads' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalLeads' => $count
        ];
    }

    public function customer(Lead $lead)
    {
        return [
            'lead' => [
                'id' => $lead->id,
                'firstname' => $lead->customer->user->firstname,
                'lastname' => $lead->customer->user->lastname,
                'email' => $lead->email ?? $lead->customer->user->email,
                'phone_number' => $lead->phone_number ?? $lead->customer->phone_number,
                'office_number' => $lead->office_number ?? $lead->customer->office_number,
                'dob' => $lead->customer->dob,
                'gender' => $lead->customer->gender,
                'country' => $lead->customer->country,
                'residence' => $lead->customer->residence,
                'profession' => $lead->customer->profession,
                'agent' => $lead->agent_id,
                'status' => $lead->status,
                'product' => $lead->visa_id,
                'results' => $lead->results ?? [],
                'retainers' => $lead->retainers->map(function($retainer) {
                    return [
                        'visa_id' => $retainer->visa_id,
                        'name' => $retainer->visa->name,
                        'signed_at' => $retainer->signed_at
                            ? date('M d, Y H:i', strtotime($retainer->signed_at))
                            : null,
                        'retainer' => $retainer->file,
                    ];
                }),
            ],
        ];
    }

    public function setUp()
    {
        return [
            'products' => Visa::select('id as value', 'name as text')->get(),
            'agents' => array_map(function($agent) {
                return [
                    'value' => $agent['id'],
                    'text' => $agent['user']['firstname'] .' '.$agent['user']['lastname'],
                ];
            }, Agent::with('user')->get()->toArray()),
        ];
    }

    public function customerUpdate(Request $request, Lead $lead)
    {
        $newInfo = [
            'agent_id' => $request->post('agent'),
            'email' => $request->post('email'),
            'visa_id' => $request->post('product'),
            'status' => $request->post('status'),
        ];

        if($lead->visa_id) {
            $paid = $lead
                ->payments()
                ->where('complete', false)
                ->where('status', PaymentStatus::SUCCESSFUL)
                ->sum('amount');

            if($paid >= $lead->visa->price) {
                $lead->payments()->update([
                    'complete' => true
                ]);
                $lead->history()->create([
                    'status' => $lead->status,
                    'visa_id' => $lead->visa_id,
                    'agent_id' => $lead->agent_id,
                ]);
            }
        }

        if($lead->agent_id != $request->post('agent')) {
            if(!$lead->assigned_at) {
                $newInfo['assigned_at'] = date('Y-m-d H:i:s');
            } else {
                $newInfo['reassigned_at'] = date('Y-m-d H:i:s');
                $paidCount = $lead
                    ->payments()
                    ->where('status', PaymentStatus::SUCCESSFUL)
                    ->count();

                if($paidCount == 0) {
                    (new LeadController)->addToMailchimp($lead, 'Reassign', '');
                } else if($lead->visa_id && $paid < $lead->visa->price) {
                    (new LeadController)->addToMailchimp($lead, 'Collection', '');
                }
            }
        }

        $lead->update($newInfo);
        $lead->customer->update([
            'country' => $request->post('country'),
            'gender' => $request->post('gender'),
            'residence' => $request->post('residence'),
            'profession' => $request->post('profession'),
            'phone_number' => $request->post('phone_number'),
            'office_number' => $request->post('office_number'),
            'dob' => $request->post('dob'),
        ]);
        
        $lead->customer->user->update([
            'firstname' => $request->post('firstname'),
            'lastname' => $request->post('lastname'),
        ]);

        if($retainer = $lead->retainer) {
            if(!$retainer->signed_at) {
                $uniqid = uniqid();
                $file = config('app.url') . "/pdf/uploads/$uniqid.pdf";
                $retainer->file = $file;
                $retainer->save();
    
                $filename = public_path("pdf/uploads/$uniqid.pdf");
                $controller = new RetainerController;
                $pdf = $controller->prep($retainer);
                $pdf->Output($filename, 'F');
                return ['retainer' => $retainer->file];
            }
        }
        return [];
    }

    function getCSVHeaders(Request $request) {
        if($file = $request->file('csv')) {
            $user = $request->user();
            $name = time();
            $fileName =  "$name.".$file->getClientOriginalExtension();
            $file->move(public_path('csv/uploads'), $fileName);

            $filepath = public_path("csv/uploads/$fileName");
            // Reading file
            $file = fopen($filepath, "r");

            $separator = ',';
            $filedata = fgetcsv($file, null, $separator);
            if(count($filedata) == 1) {
                fclose($file);
                $file = fopen($filepath, "r");
                $separator = ';';
                $filedata = fgetcsv($file, null, $separator);
            }

            if(count($filedata) > 1) {
                return [
                    'upload_id' => $user->agent->csvUploads()->create([
                        'file' => $fileName,
                        'separator' => $separator,
                    ])->id,
                    'headers' => array_map(function($value) {
                        static $i = -1;
                        $i++;
                        return [
                            'field' => $value,
                            'index' => $i,
                        ];
                    }, $filedata),
                ];
            }
        }
        return response('No file found', 400);
    }

    public function uploadCsv(Request $request, CSVUpload $upload)
    {
        $errors = [];
        $filepath = public_path("csv/uploads/$upload->file");
        $file = fopen($filepath, 'r');
        $importData_arr = array(); // Read through the file and store the contents as an array
        $i = 0;

        // Read the contents of the uploaded file 
        while (($filedata = fgetcsv($file, null, $upload->separator)) !== FALSE) {
            $num = count($filedata);
            // Skip first row (Remove below comment if you want to skip the first row)
            if ($i == 0) {
                $i++;
                continue;
            }
            for ($c = 0; $c < $num; $c++) {
                $importData_arr[$i][] = $filedata[$c];
            }
            $i++;
        }
        fclose($file); //Close after reading

        foreach($importData_arr as $key => $data) {
            try {
                $emailIndex = $request->post('email');
                if(isset($data[$emailIndex]) && trim($data[$emailIndex]) != '') {
                    $email = trim($data[$emailIndex]);
                    $firstnameIndex = $request->post('firstname');
                    $lastnameIndex = $request->post('lastname');
                    $userData = [
                        'email' => $email,
                        'firstname' => $firstnameIndex !== null ? ($data[$firstnameIndex] ?? '') : '',
                        'lastname' => $lastnameIndex !== null ? ($data[$lastnameIndex] ?? '') : '',
                        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    ];

                    $countryIndex = $request->post('country');
                    $country = $countryIndex !== null ? ($data[$countryIndex] ?? '') : '';
                    $residenceIndex = $request->post('residence');
                    $professionIndex = $request->post('profession');
                    $phoneNumberIndex = $request->post('phone_number');
                    $officeNumberIndex = $request->post('office_number');
                    $genderIndex = $request->post('gender');
                    $dobIndex = $request->post('dob');
                    $gender = $genderIndex !== null ? ($data[$genderIndex] ?? null) : null;
                    $gender = isset($gender) ? strtolower($gender) : null;
                    $language = 'en';
                    if(in_array($country, explode(',', FixLanguage::SPANISH))) {
                        $language = 'es';
                    }
                    $customerData = [
                        'country' => $country,
                        'language' => $language,
                        'residence' => $residenceIndex !== null ? ($data[$residenceIndex] ?? '') : '',
                        'profession' => $professionIndex !== null ? ($data[$professionIndex] ?? '') : '',
                        'phone_number' => $phoneNumberIndex !== null ? ($data[$phoneNumberIndex] ?? '') : '',
                        'office_number' => $officeNumberIndex !== null ? ($data[$officeNumberIndex] ?? '') : '',
                        'dob' => $dobIndex !== null ? ($data[$dobIndex] ?? '') : '',
                        'gender' => in_array($gender, ['male', 'female']) ? $gender : null,
                    ];

                    $leadData = [
                        'language' => $language,

                    ];

                    $oldUser = User::where('email', $email)->first();
                    DB::beginTransaction();
                    if(!$oldUser) {
                        $user = User::create($userData);

                        $customerData['user_id'] = $user->id;
                        $customer = Customer::create($customerData);

                        $leadData['customer_id'] = $customer->id;
                        $leadData['status'] = LeadStatus::FILE_OPENING->value;
                        \Log::info($leadData);
                        Lead::create($leadData);
                    } elseif($request->post('updateFields')) {
                        $oldUser->update($userData);
                        $oldUser->customer->update($customerData);
                        $oldUser->customer->lead->update($leadData);
                    }
                    DB::commit();
                } else {
                    $errors[$key] = 'No email address.';
                }
            } catch (\Exception $e) {
                $errors[$key] = $e->getMessage();
            }
        }

        if(count($errors)) {
            $upload->update([
                'results' => $errors,
            ]);
        }
        return [
            'failedItems' => count($errors),
        ];
    }

    public function csvErrors(Request $request, CSVUpload $upload) {

        $fileName = time() . '_errors.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function() use($upload) {

            $filepath = public_path("csv/uploads/$upload->file");
            $file = fopen($filepath, 'r');

            $i = 0;
            $newFile = fopen('php://output', 'w');
            while (($filedata = fgetcsv($file, null, $upload->separator)) !== FALSE) {
                if($i == 0) {
                    $filedata[] = 'Error';
                    fputcsv($newFile, $filedata);
                } elseif(isset($upload->results[$i])) {
                    $filedata[] = $upload->results[$i];
                    fputcsv($newFile, $filedata);
                }
                $i++;
            }
            fclose($file); //Close after reading
            fclose($newFile);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function agents()
    {
        return array_map(
            fn($agent) => [
                'id' => $agent['id'],
                'name' => $agent['user']['firstname'].' '.$agent['user']['lastname'],
            ],
            Agent::select('id', 'user_id')->with('user:id,firstname,lastname')->get()->toArray()
        );
    }

    public function rmvAgents(Request $request)
    {
        Lead::whereIn('id', $request->ids)->update([
            'agent_id' => null
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $agent = $user->agent;
        $user->password = $agent->password_reset['password'];
        $user->remember_token = null;
        $user->save();

        $agent->password_reset = null;
        $agent->save();
    }

    public function assignAgents(Request $request, Agent $agent, bool $ignore = false)
    {
        $leads = Lead::whereIn('id', $request->ids)->get();
        foreach($leads as $lead) {

            if($lead->visa_id) {
                $paid = $lead
                    ->payments()
                    ->where('complete', false)
                    ->where('status', PaymentStatus::SUCCESSFUL)
                    ->sum('amount');

                if($paid >= $lead->visa->price) {
                    $lead->payments()->update([
                        'complete' => true
                    ]);
                    $lead->history()->create([
                        'status' => $lead->status,
                        'visa_id' => $lead->visa_id,
                        'agent_id' => $lead->agent_id,
                    ]);
                }
            }

            $data = [];
            if(!$ignore) {
                if(!$lead->assigned_at) {
                    $data['assigned_at'] = date('Y-m-d H:i:s');
                } else {
                    $data['reassigned_at'] = date('Y-m-d H:i:s');
                    $paidCount = $lead
                        ->payments()
                        ->where('status', PaymentStatus::SUCCESSFUL)
                        ->count();

                    if($paidCount == 0) {
                        (new LeadController)->addToMailchimp($lead, 'Reassign', '');
                    } else if($lead->visa_id && $paid < $lead->visa->price) {
                        (new LeadController)->addToMailchimp($lead, 'Collection', '');
                    }
                }
            }

            if($agent->type == AgentType::BACK_OFFICE) {
                $data['bo_agent_id'] = $agent->id;
            } elseif($agent->type == AgentType::CUSTOMER_SERVICE) {
                $data['cs_agent_id'] = $agent->id;
            } else {
                $data['agent_id'] = $agent->id;
            }

            Lead::whereIn('id', $request->ids)->update($data);
        }
    }

    public function lastPayment($id) {
        $paymentResponse = PaymentResponse::where('id', '>', $id)
            ->whereNotNull('agent_id')
            ->whereHas('payment', function($query) {
                return $query->where('status', PaymentStatus::SUCCESSFUL);
            })
            ->with('payment')->firstOrFail();

        return [
            'name' => $paymentResponse->agent->user->firstname . ' ' . $paymentResponse->agent->user->lastname,
            'amount' => '$' . number_format($paymentResponse->payment->amount)
        ];
    }

    public function tvData() {
        $fromDate = strtotime(date('Y-m-27 03:00:00'));
        if($fromDate > time()) {
            $date = date('Y-m', strtotime('-1 Month'));
            $fromDate = strtotime(date("$date-27 03:00:00"));
        }
        $fromDate = date('Y-m-d H:i:s', $fromDate);

        $types = [
            AgentType::FILE_OPENING->value => [
                'name' => 'FILE OPENING',
                'agents' => [],
            ],
            AgentType::UPGRADE->value => [
                'name' => 'UPGRADE',
                'agents' => [],
            ],
        ];

        $agents = Agent::where('type', AgentType::UPGRADE->value)
            ->with(['payments' => function($query) use($fromDate) {
                return $query
                    ->where('completed_at', '>=', $fromDate)
                    ->where('status', PaymentStatus::SUCCESSFUL);
            }])
            ->withSum(['payments' => function($query) use($fromDate) {
                return $query
                    ->where('completed_at', '>=', $fromDate)
                    ->where('status', PaymentStatus::SUCCESSFUL);
            }], 'amount')
            ->orderBy('payments_sum_amount', 'desc')
            ->get();

        foreach($agents as $agent) {
            $types[$agent->type->value]['agents'][] = [
                'name' => $agent->user->firstname . ' ' . $agent->user->lastname,
                'count' => $agent->payments->unique('lead_id')->count(),
                'progress' => isset($agent->targets['goal']) && $agent->targets['goal'] > 0
                    ? round($agent->payments_sum_amount/$agent->targets['goal']*100, 1)
                    : 0,
            ];
        }

        $agents = Agent::where('type', AgentType::FILE_OPENING->value)
            ->with(['history' => function($query) use($fromDate) {
                return $query
                    ->where('created_at', '>=', $fromDate);
            }])
            ->with(['payments' => function($query) use($fromDate) {
                return $query
                    ->where('completed_at', '>=', $fromDate)
                    ->where('status', PaymentStatus::SUCCESSFUL);
            }])
            ->withCount(['history' => function($query) use($fromDate) {
                return $query->where('created_at', '>=', $fromDate);
            }])
            ->orderBy('history_count', 'desc')
            ->get();

        foreach($agents as $agent) {
            $types[$agent->type->value]['agents'][] = [
                'name' => $agent->user->firstname . ' ' . $agent->user->lastname,
                'count' => $agent->payments->unique('lead_id')->count(),
                'sales' => $agent->history_count,
                'progress' => isset($agent->targets['goal']) && $agent->targets['goal'] > 0
                    ? round($agent->history_count/$agent->targets['goal']*100, 1)
                    : 0,
            ];
        }

        $res = PaymentResponse::latest('id')->first();

        return [
            'departments' => array_map(function($department) {
                $department['count'] = array_sum(
                    array_column($department['agents'], 'count')
                );
                return $department;
            }, $types),
            'latest' => $res ? $res->id : 0,
        ];
    }

    public function sendCreds(Request $request, Lead $lead) {
        $email = $request->post('email');
        $user = $lead->customer->user;

        $password = $user->text_password;
        if(!$password) {
            $password = uniqid();
            $user->text_password = Crypt::encryptString($password);
            $user->password = Hash::make($password);
            $user->save();
        } else {
            $password = Crypt::decryptString($password);
        }

        $sg = new \SendGrid(config('sendgrid.apiKey'));
        $response = $sg->client->mail()->send()->post([
            'template_id' => 'd-f5a8867ab168470d8d37fc362c814554',
            'from' => [
                'email' => 'hello@visascanada.org',
                'name' => 'Visas Canada'
            ],
            'personalizations' => [[
                'to' => [[
                    'email' => $email,
                    'name' => $user->firstname .' '. $user->lastname
                ]],
                'dynamic_template_data' => [
                    'lead_firstname' => $user->firstname,
                    'lead_lastname' => $user->lastname,
                    'user_email' => $user->email,
                    'user_password' => $password,
                    'language' => 'en',
                    'link' => config('app.customer_url') . '/customer/login',
                ]
            ]]
        ]);

        return response()->json(['message' => $response->body()], $response->statusCode());
    }

    public function getCreds(Request $request, Lead $lead) {
        $user = $lead->customer->user;
        $password = $user->text_password;
        if(!$password) {
            $password = uniqid();
            $user->text_password = Crypt::encryptString($password);
            $user->password = Hash::make($password);
            $user->save();
        } else {
            $password = Crypt::decryptString($password);
        }

        return [
            'email' => $user->email,
            'password' => $password,
        ];
    }
}
