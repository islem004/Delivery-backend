<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user()->client;

        $deliveries = Delivery::where('client_id', $client->id)
            ->with(['items', 'statusHistories', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($deliveries);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pickup_address_text'    => 'required|string',
            'delivery_address_text'  => 'required|string',
            'scheduled_pickup_time'  => 'nullable|date',
            'scheduled_delivery_time'=> 'nullable|date',
            'priority'               => 'in:low,normal,high,urgent',
            'special_instructions'   => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.item_name'      => 'required|string',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.unit'           => 'nullable|string',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate'       => 'nullable|numeric|min:0',
        ]);

        $client = $request->user()->client;

        $deliveryNumber = 'DEL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        $barcodeValue = strtoupper(Str::random(12));

        $delivery = Delivery::create([
            'delivery_number'        => $deliveryNumber,
            'client_id'              => $client->id,
            'pickup_address_text'    => $request->pickup_address_text,
            'delivery_address_text'  => $request->delivery_address_text,
            'scheduled_pickup_time'  => $request->scheduled_pickup_time,
            'scheduled_delivery_time'=> $request->scheduled_delivery_time,
            'priority'               => $request->priority ?? 'normal',
            'special_instructions'   => $request->special_instructions,
            'status'                 => 'draft',
            'barcode_value'          => $barcodeValue,
            'barcode_format'         => 'CODE128',
            'created_by'             => $request->user()->id,
        ]);

        foreach ($request->items as $index => $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $discountAmount = $subtotal * (($item['discount_percent'] ?? 0) / 100);
            $taxAmount = ($subtotal - $discountAmount) * (($item['tax_rate'] ?? 0) / 100);
            $total = $subtotal - $discountAmount + $taxAmount;

            $delivery->items()->create([
                'item_code'        => $item['item_code'] ?? null,
                'item_name'        => $item['item_name'],
                'description'      => $item['description'] ?? null,
                'quantity'         => $item['quantity'],
                'unit'             => $item['unit'] ?? 'piece',
                'unit_price'       => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'discount_amount'  => $discountAmount,
                'tax_rate'         => $item['tax_rate'] ?? 0,
                'tax_amount'       => $taxAmount,
                'subtotal'         => $subtotal,
                'total'            => $total,
                'notes'            => $item['notes'] ?? null,
                'sort_order'       => $index,
            ]);
        }

        $delivery->statusHistories()->create([
            'status'     => 'draft',
            'updated_by' => $request->user()->id,
            'notes'      => 'Delivery created',
        ]);

        return response()->json([
            'message'  => 'Delivery created successfully',
            'delivery' => $delivery->load('items', 'statusHistories'),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $client = $request->user()->client;

        $delivery = Delivery::where('client_id', $client->id)
            ->with(['items', 'statusHistories', 'invoice', 'proofOfDelivery'])
            ->findOrFail($id);

        return response()->json($delivery);
    }

    public function update(Request $request, $id)
    {
        $client = $request->user()->client;

        $delivery = Delivery::where('client_id', $client->id)->findOrFail($id);

        if ($delivery->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft deliveries can be edited'
            ], 403);
        }

        $request->validate([
            'pickup_address_text'    => 'sometimes|string',
            'delivery_address_text'  => 'sometimes|string',
            'scheduled_pickup_time'  => 'nullable|date',
            'scheduled_delivery_time'=> 'nullable|date',
            'priority'               => 'sometimes|in:low,normal,high,urgent',
            'special_instructions'   => 'nullable|string',
        ]);

        $delivery->update($request->only([
            'pickup_address_text',
            'delivery_address_text',
            'scheduled_pickup_time',
            'scheduled_delivery_time',
            'priority',
            'special_instructions',
        ]));

        return response()->json([
            'message'  => 'Delivery updated successfully',
            'delivery' => $delivery->load('items'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $client = $request->user()->client;

        $delivery = Delivery::where('client_id', $client->id)->findOrFail($id);

        if ($delivery->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft deliveries can be deleted'
            ], 403);
        }

        $delivery->delete();

        return response()->json(['message' => 'Delivery deleted successfully']);
    }

    public function track(Request $request, $id)
    {
        $client = $request->user()->client;

        $delivery = Delivery::where('client_id', $client->id)
            ->with(['statusHistories' => function($query) {
                $query->orderBy('updated_at', 'desc');
            }])
            ->findOrFail($id);

        return response()->json([
            'delivery_number' => $delivery->delivery_number,
            'current_status'  => $delivery->status,
            'history'         => $delivery->statusHistories,
        ]);
    }
    public function printDeliveryForm(Request $request, $id)
{
    $delivery = Delivery::where('client_id', $request->user()->client->id)
        ->with(['items', 'client'])
        ->findOrFail($id);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.delivery-form', compact('delivery'));
    return $pdf->download('bon-livraison-' . $delivery->delivery_number . '.pdf');
}
}