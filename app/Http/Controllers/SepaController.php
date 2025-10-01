<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use anlutro\LaravelSettings\Facade as Settings;

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
    public function store()
    {
        $v = Validator::make(
            Input::all(),
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
            Settings::set('creditorName', Input::get('creditorName'));
            Settings::set('creditorAccountIBAN', Input::get('creditorAccountIBAN'));
            Settings::set('creditorAgentBIC', Input::get('creditorAgentBIC'));
            Settings::set('creditorId', Input::get('creditorId'));
            Settings::set('creditorPain', Input::get('creditorPain'));
            Settings::set('creditorMaxMoneyPerBatch', Input::get('creditorMaxMoneyPerBatch'));
            Settings::set('creditorMaxMoneyPerTransaction', Input::get('creditorMaxMoneyPerTransaction'));
            Settings::set('creditorMaxTransactionsPerBatch', Input::get('creditorMaxTransactionsPerBatch'));
            Settings::set('ReqdColltnDt', Input::get('ReqdColltnDt'));
            Settings::save();

            return Response::json(['success' => true]);
        }
    }
}
