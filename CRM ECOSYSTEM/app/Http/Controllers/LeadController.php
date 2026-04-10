<?php

namespace App\Http\Controllers;

use App\Console\Commands\FixLanguage;
use App\Enums\AgentType;
use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Visa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{

    public const BACK_OFFICE_DOCS = [
        // Awaiting LOA
        'RETAINER AGREEMENT',
        'LETTER OF ACCEPTANCE & RECEIPT OF TUITIONS OR FEES CONTRIBUTED',
        'EMPLOYMENT LETTER',
        'IELTS',
        'DIGITAL PHOTO (LIKE THE ONE IN YOUR PASSPORT)',
        'STUDENT VISA ELIGIBILITY FORM',
        'PASSPORT COPY',
        'FAMILY RELATIONSHIP DOCUMENT',
        'AFFIDAVIT LETTER',
        'REFERENCE LETTER FROM FRIENDS AND FAMILY',
        'STUDY PLAN',
        'COPY OF DIPLOMA or DEGREE or TRANSCRIPTS',

        // LOA Received
        'IMM 1294 STUDY PERMIT FORM',
        'IMM 5645 FAMILY INFO FORM',
        'IMM 5476 USE OF REPRESENTATIVE',
        'PROOF OF FUNDS',

        // Pre-submission
        'COPY OF PASSPORT: STAMPED PAGES',
    ];

    public function index(Request $request, $type)
    {
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');
        $search = $request->post('search');

        $user = $request->user();
        $leads = $user->agent->leads()->with('customer.user');
        if($search) {
            $leads->whereHas('customer', function ($query) use($search) {
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
        $leads->whereNull('reason');
        
        $leads = $this->filterType($leads, $type, $request->post('recallsOnly'));

        $count = $leads->count();
        $leads = $leads->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('sort_date', 'desc')->get();

        return [
            'leads' => array_map(function($lead) {
                return [
                    'id' => $lead['id'],
                    'firstname' => $lead['customer']['user']['firstname'],
                    'lastname' => $lead['customer']['user']['lastname'],
                    'country' => $lead['customer']['country'],
                    'phone_number' => $lead['phone_number'] ?? $lead['customer']['phone_number'],
                    'office' => $lead['customer']['office_number'],
                    'email' => $lead['email'] ?? $lead['customer']['user']['email'],
                ];
            }, $leads->toArray()),
            'totalPage' => ceil($count/$perPage),
            'totalLeads' => $count
        ];
    }

    public function filterSection(Request $request)
    {
        $search = $request->post('search');
        $agent = $request->user()->agent;
        $firstLead = $agent->leads()
            ->whereNull('reason')
            ->whereHas('customer', function ($query) use($search) {
                return $query->where('phone_number', 'LIKE', "%$search%")
                    ->orWhere('country', 'LIKE', "%$search%")
                    ->orWhereHas('user', function($query) use($search) {
                        $query->where('firstname', 'LIKE', "%$search%")
                            ->orWhere('lastname', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    })->orWhereHas('lead', function($query) use($search) {
                        $query->where('id', $search);
                    });
            })->first();

        
        if($firstLead) {
            \Log::info("$firstLead->id: $firstLead->reassigned_at");
            return $firstLead->reassigned_at ? 'reasigned' : 'fresh';
        }
        return 0;
    }

    public function backOffice(Request $request)
    {
        $search = $request->post('search');
        $perPage = $request->post('perPage');
        $currentPage = $request->post('currentPage');

        $agent = $request->user()->agent;

        if($agent->type == AgentType::CUSTOMER_SERVICE) {
            $leadsQ = $agent->csLeads()
                ->with('csAgent.user')
                ->withSum(['payments' => function($q) {
                    $q->where('status', PaymentStatus::SUCCESSFUL);
                }], 'amount');
        } else {
            $leadsQ = $agent->boLeads()
                ->whereHas('payments', function($q) {
                    $q->select(\DB::raw('SUM(amount) as total'))
                        ->where('status', PaymentStatus::SUCCESSFUL)
                        ->havingRaw('total >= 500');
                })
                ->with('cbAgent.user')
                ->withSum(['payments' => function($q) {
                    $q->where('status', PaymentStatus::SUCCESSFUL);
                }], 'amount');
        }

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

        // managing outputs
        $count = $leadsQ->count();
        $leads = $leadsQ->skip(($currentPage - 1) * $perPage)->take($perPage)->orderBy('created_at', 'desc')->get();
        
        // processing data
        $array = [];
        foreach($leads as $lead) {
            $progress = $lead->backoffice
                ? floor(
                    count(array_filter($lead->backoffice, fn($b) => $b['status'] == 'pre-approved')) /
                    count(self::BACK_OFFICE_DOCS) *
                    100
                )
                : 0;
            $array[] = [
                'id' => $lead->id,
                'product' => $lead->visa ? $lead->visa->name : '-',
                'progress' => $progress,
                'agent' => $lead->agent ? $lead->agent->user->firstname.' '.$lead->agent->user->lastname : '-',
                'paid' => '$' . number_format($lead->payments_sum_amount, 2),
                'name' => $lead->customer->user->firstname.' '.$lead->customer->user->lastname,
                'country' => $lead->customer->country,
                'created_at' => date('M d, Y H:i', strtotime($lead->created_at)),
                'residence' => $lead->customer->residence,
                'phone_number' => $lead->phone_number ?? $lead->customer->phone_number,
                'office' => $lead->customer->office_number,
                'email' => $lead->email ?? $lead->customer->user->email,
                'status' => $lead->status->name,
                'callback' => [
                    'date' => $lead->callback ? date('M d, Y H:i', strtotime($lead->callback)) : null,
                    'agent' => $lead->cbAgent ? $lead->cbAgent->user->firstname . ' ' . $lead->cbAgent->user->lastname : null,
                ],
                'gender' => $lead->customer->gender ? str($lead->customer->gender)->ucfirst() : null,
                'dob' => $lead->customer->dob,
                'reason' => $lead->reason,
            ];
        }
        
        return [
            'leads' => $array,
            'totalPage' => ceil($count/$perPage),
            'totalLeads' => $count
        ];
    }

    public function uploadBackOfficeDoc(Request $request, Lead $lead, $slug) {
        if($file = $request->file('file')) {
            $name = uniqid();
            $extension = $file->getClientOriginalExtension();
            $fileName =  "$name.$extension";
            $file->move(public_path('pdf/uploads'), $fileName);

            $backoffice = $lead->backoffice ?? [];
            $item = $backoffice[$slug] ?? [
                'status' => 'review',
                'name' => $request->post('name'),
            ];
            $item['doc'] = config('app.url') . "/pdf/uploads/$name.$extension";
            $backoffice[$slug] = $item;
            $lead->update(['backoffice' => $backoffice]);

            return $item['doc'];
        } else {
            return response('No file found', 400);
        }
    }

    public function show(Request $request, Lead $lead, bool $isAdmin = false)
    {
        $user = $request->user();
        $visaPrice = $lead->visa_id ? $lead->visa->price : 0;
        $paid = $lead
            ->payments()
            ->where('complete', false)
            ->where('lead_status', $lead->status)
            ->where('status', PaymentStatus::SUCCESSFUL)->sum('amount');

        $payments = $lead->responses()
            ->with('agent.user')
            ->orderBy('payment_responses.created_at', 'desc')
            ->get();

        $products = $isAdmin ? Visa::orderBy('order', 'asc')->get() : $user->agent->type->products();
        $products = $products->map(function($product) use($lead, $paid, $visaPrice) {
            $newAmount = $lead->visa_id == $product['id']
                ? $visaPrice
                : $product['price'];
            return [
                'value' => $product['id'],
                'text' => $product['name'],
                'outstanding' => $newAmount - $paid,
                'outstanding_str' => '$' . number_format($newAmount - $paid, 2),
                'amount' => $newAmount,
                'amount_str' => '$' . number_format($newAmount, 2),
            ];
        });

        $backOfficeDocs = [];
        if($user->agent->type == AgentType::BACK_OFFICE) {
            $backoffice = $lead->backoffice ?? [];
            $backOfficeDocs = [];
            foreach(self::BACK_OFFICE_DOCS as $doc) {
                $backOfficeDocs[$doc] = isset($backoffice[$doc]) ? [
                    'status' => $backoffice[$doc]['status'] ?? 'required',
                    'doc' => $backoffice[$doc]['doc'] ?? null
                ] : [
                    'status' => 'required',
                ];
            }
        }

        $callback = [];
        if($user->agent->type == AgentType::BACK_OFFICE) {
            if($lead->bo_callback && $lead->cbBOAgent) {
                $callback = [
                    'date' => date('M d, Y H:i', strtotime($lead->bo_callback)),
                    'agent' => $lead->cbBOAgent->user->firstname . ' ' . $lead->cbBOAgent->user->lastname,
                ];
            }
        } else if($lead->callback && $lead->cbAgent) {
            $callback = [
                'date' => date('M d, Y H:i', strtotime($lead->callback)),
                'agent' => $lead->cbAgent->user->firstname . ' ' . $lead->cbAgent->user->lastname,
            ];
        }

        $retainer = $lead->status == LeadStatus::FILE_OPENING
            ? $lead->retainers()->whereIn('visa_id', [10, 11, 12, 13])->first()  // File Opening
            : $lead->retainers()->whereNotIn('visa_id', [10, 11, 12, 13])->first();

        return [
            'lead' => [
                'id' => $lead->id,
                'firstname' => $lead->customer->user->firstname,
                'lastname' => $lead->customer->user->lastname,
                'marital_status' => $lead->customer->marital_status ? str($lead->customer->marital_status)->ucfirst() : null,
                'language' => $lead->customer->language,
                'education' => $lead->customer->education,
                'occupation' => $lead->customer->occupation,
                'experience' => $lead->customer->experience,
                'arrange_after_employment' => $lead->customer->arrange_after_employment,
                'spouse' => $lead->customer->spouse,
                'country' => $lead->customer->country,
                'residence' => $lead->customer->residence,
                'product' => $lead->visa_id,
                'product_str' => $lead->visa_id ? $lead->visa->name : '-',
                'phone_number' => $lead->phone_number ?? $lead->customer->phone_number,
                'office' => $lead->customer->office_number,
                'email' => $lead->email ?? $lead->customer->user->email,
                'status' => $lead->status->value,
                'status_str' => $lead->status->name,
                'callback' => $callback,
                'gender' => $lead->customer->gender ? str($lead->customer->gender)->ucfirst() : null,
                'dob' => $lead->customer->dob,
                'age' => $lead->customer->dob ? floor((time() - strtotime($lead->customer->dob))/60/60/24/30.42/12) . ' Years' : '-',
                'amount_str' => '$' . number_format($visaPrice, 2),
                'paid_str' => '$' . number_format($paid, 2),
                'amount' => $visaPrice,
                'paid' => $paid,
                'outstanding' => $visaPrice - $paid,
                'outstanding_str' => '$' . number_format($visaPrice - $paid, 2),
                'reason' => $lead->reason,
                'results' => $lead->results ?? [],
                'retainer' => $retainer,
            ],
            'payments' => $payments->map(function($payment) {
                $agent = $payment->agent_id
                    ? $payment->agent->user->firstname . ' ' .  $payment->agent->user->lastname
                    : 'Unknown';

                $visa = isset($payment->payload['visa'])
                    ? Visa::find($payment->payload['visa'])
                    : null;

                return [
                    'id' => $payment->id,
                    'csv' => $payment->payload['csv'] ?? null,
                    'title' => $payment->payload['title'],
                    'message' => $payment->payload['message'],
                    'visa' => $visa ? $visa->name : null,
                    'agent' => $agent,
                    'status' => $payment->status,
                    'date' => date('M d, Y, h:i A', strtotime($payment->created_at)),
                ];
            }),
            'comments' => $lead->comments()->with('agent.user')->orderBy('created_at', 'desc')->get()->map(function($comment) {
                $agent = $comment->agent_id
                    ? $comment->agent->user->firstname . ' ' .  $comment->agent->user->lastname
                    : 'Unknown';
                    
                return [
                    'comment' => $comment->comment,
                    'date' => date('M d, Y - H:i', strtotime($comment->created_at)),
                    'agent' => $agent,
                ];
            }),
            'documents' => $lead->documents()->with('agent.user')->orderBy('created_at', 'desc')->get()->map(function($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->original_name,
                    'agent' => $document->agent->user->firstname . ' ' .  $document->agent->user->lastname,
                    'url' => $document->file_path,
                    'date' => date('M d, Y - H:i', strtotime($document->created_at)),
                ];
            }),
            'products' => $products,
            'backoffice' => $backOfficeDocs,
        ];
    }

    public function updateBackOfficeDoc(Lead $lead, $slug, $status) {
        $backoffice = $lead->backoffice ?? [];
        $item = $backoffice[$slug] ?? [];
        $item['status'] = $status;
        $backoffice[$slug] = $item;
        $lead->update(['backoffice' => $backoffice]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // user
            'email' => 'required|email|unique:users|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',

            // customer
            'country' => 'required|string|max:255',
            'residence' => 'required|string|max:255',
            'gender' => 'in:male,female',
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        
        DB::beginTransaction();

        $user =
            User::where('email', $request->post('email'))->first() ??
            User::create([
                'firstname' => $request->post('firstname'),
                'lastname' => $request->post('lastname'),
                'email' => $request->post('email'),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            ]);
        
        if($user->customer && $user->customer->lead) {
            $user->customer->lead->delete();
        }

        $country = $request->post('country');
        $language = $request->post('language') && strlen($request->post('language')) == 2
            ? strtolower($request->post('language'))
            : 'en';

        if(in_array($country, explode(',', FixLanguage::ENGLISH))) {
            $language = 'en';
        } else if(in_array($country, explode(',', FixLanguage::SPANISH))) {
            $language = 'es';
        }
        
        $customer = [
            'user_id' => $user->id,
            'country' => $request->post('country'),
            'dob' => $request->post('dob'),
            'residence' => $request->post('residence'),
            'gender' => $request->post('gender'), // nullable
            'phone_number' => $request->post('phone_number'),
            'profession' => $request->post('profession'),
            'office_number' => $request->post('office_number'), // nullable
        ];
        
        $customer = Customer::create($customer);

        $lead = [
            'visa_id' => $request->post('visa_id'), // nullable
            'agent_id' => $request->post('agent_id'), // nullable
            'customer_id' => $customer->id,
            'language' => $language,
            'status' => LeadStatus::FILE_OPENING,
        ];

        if($status = $request->post('status')) {
            $lead['status'] = $status;
        }

        if($request->post('agent_id')) {
            $lead['assigned_at'] = date('Y-m-d H:i:s');
        }
        
        $lead = Lead::create($lead);
        DB::commit();

        
        return $this->show($request, Lead::find($lead->id));
    }

    public function next(Request $request, Lead $lead, $type)
    {
        $user = $request->user();
        $leads = $user->agent->leads()->whereNull('reason')->with('customer.user');
        $leads = $this->filterType($leads, $type);

        $newLead = $leads->where('sort_date', '<', $lead->sort_date)->orderBy('sort_date', 'desc')->firstOrFail();
        return $this->show($request, $newLead);
    }

    public function prev(Request $request, Lead $lead, $type)
    {
        $user = $request->user();
        $leads = $user->agent->leads()->whereNull('reason')->with('customer.user');
        $leads = $this->filterType($leads, $type);
        $newLead = $leads->where('sort_date', '>', $lead->sort_date)->orderBy('sort_date', 'asc')->firstOrFail();
        return $this->show($request, $newLead);
    }

    public function saveComments(Request $request, Lead $lead)
    {
        $user = $request->user();
        $comment = $lead->comments()->create([
            'comment' => $request->post('comment'),
            'agent_id' => $user->agent->id,
        ]);
        
        return [
            'comment' => $comment->comment,
            'date' => $comment->created_at->format('M d, Y - H:i'),
            'agent' => "$user->firstname $user->lastname",
        ];
    }

    public function recall(Lead $lead)
    {
        $lead->update([
            'recall' => true,
        ]);
    }
    
    public function paymentLink(Request $request, Lead $lead)
    {
        $agent = $request->user()->agent;
        $payment = $lead->payments()->where('status', PaymentStatus::SENT)->first();

        if($payment) {
            $payment->amount = $request->post('amount');
            $payment->lead_status = $lead->status;
            $payment->lan = $request->post('lan');
            $payment->agent_id = $agent ? $agent->id : $lead->agent_id;
            $payment->save();
        } else {
            $payment = $lead->payments()->create([
                'amount' => $request->post('amount'),
                'lead_status' => $lead->status,
                'lan' => $request->post('lan'),
                'agent_id' => $agent ? $agent->id : $lead->agent_id,
            ]);
        }

        $visa = Visa::find($request->post('product'));
        $newEmail =  $request->post('email');
        $sendVia =  $request->post('sendVia');
        $phoneNumber =  $request->post('phone_number');
        $lead->visa_id = $visa->id;
        $lead->save();

        $paid = $lead
            ->payments()
            ->where('complete', false)
            ->where('lead_status', $lead->status)
            ->where('status', PaymentStatus::SUCCESSFUL)
            ->sum('amount');

        $regen = false;
        $partial = null;
        if($paid + $request->post('amount') < $visa->price) {
            $partial = $paid + $request->post('amount');
            $regen = true;
        } else if($paid) {
            $regen = true;
        } else {
            $history = $lead
                ->payments()
                ->where('complete', false)
                ->where('lead_status', $lead->status)
                ->sum('amount');
            $regen = $history || !!$lead->retainers()->where('visa_id', $lead->visa_id)->count();
        }
        
        if($regen) (new RetainerController)->updateVisa(
            $lead,
            $lead->visa_id,
            $partial,
        );
        
        $amount = round($payment->amount * 100);
        $productId = $lead->visa->external_id;
        $paymentMethod = $request->post('method');

        if($paymentMethod == 'square') {
            $baseUrl = config('square.env') == 'production'
                ? 'https://connect.squareup.com/v2/online-checkout/payment-links'
                : 'https://connect.squareupsandbox.com/v2/online-checkout/payment-links';

            $idempotencyKey = str()->uuid();
            $data = [
                'idempotency_key' => $idempotencyKey,
                'quick_pay' => [
                    'location_id' => config('square.locationId'),
                    'price_money' => [
                        'amount' => $amount,
                        'currency' => 'USD',
                    ],
                    'name' => $lead->visa->name,
                ],
                'checkout_options' => [
                    'accepted_payment_methods' => [
                        'afterpay_clearpay' => false,
                        'apple_pay' => false,
                        'cash_app_pay' => false,
                        'google_pay' => false,
                    ],
                    'allow_tipping' => false,
                    'ask_for_shipping_address' => false,
                    'enable_loyalty' => false,
                    'enable_coupon' => false,
                    // 'redirect_url' => config('app.url') . "/sq/return/$payment->id"
                ],
                'pre_populated_data' => [
                    'buyer_email' => $newEmail ?? $lead->customer->user->email,
                ],
            ];

            $squareRequest = Http::withToken(config('square.accessToken'))
                ->withHeaders([
                    'Square-Version' => '2024-01-18',
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl, $data);

            $link = $squareRequest->json('payment_link.url');
            $payment->update([
                'misc' => [
                    'order_id' => $squareRequest->json('payment_link.order_id'),
                    'method' => $paymentMethod,
                ],
            ]);
        } elseif($paymentMethod == 'authorize' || $paymentMethod == 'authorize-3d') {
            $baseUrl  = config('authorize.baseUrl');
            $merchantName  = config('authorize.merchantName');
            $transactionKey  = config('authorize.transactionKey');
            // {
            //     "getHostedPaymentPageRequest": {
            //       "merchantAuthentication": {
            //         "name": "6am8J8FF",
            //         "transactionKey": "4h3nWx22Tg2VqL3q"
            //       },
            //       "transactionRequest": {
            //         "transactionType": "authCaptureTransaction",
            //         "amount": "20.00",
            //         "customer": {
            //           "email": $newEmail ?? $lead->customer->user->email
            //         }
            //       },
            //       "hostedPaymentSettings": {
                // [
                    //     'settingName' => 'hostedPaymentReturnOptions',
                    //     'settingValue' => ''
                    // ]
            //         "setting": [{
            //           "settingName": "hostedPaymentReturnOptions",
            //           "settingValue": "{\"showReceipt\": true, \"url\": \"https://mysite.com/receipt\", \"urlText\": \"Continue\", \"cancelUrl\": \"https://mysite.com/cancel\", \"cancelUrlText\": \"Cancel\"}"
            //         }, {
            //           "settingName": "hostedPaymentPaymentOptions",
            //           "settingValue": "{\"cardCodeRequired\": false, \"showCreditCard\": true, \"showBankAccount\": true}"
            //         }, {
            //           "settingName": "hostedPaymentSecurityOptions",
            //           "settingValue": "{\"captcha\": false}"
            //         }, {
            //           "settingName": "hostedPaymentShippingAddressOptions",
            //           "settingValue": "{\"show\": false, \"required\": false}"
            //         }, {
            //           "settingName": "hostedPaymentCustomerOptions",
            //           "settingValue": "{\"showEmail\": false, \"requiredEmail\": false, \"addPaymentProfile\": true}"
            //         }, {
            //           "settingName": "hostedPaymentOrderOptions",
            //           "settingValue": "{\"show\": true, \"merchantName\": \"G and S Questions Inc.\"}"
            //         }, {
            //           "settingName": "hostedPaymentIFrameCommunicatorUrl",
            //           "settingValue": "{\"url\": \"https://mysite.com/special\"}"
            //         }]
            //       }
            //     }
            // }
            $response = Http::post("$baseUrl/xml/v1/request.api", [
                'getHostedPaymentPageRequest' => [
                    'merchantAuthentication' => [
                        'name' => $merchantName,
                        'transactionKey' => $transactionKey,
                    ],
                    'transactionRequest' => [
                        'transactionType' => 'authCaptureTransaction',
                        'amount' => $payment->amount,
                        'customer' => [
                            'email' => $newEmail ?? $lead->customer->user->email,
                        ],
                    ],
                    'hostedPaymentSettings' => [
                        'setting' => [
                            [
                                'settingName' => 'hostedPaymentButtonOptions',
                                'settingValue' => \json_encode(['text' => 'Pay']),
                            ],
                            [
                                'settingName' => 'hostedPaymentStyleOptions',
                                'settingValue' => \json_encode(['bgColor' => '#EC1A23']),
                            ],
                            [
                                'settingName' => 'hostedPaymentPaymentOptions',
                                'settingValue' => \json_encode(['showBankAccount' => false]),
                            ],
                            [
                                'settingName' => 'hostedPaymentBillingAddressOptions',
                                'settingValue' => \json_encode(['show' => false, 'required' => false]),
                            ],
                        ],
                    ],
                ],
            ]);

            $body = \explode('{"token"', $response->body());

            // Create a webhook for this payment
            $response = Http::withBasicAuth($merchantName, $transactionKey)
                ->post("$baseUrl/rest/v1/webhooks", [
                    'name' => "Payment $payment->id",
                    'url' => \config('app.url') . "/api/authorize/webhook/$payment->id",
                    'eventTypes' => [
                        "net.authorize.payment.authcapture.created",
                        "net.authorize.payment.authorization.created",
                        "net.authorize.payment.capture.created",
                        "net.authorize.payment.fraud.approved",
                        "net.authorize.payment.fraud.declined",
                        "net.authorize.payment.fraud.held",
                        "net.authorize.payment.priorAuthCapture.created",
                        "net.authorize.payment.refund.created",
                        "net.authorize.payment.void.created",
                    ],
                    'status' => App::isProduction() ? 'active' : 'inactive',
                ]);
            
            if(!$response->successful()) {
                \logger()->error('Error creating webhook:' . $response->body());
            }
            $payment->update([
                'misc' => [
                    'token' => \json_decode('{"token"' . $body[1], true)['token'],
                    'webhook' => $response->json('webhookId'),
                ],
            ]);
            $link = \config('app.url') . "/authorize/form/$payment->id";
        } else {
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $prices = $stripe->prices->search([
                'query' => "active:'true' AND metadata['price']:'$amount' AND product:'$productId'",
            ]);
    
            foreach($prices->data as $price) { }
    
            if(!isset($price)) {
                $price = $stripe->prices->create([
                    'unit_amount' => $amount,
                    'currency' => 'usd',
                    'product' => $productId,
                ]);
            }
    
            $checkout = $stripe->checkout->sessions->create([
                'success_url' => config('app.url') . "/s/success/$payment->id",
                'currency' => 'usd',
                'customer_email' => $request->post('email'),
                'payment_method_types' => ['card'],
                'expires_at' => strtotime('+12 hours'),
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
            ]);

            $payment->update([
                'misc' => [
                    'cs_id' => $checkout->id,
                    'method' => $paymentMethod,
                ],
            ]);

            $link = $checkout->url;
        }

        $amount_s = number_format($payment->amount, 2);
        $payment->responses()->create([
            'status' => 'secondary',
            'payload' => [
                'title' => "$paymentMethod - $$amount_s - Payment link sent",
                'message' => 'Payment link sent to the customer successfully.',
            ],
            'agent_id' => $agent ? $agent->id : $lead->agent_id,
        ]);

        if($sendVia == 'Email') {
            $sg = new \SendGrid(config('sendgrid.apiKey'));

            $response = $sg->client->mail()->send()->post([
                'template_id' => config('sendgrid.templateId'),
                'from' => [
                    'email' => 'hello@visascanada.org',
                    'name' => 'Visas Canada'
                ],
                'personalizations' => [[
                    'to' => [[
                        'email' => $newEmail ?? $lead->customer->user->email,
                        'name' => $lead->customer->user->firstname .' '. $lead->customer->user->lastname
                    ]],
                    'dynamic_template_data' => [
                        'lead_firstname' => $lead->customer->user->firstname,
                        'lead_lastname' => $lead->customer->user->lastname,
                        'language' => $request->post('lan'),
                        'amount' => "$$amount_s",
                        'link' => $link,
                    ]
                ]]
            ]);

            // return response()->json(['message' => $response->body()], $response->statusCode());
        } else {
            $client = new \Twilio\Rest\Client(config('twilio.sid'), config('twilio.authToken'));

            $apiKey = config('bitly.apiKey');
            $res = Http::withToken($apiKey)->post('https://api-ssl.bitly.com/v4/shorten', [
                'long_url' => $link,
            ]);

            if($request->post('lan') == 'fr') {
                $lanMsg = 'Poursuivre le processus de visa';
            } else if($request->post('lan') == 'es') {
                $lanMsg = 'Continuar con el proceso de visa';
            } else {
                $lanMsg = 'Continue with your visa process';
            }

            $link = $res->json('id'); // link
            $message = $client->messages->create(
                $phoneNumber,
                [
                    'from' => config('twilio.number'),
                    'body' => "Visascanada\n$lanMsg: $link",
                ]
            );

            if(!$message->sid) {
                return response()->json(['message' => $message->message], 500);
            }
            // return response()->json(['message' => 'SMS Sent'], 200);
        }
    }

    public function latestPayments(Request $request, Lead $lead, $last)
    {
        $payments = $lead->responses()
            ->with('agent.user')
            ->where('payment_responses.id', '>', $last)
            ->orderBy('payment_responses.created_at', 'asc')
            ->get();

        return $payments->map(function($payment) {
            $agent = $payment->agent
                ? $payment->agent->user->firstname . ' ' . $payment->agent->user->lastname
                : 'Unknown';
            return [
                'id' => $payment->id, // TODO:: change this to updated at if payments take too long
                'date' => date('M d, Y H:i', strtotime($payment->created_at)),
                'csv' => $payment->payload['cvv'] ?? null,
                'agent' => $agent,
                'message' => $payment->payload['message'],
                'title' => $payment->payload['title'],
                'status' => $payment->status,
            ];
        });
    }

    public function allPayments(Request $request, $latest = 0)
    {
        $user = $request->user();
        if($user) {
            if($agent = $user->agent) {
                $payments = $agent->payments()
                    ->has('lead')
                    ->where('status', PaymentStatus::SUCCESSFUL)
                    ->where('updated_at', '>', date('Y-m-d H:i:s', $latest)) // TODO:: use completed_at if you get old multiple or payments
                    ->orderBy('updated_at', 'desc')
                    ->get();
            }
    
            $array = [];
            foreach($payments as $payment) {
                $array[] = [
                    'id' => $payment->lead->id,
                    'p_id' => $payment->id,
                    'lead_number' => $payment->lead_id,
                    'firstname' => $payment->lead->customer->user->firstname,
                    'lastname' => $payment->lead->customer->user->lastname,
                    'date' => date('M d, Y H:i', strtotime($payment->created_at)),
                    'updated_at' => strtotime($payment->updated_at),
                    'read' => true,
                    'name' => $payment->lead->customer->user->firstname.' '.$payment->lead->customer->user->lastname,
                    'message' => '$' . number_format($payment->amount, 2) . ' payment successful'
                ];
            }

            $minutes = 5;
            $callback = $agent->leads()
                ->where('callback', '>', date('Y-m-d H:i:s'))
                ->where('callback', '<', date('Y-m-d H:i:s', time() + 5 * 60))
                ->first();

            if(!$callback) {
                $minutes = 10;
                $callback = $agent->leads()
                    ->where('callback', '>', date('Y-m-d H:i:s'))
                    ->where('callback', '<', date('Y-m-d H:i:s', time() + 10 * 60))
                    ->first();
            }

            if($callback) {
                $callback = [
                    'id' => $callback->id,
                    'name' => $callback->customer->user->firstname . ' ' . $callback->customer->user->lasttname,
                    'minutes' => $minutes,
                ];
            }
            return [
                'payments' => $array,
                'callback' => $callback,
                'unread' => 0,
            ];
        }

    }

    public function addCallback(Request $request, Lead $lead)
    {
        $user = $request->user();
        if($user->agent->type == AgentType::BACK_OFFICE) {
            $lead->update([
                'bo_callback' => date('Y-m-d H:i', strtotime($request->post('date'))),
                'callback_bo_agent_id' => $user->agent->id,
            ]);
        } else {
            $lead->update([
                'callback' => date('Y-m-d H:i', strtotime($request->post('date'))),
                'callback_agent_id' => $user->agent->id,
            ]);
        }
    }

    public function events(Request $request, User $user = null)
    {
        $user = $user ?? $request->user();
        $leads = collect([]);
        if($user->role == UserRole::ADMIN || $user->agent->type == AgentType::LEAD_ASSIGNER) {
            $leads = Lead::whereNotNull('callback')
                ->orwhereNotNull('bo_callback')
                ->with('customer.user')
                ->get();
        } else {
            $leads = Lead::where('callback_agent_id', $user->agent->id)
                ->orWhere('callback_bo_agent_id', $user->agent->id)
                ->orWhere(fn($query) => $query->whereNotNull('callback_agent_id')->where('agent_id', $user->agent->id))
                ->orWhere(fn($query) => $query->whereNotNull('callback_bo_agent_id')->where('bo_agent_id', $user->agent->id))
                ->with('customer.user')
                ->get();
        }
        
        $boCallbacks = $leads->filter(fn (Lead $lead) => $lead->bo_callback)->map(function(Lead $lead) {
            $firstname = $lead->customer->user->firstname;
            $lastname = $lead->customer->user->lastname;

            $givenStart = new \DateTime($lead->bo_callback);
            $givenStart->setTimezone(new \DateTimeZone("UTC"));

            $title = strtoupper(substr($firstname, 0, 1)).' '. ucfirst($lastname);
            return [
                'id' => $lead->id,
                'title' =>  mb_convert_encoding($title, 'UTF-8', 'UTF-8'),
                'start' => $givenStart->format('D, d M Y H:i:s T'),
                'end' => $givenStart->modify('+30 minutes')->format('D, d M Y H:i:s T'),
                'allDay' => false,
                'bo' => true,
                'extendedProps' => [
                    'calendar' => $lead->status->name
                ],
            ];
        })->toArray();

        $callbacks = $leads->filter(fn (Lead $lead) => $lead->callback)->map(function(Lead $lead) {
            $firstname = $lead->customer->user->firstname;
            $lastname = $lead->customer->user->lastname;

            $givenStart = new \DateTime($lead->callback);
            $givenStart->setTimezone(new \DateTimeZone("UTC"));

            $title = strtoupper(substr($firstname, 0, 1)).' '. ucfirst($lastname);
            return [
                'id' => $lead->id,
                'title' =>  mb_convert_encoding($title, 'UTF-8', 'UTF-8'),
                'start' => $givenStart->format('D, d M Y H:i:s T'),
                'end' => $givenStart->modify('+30 minutes')->format('D, d M Y H:i:s T'),
                'allDay' => false,
                'bo' => false,
                'extendedProps' => [
                    'calendar' => $lead->status->name
                ],
            ];
        })->toArray();

        return array_merge($callbacks, $boCallbacks);
    }

    public function saveFile(Request $request, Lead $lead)
    {
        if($files = $request->file('files')) {
            $toCreate = [];
            foreach($files as $file) {
                $originalName = $file->getClientOriginalName();
                $name = uniqid();
                $extension = $file->getClientOriginalExtension();
                $fileName =  "$name.$extension";
                $file->move(public_path('pdf/uploads'), $fileName);
    
                $toCreate[] = [
                    'original_name' => $originalName,
                    'name' => $fileName,
                ];
            }

            $results = [];
            $agent = null;
            foreach($toCreate as $rec) {
                $doc = $lead->documents()->create([
                    'original_name' => $rec['original_name'],
                    'agent_id' => $lead->agent_id,
                    'file_path' => config('app.url') . "/pdf/uploads/$rec[name]",
                ]);

                if($agent == null) {
                    $agent = $doc->agent->user->firstname . ' ' .  $doc->agent->user->lastname;
                }

                $results[] = [
                    'id' => $doc->id,
                    'name' => $rec['original_name'],
                    'agent' => $agent,
                    'url' => config('app.url') . "/pdf/uploads/$rec[name]",
                    'date' => $doc->created_at->format('M d, Y - H:i'),
                ];
            }
            return $results;
        } else {
            return response('No file found', 400);
        }
    }

    public function deleteFile(Request $request, $document)
    {
        Document::find($document)->delete();
    }

    public function updateField(Request $request, Lead $lead, $field)
    {
        if($field == 'office')
            $field = 'office_number';

        $lead->customer->update(["$field" => $request->value]);
        if($field != 'language')
            $lead->update(["$field" => $request->value]);
        $lead->customer->user->update(["$field" => $request->value]);
    }

    public function destroy(Request $request)
    {
        Lead::whereIn('id', $request->post('ids'))->delete();
    }

    public function putBack(Request $request)
    {
        Lead::whereIn('id', $request->post('ids'))->update([
            'reason' => null,
        ]);

        $baseUrl = config('mailchimp.baseUrl');
        $apiKey = config('mailchimp.apiKey');
        $listId = config('mailchimp.listId');
        $leads = Lead::whereIn('id', $request->post('ids'))->whereNotNull('mailchimp_id')->get();
        foreach($leads as $lead) {
            Http::withBasicAuth('key', $apiKey)
                ->delete("$baseUrl/lists/$listId/members/$lead->mailchimp_id");
        }
    }

    public function setReason(Request $request, Lead $lead, $status)
    {
        $reason = [
            'status' => $status,
            'reason' => $request->post('reason'),
            'date' => date('Y-m-d H:i:s'),
        ];

        $lead->reason = $reason;
        $lead->save();
        $this->addToMailchimp($lead);
    }

    public function addToMailchimp(Lead $lead, $status = null, $reason = null) {
        $baseUrl = config('mailchimp.baseUrl');
        $apiKey = config('mailchimp.apiKey');
        $listId = config('mailchimp.listId');

        $user = $lead->customer->user;
        $data = [
            'email_address' => $user->email,
            'status' => 'subscribed',
            'merge_fields' => [
                "FNAME" => $user->firstname,
                "LNAME" => $user->lastname,
                'PHONE' => $lead->customer->phone_number ?? '',
                'MMERGE6' => $reason ?? $lead->reason['reason'], // Reason
                'MMERGE7' => $lead->customer->dob ?? '', // String birthday
                'MMERGE8' => $lead->customer->residence ?? '', // Country
                'MMERGE9' => $lead->id, // Lead number
            ],
            'language' => $lead->language ?? '',
            'tags' => [$status ?? $lead->reason['status']]
        ];

        $res = Http::withBasicAuth('key', $apiKey);
        $res = !$lead->mailchimp_id
            ? $res->post("$baseUrl/lists/$listId/members", $data)
            : $res->patch("$baseUrl/lists/$listId/members/$lead->mailchimp_id", $data);
        
        if($res->successful()) {
            $lead->mailchimp_id = $res->json('id');
            $lead->save();
        } else {
            $lead->mailchimp_id = 'Error - ' . $res->json('title');
            $lead->save();
        }
    }

    public function moveLeads(Request $request)
    {
        Lead::whereIn('id', $request->post('ids'))->update([
            'language' => $request->post('lan') == 'spanish' ? 'es' : 'en'
        ]);
    }

    function spouse(Request $request, Lead $lead) {
        $lead->customer()->update([
            'spouse' => $request->post('spouse')
        ]);
    }

    private function filterType($leads, $type, $recall = false) {
        switch($type) {
            case 'fresh-leads':
                $leads->whereNull('reassigned_at');
                if($recall) {
                    $leads->where('recall', true);
                }
                break;

            case 'upsales':
                $leads->whereHas('history', function($query) {
                    $query->whereIn('visa_id', [1, 2, 3, 4, 5, 6, 7, 12, 13, 14]); // UPGRADE
                });
                break;
            
            case 'reassigned':
                $leads->whereNotNull('reassigned_at')->whereDoesntHave('history', function($query) {
                    $query->whereIn('visa_id', [1, 2, 3, 4, 5, 6, 7, 12, 13, 14]); // UPGRADE
                })->whereDoesntHave('payments', function($query) {
                    $query->where('complete', false)->where('status', PaymentStatus::SUCCESSFUL);
                });
                break;
            
            case 'collection':
                $leads->whereHas('payments', function($q) {
                    $q->where('complete', false)->where('status', PaymentStatus::SUCCESSFUL);
                });
                break;
        }
        return $leads;
    }
}
