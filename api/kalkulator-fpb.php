<?php
session_start();

/**
 * Menghitung FPB menggunakan algoritma Euclidean.
 * @param int $m Bilangan pertama
 * @param int $n Bilangan kedua
 * @param array &$langkah Array untuk menyimpan langkah-langkah
 * @return int FPB dari $m dan $n
 */
function fpb($m, $n, &$langkah) {
    $langkah = [];
    while ($n != 0) {
        $kali = intdiv($m, $n);
        $sisa = $m % $n;
        $langkah[] = "$m = $kali × $n + $sisa";
        $m = $n;
        $n = $sisa;
    }
    return $m;
}

$hasil = null;
$a_input = '';
$b_input = '';
$langkah = [];
$relatif_prima = null;

// Proses perhitungan
if (isset($_POST['hitung'])) {
    $a = abs((int)($_POST['a'] ?? 0));
    $b = abs((int)($_POST['b'] ?? 0));
    $a_input = $a;
    $b_input = $b;

    if ($a > 0 && $b > 0) {
        $hasil = fpb($a, $b, $langkah);
        $relatif_prima = ($hasil == 1);

        // Simpan ke session
        if (!isset($_SESSION['history'])) {
            $_SESSION['history'] = [];
        }
        $_SESSION['history'][] = [
            'a' => $a,
            'b' => $b,
            'fpb' => $hasil,
            'waktu' => date('H:i:s')
        ];

        // Batasi 8 item
        if (count($_SESSION['history']) > 8) {
            array_shift($_SESSION['history']);
        }
    }
}

$current_page = 'kalkulator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator FPB | Algoritma Euclidean</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav-bar">
    <a href="index.php">🏠 Beranda</a>
    <a href="kalkulator-fpb.php" class="active">🧮 Kalkulator FPB</a>
    <a href="riwayat.php">📋 Riwayat</a>
    <a href="tentang.php">ℹ️ Tentang</a>
    <a href="simulasi-rsa.php">📬 Simulasi RSA</a>
    <a href="xor-cipher.php">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php">🛡️ Verifikator Dokumen</a>
</nav>

<div class="container">
    <h1>🧮 Kalkulator FPB Euclidean</h1>
    <p class="subtitle">Masukkan dua bilangan untuk menghitung FPB</p>

    <!-- Form Input -->
    <form method="POST">
        <div class="form-group">
            <label>Angka 1 (A)</label>
            <input type="number" name="a" min="1" 
                   value="<?= htmlspecialchars($a_input ?: ($_POST['a'] ?? '')) ?>" 
                   placeholder="Masukkan bilangan pertama" required>
        </div>

        <div class="form-group">
            <label>Angka 2 (B)</label>
            <input type="number" name="b" min="1" 
                   value="<?= htmlspecialchars($b_input ?: ($_POST['b'] ?? '')) ?>" 
                   placeholder="Masukkan bilangan kedua" required>
        </div>

        <button type="submit" name="hitung" value="1" class="btn btn-primary">
            🔢 Hitung FPB
        </button>
    </form>

    <!-- Hasil Perhitungan -->
    <?php if ($hasil !== null): ?>
    <div class="hasil">
        <h3>✅ Hasil Perhitungan</h3>
        <p>FPB dari <b><?= $a_input ?></b> dan <b><?= $b_input ?></b> adalah: 
           <b style="font-size:1.3em;"><?= $hasil ?></b></p>

        <!-- Langkah-langkah -->
        <div class="proses">
            <b>📐 Proses Euclidean Algorithm:</b><br>
            <?php foreach ($langkah as $step): ?>
                <?= htmlspecialchars($step) ?><br>
            <?php endforeach; ?>
        </div>

        <!-- Status Relatif Prima -->
        <p style="margin-top:14px;">
            Status: 
            <?php if ($relatif_prima): ?>
                <span class="relatif">🔒 RELATIF PRIMA (FPB = 1)</span>
            <?php else: ?>
                <span class="tidak">🔓 TIDAK RELATIF PRIMA (FPB > 1)</span>
            <?php endif; ?>
        </p>

        <!-- Tombol aksi -->
        <div class="btn-group">
            <a href="kalkulator-fpb.php" class="btn btn-outline btn-sm">🔄 Hitung Lagi</a>
            <a href="riwayat.php" class="btn btn-outline btn-sm">📋 Lihat Riwayat</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="creator">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Kalkulator FPB - Algoritma Euclidean</p>
    </div>
</div>

</body>
</html>