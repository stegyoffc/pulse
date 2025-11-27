<?php
// Debug mód – zobrazí chyby (v produkci klidně vypni)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------------------------
// 1) Databázové připojení
// ----------------------------------------
$db_host = "a043um.forpsi.com";
$db_name = "f193179";
$db_user = "f193179";
$db_pass = "9D6aM@Em";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "msg" => "DB error: " . $conn->connect_error]));
}

// ----------------------------------------
// 2) Musí být POST request
// ----------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "msg" => "Invalid request method"]);
    exit;
}

// ----------------------------------------
// 3) Přijetí dat z POST
// ----------------------------------------
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$products = trim($_POST['products'] ?? '');
$price    = trim($_POST['price'] ?? '');
$shipping = trim($_POST['shipping'] ?? '');

// Kontroly
if ($name === '' || $email === '' || $address === '') {
    echo json_encode(["status" => "error", "msg" => "Chybí údaje (jméno, email nebo adresa)."]);
    exit;
}

// Pokud price není číslo, dáme 0
if ($price === '' || !is_numeric($price)) {
    $price = 0;
}

// ----------------------------------------
// 4) Generování ID a variabilního symbolu
// ----------------------------------------
$order_id = "P" . date("ymdHis"); // např. P250126112055
$variable_symbol = $order_id;

// ----------------------------------------
// 5) Uložení do databáze podle tvé struktury
// ----------------------------------------
$sql = "
    INSERT INTO orders 
    (order_id, variable_symbol, name, email, phone, address, products, price, shipping, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssssssis",
    $order_id,
    $variable_symbol,
    $name,
    $email,
    $phone,
    $address,
    $products,
    $price,
    $shipping
);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "msg" => "DB insert error: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// ----------------------------------------
// 6) Odeslání emailu přes Resend
// ----------------------------------------
$apiKey = "re_YhfAKtZz_LZZZqo4aZX8X7NtSRPnE6eMF";
$url = "https://api.resend.com/emails";

function sendEmail($data, $apiKey, $url) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ["success" => false, "error" => $error];
    }

    return ["success" => true, "response" => $response];
}

// Text emailu
$htmlMessage = "
    <h2>Děkujeme za objednávku!</h2>
    <p><strong>Objednávka:</strong> $order_id</p>
    <p><strong>Variabilní symbol:</strong> $variable_symbol</p>
    <p><strong>Jméno:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Telefon:</strong> $phone</p>
    <p><strong>Adresa:</strong> $address</p>
    <p><strong>Produkty:</strong> $products</p>
    <p><strong>Doprava:</strong> $shipping</p>
    <p><strong>Částka:</strong> $price Kč</p>
    <br>
    <p>Tým PULSE ❤️</p>
";

// Data pro Resend
$mailData = [
    "from" => "PULSE <onboarding@resend.dev>",
    "to"   => [$email],
    "bcc"  => ["pulseteam@seznam.cz"],
    "subject" => "Potvrzení objednávky – $order_id",
    "html" => $htmlMessage
];

$emailResult = sendEmail($mailData, $apiKey, $url);

// ----------------------------------------
// 7) Odpověď pro frontend
// ----------------------------------------
if ($emailResult["success"]) {
    echo json_encode(["status" => "ok", "order_id" => $order_id]);
} else {
    echo json_encode([
        "status" => "warning",
        "order_id" => $order_id,
        "msg" => "Objednávka uložena, ale e-mail se nepodařilo odeslat: " . $emailResult["error"]
    ]);
}

$conn->close();
?>
