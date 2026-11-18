<?php
session_start();

try {
    // สร้างและเชื่อมต่อฐานข้อมูล SQLite
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. สร้างตาราง users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
    )");

    // 2. สร้างตาราง products (อุปกรณ์อิเล็กทรอนิกส์)
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        price REAL NOT NULL,
        stock INTEGER NOT NULL,
        description TEXT
    )");

    // เพิ่ม user เริ่มต้น (admin / 1234) หากยังไม่มี
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('1234', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")
            ->execute(['admin', $hash]);
    }

    // เพิ่มข้อมูลสินค้าตัวอย่างหากยังไม่มี
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $sampleData = [
            ['Raspberry Pi 5 (8GB)', 'Microcontroller', 3200.00, 15, 'บอร์ดคอมพิวเตอร์ขนาดเล็ก ประสิทธิภาพสูง'],
            ['Arduino Uno R4 WiFi', 'Development Board', 950.00, 30, 'บอร์ดไมโครคอนโทรลเลอร์รองรับ WiFi/Bluetooth'],
            ['ESP32-S3 DevKitC', 'IoT Module', 280.00, 50, 'โมดูล IoT Dual-Core รองรับ AI'],
            ['Digital Multimeter Fluke 101', 'Measuring Tool', 1850.00, 10, 'มัลติมิเตอร์ดิจิทัลขนาดพกพา']
        ];
        $insert = $pdo->prepare("INSERT INTO products (name, category, price, stock, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleData as $row) {
            $insert->execute($row);
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>