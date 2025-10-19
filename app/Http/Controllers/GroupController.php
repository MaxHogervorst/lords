<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use App\Models\GroupMember;
use App\Repositories\GroupRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly MemberRepository $memberRepository,
        private readonly ProductRepository $productRepository,
        private readonly InvoiceRepository $invoiceRepository
    ) {
    }

    public function index(): View
    {
        $currentMonth = $this->invoiceRepository->getCurrentMonth();
        $groups = $this->groupRepository->getByInvoiceGroup($currentMonth);

        return view('group.index')->withResults([$groups, date('d-m-Y')]);
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $myDateTime = new \DateTime($validated['groupdate']);
        $date = $myDateTime->format('d-m-Y');
        $name = $validated['name'] . ' ' . $date;

        $currentMonth = $this->invoiceRepository->getCurrentMonth();
        $group = $this->groupRepository->create([
            'name' => $name,
            'invoice_group_id' => $currentMonth->id,
        ]);

        return response()->json([
            'success' => true,
            'id' => $group->id,
            'name' => $group->name
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $group = $this->groupRepository->find((int) $id, ['*'], ['members']);
        $products = $this->productRepository->all();
        $members = $this->memberRepository->all();
        $currentmonth = $this->invoiceRepository->getCurrentMonth();

        // Get orders for this group in current month
        $orders = $group->orders()
            ->where('invoice_group_id', '=', $currentmonth->id)
            ->with('product')
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'product_name' => $order->product->name,
                'amount' => $order->amount
            ]);

        // Get order totals grouped by product
        $orderTotals = $group->orders()
            ->where('invoice_group_id', '=', $currentmonth->id)
            ->selectRaw('orders.product_id, count(orders.id) as count')
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->map(fn($total) => [
                'product_id' => $total->product_id,
                'product_name' => $total->product->name,
                'count' => $total->count
            ]);

        // Get current group members with pivot id
        $groupMembers = $group->members->map(fn($member) => [
            'id' => $member->id,
            'firstname' => $member->firstname,
            'lastname' => $member->lastname,
            'pivot_id' => $member->pivot->id
        ]);

        return response()->json([
            'group' => [
                'id' => $group->id,
                'name' => $group->name
            ],
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name
            ]),
            'members' => $members->map(fn($m) => [
                'id' => $m->id,
                'firstname' => $m->firstname,
                'lastname' => $m->lastname
            ]),
            'groupMembers' => $groupMembers,
            'orders' => $orders,
            'orderTotals' => $orderTotals,
            'currentMonth' => $currentmonth->id
        ]);
    }

    public function edit(string $id): JsonResponse
    {
        $group = $this->groupRepository->find((int) $id);

        return response()->json([
            'id' => $group->id,
            'name' => $group->name
        ]);
    }

    public function update(StoreGroupRequest $request, Group $group): JsonResponse
    {
        $validated = $request->validated();
        $group = $this->groupRepository->update($group, [
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $group->name . ' Successfully edited'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $group = $this->groupRepository->find((int) $id);
        $name = $group->name;
        $this->groupRepository->delete($group);

        return response()->json([
            'success' => true,
            'message' => $name . ' Successfully deleted'
        ]);
    }

    public function postAddmember(Request $request): JsonResponse
    {
        $v = Validator::make(
            $request->all(),
            [
                'groupid' => 'required|numeric',
                'member' => 'required|numeric',
            ]
        );

        if (!$v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        }

        $groupmember = new GroupMember();
        $groupmember->group_id = $request->get('groupid');
        $groupmember->member_id = $request->get('member');
        $groupmember->save();

        $member = $this->memberRepository->find((int) $request->get('member'));

        return response()->json([
            'success' => true,
            'membername' => $member->firstname . ' ' . $member->lastname,
            'memberid' => $member->id,
            'id' => $groupmember->id
        ]);
    }

    public function deleteGroupMember(GroupMember $groupMember): JsonResponse
    {
        $groupMember->delete();

        if ($groupMember->exists) {
            return response()->json(['success' => false, 'message' => "Couldn't delete member from group"], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Member removed from group successfully',
            'id' => $groupMember->id
        ]);
    }
}
