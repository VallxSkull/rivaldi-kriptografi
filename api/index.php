<!DOCTYPE html>
<html>
<head>
    <title>Kalkulator FPB Euclidean</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EEEEEE;
        }
        .container {
            width: 800px;
            margin: 150px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #1F6F5F;
        }
        label {
            color: #1F6F5F;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #2FA084;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #1F6F5F;
        }
        .hasil {
            margin-top: 20px;
            padding: 15px;
            background: #EEEEEE;
            border-left: 5px solid #2FA084;
            border-radius: 8px;
        }
        .proses {
            margin-top: 10px;
            font-size: 14px;
            color: #333;
        }
        .relatif {
            color: #2FA084;
            font-weight: bold;
        }
        .tidak {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Kalkulator FPB</h2>

    <form method="POST">
        <label>Angka 1 (A)</label>
        <input type="number" name="a" required>

        <label>Angka 2 (B)</label>
        <input type="number" name="b" required>

        <button type="submit">Hitung FPB</button>
    </form>

    <?php
    function fpb($m, $n, &$langkah) {
        while ($n != 0) {
            $kali = intdiv($m, $n);
            $sisa = $m % $n;

            $langkah[] = "$m = $kali × $n + $sisa";

            $m = $n;
            $n = $sisa;
        }
        return $m;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $a = $_POST['a'];
        $b = $_POST['b'];

        $langkah = [];
        $hasil = fpb($a, $b, $langkah);

        echo "<div class='hasil'>";
        echo "FPB dari <b>$a</b> dan <b>$b</b> adalah: <b>$hasil</b><br><br>";

        echo "<div class='proses'><b>Proses:</b><br>";
        foreach ($langkah as $step) {
            echo "$step<br>";
        }
        echo "</div><br>";

        if ($hasil == 1) {
            echo "<span class='relatif'>Keterangan: RELATIF PRIMA</span>";
        } else {
            echo "<span class='tidak'>Keterangan: TIDAK RELATIF PRIMA</span>";
        }

        echo "</div>";
    }
    ?>
</div>

</body>
</html>