<?php
session_start();
$current_page = 'caesar-vigenere';

// --- Fungsi Enkripsi & Dekripsi ---

function caesarCipher($text, $key, $encrypt = true) {
    $shift = $encrypt ? $key : -$key;
    $result = '';
    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $upper = ctype_upper($char);
            $base = $upper ? ord('A') : ord('a');
            $offset = ord($char) - $base;
            $new_offset = ($offset + $shift) % 26;
            if ($new_offset < 0) $new_offset += 26;
            $new_char = chr($base + $new_offset);
            $result .= $new_char;
        } else {
            $result .= $char;
        }
    }
    return $result;
}

function vigenereCipher($text, $key, $encrypt = true) {
    $result = '';
    $key_len = strlen($key);
    $key_index = 0;
    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $upper = ctype_upper($char);
            $base = $upper ? ord('A') : ord('a');
            $text_offset = ord($char) - $base;
            $key_char = $key[$key_index % $key_len];
            $key_offset = ord(strtoupper($key_char)) - ord('A');
            if ($encrypt) {
                $new_offset = ($text_offset + $key_offset) % 26;
            } else {
                $new_offset = ($text_offset - $key_offset) % 26;
                if ($new_offset < 0) $new_offset += 26;
            }
            $new_char = chr($base + $new_offset);
            $result .= $new_char;
            $key_index++;
        } else {
            $result .= $char;
        }
    }
    return $result;
}

// --- Inisialisasi Variabel ---
$result_text = '';
$error = '';
$plaintext = '';
$key_caesar = '';
$key_vigenere = '';
$algorithm = 'caesar';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $plaintext = $_POST['plaintext'] ?? '';
    $algorithm = $_POST['algorithm'] ?? 'caesar';
    $action = $_POST['action'];

    if ($algorithm === 'caesar') {
        $key_caesar = $_POST['key_caesar'] ?? '';
        if (!is_numeric($key_caesar) || $key_caesar === '') {
            $error = 'Key Caesar harus berupa angka.';
        } else {
            $key = (int)$key_caesar;
            $is_encrypt = ($action === 'encrypt');
            $result_text = caesarCipher($plaintext, $key, $is_encrypt);
        }
    } elseif ($algorithm === 'vigenere') {
        $key_vigenere = $_POST['key_vigenere'] ?? '';
        $key_clean = preg_replace('/[^A-Za-z]/', '', $key_vigenere);
        if ($key_clean === '') {
            $error = 'Key Vigenère harus terdiri dari huruf.';
        } else {
            $key_vigenere = $key_clean;
            $is_encrypt = ($action === 'encrypt');
            $result_text = vigenereCipher($plaintext, $key_vigenere, $is_encrypt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caesar & Vigenère Cipher</title>
    <link rel="stylesheet" href="style.css">
    <style>
        textarea,
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            background: #fafafa;
            transition: 0.2s;
            margin-bottom: 6px;
        }

        textarea:focus, input:focus {
            border-color: #2FA084;
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(47, 160, 132, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        .radio-group {
            display: flex;
            gap: 30px;
            margin: 10px 0 5px;
        }

        .radio-group label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: #1F6F5F;
            cursor: pointer;
            font-size: 15px;
        }

        input[type="radio"] {
            accent-color: #2FA084;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .key-field {
            margin-top: 12px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin: 28px 0 15px;
        }

        .btn-encrypt {
            flex: 1;
            padding: 14px 10px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            background: #2FA084;
            color: white;
            transition: 0.2s;
        }

        .btn-encrypt:hover {
            background: #1F6F5F;
        }

        .btn-decrypt {
            flex: 1;
            padding: 14px 10px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            background: #1F6F5F;
            color: white;
            transition: 0.2s;
        }

        .btn-decrypt:hover {
            background: #154e41;
        }

        .error {
            color: #c0392b;
            background: #fdedec;
            padding: 12px 18px;
            border-radius: 12px;
            margin: 15px 0 5px;
            font-weight: 500;
            border-left: 6px solid #e74c3c;
        }

        .result-box {
            background: #f8f9fa;
            border: 2px solid #cde3da;
            border-radius: 12px;
            padding: 14px 16px;
            font-weight: 500;
            color: #1a3b33;
            min-height: 90px;
            word-break: break-all;
            white-space: pre-wrap;
            margin-top: 6px;
        }

        .catatan {
            color: #1F6F5F;
            font-size: 13px;
            margin-top: 12px;
            text-align: center;
            opacity: 0.8;
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
    <a href="caesar-vigenere.php" class="active">🔡 Caesar & Vigenère</a>
    <a href="verifikator-dokumen.php">🛡️ Verifikator Dokumen</a>
</nav>

<div class="container">
    <h1>🔡 Enkripsi & Dekripsi Pesan</h1>
    <p class="subtitle">Caesar Cipher dan Vigenère Cipher</p>

    <form method="POST">
        <!-- Input Pesan -->
        <div class="form-group">
            <label>📝 Pesan (Plaintext / Ciphertext)</label>
            <textarea name="plaintext" placeholder="Masukkan pesan..."><?= htmlspecialchars($plaintext) ?></textarea>
        </div>

        <!-- Pilihan Algoritma -->
        <div class="form-group">
            <label>⚙️ Algoritma</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="algorithm" value="caesar" <?= $algorithm === 'caesar' ? 'checked' : '' ?>>
                    Caesar Cipher
                </label>
                <label>
                    <input type="radio" name="algorithm" value="vigenere" <?= $algorithm === 'vigenere' ? 'checked' : '' ?>>
                    Vigenère Cipher
                </label>
            </div>
        </div>

        <!-- Key Dinamis -->
        <div id="caesar-key" class="key-field" style="<?= $algorithm === 'vigenere' ? 'display:none;' : '' ?>">
            <label for="key_caesar">🔑 Key (Pergeseran Caesar)</label>
            <input type="number" name="key_caesar" id="key_caesar" min="0" step="1"
                   value="<?= htmlspecialchars($key_caesar) ?>" placeholder="Contoh: 3">
        </div>

        <div id="vigenere-key" class="key-field" style="<?= $algorithm === 'caesar' ? 'display:none;' : '' ?>">
            <label for="key_vigenere">🔑 Key (Kata Kunci Vigenère)</label>
            <input type="text" name="key_vigenere" id="key_vigenere"
                   value="<?= htmlspecialchars($key_vigenere) ?>" placeholder="Contoh: SECRET">
        </div>

        <!-- Tombol Aksi -->
        <div class="button-group">
            <button type="submit" name="action" value="encrypt" class="btn-encrypt">🔒 Enkripsi</button>
            <button type="submit" name="action" value="decrypt" class="btn-decrypt">🔓 Dekripsi</button>
        </div>

        <!-- Pesan Error -->
        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Hasil -->
        <div class="hasil">
            <label>📋 Hasil</label>
            <div class="result-box"><?= htmlspecialchars($result_text) ?></div>
        </div>
    </form>

    <div class="catatan">
        ※ Hanya huruf A-Z dan a-z yang diproses. Spasi, angka, dan karakter lain diabaikan.
    </div>

    <div class="creator">
        <p><strong>Creator:</strong> Rivaldi (231220056)</p>
        <p>Aplikasi Enkripsi & Dekripsi · Caesar & Vigenère Cipher</p>
        <p>Dibuat untuk memenuhi tugas Kriptografi</p>
    </div>
</div>

<script>
    const radioCaesar = document.querySelector('input[value="caesar"]');
    const radioVigenere = document.querySelector('input[value="vigenere"]');
    const caesarDiv = document.getElementById('caesar-key');
    const vigenereDiv = document.getElementById('vigenere-key');

    function toggleKeyFields() {
        if (radioCaesar.checked) {
            caesarDiv.style.display = 'block';
            vigenereDiv.style.display = 'none';
        } else {
            caesarDiv.style.display = 'none';
            vigenereDiv.style.display = 'block';
        }
    }

    radioCaesar.addEventListener('change', toggleKeyFields);
    radioVigenere.addEventListener('change', toggleKeyFields);
    toggleKeyFields();
</script>

</body>
</html>