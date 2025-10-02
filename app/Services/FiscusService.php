<?php

namespace App\Services;

use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use Illuminate\Support\Facades\DB;

class FiscusService
{
    public function createInvoiceProduct(array $data, int $invoiceGroupId): array
    {
        return DB::transaction(function () use ($data, $invoiceGroupId) {
            $invoiceproduct = new InvoiceProduct;
            $invoiceproduct->name = $data['finalproductname'];
            $invoiceproduct->invoice_group_id = $invoiceGroupId;
            $invoiceproduct->save();

            $invoiceproductprice = new InvoiceProductPrice;
            $invoiceproductprice->invoice_product_id = $invoiceproduct->id;
            $invoiceproductprice->price = $data['finalpriceperperson'];
            $invoiceproductprice->description = $data['finalproductdescription'];
            $invoiceproductprice->save();

            $memberCount = $this->createInvoiceLines($invoiceproductprice->id, $data['member']);

            return [
                'product_name' => $invoiceproduct->name,
                'price' => $invoiceproductprice->price,
                'member_count' => $memberCount,
            ];
        });
    }

    public function updateInvoiceProduct(InvoiceProduct $invoiceProduct, array $data, ?string $updatePriceId = null): array
    {
        return DB::transaction(function () use ($invoiceProduct, $data, $updatePriceId) {
            $update = 'added new price';

            if ($updatePriceId) {
                $update = 'updated';
                $invoiceproductprice = InvoiceProductPrice::findOrFail($updatePriceId);
                $invoiceproductprice->price = $data['finalpriceperperson'];
                $invoiceproductprice->description = $data['finalproductdescription'];
                $invoiceproductprice->save();

                InvoiceLine::where('invoice_product_price_id', '=', $invoiceproductprice->id)->delete();
            } else {
                $invoiceproductprice = new InvoiceProductPrice;
                $invoiceproductprice->invoice_product_id = $invoiceProduct->id;
                $invoiceproductprice->price = $data['finalpriceperperson'];
                $invoiceproductprice->description = $data['finalproductdescription'];
                $invoiceproductprice->save();
            }

            $memberCount = 0;
            if (isset($data['member'])) {
                $memberCount = $this->createInvoiceLines($invoiceproductprice->id, $data['member']);
            }

            return [
                'product_name' => $invoiceProduct->name,
                'update_type' => $update,
                'price' => $invoiceproductprice->price,
                'member_count' => $memberCount,
            ];
        });
    }

    public function deleteInvoiceProduct(InvoiceProduct $invoiceProduct): string
    {
        return DB::transaction(function () use ($invoiceProduct) {
            $name = $invoiceProduct->name;

            foreach ($invoiceProduct->productprice as $price) {
                foreach ($price->invoiceline as $line) {
                    $line->delete();
                }
                $price->delete();
            }

            $invoiceProduct->delete();

            return $name;
        });
    }

    private function createInvoiceLines(int $priceId, array $memberIds): int
    {
        $count = 0;
        foreach ($memberIds as $memberId) {
            $invoiceline = new InvoiceLine;
            $invoiceline->invoice_product_price_id = $priceId;
            $invoiceline->member_id = $memberId;
            $invoiceline->save();
            if ($invoiceline->exists) {
                $count++;
            }
        }

        return $count;
    }
}
