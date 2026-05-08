<?php
session_start();

/**
 * ============================================
 * SIMULASI KIRIM SURAT DENGAN ALGORITMA RSA
 * ============================================
 * Prioritas: OpenSSL, fallback: BCMath.
 * Jika OpenSSL gagal generate kunci, otomatis
 * beralih ke BCMath (tanpa crash).
 */

// ============================================================
//  KELAS RSA DENGAN BCMATH (FALLBACK)
// ============================================================
class SimpleRSA {
    private $n, $e, $d;

    public function __construct($bits = 256) {
        $this->generateKeys($bits);
    }

    private function generateKeys($bits) {
        $p = $this->generatePrime(intval($bits/2));
        $q = $this->generatePrime(intval($bits/2));
        $this->n = bcmul($p, $q);
        $phi = bcmul(bcsub($p, 1), bcsub($q, 1));
        $this->e = '65537';
        while (bccomp($this->gcd($this->e, $phi), '1') != 0) {
            $this->e = bcadd($this->e, 2);
        }
        $this->d = $this->modInverse($this->e, $phi);
    }

    private function generatePrime($bits) {
        $min = bcpow('2', strval($bits-1));
        $max = bcsub(bcpow('2', strval($bits)), '1');
        while (true) {
            $num = bcadd($min, $this->randomBigInt(bcsub($max, $min)));
            if (bccomp(bcmod($num, '2'), '0') == 0) {
                $num = bcadd($num, '1');
            }
            if ($this->isPrime($num)) return $num;
        }
    }

    private function randomBigInt($max) {
        $len = strlen($max);
        $rand = '0';
        for ($i=0; $i<$len; $i++) {
            $rand .= rand(0,9);
        }
        return bcmod($rand, $max);
    }

    private function isPrime($num, $k=5) {
        if (bccomp($num, '2') < 0) return false;
        if (bccomp($num, '2') == 0) return true;
        if (bcmod($num, '2') == '0') return false;
        $d = bcsub($num, '1');
        $s = 0;
        while (bcmod($d, '2') == '0') {
            $d = bcdiv($d, '2');
            $s++;
        }
        for ($i=0; $i<$k; $i++) {
            $a = bcadd('2', $this->randomBigInt(bcsub($num,'4')));
            $x = bcpowmod($a, $d, $num);
            if (bccomp($x, '1') == 0 || bccomp($x, bcsub($num,'1')) == 0) continue;
            for ($r=1; $r<$s; $r++) {
                $x = bcpowmod($x, '2', $num);
                if (bccomp($x, '1') == 0) return false;
                if (bccomp($x, bcsub($num,'1')) == 0) break;
            }
            if ($r == $s) return false;
        }
        return true;
    }

    private function gcd($a, $b) {
        while (bccomp($b, '0') != 0) {
            $t = $b;
            $b = bcmod($a, $b);
            $a = $t;
        }
        return $a;
    }

    private function modInverse($e, $phi) {
        $x = '0'; $y = '1'; $u = '1'; $v = '0';
        $a = $e; $b = $phi;
        while (bccomp($a, '0') != 0) {
            $q = bcdiv($b, $a, 0);
            $r = bcmod($b, $a);
            $m = bcsub($x, bcmul($u, $q));
            $n = bcsub($y, bcmul($v, $q));
            $b = $a; $a = $r; $x = $u; $y = $v; $u = $m; $v = $n;
        }
        return bcmod($x, $phi);
    }

    public function getPublicKey() {
        return ['n' => $this->n, 'e' => $this->e];
    }

    public function getPrivateKey() {
        return ['n' => $this->n, 'd' => $this->d];
    }

    public function encrypt($plaintext, $pubKey) {
        $m = $this->stringToBigInt($plaintext);
        return bcpowmod($m, $pubKey['e'], $pubKey['n']);
    }

    public function decrypt($cipherInt, $privKey) {
        $m = bcpowmod($cipherInt, $privKey['d'], $privKey['n']);
        return $this->bigIntToString($m);
    }

    private function stringToBigInt($str) {
        $hex = bin2hex($str);
        return $this->hexToDec($hex);
    }

    private function bigIntToString($dec) {
        $hex = $this->decToHex($dec);
        if (strlen($hex) % 2 != 0) $hex = '0'.$hex;
        return hex2bin($hex);
    }

    private function hexToDec($hex) {
        $dec = '0';
        $len = strlen($hex);
        for ($i=0; $i<$len; $i++) {
            $dec = bcadd(bcmul($dec, '16'), base_convert($hex[$i], 16, 10));
        }
        return $dec;
    }

    private function decToHex($dec) {
        $hex = '';
        while (bccomp($dec, '0') > 0) {
            $rem = bcmod($dec, '16');
            $hex = base_convert($rem, 10, 16) . $hex;
            $dec = bcdiv($dec, '16', 0);
        }
        return $hex ?: '0';
    }
}

// ============================================================
//  DETEKSI & INISIALISASI ENGINE
// ============================================================
$engine = '';
$keygenError = '';

// Cek ketersediaan ekstensi
$hasOpenSSL = function_exists('openssl_pkey_new');
$hasBcmath  = function_exists('bcmul');

if ($hasOpenSSL) {
    // Coba generate kunci, jika gagal kita pakai BCMath
    $keyRes = @openssl_pkey_new([
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);
    if ($keyRes !== false) {
        $engine = 'openssl';
        // Simpan resource di session? Tidak, kita simpan PEM string saja
        if (!isset($_SESSION['alice_private_key'])) {
            openssl_pkey_export($keyRes, $privPem);
            $pubPem = openssl_pkey_get_details($keyRes)['key'];
            $_SESSION['alice_private_key'] = $privPem;
            $_SESSION['alice_public_key']  = $pubPem;
        }
    } else {
        // OpenSSL gagal, catat error
        $keygenError = openssl_error_string();
        if ($hasBcmath) {
            $engine = 'bcmath';
        }
    }
} elseif ($hasBcmath) {
    $engine = 'bcmath';
}

// Jika engine masih kosong, berarti tidak ada yang bisa digunakan
if ($engine === '') {
    header('Content-Type: text/html; charset=utf-8');
    die('
    <div style="max-width:600px;margin:50px auto;background:white;padding:30px;border-radius:12px;font-family:Arial;">
        <h2 style="color:#d9534f;">❌ Tidak dapat menjalankan simulasi RSA</h2>
        <p>Server tidak memiliki ekstensi yang dibutuhkan.</p>
        <ul>
            <li><b>OpenSSL</b>: ' . ($hasOpenSSL ? '✅ tersedia tetapi gagal: ' . $keygenError : '❌ tidak ada') . '</li>
            <li><b>BCMath</b>: ' . ($hasBcmath ? '✅ tersedia' : '❌ tidak ada') . '</li>
        </ul>
        <p>Aktifkan <code>extension=openssl</code> atau <code>extension=bcmath</code> di php.ini, lalu restart server.</p>
    </div>');
}

// Jika BCMath terpilih, inisialisasi kunci di session
if ($engine === 'bcmath' && !isset($_SESSION['alice_rsa_keys'])) {
    $rsa = new SimpleRSA(256);
    $_SESSION['alice_rsa_keys'] = [
        'private' => $rsa->getPrivateKey(),
        'public'  => $rsa->getPublicKey(),
    ];
}

// Tampilan public key
if ($engine === 'openssl') {
    $publicKeyDisplay = $_SESSION['alice_public_key'];
} else {
    $keys = $_SESSION['alice_rsa_keys']['public'];
    $publicKeyDisplay = "n = {$keys['n']}\ne = {$keys['e']}";
}

// ============================================================
//  PROSES FORM
// ============================================================
$pesanBob     = '';
$cipherBase64 = '';
$pesanDekripsi= '';
$errorPesan   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_pesan'])) {
    $pesanBob = trim($_POST['pesan_bob'] ?? '');
    if ($pesanBob === '') {
        $errorPesan = 'Pesan tidak boleh kosong.';
    } else {
        if ($engine === 'openssl') {
            $cipherRaw = '';
            $ok = @openssl_public_encrypt($pesanBob, $cipherRaw, $_SESSION['alice_public_key'], OPENSSL_PKCS1_PADDING);
            if ($ok) {
                $cipherBase64 = base64_encode($cipherRaw);
                $decRaw = '';
                openssl_private_decrypt($cipherRaw, $decRaw, $_SESSION['alice_private_key'], OPENSSL_PKCS1_PADDING);
                $pesanDekripsi = $decRaw;
            } else {
                $errorPesan = 'Gagal enkripsi. Mungkin pesan terlalu panjang (maks. 214 byte).';
            }
        } else { // bcmath
            $rsaDec = new SimpleRSA(); // hanya untuk panggil method
            $pubKey = $_SESSION['alice_rsa_keys']['public'];
            $privKey = $_SESSION['alice_rsa_keys']['private'];
            try {
                $cipherInt = $rsaDec->encrypt($pesanBob, $pubKey);
                $cipherBase64 = base64_encode($cipherInt);
                $pesanDekripsi = $rsaDec->decrypt($cipherInt, $privKey);
            } catch (Exception $e) {
                $errorPesan = 'Gagal enkripsi: ' . $e->getMessage();
            }
        }
    }
}

// Reset kunci
if (isset($_POST['reset_kunci'])) {
    unset($_SESSION['alice_private_key'], $_SESSION['alice_public_key'], $_SESSION['alice_rsa_keys']);
    header('Location: simulasi-rsa.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Kirim Surat RSA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .key-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
            margin: 10px 0;
        }
        .step {
            background: #eef6f3;
            border-left: 5px solid #2FA084;
            padding: 15px 18px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .cipher-display {
            background: #1e1e1e;
            color: #0f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
        }
        .pesan-asli {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
        }
        .error {
            color: #e74c3c;
            background: #ffeaea;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .engine-info {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            background: #fefefe;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<nav class="nav-bar">
    <a href="index.php">🏠 Beranda</a>
    <a href="kalkulator-fpb.php">🧮 Kalkulator FPB</a>
    <a href="riwayat.php">📋 Riwayat</a>
    <a href="tentang.php">ℹ️ Tentang</a>
    <a href="simulasi-rsa.php" class="active">📬 Simulasi RSA</a>
    <a href="xor-cipher.php">🔐 XOR Cipher</a>
    <a href="caesar-vigenere.php">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php">🛡️ Verifikator Dokumen</a>
</nav>

<div class="container">
    <h1>📬 Simulasi Kirim Surat Rahasia (RSA)</h1>
    <p class="subtitle">Bob mengirim pesan terenkripsi → Alice membaca dengan kunci privat</p>
    <div class="engine-info">
        ⚙️ Engine: <strong><?= $engine === 'openssl' ? 'OpenSSL 2048-bit' : 'BCMath 256-bit' ?></strong>
    </div>

    <?php if ($keygenError && $engine==='bcmath'): ?>
        <div style="background:#fff3cd;padding:8px 12px;border-radius:6px;margin-bottom:15px;font-size:13px;">
            ⚠️ OpenSSL gagal membuat kunci: <em><?= htmlspecialchars($keygenError) ?></em>. Otomatis beralih ke BCMath.
        </div>
    <?php endif; ?>

    <!-- PUBLIC KEY ALICE -->
    <div class="step">
        <h3>🔑 Langkah 1: Public Key Alice</h3>
        <p>Alice membagikan kunci publiknya (boleh dilihat siapa saja).</p>
        <div class="key-box"><?= htmlspecialchars($publicKeyDisplay) ?></div>
        <form method="post">
            <button type="submit" name="reset_kunci" class="btn btn-secondary btn-sm" style="margin-top:8px;">🔄 Generate Ulang Kunci</button>
        </form>
    </div>

    <!-- BOB ENKRIPSI -->
    <div class="step">
        <h3>✉️ Langkah 2: Bob Kirim Pesan Terenkripsi</h3>
        <form method="post">
            <div class="form-group">
                <label>Pesan rahasia Bob:</label>
                <textarea name="pesan_bob" rows="3" maxlength="200" placeholder="Tulis pesan..."><?= htmlspecialchars($pesanBob) ?></textarea>
            </div>
            <button type="submit" name="kirim_pesan" class="btn btn-primary">🔐 Enkripsi & Kirim</button>
        </form>
        <?php if ($errorPesan): ?>
            <div class="error"><?= $errorPesan ?></div>
        <?php endif; ?>
        <?php if ($cipherBase64): ?>
            <label>📦 Ciphertext (base64):</label>
            <div class="cipher-display"><?= htmlspecialchars($cipherBase64) ?></div>
            <p style="font-size:13px;color:#666;">Ini yang akan dilihat penyadap.</p>
        <?php endif; ?>
    </div>

    <!-- ALICE DEKRIPSI -->
    <?php if ($pesanDekripsi): ?>
    <div class="step">
        <h3>🔓 Langkah 3: Alice Membaca Pesan Asli</h3>
        <p>Alice mendekripsi dengan <strong>kunci privat</strong>-nya.</p>
        <div class="pesan-asli">📝 « <?= htmlspecialchars($pesanDekripsi) ?> »</div>
    </div>
    <?php endif; ?>

    <div class="creator">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Simulasi RSA - Tugas Kriptografi</p>
    </div>
</div>

</body>
</html>