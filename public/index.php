<?php
declare(strict_types=1);
$file = __DIR__ . '/../storage/pos.json';
$db = json_decode((string)file_get_contents($file), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['qty']);
    foreach ($db['products'] as &$p) {
        if ((int)$p['id'] === $pid) {
            if ($p['stock'] < $qty) {
                $error = 'المخزون غير كافٍ';
                break;
            }
            $p['stock'] -= $qty;
            $invoice = [
                'id' => count($db['invoices']) + 1,
                'product' => $p['name'],
                'qty' => $qty,
                'unit_price' => $p['price'],
                'total' => $qty * $p['price'],
                'created_at' => date('c'),
            ];
            $db['invoices'][] = $invoice;
            file_put_contents($file, json_encode($db, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            header('Location: /');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>POS Lite</title>
<style>body{font-family:Tahoma;background:#f8fafc;padding:1.5rem}table{border-collapse:collapse;width:100%;background:#fff}td,th{border:1px solid #e2e8f0;padding:.5rem}.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}</style>
</head><body>
<h1>نقطة بيع خفيفة (POS Lite)</h1>
<?php if (!empty($error)): ?><p style="color:#b91c1c"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<div class="grid">
<section>
<h2>المنتجات</h2>
<table><tr><th>الصنف</th><th>السعر</th><th>المخزون</th><th>بيع</th></tr>
<?php foreach ($db['products'] as $p): ?>
<tr>
<td><?= htmlspecialchars($p['name']) ?></td>
<td><?= (float)$p['price'] ?></td>
<td><?= (int)$p['stock'] ?></td>
<td>
<form method="post">
<input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
<input type="number" name="qty" value="1" min="1" style="width:60px">
<button>بيع</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</section>
<section>
<h2>الفواتير</h2>
<table><tr><th>#</th><th>الصنف</th><th>الكمية</th><th>الإجمالي</th></tr>
<?php foreach (array_reverse($db['invoices']) as $inv): ?>
<tr>
<td><?= (int)$inv['id'] ?></td>
<td><?= htmlspecialchars($inv['product']) ?></td>
<td><?= (int)$inv['qty'] ?></td>
<td><?= (float)$inv['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</section>
</div>
</body></html>
