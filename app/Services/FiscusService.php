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
            $invoiceproduct = new InvoiceProduct();
            $invoiceproduct->name = $data['finalproductname'];
            $invoiceproduct->invoice_group_id = $invoiceGroupId;
            $invoiceproduct->save();

            $invoiceproductprice = new InvoiceProductPrice();
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
                $invoiceproductprice = new InvoiceProductPrice();
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

            // Bulk delete invoice lines for all prices of this product
            $priceIds = $invoiceProduct->productprice()->pluck('id');
            InvoiceLine::whereIn('invoice_product_price_id', $priceIds)->delete();

            // Bulk delete all prices for this product
            InvoiceProductPrice::where('invoice_product_id', $invoiceProduct->id)->delete();

            $invoiceProduct->delete();

            return $name;
        });
    }

    private function createInvoiceLines(int $priceId, array $memberIds): int
    {
        // Prepare bulk insert data
        $now = now();
        $data = array_map(fn ($memberId) => [
            'invoice_product_price_id' => $priceId,
            'member_id' => $memberId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $memberIds);

        // Bulk insert all invoice lines
        InvoiceLine::insert($data);

        return count($memberIds);
    }
}
