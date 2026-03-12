<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Livraison {{ $delivery->delivery_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; color: #333; }
        .info { margin-bottom: 20px; }
        .barcode { text-align: center; margin: 30px 0; border: 1px solid #ccc; padding: 15px; background: white; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; font-size: 18px; }
    </style>
</head>
<body>
    <h1>Bon de Livraison N° {{ $delivery->delivery_number }}</h1>

    <div class="info">
        <p><strong>Client :</strong> {{ $delivery->client->company_name ?? 'Client inconnu' }}</p>
        <p><strong>Adresse départ :</strong> {{ $delivery->pickup_address_text }}</p>
        <p><strong>Adresse livraison :</strong> {{ $delivery->delivery_address_text }}</p>
        <p><strong>Priorité :</strong> {{ ucfirst($delivery->priority ?? 'normal') }}</p>
        <p><strong>Instructions :</strong> {{ $delivery->special_instructions ?? 'Aucune' }}</p>
    </div>

    <!-- BARCODE ICI -->
    <div class="barcode">
       {!! DNS1D::getBarcodeHTML($delivery->barcode_value, 'C128', 3, 120, 'black', true) !!}
        <p style="font-size: 18px; margin-top: 10px;">
            <strong>Code-barres :</strong> {{ $delivery->barcode_value }}
        </p>
        <p style="color: #555;">Scannez pour suivre la livraison</p>
    </div>

    <h2>Articles</h2>
    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($delivery->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->quantity }} {{ $item->unit ?? 'pcs' }}</td>
                <td>{{ number_format($item->unit_price, 3) }} TND</td>
                <td>{{ number_format($item->total, 3) }} TND</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Total : {{ number_format($delivery->items->sum('total'), 3) }} TND</p>
    </div>
</body>
</html>