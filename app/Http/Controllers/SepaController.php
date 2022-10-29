<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SepaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('sepa.index', ['settings' => Setting::toMap()]);
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
                'creditorMaxTransactionsPerBatch' => 'required',
            ]
        );

        if (! $v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        }

        foreach ($request->all() as $key => $value) {
            Setting::where('key', '=', $key)->update(['key' => $key, 'value' => $value]);
        }

        return Response::json(['success' => true]);
    }

    public function downloadFile($file_name)
    {
        // $file = Storage::disk('public')->get('SEPA/' . $file_name);
        // $file_e = Storage::disk('public')->exists('SEPA/' . $file_name);

        return response()->download(storage_path($file_name));
    }
}
