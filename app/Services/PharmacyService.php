<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    public function processSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $data['invoice_number'] = Sale::generateInvoiceNumber();
            $data['created_by']     = auth()->id();

            $subtotal         = 0;
            $processedItems   = [];

            foreach ($data['items'] as $item) {
                $medicine    = Medicine::findOrFail($item['medicine_id']);
                $unitPrice   = $item['unit_price'] ?? $medicine->sale_price;
                $qty         = $item['quantity'];
                $discAmt     = ($unitPrice * $qty) * (($item['discount_percentage'] ?? 0) / 100);
                $lineTotal   = ($unitPrice * $qty) - $discAmt;
                $subtotal   += $lineTotal;

                // find purchase cost for profit calculation
                $purchaseCost = $medicine->purchase_price;
                $profit       = $lineTotal - ($purchaseCost * $qty);

                $processedItems[] = array_merge($item, [
                    'unit_price'          => $unitPrice,
                    'discount_amount'     => $discAmt,
                    'total_price'         => $lineTotal,
                    'profit'              => $profit,
                ]);
            }

            $discountAmt  = $data['discount_amount'] ?? ($subtotal * (($data['discount_percentage'] ?? 0) / 100));
            $totalAmount  = $subtotal - $discountAmt + ($data['tax_amount'] ?? 0);
            $paidAmount   = $data['paid_amount'] ?? $totalAmount;

            $hour  = now()->hour;
            $shift = $hour >= 8 && $hour < 14 ? 'morning' : ($hour >= 14 && $hour < 20 ? 'evening' : 'night');

            $sale = Sale::create(array_merge($data, [
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmt,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => max(0, $paidAmount - $totalAmount),
                'sale_date'       => $data['sale_date'] ?? today(),
                'shift'           => $data['shift'] ?? $shift,
                'payment_status'  => 'paid',
                'status'          => 'completed',
            ]));

            foreach ($processedItems as $item) {
                $sale->items()->create($item);

                // deduct stock
                Medicine::find($item['medicine_id'])->decrement('stock_quantity', $item['quantity']);

                MedicineStock::create([
                    'medicine_id'      => $item['medicine_id'],
                    'type'             => 'out',
                    'quantity'         => -$item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'reference_type'   => 'sale',
                    'reference_id'     => $sale->id,
                    'reference_number' => $sale->invoice_number,
                    'created_by'       => auth()->id(),
                ]);
            }

            return $sale;
        });
    }

    public function processPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $data['purchase_number'] = Purchase::generateNumber();
            $data['created_by']      = auth()->id();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['total_price'];
            }

            $data['subtotal']     = $subtotal;
            $data['total_amount'] = $subtotal - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);
            $data['due_amount']   = $data['total_amount'] - ($data['paid_amount'] ?? 0);

            $purchase = Purchase::create($data);

            foreach ($data['items'] as $item) {
                $purchase->items()->create($item);

                // create batch
                $batch = MedicineBatch::create([
                    'medicine_id'      => $item['medicine_id'],
                    'batch_number'     => $item['batch_number'] ?? 'BATCH-' . now()->format('Ymd'),
                    'expiry_date'      => $item['expiry_date'],
                    'purchase_price'   => $item['unit_price'],
                    'sale_price'       => $item['sale_price'] ?? Medicine::find($item['medicine_id'])?->sale_price ?? 0,
                    'quantity'         => $item['quantity'],
                    'remaining_quantity' => $item['quantity'],
                    'supplier_id'      => $data['supplier_id'],
                ]);

                // add stock
                Medicine::find($item['medicine_id'])->increment('stock_quantity', $item['quantity']);

                MedicineStock::create([
                    'medicine_id'      => $item['medicine_id'],
                    'batch_id'         => $batch->id,
                    'type'             => 'in',
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'reference_type'   => 'purchase',
                    'reference_id'     => $purchase->id,
                    'reference_number' => $purchase->purchase_number,
                    'created_by'       => auth()->id(),
                ]);
            }

            return $purchase;
        });
    }

    public function adjustStock(Medicine $medicine, int $quantity, string $type, string $notes = ''): void
    {
        DB::transaction(function () use ($medicine, $quantity, $type, $notes) {
            if ($type === 'in') {
                $medicine->increment('stock_quantity', $quantity);
            } else {
                $medicine->decrement('stock_quantity', $quantity);
            }

            MedicineStock::create([
                'medicine_id' => $medicine->id,
                'type'        => 'adjustment',
                'quantity'    => $type === 'in' ? $quantity : -$quantity,
                'unit_price'  => $medicine->purchase_price,
                'notes'       => $notes,
                'created_by'  => auth()->id(),
            ]);
        });
    }

    public function getProfitLoss(string $from, string $to): array
    {
        $revenue  = Sale::whereBetween('sale_date', [$from, $to])->where('status', 'completed')->sum('total_amount');
        $profit   = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$from, $to])->where('status', 'completed'))->sum('profit');

        return compact('revenue', 'profit');
    }
}
