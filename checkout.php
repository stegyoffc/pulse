<?php
// Získání dat z URL / session / POST (podle toho jak to máš řešené)
$cartJson = $_POST["cart_json"] ?? "[]";
$cart = json_decode($cartJson, true);

// Spočítání ceny
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item["price"] * $item["qty"];
}

// předvyplněné hodnoty
$name    = "";
$email   = "";
$phone   = "";
$street  = "";
$city    = "";
$zip     = "";
$country = "Česká republika";
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8" />
<title>PULSE | Objednávka merchu</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="index.css" />
</head>

<body class="light">
<header>
    <a href="index.html" class="logo">
        <img src="logo.jpg" alt="PULSE logo" />
        PULSE
    </a>

    <nav>
        <a href="index.html">Domů</a>
        <a href="koncerty.html">Koncerty</a>
        <a href="galerie.html">Galerie</a>
        <a href="kontakt.html">Kontakt</a>
        <a href="merch.html">Merch</a>
    </nav>

    <button id="themeToggle" aria-label="Přepnout motiv"></button>
</header>

<main class="checkout-container">

<h1>Objednávka merchu</h1>

<section class="cart-summary">
    <h2>Rekapitulace košíku</h2>

    <?php if (!$cart): ?>
        <p>Košík je prázdný.</p>
    <?php else: ?>
        <?php foreach ($cart as $item): ?>
            <p>
                <?= htmlspecialchars($item["title"]) ?>
                (<?= htmlspecialchars($item["size"]) ?>, <?= htmlspecialchars($item["color"]) ?>)
                – <?= $item["qty"] ?> ks,
                <?= $item["price"] * $item["qty"] ?> Kč
            </p>
        <?php endforeach; ?>

        <p><strong>Mezisoučet za merch: <?= $subtotal ?> Kč</strong></p>
    <?php endif; ?>
</section>

<!-- FORMULÁŘ – pouze 1x!!! -->
<section class="checkout-form">
<h2>Kontaktní údaje a doručení</h2>

<form action="process_order.php" method="POST">

    <!-- Hidden inputs -->
    <input type="hidden" name="products" value='<?= json_encode($cart, JSON_UNESCAPED_UNICODE) ?>'>
    <input type="hidden" name="price" value="<?= $subtotal ?>">

    <!-- Jméno -->
    <label>Jméno a příjmení*  
        <input type="text" name="name" value="<?= $name ?>" required>
    </label>

    <!-- Email -->
    <label>E-mail*  
        <input type="email" name="email" value="<?= $email ?>" required>
    </label>

    <!-- Telefon -->
    <label>Telefon  
        <input type="tel" name="phone" value="<?= $phone ?>">
    </label>

    <!-- Adresa -->
    <label>Ulice a číslo domu*  
        <input type="text" name="street" value="<?= $street ?>" required>
    </label>

    <label>Město*  
        <input type="text" name="city" value="<?= $city ?>" required>
    </label>

    <label>PSČ*  
        <input type="text" name="zip" value="<?= $zip ?>" required>
    </label>

    <label>Stát  
        <input type="text" name="country" value="<?= $country ?>" required>
    </label>

    <!-- Doprava -->
    <h3>Doprava</h3>

    <label>
        <input type="radio" name="shipping" value="Česká pošta – balík do ruky" required>
        Česká pošta – balík do ruky (129 Kč)
    </label>

    <label>
        <input type="radio" name="shipping" value="PPL" required>
        PPL (129 Kč)
    </label>

    <button type="submit">Odeslat objednávku a zobrazit platbu</button>

</form>
</section>

</main>

<footer>
    © 2025 PULSE  
    <a href="https://www.tiktok.com/@pulse_pilsen"></a>
    <a href="https://www.youtube.com/@pulse_pilsen"></a>
    <a href="https://www.instagram.com/pulse_pilsen/"></a>
</footer>

<script>
// light/dark toggle
const toggle = document.getElementById("themeToggle");
toggle.addEventListener("click", () => {
    document.body.classList.toggle("dark");
});
</script>

</body>
</html>
