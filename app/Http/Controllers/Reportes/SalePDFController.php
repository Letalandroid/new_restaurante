<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use TCPDF;
use Illuminate\Support\Facades\Log;
use NumberFormatter; // Importar NumberFormatter

class SalePDFController extends Controller
{
    public function exportPDF($idOrder)
    {
        // Obtener la venta asociada al idOrder con la relación 'order_dishes' y 'dish'
        $saleOrder = SalesOrder::with([
        'sale', 
        'order', 
        'order.orderDishes.dish',  // Platos
        'order.orderDishes.product' // Productos ← AGREGAR ESTA RELACIÓN
            ])
            ->where('idOrder', $idOrder)
            ->first();

        // Log para verificar si la venta se encontró
        Log::info("Sale Order: ", [$saleOrder]);

        if (!$saleOrder) {
            Log::error("No se encontró la venta para el idOrder: " . $idOrder);
            return response()->json(['message' => 'No se encontró la venta para este idOrder'], 404);
        }

        // Obtener los datos de la venta, filtrando los platos completados y calculando el subtotal
        $saleData = [
            'customer' => $saleOrder->sale->customer->name . ' ' . $saleOrder->sale->customer->lastname,
            'codeCustomer' => $saleOrder->sale->customer->codigo,
            'typeCustomer' => $saleOrder->sale->customer->Cliente_Tipo,
            'documentType' => $saleOrder->sale->documentType,
            'paymentType' => $saleOrder->sale->paymentType,
            'operationCode' => $saleOrder->sale->operationCode,
            'orderDetails' => $saleOrder->order->orderDishes->filter(function ($orderDish) {
                return $orderDish->state === 'completado';
            })->map(function ($orderDish) {
                // Determinar si es plato o producto y obtener el nombre
                if ($orderDish->idDishes !== null) {
                    $name = $orderDish->dish->name; // Es un plato
                } else if ($orderDish->idProduct !== null) {
                    $name = $orderDish->product->name; // Es un producto
                } else {
                    $name = 'Item sin nombre';
                }
                
                $subtotal = $orderDish->quantity * $orderDish->price;
                return [
                    'name' => $name,
                    'quantity' => $orderDish->quantity,
                    'price' => $orderDish->price,
                    'subtotal' => number_format($subtotal, 2),
                    'type' => $orderDish->idDishes !== null ? 'Plato' : 'Producto' // Para identificar el tipo
                ];
            })->toArray(),
            'total' => $saleOrder->subtotal,
        ];

        // Calcular el IGV (18%)
        $igv = $saleData['total'] * 0.10;
        // Calcular el subtotal (total - IGV)
        $subtotal = $saleData['total'] - $igv;

        // Log para verificar los datos de la venta
        Log::info("Sale Data: ", [$saleData]);

        // Crear el objeto TCPDF con un tamaño de página de 80mm (para impresoras térmicas)
        $pdf = new TCPDF('P', 'mm', array(80, 160), true, 'UTF-8', false);  // Ajuste para el tamaño adecuado

        // Establecer las propiedades del documento
        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Recibo de Venta');
        $pdf->SetSubject('Recibo de Venta');

        // Configuración de márgenes para impresoras pequeñas
        $pdf->SetMargins(2, 2, 2);  // Márgenes reducidos para ajustar mejor el contenido
        $pdf->SetAutoPageBreak(true, 5);

        // Agregar la primera página
        $pdf->AddPage();
        
        // Encabezado: Nombre de la empresa, RUC, dirección
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 4, 'RESTAURANTE EIRL', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 5);
        $pdf->Cell(0, 2, 'RUC: 10764403211', 0, 1, 'C');
        $pdf->Cell(0, 2, 'Dirección: Villa El Salvador - Lima', 0, 1, 'C');
        $pdf->Ln(3);

        // Título del documento según tipo de documento
        $documentTitle = $saleData['documentType'] === 'Boleta' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'FACTURA DE VENTA ELECTRÓNICA';
$numRecibo = $saleOrder->salesInvoice ? $saleOrder->salesInvoice->serie : ($saleData['documentType'] === 'Boleta' ? 'B01-101' : 'F01-101');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 4, $documentTitle, 0, 1, 'C');
        $pdf->Cell(0, 4, $numRecibo, 0, 1, 'C');
        $pdf->Cell(0, 4, 'Fecha de Emisión: ' . date('d/m/Y'), 0, 1, 'C');
        $pdf->Ln(3);

     // Información del cliente
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(0, 6, 'CLIENTE: ' . $saleData['customer'], 0, 1);



// Verificar el tipo de documento y mostrar el DNI o RUC según corresponda
if ($saleData['documentType'] === 'Boleta') {
    $pdf->Cell(0, 6, 'DNI: ' . $saleData['codeCustomer'], 0, 1);
} else if ($saleData['documentType'] === 'Factura') {
    $pdf->Cell(0, 6, 'RUC: ' . $saleData['codeCustomer'], 0, 1);
}
        // Mostrar otros datos de la venta
        $pdf->Cell(0, 6, 'DOCUMENTO: ' . $saleData['documentType'], 0, 1);
        $pdf->Cell(0, 6, 'METODO DE PAGO: ' . $saleData['paymentType'], 0, 1);
// Verificar si operationCode no es nulo y mostrarlo en el PDF
if (!empty($saleData['operationCode'])) {
    $pdf->Cell(0, 6, 'CODIGO PAGO: ' . $saleData['operationCode'], 0, 1);
}
        $pdf->Cell(0, 6, 'MONEDA: SOLES', 0, 1);
        $pdf->Cell(0, 6, 'IGV: 10%', 0, 1);
        $pdf->Ln(5);


        // Tabla de detalles de la venta
        $pdf->SetFont('helvetica', 'B', 7);
        // Separar platillos y productos
        $platillos = array_filter($saleData['orderDetails'], function($item) {
            return $item['type'] === 'Plato';
        });

        $productos = array_filter($saleData['orderDetails'], function($item) {
            return $item['type'] === 'Producto';
        });

        // Mostrar Platillos
        if (!empty($platillos)) {
            $pdf->Cell(0, 6, 'PLATILLOS', 0, 1, 'C');
            $pdf->Cell(35, 6, 'Nombre', 1, 0, 'C');
            $pdf->Cell(12, 6, 'Cantidad', 1, 0, 'C');
            $pdf->Cell(12, 6, 'Precio', 1, 0, 'C');
            $pdf->Cell(12, 6, 'Subtotal', 1, 1, 'C');
            $pdf->SetFont('helvetica', '', 6);

            foreach ($platillos as $item) {
                $pdf->Cell(35, 6, $item['name'], 1, 0, 'C');
                $pdf->Cell(12, 6, $item['quantity'], 1, 0, 'C');
                $pdf->Cell(12, 6, 'S/ ' . $item['price'], 1, 0, 'C');
                $pdf->Cell(12, 6, 'S/ ' . $item['subtotal'], 1, 1, 'C');
            }
            $pdf->Ln(3);
        }

        // Mostrar Productos
        if (!empty($productos)) {
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(0, 6, 'PRODUCTOS', 0, 1, 'C');
            $pdf->Cell(35, 6, 'Nombre', 1, 0, 'C');
            $pdf->Cell(12, 6, 'Cantidad', 1, 0, 'C');
            $pdf->Cell(12, 6, 'Precio', 1, 0, 'C');
            $pdf->Cell(12, 6, 'Subtotal', 1, 1, 'C');
            $pdf->SetFont('helvetica', '', 6);

            foreach ($productos as $item) {
                $pdf->Cell(35, 6, $item['name'], 1, 0, 'C');
                $pdf->Cell(12, 6, $item['quantity'], 1, 0, 'C');
                $pdf->Cell(12, 6, 'S/ ' . $item['price'], 1, 0, 'C');
                $pdf->Cell(12, 6, 'S/ ' . $item['subtotal'], 1, 1, 'C');
            }
            $pdf->Ln(3);
        }

        // Si no hay items de ningún tipo
        if (empty($platillos) && empty($productos)) {
            $pdf->Cell(0, 6, 'No se encontraron items para esta venta.', 0, 1, 'C');
        }

        // IGV y Subtotal (alineados a la derecha)
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(55, 5, '', 0, 0);  // Espacio a la izquierda
        $pdf->Cell(20, 5, 'IGV (10%): S/ ' . number_format($igv, 2), 0, 1, 'R');
        $pdf->Cell(55, 5, '', 0, 0);  // Espacio a la izquierda
        $pdf->Cell(20, 5, 'Subtotal: S/ ' . number_format($subtotal, 2), 0, 1, 'R');
        $pdf->Cell(55, 5, '', 0, 0);  // Espacio a la izquierda
        $pdf->Cell(20, 5, 'Total: S/ ' . number_format($saleData['total'], 2), 0, 1, 'R');

       // Convertir el monto total a letras
$formatter = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
$totalEnLetras = strtoupper(ucfirst($formatter->format($saleData['total'])) . " soles");

// Imprimir el monto total en letras con padding y ajuste
$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 7);

// Reducir el ancho de la celda y agregar margen para evitar desbordamiento
$pdf->MultiCell(0, 5, 'IMPORTE EN LETRAS: ' . $totalEnLetras, 0, 'C');

        // Generar el PDF y devolverlo al navegador
        $pdfOutput = $pdf->Output('Recibo_' . $idOrder . '.pdf', 'S');
        return response($pdfOutput)->header('Content-Type', 'application/pdf')->header('Content-Disposition', 'attachment; filename="Recibo_' . $idOrder . '.pdf"');
    }
}
