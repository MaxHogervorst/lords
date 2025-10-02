<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SepaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('sepa.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
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
                'creditorMaxTransactionsPerBatch' => 'required',
            ]);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            Settings::set('creditorName', $request->get('creditorName'));
            Settings::set('creditorAccountIBAN', $request->get('creditorAccountIBAN'));
            Settings::set('creditorAgentBIC', $request->get('creditorAgentBIC'));
            Settings::set('creditorId', $request->get('creditorId'));
            Settings::set('creditorPain', $request->get('creditorPain'));
            Settings::set('creditorMaxMoneyPerBatch', $request->get('creditorMaxMoneyPerBatch'));
            Settings::set('creditorMaxMoneyPerTransaction', $request->get('creditorMaxMoneyPerTransaction'));
            Settings::set('creditorMaxTransactionsPerBatch', $request->get('creditorMaxTransactionsPerBatch'));
            Settings::set('ReqdColltnDt', $request->get('ReqdColltnDt'));
            Settings::save();

            return response()->json(['success' => true]);
        }
    }
}
