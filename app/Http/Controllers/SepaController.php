<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Offline\Settings\Facades\Settings;

class SepaController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('sepa.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $v = Validator::make(
            $request->all(),
            [
                'creditorName' => 'required',
                'creditorAccountIBAN' => 'required',
                'creditorAgentBIC' => 'required',
                'creditorId' => 'required',
                'creditorPain' => 'required',
                'creditorMaxMoneyPerBatch' => 'required',
                'creditorMaxMoneyPerTransaction' => 'required',
                'ReqdColltnDt' => 'required',
                'creditorMaxTransactionsPerBatch' => 'required'
            ]);

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            Settings::set('creditorName', $request->input('creditorName'));
            Settings::set('creditorAccountIBAN', $request->input('creditorAccountIBAN'));
            Settings::set('creditorAgentBIC', $request->input('creditorAgentBIC'));
            Settings::set('creditorId', $request->input('creditorId'));
            Settings::set('creditorPain', $request->input('creditorPain'));
            Settings::set('creditorMaxMoneyPerBatch', $request->input('creditorMaxMoneyPerBatch'));
            Settings::set('creditorMaxMoneyPerTransaction', $request->input('creditorMaxMoneyPerTransaction'));
            Settings::set('creditorMaxTransactionsPerBatch', $request->input('creditorMaxTransactionsPerBatch'));
            Settings::set('ReqdColltnDt', $request->input('ReqdColltnDt'));

            return Response::json(['success' => true]);
        }
    }
}
