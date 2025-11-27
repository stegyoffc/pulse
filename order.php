<?php
// Připojení k databázi
$servername = "185.129.138.43";
$username = "f193179";      // Pokud máš vlastní uživatelské jméno, změň to
$password = "9D6aM@Em";    // Změň na svoje heslo
$dbname = "f193179";        // Název databáze – podle screenshotu

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Data z formuláře
$variable_symbol = uniqid();  
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$items = $_POST['items'];   // např. "Tričko S × 2"
$total_price = $_POST['total_price'];
$shipping = $_POST['shipping'];

// SQL dotaz
$sql = "INSERT INTO orders (variable_symbol, name, email, phone, address, items, total_price, shipping)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssds", $variable_symbol, $name, $email, $phone, $address, $items, $total_price, $shipping);

$stmt->execute();
$stmt->close();
$conn->close();

// Přesměrování zpět nebo potvrzení
header("Location: diky.html");
exit();
?>
