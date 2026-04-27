<?php
declare(strict_types=1);

$hash = "";
$password = "";
$verifyHash = "";
$verifyResult = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = (string) ($_POST["action"] ?? "generate");
    $password = (string) ($_POST["password"] ?? "");

    if ($action === "generate" && $password !== "") {
        // Same approach as login/register flow.
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($action === "verify") {
        $verifyHash = (string) ($_POST["verify_hash"] ?? "");
        if ($password !== "" && $verifyHash !== "") {
            $verifyResult = password_verify($password, $verifyHash);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Kitengeneza Hash ya Nenosiri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        .result {
            margin-top: 20px;
            word-break: break-all;
            background: #eee;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Kitengeneza Hash ya Nenosiri</h2>
    <p>Tumia hapa kutengeneza hash, kisha sasisha <code>users.password_hash</code> kwenye hifadhidata yako.</p>

    <form method="POST">
        <input type="hidden" name="action" value="generate">
        <label>Weka Nenosiri:</label>
        <input type="text" name="password" required>
        <button type="submit">Tengeneza Hash</button>
    </form>

    <?php if (!empty($hash)): ?>
        <div class="result">
            <strong>Nenosiri Asili:</strong><br>
            <?php echo htmlspecialchars($password); ?><br><br>

            <strong>Hash Iliyotengenezwa (nakili hii):</strong><br>
            <textarea readonly rows="3" style="width:100%;margin-top:8px;"><?php echo htmlspecialchars($hash); ?></textarea>
        </div>
    <?php endif; ?>

    <hr style="margin: 28px 0;">
    <h3>Hakikisha Nenosiri Dhidi ya Hash Iliyopo</h3>
    <form method="POST">
        <input type="hidden" name="action" value="verify">
        <label>Nenosiri:</label>
        <input type="text" name="password" required>
        <label>Hash Iliyopo:</label>
        <textarea name="verify_hash" rows="3" style="width:100%;margin-top:10px;" required><?php echo htmlspecialchars($verifyHash); ?></textarea>
        <button type="submit">Hakikisha</button>
    </form>
    <?php if ($verifyResult !== null): ?>
        <div class="result">
            <strong>Matokeo ya Uhakiki:</strong><br>
            <?php echo $verifyResult ? "INALINGANA (nenosiri ni sahihi kwa hash hii)" : "HAILINGANI"; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>