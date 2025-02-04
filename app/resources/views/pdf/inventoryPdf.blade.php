<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Detallado de Inventario</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 5px 0;
        }
        .summary {
            background-color: #ecf0f1;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .summary h2 {
            color: #2980b9;
            font-size: 18px;
            margin-top: 0;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .summary-item {
            background-color: #fff;
            border-radius: 3px;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .summary-item strong {
            display: block;
            font-size: 16px;
            color: #34495e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #bdc3c7;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 30px;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Detallado de Inventario</h1>
        <p>Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h2>Resumen de Inventario</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>{{ number_format($totalProducts) }}</strong>
                Total de Productos
            </div>
            <div class="summary-item">
                <strong>{{ number_format($totalValue, 2) }} Bs</strong>
                Valor Total del Inventario
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Producto</th>
                <th>Nombre del Producto</th>
                <th>Cantidad</th>
                <th>Precio de Compra</th>
                <th>Valor Total</th>
                <th>Fecha de Vencimiento</th>
                <th>Última Actualización</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inventory)
            <tr>
                <td>{{ $inventory->product_id }}</td>
                <td>{{ $inventory->product->name ?? 'N/A' }}</td>
                <td>{{ $inventory->quantity }}</td>
                <td>{{ number_format($inventory->product->purchase_price, 2) }} Bs</td>
                <td>{{ number_format($inventory->quantity * $inventory->product->purchase_price, 2) }} Bs</td>
                <td>{{ $inventory->expiration_date ? \Carbon\Carbon::parse($inventory->expiration_date)->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ optional($inventory->updated_at)->format('d/m/Y H:i:s') ?? 'Sin actualización' }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este es un reporte generado automáticamente. Para cualquier consulta, por favor contacte al departamento de inventario.</p>
    </div>
</body>
</html>