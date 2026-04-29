<?php
declare(strict_types=1);

$hash = "";
$password = "";
$verifyHash = "";
$verifyResult = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "generate") {
        $password = trim($_POST["password"] ?? "");

        if ($password === "") {
            $error = "Weka nenosiri kwanza.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if ($action === "verify") {
        $password = trim($_POST["password"] ?? "");
        $verifyHash = trim($_POST["verify_hash"] ?? "");

        if ($password === "" || $verifyHash === "") {
            $error = "Jaza sehemu zote.";
        } else {
            $verifyResult = password_verify($password, $verifyHash);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Password Hash Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }
        .container {
            max-width: 520px;
            margin: auto;
            background: #fff;
            padding: 24px;
            border-radius: 10px;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        .result {
            margin-top: 20px;
            background: #eee;
            padding: 12px;
            border-radius: 6px;
            word-break: break-all;
        }
        .error {
            margin-top: 15px;
            color: red;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Password Hash Generator</h2>
    <p>Generate a secure hash and store it in <code>users.password_hash</code>.</p>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- GENERATE -->
    <form method="POST">
        <input type="hidden" name="action" value="generate">
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Generate Hash</button>
    </form>

    <?php if ($hash): ?>
        <div class="result">
            <strong>Generated Hash (copy this):</strong><br>
            <textarea readonly rows="3"><?php echo htmlspecialchars($hash); ?></textarea>
        </div>
    <?php endif; ?>

    <hr style="margin: 30px 0;">

    <!-- VERIFY -->
    <h3>Verify Password</h3>
    <form method="POST">
        <input type="hidden" name="action" value="verify">
        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Hash:</label>
        <textarea name="verify_hash" rows="3" required><?php echo htmlspecialchars($verifyHash); ?></textarea>

        <button type="submit">Verify</button>
    </form>

    <?php if ($verifyResult !== null): ?>
        <div class="result">
            <?php if ($verifyResult): ?>
                <span class="success">MATCH ✅ (Password is correct)</span>
            <?php else: ?>
                <span style="color:red;">NO MATCH ❌</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>