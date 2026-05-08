<?php
session_start();
$current_page = 'verifikator-dokumen';

/**
 * =================================================
 *  WEB VERIFIKATOR DOKUMEN – DIGITAL SIGNATURE
 *  Menggunakan OpenSSL (RSA + SHA256)
 * =================================================
 */

// Deteksi ketersediaan OpenSSL
$hasOpenSSL = function_exists('openssl_pkey_new');
$engineError = '';

// Jika ada request untuk generate kunci baru
if (isset($_POST['generate_key'])) {
    if ($hasOpenSSL) {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        $res = openssl_pkey_new($config);
        if ($res) {
            openssl_pkey_export($res, $privPem);
            $pubPem = openssl_pkey_get_details($res)['key'];
            $_SESSION['sign_private'] = $privPem;
            $_SESSION['sign_public']  = $pubPem;
            $keyMessage = '✅ Kunci baru berhasil dibuat dan disimpan di sesi.';
        } else {
            $keyMessage = '❌ Gagal membuat kunci: ' . openssl_error_string();
        }
    } else {
        $keyMessage = '❌ Ekstensi OpenSSL tidak tersedia. Aktifkan extension=openssl di php.ini.';
    }
    header('Location: verifikator-dokumen.php');
    exit;
}

// Proses Sign (Tanda Tangani)
$signMessage = '';
$signResultText = '';
if (isset($_POST['sign'])) {
    $document = $_POST['document'] ?? '';
    if ($document === '') {
        $signMessage = '❌ Dokumen tidak boleh kosong.';
    } elseif (!isset($_SESSION['sign_private'])) {
        $signMessage = '❌ Belum ada kunci privat. Silakan generate key terlebih dahulu.';
    } else {
        $privateKey = $_SESSION['sign_private'];
        $signatureRaw = '';
        $ok = openssl_sign($document, $signatureRaw, $privateKey, OPENSSL_ALGO_SHA256);
        if ($ok) {
            $signatureBase64 = base64_encode($signatureRaw);
            // Simpan di session untuk nanti verifikasi (bisa juga tidak, user copy sendiri)
            $_SESSION['last_document'] = $document;
            $_SESSION['last_signature'] = $signatureBase64;
            $signResultText = "📄 Dokumen asli:\n" . $document . "\n\n🔏 Signature (base64):\n" . $signatureBase64;
            $signMessage = '✅ Dokumen berhasil ditandatangani. Salin signature di atas.';
        } else {
            $signMessage = '❌ Gagal menandatangani. ' . openssl_error_string();
        }
    }
}

// Proses Verify (Verifikasi)
$verifyMessage = '';
if (isset($_POST['verify'])) {
    $verifyDoc = $_POST['verify_doc'] ?? '';
    $verifySig = $_POST['verify_sig'] ?? '';
    $verifyPubKey = $_POST['verify_pubkey'] ?? '';
    if ($verifyDoc === '' || $verifySig === '' || $verifyPubKey === '') {
        $verifyMessage = '❌ Semua field harus diisi.';
    } else {
        // Hapus spasi, newline dari signature base64
        $verifySig = preg_replace('/\s+/', '', $verifySig);
        $signatureRaw = base64_decode($verifySig);
        if ($signatureRaw === false) {
            $verifyMessage = '❌ Signature tidak valid (base64 decode gagal).';
        } else {
            $publicKey = $verifyPubKey;
            $ok = openssl_verify($verifyDoc, $signatureRaw, $publicKey, OPENSSL_ALGO_SHA256);
            if ($ok === 1) {
                $verifyMessage = '✅ Dokumen VALID dan ASLI. Tanda tangan cocok.';
            } elseif ($ok === 0) {
                $verifyMessage = '❌ Dokumen TIDAK VALID atau telah DIMODIFIKASI.';
            } else {
                $verifyMessage = '❌ Error verifikasi: ' . openssl_error_string();
            }
        }
    }
}

// Ambil data dari session untuk convenience
$savedDoc = $_SESSION['last_document'] ?? '';
$savedSig = $_SESSION['last_signature'] ?? '';
$savedPubKey = $_SESSION['sign_public'] ?? '';

// Tab aktif (default tab1: generate key)
$activeTab = $_GET['tab'] ?? 'generate';
if (!in_array($activeTab, ['generate', 'sign', 'verify'])) {
    $activeTab = 'generate';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Verifikator Dokumen</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tab-container {
            border-bottom: 2px solid #ddd;
            margin-bottom: 25px;
        }
        .tab {
            display: inline-block;
            padding: 12px 20px;
            background: #f0f0f0;
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
            cursor: pointer;
            color: #555;
            text-decoration: none;
            font-weight: 500;
        }
        .tab.active {
            background: #2FA084;
            color: white;
        }
        .panel {
            display: none;
        }
        .panel.active {
            display: block;
        }
        textarea, input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            background: #2FA084;
            color: white;
            margin-top: 5px;
        }
        .btn:hover { background: #1F6F5F; }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .message.success { background: #e0f5e9; color: #1F6F5F; border-left: 4px solid #2FA084; }
        .message.error { background: #ffeaea; color: #c0392b; border-left: 4px solid #e74c3c; }
        .key-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 12px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-all;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .example-box {
            background: #fef9e7;
            border: 1px solid #f9e79f;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 14px;
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
    <a href="xor-cipher.php">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php" class="active">🛡️ Verifikator Dokumen</a>
</nav>

<div class="container">
    <h1>🛡️ Web Verifikator Dokumen</h1>
    <p class="subtitle">Digital Signature dengan RSA & SHA-256</p>

    <?php if (!$hasOpenSSL): ?>
        <div class="message error" style="margin-bottom:20px;">
            ❌ Ekstensi OpenSSL tidak tersedia. Aplikasi ini memerlukan <code>extension=openssl</code> diaktifkan di php.ini.
        </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="tab-container">
        <a href="?tab=generate" class="tab <?= $activeTab=='generate'?'active':'' ?>">1. Generate Key</a>
        <a href="?tab=sign" class="tab <?= $activeTab=='sign'?'active':'' ?>">2. Tanda Tangani (Sign)</a>
        <a href="?tab=verify" class="tab <?= $activeTab=='verify'?'active':'' ?>">3. Verifikasi Keaslian</a>
    </div>

    <!-- Panel 1: Generate Key -->
    <div class="panel <?= $activeTab=='generate'?'active':'' ?>" id="panel-generate">
        <h3>🔑 Bangkitkan Pasangan Kunci RSA</h3>
        <p>Kunci privat digunakan untuk menandatangani dokumen. Kunci publik digunakan untuk verifikasi.</p>
        <form method="post">
            <button type="submit" name="generate_key" class="btn" <?= !$hasOpenSSL?'disabled':'' ?>>Generate Key Baru</button>
        </form>
        <?php if (isset($keyMessage)): ?>
            <div class="message <?= strpos($keyMessage,'✅')!==false?'success':'error' ?>">
                <?= htmlspecialchars($keyMessage) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['sign_public'])): ?>
            <h4>📢 Public Key (bagikan ke pihak verifikator)</h4>
            <div class="key-box"><?= htmlspecialchars($_SESSION['sign_public']) ?></div>
            <h4>🔐 Private Key (RAHASIA, hanya untuk penanda tangan)</h4>
            <div class="key-box"><?= htmlspecialchars(substr($_SESSION['sign_private'],0,200)) ?>...</div>
            <small style="color:#888;">Private key lengkap disimpan di session server.</small>
        <?php else: ?>
            <p style="color:#999; margin-top:15px;">Belum ada kunci yang di-generate.</p>
        <?php endif; ?>
    </div>

    <!-- Panel 2: Sign -->
    <div class="panel <?= $activeTab=='sign'?'active':'' ?>" id="panel-sign">
        <h3>✍️ Tanda Tangani Dokumen</h3>
        <p>Masukkan teks dokumen yang ingin ditandatangani. Gunakan kunci privat yang sudah di-generate.</p>
        <form method="post">
            <label>📄 Dokumen (teks):</label>
            <textarea name="document" rows="4" placeholder="Contoh: Transfer ke Budi: Rp 100.000"><?= htmlspecialchars($savedDoc) ?></textarea>
            <button type="submit" name="sign" class="btn" <?= !$hasOpenSSL?'disabled':'' ?>>Tanda Tangani</button>
        </form>
        <?php if (isset($signMessage)): ?>
            <div class="message <?= strpos($signMessage,'✅')!==false?'success':'error' ?>"><?= nl2br(htmlspecialchars($signMessage)) ?></div>
        <?php endif; ?>
        <?php if ($signResultText): ?>
            <div class="key-box"><?= nl2br(htmlspecialchars($signResultText)) ?></div>
        <?php endif; ?>
        <div class="example-box">
            💡 <strong>Contoh simulasi:</strong> Tandatangani dokumen <em>"Transfer ke Budi: Rp 100.000"</em>.
            Hasil signature akan bisa digunakan di tab Verifikasi.
        </div>
    </div>

    <!-- Panel 3: Verify -->
    <div class="panel <?= $activeTab=='verify'?'active':'' ?>" id="panel-verify">
        <h3>🔍 Verifikasi Keaslian Dokumen</h3>
        <p>Masukkan dokumen (asli atau yang diragukan), signature (base64), dan public key penanda tangan.</p>
        <form method="post">
            <label>📄 Dokumen yang diuji:</label>
            <textarea name="verify_doc" rows="3" placeholder="Dokumen..."><?= htmlspecialchars($_POST['verify_doc'] ?? $savedDoc) ?></textarea>
            <label>🔏 Signature (base64):</label>
            <textarea name="verify_sig" rows="2" placeholder="Signature..."><?= htmlspecialchars($_POST['verify_sig'] ?? $savedSig) ?></textarea>
            <label>📢 Public Key:</label>
            <textarea name="verify_pubkey" rows="5" placeholder="Public key PEM..."><?= htmlspecialchars($_POST['verify_pubkey'] ?? $savedPubKey) ?></textarea>
            <button type="submit" name="verify" class="btn" <?= !$hasOpenSSL?'disabled':'' ?>>Verifikasi</button>
        </form>
        <?php if (isset($verifyMessage)): ?>
            <div class="message <?= strpos($verifyMessage,'✅')!==false?'success':'error' ?>"><?= htmlspecialchars($verifyMessage) ?></div>
        <?php endif; ?>
        <div class="example-box">
            💡 <strong>Uji modifikasi:</strong> Setelah menandatangani "Transfer ke Budi: Rp 100.000", ganti kata <em>Budi</em> menjadi <em>Andi</em> di field dokumen (jangan ubah signature). Lalu verifikasi. Sistem akan mendeteksi <strong style="color:red;">TIDAK VALID</strong>.
        </div>
    </div>

    <div class="creator" style="margin-top:40px;">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Web Verifikator Dokumen — Digital Signature</p>
    </div>
</div>

<script>
    // Jika tab diklik melalui link, halaman akan reload dengan parameter tab.
    // Tidak perlu JS tambahan.
</script>
</body>
</html>