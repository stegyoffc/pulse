<?php
// cart.php – přehled košíku

$cartJson = $_POST['cart_json'] ?? '[]';
$items = json_decode($cartJson, true);
if (!is_array($items)) {
    $items = [];
}

$total = 0;
foreach ($items as $it) {
    $price = isset($it['price']) ? (int)$it['price'] : 0;
    $qty   = isset($it['qty']) ? (int)$it['qty'] : 0;
    $total += $price * $qty;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>PULSE | Košík</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/koncerty.css">

    <style>
.cart-wrapper {
  margin-top: 2.5rem;
  margin-bottom: 3rem;
}

.cart-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
}

.cart-table th,
.cart-table td {
  padding: 0.6rem 0.5rem;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.cart-table th {
  text-align: left;
  font-weight: 600;
}

.cart-summary-total {
  text-align: right;
  margin-top: 0.5rem;
  font-size: 1.05rem;
  font-weight: 600;
}

.cart-actions {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-link {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 999px;
  padding: 0.6rem 1.4rem;
  color: inherit;
  text-decoration: none;
  font-size: 0.9rem;
}

.btn-primary {
  background: #ff3355;
  border-radius: 999px;
  padding: 0.7rem 1.5rem;
  border: none;
  cursor: pointer;
  color: #fff;
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

<main class="page-merch">
  <section class="container cart-wrapper">
    <h1>Košík</h1>

    <?php if (empty($items)): ?>
      <p>Tvůj košík je prázdný.</p>
      <p><a href="merch.html" class="btn-link">Zpět na merch</a></p>
    <?php else: ?>

      <table class="cart-table">
        <thead>
          <tr>
            <th>Produkt</th>
            <th>Velikost</th>
            <th>Barva</th>
            <th>Počet</th>
            <th>Cena / ks</th>
            <th>Celkem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it):
              $title = $it['title'] ?? '';
              $size  = $it['size'] ?? '';
              $color = $it['color'] ?? '';
              $price = (int)($it['price'] ?? 0);
              $qty   = (int)($it['qty'] ?? 0);
              $line  = $price * $qty;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($title); ?></td>
            <td><?php echo htmlspecialchars($size); ?></td>
            <td><?php echo $color ? htmlspecialchars($color) : '—'; ?></td>
            <td><?php echo $qty; ?></td>
            <td><?php echo $price; ?> Kč</td>
            <td><?php echo $line; ?> Kč</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="cart-summary-total">
        Mezisoučet za merch: <?php echo $total; ?> Kč
      </div>

      <div class="cart-actions">
        <a href="merch.html" class="btn-link">Pokračovat v nákupu</a>

        <form method="post" action="checkout.php">
          <input type="hidden" name="cart_json" value="<?php echo htmlspecialchars($cartJson, ENT_QUOTES, 'UTF-8'); ?>">
          <button type="submit" class="btn-primary">Přejít na objednávku</button>
        </form>
      </div>

    <?php endif; ?>

  </section>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <span>© <span id="year"></span> PULSE</span>
    <div class="footer-socials">
      <a href="https://www.tiktok.com/@pulse_pilsen"><i class="fa-brands fa-tiktok"></i></a>
      <a href="https://www.youtube.com/@pulse_pilsen"><i class="fa-brands fa-youtube"></i></a>
      <a href="https://www.instagram.com/pulse_pilsen/"><i class="fa-brands fa-instagram"></i></a>
    </div>
  </div>
</footer>

<script src="js/index.js"></script>
</body>
</html>