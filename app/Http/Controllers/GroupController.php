<?php namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Member;
use App\Models\Product;
use App\Models\InvoiceGroup;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class GroupController extends Controller {


    public function index()
    {
        $Group = Group::where('invoice_group_id', '=', InvoiceGroup::getCurrentMonth()->id)->get();
        return view('group.index')->withResults(array($Group, date('d-m-Y')));
    }


    public function store()
    {
        $v = Validator::make(Input::all(), array('name' => 'required'));

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $myDateTime = new \DateTime(Input::get('groupDate'));
            $date = $myDateTime->format('d-m-Y');
            $name = Input::get('name') . " " . $date;
            $group = new Group;
            $group->name = $name;
            $group->invoice_group_id = InvoiceGroup::getCurrentMonth()->id;
            $group->save();
            if ($group->exists) {
                return Response::json(array('success' => true, 'id' => $group->id, 'name' => $group->name));
            } else {
                return Response::json(['errors' => "Could not be added to the database"]);
            }
        }
    }
    public function show($id)
    {
        return view('group.order')->with('group', Group::find($id))->with('products', Product::all())->with('members', Member::all())->with('currentmonth', InvoiceGroup::getCurrentMonth());
    }

    public function edit($id)
    {
        return view('group.edit')->with('group', Group::find($id));
    }

    public function update($id)
    {
        $v = Validator::make(Input::all(), array('name' => 'required'));

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $member = Group::find($id);
            $member->name = Input::get('name');

            $member->save();
            if ($member->exists) {
                return Response::json(array('success' => true, 'message' => $member->name . ' Successfully edited'));
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
        $member = Group::find($id);
        $member->delete();
        if ($member->exists) {
            return Response::json(['errors' => $member->name ." Couldn't be deleted"]);
        } else {
            return Response::json(array('success' => true, 'message' => $member->name . ' Successfully deleted'));
        }

    }

    public function  postAddmember()
    {
        $v = Validator::make(
                Input::all(),
                array(
                    'groupid' => 'required|numeric',
                    'member' => 'required|numeric'
                )
        );

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $groupmember = new GroupMember();
            $groupmember->group_id = Input::get('groupid');
            $groupmember->member_id = Input::get('member');
            $groupmember->save();
            $member = Member::find(Input::get('member'));

            return Response::json(array('success' => true, 'membername' => $member->firstname . ' ' . $member->lastname, 'memberid' => $member->member_id, 'id' => $groupmember->id));




        }
    }

    public function getDeletegroupmember($id)
    {

        $groupmember = GroupMember::find($id);
        $groupmember->delete();

        if ($groupmember->exists) {
            return Response::json(['errors' => "Couldn't be deleted"]);
        } else {
            return Response::json(array('success' => true, 'id' => $id));
        }


    }

}
