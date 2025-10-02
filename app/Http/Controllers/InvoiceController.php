<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Settings;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\Member;
use App\Models\Product;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    private $exceldata;

    private $currentpaymentinfo;

    private $total;

    public function getIndex(): View
    {
        $currentmonth = InvoiceGroup::getCurrentMonth();
        $products = Product::toArrayIdAsKey();
        $members = Member::with(['orders'])
            ->with(['groups.orders.product'])
            ->with(['invoice_lines.productprice.product'])->get();

        return view('invoice.index')->with('invoicegroups', InvoiceGroup::orderBy('id', 'desc')->get())
            ->with('currentmonth', $currentmonth)
            ->with('members', $members)
            ->with('products', $products);
    }

    public function getPerPerson(): View
    {
        $m = null;
        if (! is_null(session('member'))) {
            $m = Member::with('orders.product', 'groups.orders.product')
                ->whereHas('invoice_lines.productprice.product', function ($q) {
                    $q->where('member_id', session('member')->id);
                })->first();
        }

        $products = Product::toArrayIdAsKey();
        if (is_null(session('personal_invoice_group'))) {
            $currentmonth = InvoiceGroup::getCurrentMonth();
        } else {
            $currentmonth = session('personal_invoice_group');
        }

        return view('invoice.person')
            ->with('invoicegroups', InvoiceGroup::orderBy('id', 'desc')->get())
            ->with('currentmonth', $currentmonth)
            ->with('m', $m)
            ->with('products', $products);
    }

    public function getPdf(): View
    {
        $currentmonth = InvoiceGroup::getCurrentMonth();

        return view('invoice.pdf')
            ->with('currentmonth', $currentmonth)
            ->with('members', Member::with('orders.product', 'groups.orders.product', 'invoice_lines.productprice.product')->get());
    }

    public function getExcel(): View
    {
        $currentmonth = InvoiceGroup::getCurrentMonth();
        $result = [];
        $this->total = 0;
        foreach (Member::with('orders.product', 'groups.orders.product', 'invoice_lines.productprice.product')->get() as $m) {
            $memberinfo = [];
            $memberinfo[] = $m->firstname.' '.$m->lastname;
            $manor = 0;
            $member_total = 0;

            $manor += $this->CalculateMemberOrders($m);
            $manor += $this->CalculateGroupOrders($m);

            $memberinfo[] = $manor;
            $member_total += $manor;
            $products = [];
            foreach (InvoiceProduct::where('invoice_group_id', '=', $currentmonth->id)->get() as $product) {
                $products[$product->id] = 0;
            }
            foreach ($m->invoice_lines as $il) {
                if ($il->productprice->product->invoice_group_id == $currentmonth->id) {
                    $products[$il->productprice->product->id] = $il->productprice->price;
                }
            }
            foreach ($products as $p) {
                $member_total += $p;
                $memberinfo[] = $p;
            }
            $memberinfo[] = $member_total;
            $this->total += $member_total;
            //            if($total == 0)
            //			{
            //				continue;
            //			}
            //
            $result[] = $memberinfo;
        }
        $this->exceldata = $result;

        Excel::create($currentmonth->name, function ($excel) {
            $excel->sheet('First sheet', function ($sheet) {
                $sheet->loadView('invoice.excel')->with('result', $this->exceldata)
                    ->with('products', InvoiceProduct::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get())
                    ->with('total', $this->total);
            });
        })->download('xls');

        return view('invoice.excel')
            ->with('result', $result)
            ->with('products', InvoiceProduct::where('invoice_group_id', '=', $currentmonth->id)->get());
    }

    private function newMemberInfo($m): ?array
    {
        $memberinfo = [];
        $memberinfo['name'] = $m->firstname.' '.$m->lastname;
        $memberinfo['iban'] = $m->iban;
        $memberinfo['bic'] = $m->bic;
        $memberinfo['mandate'] = str_pad($m->id, 10, '0', STR_PAD_LEFT);
        $memberinfo['m'] = $m;

        $manor = 0;
        $manor += $this->CalculateMemberOrders($m);
        $manor += $this->CalculateGroupOrders($m);

        foreach ($m->invoice_lines as $il) {
            if ($il->productprice->product->invoice_group_id == InvoiceGroup::getCurrentMonth()->id) {
                $manor += $il->productprice->price;
            }
        }
        $memberinfo['amount'] = $manor;
        if ($manor > 0) {
            return $memberinfo;
        } else {
            return null;
        }
    }

    private function newBatch($seqType): mixed
    {
        $this->currentpaymentinfo = 'GSRC'.date('Y-m-d H:i:s');
        $currentbatch = TransferFileFacadeFactory::createDirectDebit('GSRC'.date('Y-m-d H:i:s'), 'me', Settings::get('creditorPain'));
        $currentbatch->addPaymentInfo($this->currentpaymentinfo, [
            'id' => 'GSRC'.date('Y-m-d H:i:s'),
            'creditorName' => Settings::get('creditorName'),
            'creditorAccountIBAN' => Settings::get('creditorAccountIBAN'),
            'creditorAgentBIC' => Settings::get('creditorAgentBIC'),
            'seqType' => $seqType,
            'creditorId' => Settings::get('creditorId'),
            'dueDate' => new \DateTime(date('Y-m-d', strtotime('now +'.Settings::get('ReqdColltnDt').' weekdays'))),
        ]);

        return $currentbatch;
    }

    public function getSepa(): View
    {
        $memberswithoutbankinfo = Member::whereNull('bic')->whereNull('iban')->get();

        $members = ['RCUR' => [], 'FRST' => []];
        $member_rcur = Member::whereNotNull('bic')->whereNotNull('iban')->rcur()
            ->with(['orders'])
            ->with(['groups.orders.product'])
            ->with(['invoice_lines.productprice.product'])->get();
        foreach ($member_rcur as $m) {
            $info = $this->newMemberInfo($m);
            if ($info != null) {
                $members['RCUR'][] = $info;
            }
        }
        $member_frst = Member::whereNotNull('bic')->whereNotNull('iban')->frst()
            ->with(['orders'])
            ->with(['groups.orders.product'])
            ->with(['invoice_lines.productprice.product'])->get();
        foreach ($member_frst as $m) {
            $info = $this->newMemberInfo($m);
            if ($info != null) {
                $members['FRST'][] = $info;
            }
        }
        $batches = ['RCUR' => [], 'FRST' => []];

        $batchfailedmembers = [];

        $transactions = 0;
        $batchtotalmoney = 0;
        if (! empty($members['RCUR'])) {
            $currentbatch = $this->newBatch('RCUR');

            foreach ($members['RCUR'] as $m) {
                if ($batchtotalmoney + $m['amount'] > Settings::get('creditorMaxMoneyPerBatch') || $transactions == Settings::get('creditorMaxTransactionsPerBatch')) {
                    $batches['RCUR'][] = $currentbatch;
                    $this->currentpaymentinfo = 'GSRC'.date('Y-m-d H:i:s');
                    $currentbatch = $this->newBatch('RCUR');
                    $transactions = 0;
                    $batchtotalmoney = 0;
                }
                if ($m['amount'] > Settings::get('creditorMaxMoneyPerTransaction')) {
                    $batchfailedmembers[] = $m;
                } else {
                    $currentbatch->addTransfer($this->currentpaymentinfo, [
                        'amount' => $m['amount'],
                        'debtorIban' => $m['iban'],
                        'debtorBic' => $m['bic'],
                        'debtorName' => $m['name'],
                        'debtorMandate' => $m['mandate'],
                        'debtorMandateSignDate' => '13.10.2012',
                        'remittanceInformation' => 'GSRC Incasso '.date('Y-m'),
                    ]);
                    $batchtotalmoney += $m['amount'];
                    $transactions++;
                }
            }
            $batches['RCUR'][] = $currentbatch;
        }
        $transactions = 0;
        $batchtotalmoney = 0;
        if (! empty($members['FRST'])) {
            $currentbatch = $this->newBatch('FRST');
            foreach ($members['FRST'] as $m) {
                if ($batchtotalmoney + $m['amount'] > Settings::get('creditorMaxMoneyPerBatch') || $transactions == Settings::get('creditorMaxTransactionsPerBatch')) {
                    $batches['FRST'][] = $currentbatch;
                    $this->currentpaymentinfo = 'GSRC'.date('Y-m-d H:i:s');
                    $currentbatch = $this->newBatch('FRST');
                    $transactions = 0;
                    $batchtotalmoney = 0;
                }
                if ($m['amount'] > Settings::get('creditorMaxMoneyPerTransaction')) {
                    $batchfailedmembers[] = $m;
                } else {
                    $currentbatch->addTransfer($this->currentpaymentinfo, [
                        'amount' => $m['amount'],
                        'debtorIban' => $m['iban'],
                        'debtorBic' => $m['bic'],
                        'debtorName' => $m['name'],
                        'debtorMandate' => $m['mandate'],
                        'debtorMandateSignDate' => '13.10.2012',
                        'remittanceInformation' => 'GSRC Incasso '.date('Y-m'),
                    ]);
                    $batchtotalmoney += $m['amount'];
                    $transactions++;
                }
            }
            $batches['FRST'][] = $currentbatch;
        }
        $total = 0;
        $i = 0;
        $batchlink = [];
        foreach ($batches['RCUR'] as $b) {
            $i++;
            $filename = 'GSRC RCUR '.$i.' '.date('Y-m-d').'.xml';
            $filepath = storage_path('SEPA/'.$filename);
            $file = fopen($filepath, 'w');
            fwrite($file, $b->asXML());
            fclose($file);
            $total++;
            $batchlink[] = $filename;
        }
        $i = 0;
        foreach ($batches['FRST'] as $b) {
            $i++;
            $filename = 'GSRC FRST '.$i.' '.date('Y-m-d').'.xml';
            $filepath = storage_path('SEPA/'.$filename);
            $file = fopen($filepath, 'w');
            fwrite($file, $b->asXML());
            fclose($file);
            $total++;
            $batchlink[] = $filename;
        }

        return view('invoice.sepa')
            ->with('total', $total)
            ->with('batchlink', $batchlink)
            ->with('memberswithtohightransaction', $batchfailedmembers)
            ->with('memberswithoutbankinfo', $memberswithoutbankinfo);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function postStoreinvoicegroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceMonth' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $invoicegroups = InvoiceGroup::where('status', '=', true);
            $invoicegroups->update(['status' => false]);

            $invoicegroup = new InvoiceGroup;
            $invoicegroup->name = $request->get('invoiceMonth');
            $invoicegroup->status = true;
            $invoicegroup->save();

            if ($invoicegroup->exists) {
                Cache::forget('invoice_group');

                return response()->json(['success' => true, 'id' => $invoicegroup->id, 'name' => $invoicegroup->name]);
            } else {
                return response()->json(['errors' => 'Could not be added to the database']);
            }
        }
    }

    public function postSetPersonalInvoiceGroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceGroup' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $invoicegroup = InvoiceGroup::find($request->get('invoiceGroup'));

            if (! is_null($invoicegroup)) {
                session(['personal_invoice_group' => $invoicegroup]);

                return response()->json(['success' => true]);
            } else {
                return response()->json(['errors' => 'Could not find month']);
            }
        }
    }

    public function postSetPerson(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['name' => 'required', 'iban' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $member = Member::where(['lastname' => $request->get('name'), 'iban' => $request->get('iban')])->first();
            if (! is_null($member)) {
                session(['member' => $member]);

                return response()->json(['success' => true]);
            } else {
                return response()->json(['errors' => 'Could not find member']);
            }
        }
    }

    public function postSelectinvoicegroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceGroup' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $invoicegroups = InvoiceGroup::where('status', '=', true);
            $invoicegroups->update(['status' => false]);

            $invoicegroup = InvoiceGroup::find($request->get('invoiceGroup'));
            $invoicegroup->status = true;
            $invoicegroup->save();

            $invoicegroups = InvoiceGroup::where('status', '=', true);

            if ($invoicegroups->count() > 0) {
                Cache::forget('invoice_group');

                return response()->json(['success' => true]);
            } else {
                return response()->json(['errors' => 'Could not be added to the database']);
            }
        }
    }

    private function CalculateMemberOrders($member): float|int
    {
        $price = 0;

        $products = Product::toArrayIdAsKey();
        foreach ($member->orders()->where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get() as $o) {
            $price += $o->amount * $products[$o->product_id]['price'];
        }

        return $price;
    }

    private function CalculateGroupOrders($member): float|int
    {
        $price = 0;
        $products = Product::toArrayIdAsKey();
        foreach ($member->groups()->where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get() as $g) {
            $totalprice = 0;
            foreach ($g->orders as $o) {
                $totalprice += $o->amount * $products[$o->product_id]['price'];
            }
            $totalmebers = $g->members->count();

            $price += ($totalprice / $totalmebers);
        }

        return $price;
    }
}
