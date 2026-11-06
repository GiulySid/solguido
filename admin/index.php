<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();

$attivita = data_attivita(true);
$pending = data_bacheca_pending_count();
$posts = data_bacheca_posts(null);

ob_start();
?>
<h1 class="admin-title">Cosa vuoi modificare?</h1>
<p class="admin-lead">I testi stanno nei JSON, le foto in /media. Il sito pubblico le legge da lì.</p>
<div class="admin-grid">
  <a class="admin-card" href="sito.php">
    <div>
      <h2>Testi e contatti</h2>
      <p>Chi siamo, cosa facciamo, email, social, 8×1000</p>
    </div>
    <span class="admin-count">pagina</span>
  </a>
  <a class="admin-card" href="attivita.php">
    <div>
      <h2>Attività</h2>
      <p>Eventi, laboratori, concerti</p>
    </div>
    <span class="admin-count"><?= count($attivita) ?> voci</span>
  </a>
  <a class="admin-card" href="bacheca.php">
    <div>
      <h2>Bacheca</h2>
      <p>Approva, nascondi, elimina, scrivi come Guido</p>
    </div>
    <span class="admin-count"><?= $pending ?> in attesa · <?= count($posts) ?> totali</span>
  </a>
</div>
<?php
admin_layout("Dashboard", ob_get_clean(), ["nav" => true, "wide" => true]);
