<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceGroup extends Model {

	protected $table = 'invoice_groups';

    public static function getCurrentMonth()
    {
        $invoicegroup = InvoiceGroup::where('status', '=', true)->first();

        if(is_null($invoicegroup))
        {
            return false;
        }

        return $invoicegroup;
    }

}
