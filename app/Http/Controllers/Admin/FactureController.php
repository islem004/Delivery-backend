<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Delivery;

class FactureController extends Controller
{
    public function index()
    {
        return response()->json(
            Invoice::with('client.user')->get()
        );
    }

    /**
     * Generate invoice with PDF + barcode
     */
    public function generate(Request $request, $id = null)
    {
        $deliveryId = $id ?? $request->input('delivery_id');

        if (!$deliveryId) {
            return response()->json([
                'message' => 'ID de livraison requis'
            ], 400);
        }

        // Load delivery with items
        $delivery = Delivery::with([
            'client.user',
            'assignedStaff.user',
            'items' // ✅ Important: include items for calculation
        ])->findOrFail($deliveryId);

        // Check for duplicate invoice
        $existingInvoice = Invoice::where('delivery_id', $delivery->id)->first();

        if ($existingInvoice) {
            return response()->json([
                'message' => 'Invoice already exists for this delivery'
            ], 400);
        }

        // Check if delivery has items
        if ($delivery->items->isEmpty()) {
            return response()->json([
                'message' => 'Cannot generate invoice without items'
            ], 400);
        }

        // Calculate totals
        $subtotal = 0;

        foreach ($delivery->items as $item) {
            $subtotal += floatval($item->quantity) * floatval($item->unit_price);
        }

        $tax = $subtotal * 0.19; // Example tax rate (19%)
        $total = $subtotal + $tax;

        // Generate barcode
        $barcodeValue = $delivery->barcode_value ?? 'DEL-' . $delivery->id;
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128);

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . date('Ymd') . '-' . $delivery->id,
            'client_id' => $delivery->client_id,
            'delivery_id' => $delivery->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'pending',

            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'total' => $total,
            'balance_due' => $total,

            'created_by' => auth()->id(),
        ]);

        // Generate PDF
        $pdf = Pdf::loadView('admin.documents.facture', [
            'commande' => $delivery,
            'barcode' => $barcode,
            'invoice' => $invoice
        ]);

        // Save PDF
        $fileName = 'facture_' . $delivery->delivery_number . '.pdf';
        $pdf->save(storage_path('app/public/' . $fileName));

        // Update invoice with PDF URL
        $invoice->update([
            'pdf_url' => '/storage/' . $fileName
        ]);

        // Return JSON
        return response()->json([
            'message' => 'Invoice generated successfully',
            'invoice' => $invoice
        ]);
    }
}