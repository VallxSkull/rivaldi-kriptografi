<?php
session_start();
$current_page = 'tentang';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Algoritma Euclidean</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav-bar">
    <a href="index.php">🏠 Beranda</a>
    <a href="kalkulator-fpb.php">🧮 Kalkulator FPB</a>
    <a href="riwayat.php">📋 Riwayat</a>
    <a href="tentang.php" class="active">ℹ️ Tentang</a>
    <a href="simulasi-rsa.php">📬 Simulasi RSA</a>
    <a href="xor-cipher.php">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
</nav>

<div class="container">
    <h1>ℹ️ Algoritma Euclidean</h1>
    <p class="subtitle">Memahami cara kerja algoritma FPB</p>

    <div class="about-section">
        <p>
            <b>Algoritma Euclidean</b> adalah metode kuno yang efisien untuk mencari 
            <b>Faktor Persekutuan Terbesar (FPB)</b> dari dua bilangan bulat. 
            Algoritma ini ditemukan oleh matematikawan Yunani, <b>Euclid</b> (abad ke-3 SM).
        </p>

        <div class="highlight">
            <b>💡 Prinsip Utama:</b><br>
            FPB(a, b) = FPB(b, a mod b)<br>
            Proses diulang terus hingga sisa pembagian = 0. Bilangan terakhir yang bukan nol adalah FPB-nya.
        </div>

        <h3>🔍 Contoh Perhitungan</h3>
        <p>Mencari FPB dari <b>48</b> dan <b>18</b>:</p>
        <div class="proses">
            48 = 2 × 18 + 12<br>
            18 = 1 × 12 + 6<br>
            12 = 2 × 6 + 0<br>
            → <b>FPB = 6</b>
        </div>

        <h3 style="margin-top:18px;">🔒 Apa itu Relatif Prima?</h3>
        <p>
            Dua bilangan dikatakan <b>relatif prima</b> (koprima) jika FPB-nya = <b>1</b>.
            Artinya, kedua bilangan tersebut tidak memiliki faktor bersama selain 1. 
            Konsep ini sangat penting dalam <b>kriptografi</b>, khususnya pada algoritma RSA.
        </p>
    </div>

    <div class="creator">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Tugas Kriptografi — Algoritma Euclidean</p>
    </div>
</div>

</body>
</html>