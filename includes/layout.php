<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "data.php";

function site_nav_items() {
    return [
        "chi-siamo" => ["label" => "Chi siamo", "href" => site_asset("chi-siamo")],
        "cosa-facciamo" => ["label" => "Cosa facciamo", "href" => site_asset("cosa-facciamo")],
        "attivita" => ["label" => "Eventi", "href" => site_asset("attivita")],
        "bacheca" => ["label" => "Ricordi", "href" => site_asset("bacheca")],
        "sostienici" => ["label" => "Sostienici", "href" => site_asset("sostienici")],
        "contatti" => ["label" => "Contatti", "href" => site_asset("contatti")],
    ];
}

function site_header($current, $title, $description = "", $opts = []) {
    $site = data_site();
    $name = $site["nome"] ?: "SolGuido";
    $fullTitle = $current === "home" ? $name : ($title . " · " . $name);
    $description = $description !== "" ? $description : ($site["tagline"] ?: $name);
    $nav = site_nav_items();
    $root = dirname(__DIR__);
    $GLOBALS["site_css_v"] = (int) @filemtime($root . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "site.css");
    $GLOBALS["site_js_v"] = (int) @filemtime($root . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . "site.js");

    header("Content-Type: text/html; charset=utf-8");
    ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= data_h($fullTitle) ?></title>
  <meta name="description" content="<?= data_h($description) ?>">
  <?php if (!empty($opts["robots"])) { ?>
    <meta name="robots" content="<?= data_h($opts["robots"]) ?>">
  <?php } ?>
  <link rel="icon" href="<?= data_h(site_asset("img/favicon.svg")) ?>" type="image/svg+xml">
  <link rel="apple-touch-icon" href="<?= data_h(site_asset("img/logo-mark.svg")) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=Josefin+Slab:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= data_h(site_asset("css/site.css")) ?>?v=<?= (int) ($GLOBALS["site_css_v"] ?? 0) ?>">
</head>
<body data-page="<?= data_h($current) ?>">
  <div class="sky" aria-hidden="true">
    <svg class="sky__mountains" viewBox="0 0 1440 320" preserveAspectRatio="none" focusable="false">
      <path class="sky__layer sky__layer--far" d="M0 220 L120 160 L240 210 L360 120 L480 190 L600 90 L720 170 L840 110 L960 200 L1080 130 L1200 185 L1320 140 L1440 190 L1440 320 L0 320 Z"></path>
      <path class="sky__layer sky__layer--mid" d="M0 250 L160 180 L300 240 L460 150 L620 230 L780 140 L940 220 L1100 160 L1280 230 L1440 175 L1440 320 L0 320 Z"></path>
      <path class="sky__layer sky__layer--near" d="M0 280 L200 210 L380 270 L560 190 L760 260 L980 200 L1180 265 L1440 220 L1440 320 L0 320 Z"></path>
    </svg>
  </div>

  <div class="nav-backdrop" data-nav-backdrop hidden></div>
  <?php if (empty($opts["hideNav"])) { ?>
    <nav id="site-nav" class="site-nav" aria-label="Principale">
      <div class="site-nav__panel">
        <div class="site-nav__top">
          <p class="site-nav__title">Menu</p>
          <button class="nav-close" type="button" data-nav-close aria-label="Chiudi menu">Chiudi</button>
        </div>
        <div class="site-nav__links">
          <?php foreach ($nav as $key => $item) { ?>
            <a href="<?= data_h($item["href"]) ?>"<?= $current === $key ? ' aria-current="page"' : "" ?>><?= data_h($item["label"]) ?></a>
          <?php } ?>
        </div>
      </div>
    </nav>
  <?php } ?>

  <header class="site-header">
    <a class="brand" href="<?= data_h(site_asset("")) ?>">
      <img class="brand__logo" src="<?= data_h(site_asset("img/logo.svg")) ?>" alt="<?= data_h($name) ?>" width="220" height="80">
    </a>
    <?php if (empty($opts["hideNav"])) { ?>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
        <span class="nav-toggle__bars" aria-hidden="true"></span>
        <span class="nav-toggle__label">Menu</span>
      </button>
      <div class="site-nav-desktop" aria-hidden="false">
        <?php foreach ($nav as $key => $item) { ?>
          <a href="<?= data_h($item["href"]) ?>"<?= $current === $key ? ' aria-current="page"' : "" ?>><?= data_h($item["label"]) ?></a>
        <?php } ?>
      </div>
    <?php } ?>
  </header>
  <main class="site-main" id="main">
    <?php
}

function site_footer() {
    $site = data_site();
    $name = $site["nome"] ?: "SolGuido";
    ?>
  </main>
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <p class="footer-name"><?= data_h($site["nomeLegale"] ?: $name) ?></p>
        <p class="footer-tag"><?= data_h($site["tagline"] ?? "") ?></p>
        <?php if (!empty($site["sede"])) { ?>
          <p class="footer-tag"><?= data_h($site["sede"]) ?></p>
        <?php } ?>
        <?php if (!empty($site["codiceFiscale"])) { ?>
          <p class="footer-tag">CF <?= data_h($site["codiceFiscale"]) ?></p>
        <?php } ?>
      </div>
      <div class="footer-links">
        <?php if (!empty($site["email"])) { ?>
          <a href="mailto:<?= data_h($site["email"]) ?>"><?= data_h($site["email"]) ?></a>
        <?php } ?>
        <?php if (!empty($site["telefono"])) { ?>
          <a href="tel:<?= data_h(preg_replace('/\s+/', '', (string) $site["telefono"])) ?>"><?= data_h($site["telefono"]) ?></a>
        <?php } ?>
        <?php if (!empty($site["instagram"])) { ?>
          <a href="<?= data_h($site["instagram"]) ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
        <?php } ?>
        <?php if (!empty($site["facebook"])) { ?>
          <a href="<?= data_h($site["facebook"]) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
        <?php } ?>
        <a href="<?= data_h(site_asset("contatti")) ?>">Contatti</a>
        <a href="<?= data_h(site_asset("sostienici")) ?>">5×1000</a>
      </div>
    </div>
    <p class="footer-note">Sol Guido ODV</p>
  </footer>
  <script src="<?= data_h(site_asset("js/site.js")) ?>?v=<?= (int) ($GLOBALS["site_js_v"] ?? 0) ?>" defer></script>
</body>
</html>
    <?php
}

function site_attivita_meta($item) {
    $bits = [];
    $date = data_format_date($item["data"] ?? "");
    if ($date !== "") {
        $bits[] = $date;
    }
    if (!empty($item["ora"])) {
        $bits[] = (string) $item["ora"];
    }
    $place = trim(($item["luogo"] ?? "") . (!empty($item["citta"]) ? ", " . $item["citta"] : ""));
    if ($place !== "") {
        $bits[] = $place;
    }
    return implode(" · ", $bits);
}
