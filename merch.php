<?php
// -------------------------------------------
// NASTAVENÍ
// -------------------------------------------

// Ceny produktů
$PRICE_SHIRT_MAIN = 300;   // tričko classic
$PRICE_SHIRT_ALT  = 300;   // tričko alternate
$PRICE_HOODIE     = 600;   // mikina
$PRICE_BRACELET   = 50;    // náramek

// Ceny dopravy
$SHIPPING_OPTIONS = [
    'posta' => [
        'label' => 'Česká pošta – balík do ruky',
        'price' => 129
    ],
    'osobne' => [
        'label' => 'Osobní předání v Plzni',
        'price' => 0
    ],
];

// Účet
$ACCOUNT_IBAN   = 'CZ7520100000002703371085';
$ACCOUNT_NUMBER = '2703371085';
$BANK_CODE      = '2010';

// Log soubor
$LOG_FILE = __DIR__ . '/orders.log';

// -------------------------------------------
// PŘEVZETÍ VÝBĚRU Z merch.html
// -------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: merch.html');
    exit;
}

// step 1 = z merch.html (jen výběr), step 2 = po vyplnění formuláře
$step = isset($_POST['step']) && $_POST['step'] === '2' ? 2 : 1;

// Produkty z merch.html – tričko classic
$shirtMainS  = (int)($_POST['shirt_main_s']  ?? 0);
$shirtMainM  = (int)($_POST['shirt_main_m']  ?? 0);
$shirtMainL  = (int)($_POST['shirt_main_l']  ?? 0);
$shirtMainXL = (int)($_POST['shirt_main_xl'] ?? 0);

// Tričko alternate
$shirtAltS   = (int)($_POST['shirt_alt_s']  ?? 0);
$shirtAltM   = (int)($_POST['shirt_alt_m']  ?? 0);
$shirtAltL   = (int)($_POST['shirt_alt_l']  ?? 0);
$shirtAltXL  = (int)($_POST['shirt_alt_xl'] ?? 0);

// Mikina
$hoodieS     = (int)($_POST['hoodie_s']  ?? 0);
$hoodieM     = (int)($_POST['hoodie_m']  ?? 0);
$hoodieL     = (int)($_POST['hoodie_l']  ?? 0);
$hoodieXL    = (int)($_POST['hoodie_xl'] ?? 0);

// Náramky
$braceletQty = (int)($_POST['bracelet_qty'] ?? 0);

$itemsSelected = (
    $shirtMainS + $shirtMainM + $shirtMainL + $shirtMainXL +
    $shirtAltS  + $shirtAltM  + $shirtAltL  + $shirtAltXL  +
    $hoodieS    + $hoodieM    + $hoodieL    + $hoodieXL    +
    $braceletQty
);

if ($itemsSelected <= 0) {
    // Někdo přišel bez výběru → zpět na merch
    header('Location: merch.html');
    exit;
}

// Proměnné pro kontakt
$name = $email = $phone = $street = $city = $zip = $country = '';
$shipping = '';
$orderSummary = '';
$orderId = '';
$orderVs = '';
$total = 0;
$qrUrl = '';
$orderCreated = false;
$errors = [];

// -------------------------------------------
// STEP 2 – zpracování formuláře (kontaktní údaje)
// -------------------------------------------

if ($step === 2) {
    $name    = trim($_POST['name']  ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $street  = trim($_POST['street'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');
    $country = trim($_POST['country'] ?? 'Česká republika');
    $shipping = $_POST['shipping'] ?? '';

    if ($name === '')   $errors[] = 'Zadej prosím jméno a příjmení.';
    if ($email === '')  $errors[] = 'Zadej prosím e-mail.';
    if ($street === '' || $city === '' || $zip === '') {
        $errors[] = 'Vyplň prosím celou adresu (ulice, město, PSČ).';
    }
    if (!array_key_exists($shipping, $SHIPPING_OPTIONS)) {
        $errors[] = 'Vyber prosím způsob dopravy.';
    }

    // Výpočet ceny produktů
    $merchTotal = 0;

    $merchLines = [];

    // Tričko classic
    $shirtMainTotalCount = $shirtMainS + $shirtMainM + $shirtMainL + $shirtMainXL;
    if ($shirtMainTotalCount > 0) {
        $price = $shirtMainTotalCount * $PRICE_SHIRT_MAIN;
        $sizes = [];
        if ($shirtMainS)  $sizes[] = "S × {$shirtMainS}";
        if ($shirtMainM)  $sizes[] = "M × {$shirtMainM}";
        if ($shirtMainL)  $sizes[] = "L × {$shirtMainL}";
        if ($shirtMainXL) $sizes[] = "XL × {$shirtMainXL}";
        $merchLines[] = "- Tričko PULSE classic (" . implode(', ', $sizes) . ") – {$price} Kč";
        $merchTotal += $price;
    }

    // Tričko alternate
    $shirtAltTotalCount = $shirtAltS + $shirtAltM + $shirtAltL + $shirtAltXL;
    if ($shirtAltTotalCount > 0) {
        $price = $shirtAltTotalCount * $PRICE_SHIRT_ALT;
        $sizes = [];
        if ($shirtAltS)  $sizes[] = "S × {$shirtAltS}";
        if ($shirtAltM)  $sizes[] = "M × {$shirtAltM}";
        if ($shirtAltL)  $sizes[] = "L × {$shirtAltL}";
        if ($shirtAltXL) $sizes[] = "XL × {$shirtAltXL}";
        $merchLines[] = "- Tričko PULSE alternate (" . implode(', ', $sizes) . ") – {$price} Kč";
        $merchTotal += $price;
    }

    // Mikina
    $hoodieTotalCount = $hoodieS + $hoodieM + $hoodieL + $hoodieXL;
    if ($hoodieTotalCount > 0) {
        $price = $hoodieTotalCount * $PRICE_HOODIE;
        $sizes = [];
        if ($hoodieS)  $sizes[] = "S × {$hoodieS}";
        if ($hoodieM)  $sizes[] = "M × {$hoodieM}";
        if ($hoodieL)  $sizes[] = "L × {$hoodieL}";
        if ($hoodieXL) $sizes[] = "XL × {$hoodieXL}";
        $merchLines[] = "- Mikina PULSE (" . implode(', ', $sizes) . ") – {$price} Kč";
        $merchTotal += $price;
    }

    // Náramky
    if ($braceletQty > 0) {
        $price = $braceletQty * $PRICE_BRACELET;
        $merchLines[] = "- Náramek PULSE × {$braceletQty} ks – {$price} Kč";
        $merchTotal += $price;
    }

    if ($merchTotal <= 0) {
        $errors[] = 'Výběr merchu je prázdný. Vrať se prosím zpět na výběr.';
    }

    // Doprava
    $shippingPrice = 0;
    if (!$errors) {
        $shippingPrice = $SHIPPING_OPTIONS[$shipping]['price'];
        $total = $merchTotal + $shippingPrice;
    }

    if (!$errors && $total <= 0) {
        $errors[] = 'Celková částka je 0 Kč – zkontroluj prosím objednávku.';
    }

    if (!$errors) {
        // ID objednávky a VS
        $orderId = 'P' . date('ymdHis');
        $orderVs = substr(date('ymdHis'), -10); // max 10 číslic

        $lines = [];
        $lines[] = "ID objednávky: {$orderId}";
        $lines[] = "Jméno: {$name}";
        $lines[] = "E-mail: {$email}";
        $lines[] = "Telefon: {$phone}";
        $lines[] = "Adresa: {$street}, {$city}, {$zip}, {$country}";
        $lines[] = "";
        $lines[] = "Merch:";
        $lines = array_merge($lines, $merchLines);
        $lines[] = "";
        $lines[] = "Doprava: " . $SHIPPING_OPTIONS[$shipping]['label'] . " – {$shippingPrice} Kč";
        $lines[] = "";
        $lines[] = "Celkem k zaplacení: {$total} Kč";
        $lines[] = "";
        $lines[] = "Pokyny k platbě:";
        $lines[] = "- Částka: {$total} Kč";
        $lines[] = "- Číslo účtu: {$ACCOUNT_IBAN}";
        $lines[] = "- Variabilní symbol: {$orderVs}";
        $lines[] = "- Zpráva pro příjemce: MERCH {$orderId}";

        $orderSummary = implode("\n", $lines);

        // Uložit do logu
        $logLine = $orderSummary . "\n-----------------------------\n";
        @file_put_contents($LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);

        // QR platba přes Paylibo
        $amount = number_format($total, 2, '.', '');
        $qrUrl = 'https://api.paylibo.com/paylibo/generator/czech/image'
            . '?accountNumber=' . urlencode($ACCOUNT_NUMBER)
            . '&bankCode='      . urlencode($BANK_CODE)
            . '&amount='        . urlencode($amount)
            . '&currency=CZK'
            . '&vs='            . urlencode($orderVs)
            . '&message='       . urlencode('MERCH ' . $orderId)
            . '&size=320'
            . '&branding=true';

        $orderCreated = true;
    }
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>PULSE | Objednávka merchu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/koncerty.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    >
    <style>
      .page-merch-form { padding-bottom: 4rem; }
      .order-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.1fr);
        gap: 2rem;
        margin-top: 2rem;
      }
      @media (max-width: 900px) {
        .order-layout { grid-template-columns: minmax(0,1fr); }
      }
      .card {
        background: rgba(10,10,25,0.96);
        border-radius: 1.5rem;
        padding: 1.8rem 2rem;
        box-shadow: 0 20px 50px rgba(0,0,0,0.7);
      }
      .card h2 { margin-bottom: 1rem; }
      label {
        display:block;
        font-size:0.9rem;
        margin-bottom:0.6rem;
      }
      input[type="text"],
      input[type="email"],
      input[type="tel"],
      input[type="number"] {
        width:100%;
        padding:0.45rem 0.7rem;
        border-radius:0.7rem;
        border:1px solid rgba(255,255,255,0.2);
        background:rgba(5,5,20,0.95);
        color:inherit;
        font-size:0.9rem;
        margin-top:0.2rem;
      }
      .shipping-option {
        display:flex;
        align-items:center;
        gap:0.5rem;
        margin-bottom:0.4rem;
        font-size:0.9rem;
      }
      .btn {
        border-radius:999px;
        padding:0.6rem 1.4rem;
        border:none;
        cursor:pointer;
        font-weight:500;
      }
      .btn-primary{
        background:#ff3355;
        color:#fff;
      }
      .btn-primary:hover{ filter:brightness(1.05); }
      .order-summary-list li{
        margin-bottom:0.3rem;
        font-size:0.9rem;
      }
      pre.order-summary {
        white-space:pre-wrap;
        font-size:0.85rem;
        background:rgba(0,0,0,0.35);
        padding:1rem 1.2rem;
        border-radius:0.9rem;
      }
    </style>
</head>
<body class="theme-dark">
<header class="site-header">
  <div class="container header-inner">
    <a href="index.html" class="logo">
      <img src="img/logo.jpg" alt="PULSE logo">
      <span>PULSE</span>
    </a>
    <nav class="main-nav">
      <a href="index.html">Domů</a>
      <a href="koncerty.html">Koncerty</a>
      <a href="galerie.html">Galerie</a>
      <a href="kontakt.html">Kontakt</a>
      <a href="merch.html" class="active">Merch</a>
    </nav>
    <button id="themeToggle" class="theme-toggle" aria-label="Přepnout motiv">
      <i class="fa-solid fa-moon"></i>
    </button>
  </div>
</header>

<main class="page-merch-form">
  <section class="container">
    <h1>Objednávka merchu</h1>

    <?php if ($orderCreated && !$errors): ?>
      <div class="card" style="margin-top:2rem;">
        <h2>Díky za objednávku! 🎉</h2>
        <p>Objednávka má ID <strong><?php echo htmlspecialchars($orderId); ?></strong>.</p>

        <pre class="order-summary"><?php echo htmlspecialchars($orderSummary); ?></pre>

        <div class="order-layout" style="margin-top:1.5rem;">
          <div>
            <h3>Údaje k platbě</h3>
            <ul class="order-summary-list">
              <li><strong>Částka:</strong> <?php echo htmlspecialchars($total); ?> Kč</li>
              <li><strong>Číslo účtu:</strong> <?php echo htmlspecialchars($ACCOUNT_IBAN); ?></li>
              <li><strong>Variabilní symbol:</strong> <?php echo htmlspecialchars($orderVs); ?></li>
              <li><strong>Zpráva pro příjemce:</strong> MERCH <?php echo htmlspecialchars($orderId); ?></li>
            </ul>
            <p style="font-size:0.85rem;opacity:0.85;margin-top:0.6rem;">
              Po připsání platby na účet ti merch odešleme na uvedenou adresu.
            </p>
          </div>
          <div>
            <h3>QR platba</h3>
            <?php if ($qrUrl): ?>
              <div style="margin-top:0.5rem;">
                <img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="QR platba" style="max-width:100%;border-radius:1rem;">
              </div>
            <?php else: ?>
              <p>QR kód se nepodařilo vygenerovat, použij prosím ruční zadání.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    <?php else: ?>

      <?php if ($errors): ?>
        <div class="card" style="margin-top:1.5rem;background:rgba(80,0,0,0.8);">
          <h3>Oprav prosím chyby ve formuláři</h3>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="order-layout" style="margin-top:2rem;">
        <div class="card">
          <h2>Kontaktní údaje</h2>
          <form method="post" action="merch.php">
            <!-- přeneseme info o kroku -->
            <input type="hidden" name="step" value="2">

            <!-- přeneseme všechny produkty dál -->
            <input type="hidden" name="shirt_main_s"  value="<?php echo $shirtMainS; ?>">
            <input type="hidden" name="shirt_main_m"  value="<?php echo $shirtMainM; ?>">
            <input type="hidden" name="shirt_main_l"  value="<?php echo $shirtMainL; ?>">
            <input type="hidden" name="shirt_main_xl" value="<?php echo $shirtMainXL; ?>">

            <input type="hidden" name="shirt_alt_s"  value="<?php echo $shirtAltS; ?>">
            <input type="hidden" name="shirt_alt_m"  value="<?php echo $shirtAltM; ?>">
            <input type="hidden" name="shirt_alt_l"  value="<?php echo $shirtAltL; ?>">
            <input type="hidden" name="shirt_alt_xl" value="<?php echo $shirtAltXL; ?>">

            <input type="hidden" name="hoodie_s"  value="<?php echo $hoodieS; ?>">
            <input type="hidden" name="hoodie_m"  value="<?php echo $hoodieM; ?>">
            <input type="hidden" name="hoodie_l"  value="<?php echo $hoodieL; ?>">
            <input type="hidden" name="hoodie_xl" value="<?php echo $hoodieXL; ?>">

            <input type="hidden" name="bracelet_qty" value="<?php echo $braceletQty; ?>">

            <label>
              Jméno a příjmení*
              <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>">
            </label>
            <label>
              E-mail*
              <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
            </label>
            <label>
              Telefon
              <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            </label>

            <h3 style="margin-top:1.2rem;">Adresa pro doručení</h3>
            <label>
              Ulice a číslo domu*
              <input type="text" name="street" required value="<?php echo htmlspecialchars($street); ?>">
            </label>
            <label>
              Město*
              <input type="text" name="city" required value="<?php echo htmlspecialchars($city); ?>">
            </label>
            <label>
              PSČ*
              <input type="text" name="zip" required value="<?php echo htmlspecialchars($zip); ?>">
            </label>
            <label>
              Stát
              <input type="text" name="country" value="<?php echo htmlspecialchars($country ?: 'Česká republika'); ?>">
            </label>

            <h3 style="margin-top:1.2rem;">Doprava</h3>
            <?php foreach ($SHIPPING_OPTIONS as $key => $opt): ?>
              <label class="shipping-option">
                <input type="radio" name="shipping" value="<?php echo $key; ?>"
                  <?php echo $shipping === $key ? 'checked' : ''; ?>>
                <span><?php echo htmlspecialchars($opt['label']); ?> (<?php echo $opt['price']; ?> Kč)</span>
              </label>
            <?php endforeach; ?>

            <div style="margin-top:1.4rem;">
              <button type="submit" class="btn btn-primary">Dokončit objednávku</button>
              <p style="font-size:0.8rem;opacity:0.8;margin-top:0.4rem;">
                Po odeslání uvidíš souhrn objednávky a QR kód pro platbu.
              </p>
            </div>
          </form>
        </div>

        <div class="card">
          <h2>Souhrn vybraného merchu</h2>
          <ul class="order-summary-list">
            <?php if ($shirtMainS + $shirtMainM + $shirtMainL + $shirtMainXL > 0): ?>
              <li>
                <strong>Tričko PULSE classic</strong><br>
                <?php
                  $parts = [];
                  if ($shirtMainS)  $parts[] = "S × {$shirtMainS}";
                  if ($shirtMainM)  $parts[] = "M × {$shirtMainM}";
                  if ($shirtMainL)  $parts[] = "L × {$shirtMainL}";
                  if ($shirtMainXL) $parts[] = "XL × {$shirtMainXL}";
                  echo implode(', ', $parts);
                ?>
              </li>
            <?php endif; ?>

            <?php if ($shirtAltS + $shirtAltM + $shirtAltL + $shirtAltXL > 0): ?>
              <li>
                <strong>Tričko PULSE alternate</strong><br>
                <?php
                  $parts = [];
                  if ($shirtAltS)  $parts[] = "S × {$shirtAltS}";
                  if ($shirtAltM)  $parts[] = "M × {$shirtAltM}";
                  if ($shirtAltL)  $parts[] = "L × {$shirtAltL}";
                  if ($shirtAltXL) $parts[] = "XL × {$shirtAltXL}";
                  echo implode(', ', $parts);
                ?>
              </li>
            <?php endif; ?>

            <?php if ($hoodieS + $hoodieM + $hoodieL + $hoodieXL > 0): ?>
              <li>
                <strong>Mikina PULSE</strong><br>
                <?php
                  $parts = [];
                  if ($hoodieS)  $parts[] = "S × {$hoodieS}";
                  if ($hoodieM)  $parts[] = "M × {$hoodieM}";
                  if ($hoodieL)  $parts[] = "L × {$hoodieL}";
                  if ($hoodieXL) $parts[] = "XL × {$hoodieXL}";
                  echo implode(', ', $parts);
                ?>
              </li>
            <?php endif; ?>

            <?php if ($braceletQty > 0): ?>
              <li>
                <strong>Náramek PULSE</strong><br>
                <?php echo $braceletQty; ?> ks
              </li>
            <?php endif; ?>
          </ul>

          <p style="font-size:0.85rem;opacity:0.8;margin-top:0.8rem;">
            Cenu spočítáme až po odeslání formuláře (podle počtu kusů a dopravy)
            a ukážeme ti ji se souhrnem a QR platbou.
          </p>
        </div>
      </div>
    <?php endif; ?>
  </section>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <span>© <span id="year"></span> PULSE</span>
    <div class="footer-socials">
      <a href="https://www.tiktok.com/@pulse_pilsen" class="social-link"><i class="fa-brands fa-tiktok"></i></a>
      <a href="https://www.youtube.com/@pulse_pilsen" class="social-link"><i class="fa-brands fa-youtube"></i></a>
      <a href="https://www.instagram.com/pulse_pilsen/" class="social-link"><i class="fa-brands fa-instagram"></i></a>
    </div>
  </div>
</footer>

<script src="js/index.js"></script>
</body>
</html>