<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceExportService;
use App\Services\SepaGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly SepaGenerationService $sepaService,
        private readonly InvoiceExportService $exportService
    ) {}

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
        return $this->exportService->exportToExcel();
    }


    public function getSepa(): View
    {
        $membersWithoutBankInfo = $this->sepaService->getMembersWithoutBankInfo();
        $members = $this->sepaService->collectMembersForSepa();
        $batchData = $this->sepaService->createBatches($members);
        $batchLinks = $this->sepaService->saveBatchesToDisk($batchData);

        return view('invoice.sepa')
            ->with('total', count($batchLinks))
            ->with('batchlink', $batchLinks)
            ->with('memberswithtohightransaction', $batchData['failedMembers'])
            ->with('memberswithoutbankinfo', $membersWithoutBankInfo);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function postStoreinvoicegroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceMonth' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        }

        InvoiceGroup::where('status', '=', true)->update(['status' => false]);

        $invoicegroup = new InvoiceGroup;
        $invoicegroup->name = $request->get('invoiceMonth');
        $invoicegroup->status = true;
        $invoicegroup->save();

        if (! $invoicegroup->exists) {
            return response()->json(['errors' => 'Could not be added to the database']);
        }

        Cache::forget('invoice_group');

        return response()->json(['success' => true, 'id' => $invoicegroup->id, 'name' => $invoicegroup->name]);
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
        }

        InvoiceGroup::where('status', '=', true)->update(['status' => false]);

        $invoicegroup = InvoiceGroup::find($request->get('invoiceGroup'));
        $invoicegroup->status = true;
        $invoicegroup->save();

        if (InvoiceGroup::where('status', '=', true)->count() === 0) {
            return response()->json(['errors' => 'Could not be added to the database']);
        }

        Cache::forget('invoice_group');

        return response()->json(['success' => true]);
    }

}
