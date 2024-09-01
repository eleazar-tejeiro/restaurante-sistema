<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas mensuales</title>
    <style>
      body {
          font-family: Arial, sans-serif;
          font-size: 12px;
          line-height: 1.4;
          color: #333;
      }
      .header {
          text-align: center;
          margin-bottom: 20px;
      }
      h1 {
          color: #2c3e50;
          font-size: 24px;
      }
      table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
      }
      th, td {
          border: 1px solid #ddd;
          padding: 8px;
          text-align: left;
      }
      th {
          background-color: #f2f2f2;
          font-weight: bold;
          color: #2c3e50;
      }
      tr:nth-child(even) {
          background-color: #f9f9f9;
      }
      .footer {
          text-align: center;
          font-size: 10px;
          color: #777;
          margin-top: 20px;
      }
      .summary {
          margin-bottom: 20px;
      }
      .summary h2 {
          color: #2c3e50;
      }
      .summary p {
          margin: 5px 0;
      }
  </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de ventas de {{ $month }} {{ $year }}</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
    <div class="summary">
        <h2>Resumen</h2>
        <p>Total de ventas: {{ number_format($totalSales, 2) }} Bs</p>
        <p>Total de productos vendidos: {{ $totalProducts }}</p>
        <p>Ganancia total: {{ number_format($totalProfit, 2) }} Bs</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Ganancia</th>
                <th>Fecha y Hora de Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->sale_id }}</td>
                <td>{{ $sale->product->name ?? 'N/A' }}</td>
                <td>{{ $sale->quantity }}</td>
                <td>{{ number_format($sale->unit_price, 2) }} Bs</td>
                <td>{{ number_format($sale->subtotal, 2) }} Bs</td>
                <td>{{ number_format(($sale->unit_price - $sale->product->purchase_price) * $sale->quantity, 2) }} Bs</td>
                <td>{{ $sale->sale->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este es un reporte generado automáticamente. Por favor, no responda a este documento.</p>
    </div>
</body>
</html>