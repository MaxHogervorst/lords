<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $members = Member::all();

        return view('member.index')->with('members', $members);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $member = new Member;
        $member->firstname = $validated['name'];
        $member->lastname = $validated['lastname'];

        $member->save();
        if ($member->exists) {
            return response()->json(['success' => true, 'id' => $member->id, 'firstname' => $member->firstname, 'lastname' => $member->lastname]);
        } else {
            return response()->json(['errors' => 'Could not be added to the database']);
        }
    }

    public function show($id): View
    {
        return view('member.order')->with('member', Member::find($id))->with('products', Product::all())->with('currentmonth', InvoiceGroup::getCurrentMonth());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        return view('member.edit')->with('member', Member::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMemberRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();
        $member = Member::find($id);
        $member->firstname = $validated['name'];
        $member->lastname = $validated['lastname'];
        $member->bic = $validated['bic'] ?? null;
        $member->iban = $validated['iban'] ?? null;
        $member->had_collection = $request->has('had_collection');

        $member->save();
        if ($member->exists) {
            return response()->json(['success' => true, 'message' => 'Succesfully edited'.$member->getFullName()]);
        } else {
            return response()->json(['errors' => 'Could not be updated']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $member = Member::find($id);
        $member->delete();
        if ($member->exists) {
            return response()->json(['errors' => $member->firsname." Couldn't be deleted"]);
        } else {
            return response()->json(['success' => true, 'message' => 'Succesfully deleted'.$member->getFullName()]);
        }
    }
}
