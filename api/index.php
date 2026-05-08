<?php
session_start();
$current_page = 'beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Tugas Kriptografi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigasi -->
<nav class="nav-bar">
    <a href="index.php" class="active">🏠 Beranda</a>
    <a href="kalkulator-fpb.php">🧮 Kalkulator FPB</a>
    <a href="riwayat.php">📋 Riwayat</a>
    <a href="tentang.php">ℹ️ Tentang</a>
    <a href="simulasi-rsa.php">📬 Simulasi RSA</a>
    <a href="xor-cipher.php">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php">🛡️ Verifikator Dokumen</a>
</nav>

<!-- Konten Utama -->
<div class="container">
    <h1> Kriptografi Task</h1>
    <p class="subtitle">Kumpulan Tugas pembelajaran Kriptografi</p>

    <!-- Grid Task Cards -->
    <div class="task-grid">
        
        <!-- Card: Kalkulator FPB -->
        <a href="kalkulator-fpb.php" class="task-card">
            <span class="icon">🧮</span>
            <span class="card-title">Kalkulator FPB</span>
            <span class="card-desc">Hitung FPB dengan algoritma Euclidean</span>
        </a>

        <!-- Card: Riwayat Perhitungan -->
        <a href="riwayat.php" class="task-card">
            <span class="icon">📋</span>
            <span class="card-title">Riwayat</span>
            <span class="card-desc">Lihat riwayat perhitungan FPB</span>
        </a>

        <!-- Card: Tentang Algoritma -->
        <a href="tentang.php" class="task-card">
            <span class="icon">ℹ️</span>
            <span class="card-title">Tentang</span>
            <span class="card-desc">Apa itu algoritma Euclidean?</span>
        </a>
        <!-- Card: Simulasi RSA -->
        <a href="simulasi-rsa.php" class="task-card">
            <span class="icon">📬</span>
            <span class="card-title">Simulasi RSA</span>
            <span class="card-desc">Kirim surat rahasia dengan enkripsi RSA</span>
        </a>
        <!-- Card: XOR Cipher -->
        <a href="xor-cipher.php" class="task-card">
            <span class="icon">🔐</span>
            <span class="card-title">XOR Cipher</span>
            <span class="card-desc">Enkripsi & dekripsi berbasis XOR</span>
        </a>
        <!-- Card: Caesar & Vigenère Cipher -->
        <a href="caesar-vigenere.php" class="task-card">
            <span class="icon">🔡</span>
            <span class="card-title">Caesar & Vigenère</span>
            <span class="card-desc">Enkripsi klasik Caesar & Vigenère</span>
        </a>
        <!-- Card: Verifikator Dokumen -->
        <a href="verifikator-dokumen.php" class="task-card">
            <span class="icon">🛡️</span>
            <span class="card-title">Verifikator Dokumen</span>
            <span class="card-desc">Digital signature, sign & verify</span>
        </a>
    </div>

    <!-- Info Session -->
    <?php if (!empty($_SESSION['history'])): ?>
    <div class="history">
        <h3>📌 Riwayat Terakhir</h3>
        <ul>
            <?php 
            $recent = array_slice(array_reverse($_SESSION['history']), 0, 3);
            foreach ($recent as $item): 
            ?>
                <li>
                    <span>FPB(<?= $item['a'] ?>, <?= $item['b'] ?>) = <b><?= $item['fpb'] ?></b></span>
                    <small><?= $item['waktu'] ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Creator -->
    <div class="creator">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Dibuat untuk memenuhi tugas Kriptografi</p>
    </div>
</div>

</body>
</html>