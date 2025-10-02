<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        $Group = Group::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get();

        return view('group.index')->withResults([$Group, date('d-m-Y')]);
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $myDateTime = new \DateTime($validated['groupdate']);
        $date = $myDateTime->format('d-m-Y');
        $name = $validated['name'].' '.$date;
        $group = new Group;
        $group->name = $name;
        $group->invoice_group_id = InvoiceGroup::getCurrentMonth()->id;
        $group->save();
        if ($group->exists) {
            return response()->json(['success' => true, 'id' => $group->id, 'name' => $group->name]);
        } else {
            return response()->json(['errors' => 'Could not be added to the database']);
        }
    }

    public function show($id): View
    {
        return view('group.order')->with('group', Group::find($id))->with('products', Product::all())->with('members', Member::all())->with('currentmonth', InvoiceGroup::getCurrentMonth());
    }

    public function edit($id): View
    {
        return view('group.edit')->with('group', Group::find($id));
    }

    public function update(StoreGroupRequest $request, Group $group): JsonResponse
    {
        $validated = $request->validated();
        $group->name = $validated['name'];

        $group->save();
        if ($group->exists) {
            return response()->json(['success' => true, 'message' => $group->name.' Successfully edited']);
        } else {
            return response()->json(['errors' => 'Could not be updated']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $member = Group::find($id);
        $member->delete();
        if ($member->exists) {
            return response()->json(['errors' => $member->name." Couldn't be deleted"]);
        } else {
            return response()->json(['success' => true, 'message' => $member->name.' Successfully deleted']);
        }
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

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $groupmember = new GroupMember;
            $groupmember->group_id = $request->get('groupid');
            $groupmember->member_id = $request->get('member');
            $groupmember->save();
            $member = Member::find($request->get('member'));

            return response()->json(['success' => true, 'membername' => $member->firstname.' '.$member->lastname, 'memberid' => $member->member_id, 'id' => $groupmember->id]);
        }
    }

    public function getDeletegroupmember(GroupMember $groupMember): JsonResponse
    {
        $groupMember->delete();

        if ($groupMember->exists) {
            return response()->json(['errors' => "Couldn't be deleted"]);
        } else {
            return response()->json(['success' => true, 'id' => $groupMember->id]);
        }
    }
}
