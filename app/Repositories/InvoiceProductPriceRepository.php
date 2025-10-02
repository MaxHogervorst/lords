<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceProductPrice;
use Illuminate\Database\Eloquent\Model;

class InvoiceProductPriceRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new InvoiceProductPrice();
    }
}
