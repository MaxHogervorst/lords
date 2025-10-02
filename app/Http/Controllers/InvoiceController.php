<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SepaSequenceType;
use App\Exports\InvoicesExport;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\Member;
use App\Models\Product;
use DateTime;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Settings;

class InvoiceController extends Controller
{
    private array $exceldata = [];

    private string $currentpaymentinfo = '';

    private float $total = 0.0;

    /**
     * Get member from session by ID.
     */
    private function getSessionMember(): ?Member
    {
        $memberId = session('member_id');

        return $memberId ? Member::find($memberId) : null;
    }

    /**
     * Get invoice group from session by ID.
     */
    private function getSessionInvoiceGroup(): ?InvoiceGroup
    {
        $invoiceGroupId = session('personal_invoice_group_id');

        return $invoiceGroupId ? InvoiceGroup::find($invoiceGroupId) : null;
    }

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
        $sessionMember = $this->getSessionMember();

        if (! is_null($sessionMember)) {
            $m = Member::with('orders.product', 'groups.orders.product')
                ->whereHas('invoice_lines.productprice.product', function ($q) use ($sessionMember) {
                    $q->where('member_id', $sessionMember->id);
                })->first();
        }

        $products = Product::toArrayIdAsKey();
        $currentmonth = $this->getSessionInvoiceGroup() ?? InvoiceGroup::getCurrentMonth();

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

    public function getExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
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

        $products = InvoiceProduct::where('invoice_group_id', '=', $currentmonth->id)->get();

        return Excel::download(
            new InvoicesExport($result, $products, $this->total, $currentmonth),
            $currentmonth->name.'.xlsx'
        );
    }

    private function newMemberInfo(Member $m): ?array
    {
        $mandatePadding = Settings::get('mandatePadding', 8);

        $memberinfo = [];
        $memberinfo['name'] = $m->firstname.' '.$m->lastname;
        $memberinfo['iban'] = $m->iban;
        $memberinfo['bic'] = $m->bic;
        $memberinfo['mandate'] = str_pad((string) $m->id, $mandatePadding, '0', STR_PAD_LEFT);
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

    private function newBatch(SepaSequenceType $seqType): mixed
    {
        $prefix = Settings::get('filePrefix', 'GSRC');
        $timestamp = date('Y-m-d H:i:s');
        $this->currentpaymentinfo = $prefix.$timestamp;

        $currentbatch = TransferFileFacadeFactory::createDirectDebit(
            $prefix.$timestamp,
            'me',
            Settings::get('creditorPain', 'pain.008.001.02')
        );

        $daysOffset = Settings::get('ReqdColltnDt', 5);
        $dueDate = new DateTime(date('Y-m-d', strtotime("now +{$daysOffset} weekdays")));

        $currentbatch->addPaymentInfo($this->currentpaymentinfo, [
            'id' => $prefix.$timestamp,
            'creditorName' => Settings::get('creditorName'),
            'creditorAccountIBAN' => Settings::get('creditorAccountIBAN'),
            'creditorAgentBIC' => Settings::get('creditorAgentBIC'),
            'seqType' => $seqType->value,
            'creditorId' => Settings::get('creditorId'),
            'dueDate' => $dueDate,
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

        $maxMoneyPerBatch = Settings::get('creditorMaxMoneyPerBatch', 999999);
        $maxTransactionsPerBatch = Settings::get('creditorMaxTransactionsPerBatch', 1000);
        $maxMoneyPerTransaction = Settings::get('creditorMaxMoneyPerTransaction', 100000);
        $mandateSignDate = Settings::get('mandateSignDate', '2014-01-01');
        $remittancePrefix = Settings::get('remittancePrefix', 'Contributie');
        $filePrefix = Settings::get('filePrefix', 'GSRC');
        $storagePath = Settings::get('storagePath', 'SEPA');

        $transactions = 0;
        $batchtotalmoney = 0;
        if (! empty($members['RCUR'])) {
            $currentbatch = $this->newBatch(SepaSequenceType::RECURRING);

            foreach ($members['RCUR'] as $m) {
                if ($batchtotalmoney + $m['amount'] > $maxMoneyPerBatch || $transactions == $maxTransactionsPerBatch) {
                    $batches['RCUR'][] = $currentbatch;
                    $currentbatch = $this->newBatch(SepaSequenceType::RECURRING);
                    $transactions = 0;
                    $batchtotalmoney = 0;
                }
                if ($m['amount'] > $maxMoneyPerTransaction) {
                    $batchfailedmembers[] = $m;
                } else {
                    $currentbatch->addTransfer($this->currentpaymentinfo, [
                        'amount' => (int) round($m['amount'] * 100),
                        'debtorIban' => $m['iban'],
                        'debtorBic' => $m['bic'],
                        'debtorName' => $m['name'],
                        'debtorMandate' => $m['mandate'],
                        'debtorMandateSignDate' => $mandateSignDate,
                        'remittanceInformation' => $remittancePrefix.' '.date('Y-m'),
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
            $currentbatch = $this->newBatch(SepaSequenceType::FIRST);
            foreach ($members['FRST'] as $m) {
                if ($batchtotalmoney + $m['amount'] > $maxMoneyPerBatch || $transactions == $maxTransactionsPerBatch) {
                    $batches['FRST'][] = $currentbatch;
                    $currentbatch = $this->newBatch(SepaSequenceType::FIRST);
                    $transactions = 0;
                    $batchtotalmoney = 0;
                }
                if ($m['amount'] > $maxMoneyPerTransaction) {
                    $batchfailedmembers[] = $m;
                } else {
                    $currentbatch->addTransfer($this->currentpaymentinfo, [
                        'amount' => (int) round($m['amount'] * 100),
                        'debtorIban' => $m['iban'],
                        'debtorBic' => $m['bic'],
                        'debtorName' => $m['name'],
                        'debtorMandate' => $m['mandate'],
                        'debtorMandateSignDate' => $mandateSignDate,
                        'remittanceInformation' => $remittancePrefix.' '.date('Y-m'),
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
            $filename = "{$filePrefix} RCUR {$i} ".date('Y-m-d').'.xml';
            Storage::disk('sepa')->put($filename, $b->asXML());
            $total++;
            $batchlink[] = $filename;
        }
        $i = 0;
        foreach ($batches['FRST'] as $b) {
            $i++;
            $filename = "{$filePrefix} FRST {$i} ".date('Y-m-d').'.xml';
            Storage::disk('sepa')->put($filename, $b->asXML());
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
                session(['personal_invoice_group_id' => $invoicegroup->id]);

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
                session(['member_id' => $member->id]);

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

    private function CalculateMemberOrders(Member $member): float|int
    {
        $price = 0;

        $products = Product::toArrayIdAsKey();
        foreach ($member->orders()->where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get() as $o) {
            $price += $o->amount * $products[$o->product_id]['price'];
        }

        return $price;
    }

    private function CalculateGroupOrders(Member $member): float|int
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
