<?php namespace App\Http\Controllers;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $members = Member::all();
        return view('member.index')->with('members', $members);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $v = Validator::make(Input::all(), ['name' => 'required', 'lastname' => 'required']);

        if (!$v->passes()) {
            //return Redirect::back()->with('errors', $v->errors());
            return Response::json(['errors' => $v->errors()]);
        } else {
            $member = new Member;
            $member->firstname = Input::get('name');
            $member->lastname = Input::get('lastname');

            $member->save();
            if ($member->exists) {
                return Response::json(['success' => true, 'id' => $member->id, 'firstname' => $member->firstname, 'lastname' => $member->lastname]);
            } else {
                return Response::json(['errors' => $v->errors()]);
            }
        }
    }

    public function show($id)
    {
        return view('member.order')->with('member', Member::find($id))->with('products', Product::all())->with('currentmonth', InvoiceGroup::getCurrentMonth());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return view('member.edit')->with('member', Member::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $v = Validator::make(Input::all(),
                            [
                                'name' => 'required',
                                'lastname' => 'required',
                                'bic' => 'required',
                                'iban' => 'required'
                            ]
        );

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $member = Member::find($id);
            $member->firstname = Input::get('name');
            $member->lastname = Input::get('lastname');
            $member->bic = Input::get('bic');
            $member->iban = Input::get('iban');
            if (Input::has('had_collection')) {
                $member->had_collection = true;
            } else {
                $member->had_collection = false;
            }

            $member->save();
            if ($member->exists) {
                return Response::json(['success' => true, 'message' => 'Succesfully edited' . $member->getFullName()]);
            } else {
                return Response::json(['errors' => $v->errors()]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $member = Member::find($id);
        $member->delete();
        if ($member->exists) {
            return Response::json(['errors' => $member->firsname . " Couldn't be deleted"]);
        } else {
            return Response::json(['success' => true, 'message' => 'Succesfully deleted' . $member->getFullName()]);
        }
    }
}
