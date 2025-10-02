<?php

namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['name' => 'required', 'lastname' => 'required']);

        if (! $v->passes()) {
            // return Redirect::back()->with('errors', $v->errors());
            return response()->json(['errors' => $v->errors()]);
        } else {
            $member = new Member;
            $member->firstname = $request->get('name');
            $member->lastname = $request->get('lastname');

            $member->save();
            if ($member->exists) {
                return response()->json(['success' => true, 'id' => $member->id, 'firstname' => $member->firstname, 'lastname' => $member->lastname]);
            } else {
                return response()->json(['errors' => $v->errors()]);
            }
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
    public function update(Request $request, $id): JsonResponse
    {
        $v = Validator::make($request->all(),
            [
                'name' => 'required',
                'lastname' => 'required',
                'bic' => 'required',
                'iban' => 'required',
            ]
        );

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $member = Member::find($id);
            $member->firstname = $request->get('name');
            $member->lastname = $request->get('lastname');
            $member->bic = $request->get('bic');
            $member->iban = $request->get('iban');
            if ($request->has('had_collection')) {
                $member->had_collection = true;
            } else {
                $member->had_collection = false;
            }

            $member->save();
            if ($member->exists) {
                return response()->json(['success' => true, 'message' => 'Succesfully edited'.$member->getFullName()]);
            } else {
                return response()->json(['errors' => $v->errors()]);
            }
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
