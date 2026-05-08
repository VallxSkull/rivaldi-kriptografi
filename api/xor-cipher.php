<?php
session_start();
$current_page = 'xor-cipher';

// ============================================================
//  LOGIKA XOR
// ============================================================
function xorEncryptDecrypt($data, $key) {
    $out = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($data); $i++) {
        $out .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
    }
    return $out;
}

$hasil = '';
$error = '';
$pesan = $_POST['pesan'] ?? '';
$kunci = $_POST['kunci'] ?? '';
$aksi  = $_POST['aksi'] ?? 'enkripsi';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses'])) {
    if ($pesan === '' || $kunci === '') {
        $error = 'Pesan dan kata kunci wajib diisi.';
    } else {
        if ($aksi === 'enkripsi') {
            // Enkripsi: plain -> hex
            $cipherRaw = xorEncryptDecrypt($pesan, $kunci);
            $hasil = bin2hex($cipherRaw);
        } else {
            // Dekripsi: hex -> plain
            $hex = preg_replace('/\s+/', '', $pesan); // hapus spasi
            if (!ctype_xdigit($hex)) {
                $error = 'Format cipher tidak valid (gunakan heksadesimal).';
            } else {
                $cipherRaw = hex2bin($hex);
                $plain = xorEncryptDecrypt($cipherRaw, $kunci);
                $hasil = $plain;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XOR Cipher — Enkripsi & Dekripsi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        textarea, input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .btn-process {
            background: #2FA084;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-process:hover {
            background: #1F6F5F;
        }
        .result-box {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            font-size: 16px;
            min-height: 50px;
            margin-top: 10px;
        }
        .error {
            color: #e74c3c;
            background: #ffeaea;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<nav class="nav-bar">
    <a href="index.php">🏠 Beranda</a>
    <a href="kalkulator-fpb.php">🧮 Kalkulator FPB</a>
    <a href="riwayat.php">📋 Riwayat</a>
    <a href="tentang.php">ℹ️ Tentang</a>
    <a href="simulasi-rsa.php">📬 Simulasi RSA</a>
    <a href="xor-cipher.php" class="active">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php">🛡️ Verifikator Dokumen</a>
    
</nav>

<div class="container">
    <h1>🔐 Aplikasi Enkripsi & Dekripsi XOR</h1>
    <p class="subtitle">Enkripsi atau dekripsi teks menggunakan kunci berbasis XOR</p>

    <form method="post">
        <label><strong>Masukkan Pesan (Teks Asli / Cipher Hex):</strong></label>
        <input type="text" name="pesan" value="<?= htmlspecialchars($pesan) ?>" 
               placeholder="Plaintext atau hex cipher..." required>

        <label><strong>Kata Kunci (Key):</strong></label>
        <input type="text" name="kunci" value="<?= htmlspecialchars($kunci) ?>" 
               placeholder="Masukkan kunci rahasia..." required>

        <label><strong>Pilih Aksi:</strong></label>
        <select name="aksi" style="width:100%; padding:10px; border-radius:6px; margin-bottom:20px;">
            <option value="enkripsi" <?= $aksi === 'enkripsi' ? 'selected' : '' ?>>Enkripsi (Plain → Cipher Hex)</option>
            <option value="dekripsi" <?= $aksi === 'dekripsi' ? 'selected' : '' ?>>Dekripsi (Cipher Hex → Plain)</option>
        </select>

        <button type="submit" name="proses" class="btn-process">Proses Kriptografi</button>
    </form>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($hasil !== '' && !$error): ?>
        <h3 style="margin-top:25px;">Hasil Proses:</h3>
        <div class="result-box"><?= htmlspecialchars($hasil) ?></div>
    <?php endif; ?>

    <div class="creator" style="margin-top:40px;">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Tugas Kriptografi — XOR Cipher</p>
    </div>
</div>

</body>
</html>