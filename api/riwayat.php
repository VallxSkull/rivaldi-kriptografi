<?php
session_start();

// Reset riwayat jika diminta
if (isset($_POST['reset'])) {
    unset($_SESSION['history']);
    header('Location: riwayat.php');
    exit;
}

$current_page = 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Perhitungan FPB</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav-bar">
    <a href="index.php">🏠 Beranda</a>
    <a href="kalkulator-fpb.php">🧮 Kalkulator FPB</a>
    <a href="riwayat.php" class="active">📋 Riwayat</a>
    <a href="tentang.php">ℹ️ Tentang</a>
    <a href="simulasi-rsa.php">📬 Simulasi RSA</a>
    <a href="xor-cipher.php">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php">🛡️ Verifikator Dokumen</a>
</nav>

<div class="container">
    <h1>📋 Riwayat Perhitungan</h1>
    <p class="subtitle">Catatan perhitungan FPB yang telah dilakukan</p>

    <?php if (!empty($_SESSION['history'])): ?>
        <div class="history">
            <ul>
                <?php foreach (array_reverse($_SESSION['history']) as $item): ?>
                    <li>
                        <span>
                            FPB(<b><?= $item['a'] ?></b>, <b><?= $item['b'] ?></b>) = 
                            <b style="color:#2FA084;"><?= $item['fpb'] ?></b>
                            <?php if ($item['fpb'] == 1): ?>
                                <span class="relatif" style="font-size:12px;">(Relatif Prima)</span>
                            <?php endif; ?>
                        </span>
                        <small><?= $item['waktu'] ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form method="POST" style="margin-top:16px; text-align:center;">
            <button type="submit" name="reset" value="1" class="btn btn-secondary">
                🗑️ Hapus Semua Riwayat
            </button>
        </form>
    <?php else: ?>
        <div class="empty-state">
            <span class="icon">📭</span>
            <p>Belum ada riwayat perhitungan.</p>
            <a href="kalkulator-fpb.php" class="btn btn-primary" style="margin-top:12px; display:inline-block; width:auto; padding:10px 20px;">
                ➕ Mulai Menghitung
            </a>
        </div>
    <?php endif; ?>

    <div class="creator">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Riwayat disimpan sementara dalam sesi browser</p>
    </div>
</div>

</body>
</html>