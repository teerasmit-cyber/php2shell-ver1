<?php
require 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// จัดการการเพิ่มข้อมูล (Create)
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'], $_POST['description']]);
    header("Location: index.php");
    exit;
}

// จัดการการแก้ไขข้อมูล (Update)
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, description=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'], $_POST['description'], $_POST['id']]);
    header("Location: index.php");
    exit;
}

// จัดการการลบข้อมูล (Delete)
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php");
    exit;
}

// ดึงข้อมูลสินค้าทั้งหมด
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คลังอุปกรณ์อิเล็กทรอนิกส์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">⚡ ระบบจัดการอุปกรณ์อิเล็กทรอนิกส์</a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3">ผู้ใช้: <b><?= htmlspecialchars($_SESSION['username']) ?></b></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>รายการอุปกรณ์ในคลัง</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ เพิ่มอุปกรณ์ใหม่</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>ชื่ออุปกรณ์</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา (บาท)</th>
                            <th>คงเหลือ (ชิ้น)</th>
                            <th>รายละเอียด</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><b><?= htmlspecialchars($p['name']) ?></b></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($p['category']) ?></span></td>
                            <td><?= number_format($p['price'], 2) ?></td>
                            <td><?= $p['stock'] ?></td>
                            <td><?= htmlspecialchars($p['description']) ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" 
                                    onclick='openEditModal(<?= json_encode($p) ?>)'>แก้ไข</button>
                                <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('ยืนยันการลบอุปกรณ์นี้?')">ลบ</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มข้อมูล -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มอุปกรณ์อิเล็กทรอนิกส์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label>ชื่ออุปกรณ์</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>หมวดหมู่</label>
                    <input type="text" name="category" class="form-control" placeholder="เช่น Microcontroller, Sensor" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>ราคา (บาท)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>จำนวนคงเหลือ</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label>รายละเอียด</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal แก้ไขข้อมูล -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขข้อมูลอุปกรณ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label>ชื่ออุปกรณ์</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>หมวดหมู่</label>
                    <input type="text" name="category" id="edit_category" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>ราคา (บาท)</label>
                        <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>จำนวนคงเหลือ</label>
                        <input type="number" name="stock" id="edit_stock" class="form-control" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label>รายละเอียด</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">อัปเดตข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditModal(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_category').value = product.category;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_description').value = product.description;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
</body>
</html>