<?php
require_once __DIR__ . '/vendor/autoload.php';

// รับข้อมูลตะกร้าสินค้าจาก Cookie
$cart_data = isset($_COOKIE['it_shop_cart']) ? json_decode($_COOKIE['it_shop_cart'], true) : [];

if (empty($cart_data)) {
    die("ไม่มีข้อมูลสินค้าในตะกร้า");
}

// ตั้งค่า mPDF
$fontDir = __DIR__ . '/assets/fonts';
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontVars = (new Mpdf\Config\FontVariables())->getDefaults();

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'default_font_size' => 14,
    'default_font' => 'sarabun',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'fontDir' => array_merge($defaultConfig['fontDir'], [$fontDir]),
    'fontdata' => $fontVars['fontdata'] + [
        'sarabun' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew Bold.ttf',
            'I' => 'THSarabunNew Italic.ttf',
        ]
    ],
]);

// CSS สำหรับการจัดรูปแบบ (ปรับ Model ให้เหมือนประเภท)
$stylesheet = '
    body { font-family: "sarabun"; color: #333; }
    .header-title { font-size: 22pt; color: #1a2a6c; font-weight: bold; text-align: center; margin-bottom: 5px; }
    .header-sub { text-align: center; font-size: 12pt; color: #666; margin-bottom: 20px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { 
        background-color: #f8f9fa; 
        color: #1a2a6c; 
        border-bottom: 2px solid #1a2a6c; 
        padding: 10px 5px; 
        font-size: 13pt;
        text-align: center;
    }
    td { 
        padding: 8px 5px; 
        border-bottom: 1px solid #eee; 
        font-size: 12pt;
        vertical-align: middle;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    
    /* ปรับ Model ให้เป็นฟอนต์ปกติเหมือนประเภท */
    .model-normal { font-size: 12pt; color: #333; }
    
    .total-row { background-color: #fcfcfc; font-weight: bold; font-size: 14pt; }
    .total-label { text-align: right; padding-right: 15px; color: #1a2a6c; }
    .total-amount { color: #d9534f; border-bottom: 2px double #d9534f; }
    
    .footer-note { font-size: 10pt; color: #888; margin-top: 30px; font-style: italic; }
';

// เนื้อหา HTML
$html = '
    <div class="header-title">รายการสรุปการเลือกซื้อสินค้า IT SHOP</div>
    <div class="header-sub">วันที่ออกเอกสาร: ' . date('d/m/Y H:i') . '</div>

    <table>
        <thead>
            <tr>
                <th width="8%">ลำดับ</th>
                <th width="15%">ประเภท</th>
                <th width="32%">รายการสินค้า / รุ่น</th>
                <th width="15%">Model</th>
                <th width="12%">ราคา/หน่วย</th>
                <th width="8%">จำนวน</th>
                <th width="10%">รวม</th>
            </tr>
        </thead>
        <tbody>';

$grandTotal = 0;
foreach ($cart_data as $index => $item) {
    $subtotal = $item['price'] * $item['qty'];
    $grandTotal += $subtotal;

    // รวมชื่อยี่ห้อและรุ่นไว้ในบรรทัดเดียวกัน
    $product_info = htmlspecialchars($item['brand_name'] . ' ' . $item['version']);

    $html .= '
        <tr>
            <td class="text-center">' . ($index + 1) . '</td>
            <td>' . htmlspecialchars($item['category_name']) . '</td>
            <td>' . $product_info . '</td>
            <td class="text-center model-normal">' . htmlspecialchars($item['model']) . '</td>
            <td class="text-right">' . number_format($item['price'], 2) . '</td>
            <td class="text-center">' . $item['qty'] . '</td>
            <td class="text-right"><strong>' . number_format($subtotal, 2) . '</strong></td>
        </tr>';
}

$html .= '
        <tr class="total-row">
            <td colspan="6" class="total-label">ราคารวมสุทธิทั้งสิ้น (Grand Total)</td>
            <td class="text-right total-amount">' . number_format($grandTotal, 2) . '</td>
        </tr>
    </tbody>
</table>

<div class="footer-note">* เอกสารฉบับนี้จัดทำโดยระบบจำลองตะกร้าสินค้าเพื่อใช้ในการพิจารณาเลือกซื้อเบื้องต้นเท่านั้น</div>
';

$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$mpdf->Output('IT_SHOP_Summary.pdf', \Mpdf\Output\Destination::INLINE);
