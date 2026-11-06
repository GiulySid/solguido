<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    admin_csrf_check();
    $action = (string) ($_POST["action"] ?? "");
    $items = data_attivita(true);

    if ($action === "hide_all" || $action === "show_all") {
        $items = admin_set_all_hidden($items, $action === "hide_all");
        data_write("attivita", ["items" => $items]);
        admin_flash("success", $action === "hide_all" ? "Tutte le attività sono nascoste." : "Tutte le attività sono visibili.");
        header("Location: attivita.php");
        exit;
    }

    $id = (string) ($_POST["id"] ?? "");
    $index = null;
    foreach ($items as $i => $item) {
        if (($item["id"] ?? "") === $id) {
            $index = $i;
            break;
        }
    }
    if ($index === null) {
        admin_flash("error", "Attività non trovata.");
        header("Location: attivita.php");
        exit;
    }

    if ($action === "delete") {
        data_delete_rel($items[$index]["immagine"] ?? "");
        data_delete_rel($items[$index]["immagineThumb"] ?? "");
        array_splice($items, $index, 1);
        data_write("attivita", ["items" => array_values($items)]);
        admin_flash("success", "Attività eliminata.");
    } elseif ($action === "hide" || $action === "show") {
        $items[$index]["hidden"] = $action === "hide";
        data_write("attivita", ["items" => array_values($items)]);
        admin_flash("success", $action === "hide" ? "Nascosta dal sito." : "Di nuovo visibile.");
    }

    header("Location: attivita.php");
    exit;
}

$items = data_attivita(true);

ob_start();
?>
<a class="admin-back" href="index.php">← Contenuti</a>
<div class="admin-toolbar">
  <div>
    <p class="admin-kicker">Calendario</p>
    <h1 class="admin-title">Attività</h1>
  </div>
  <div class="admin-toolbar__actions">
    <?php if ($items) { ?>
      <?= admin_bulk_visibility_forms() ?>
    <?php } ?>
    <a class="admin-btn" href="attivita-edit.php">Aggiungi</a>
  </div>
</div>
<?php if (!$items) { ?>
  <p class="admin-empty">Nessuna attività. Aggiungi la prima.</p>
<?php } ?>
<?php foreach ($items as $item) {
    $detailHref = "attivita-edit.php?id=" . rawurlencode((string) $item["id"]);
    ?>
  <article class="admin-item">
    <div class="admin-item__body">
      <h2>
        <a href="<?= admin_h($detailHref) ?>"><?= admin_h($item["titolo"] ?? $item["id"]) ?></a>
        <?php if (!empty($item["hidden"])) { ?><span class="admin-badge admin-badge--hidden">Nascosta</span><?php } ?>
      </h2>
      <p><?= admin_h(trim(($item["data"] ?? "") . " · " . ($item["luogo"] ?? "") . (!empty($item["citta"]) ? ", " . $item["citta"] : ""), " ·")) ?></p>
    </div>
    <div class="admin-item__actions">
      <form method="post">
        <?= admin_csrf_field() ?>
        <input type="hidden" name="id" value="<?= admin_h($item["id"]) ?>">
        <input type="hidden" name="action" value="<?= !empty($item["hidden"]) ? "show" : "hide" ?>">
        <button class="admin-btn-ghost" type="submit"><?= !empty($item["hidden"]) ? "Mostra" : "Nascondi" ?></button>
      </form>
      <form method="post" data-confirm="Eliminare questa attività e la sua immagine?">
        <?= admin_csrf_field() ?>
        <input type="hidden" name="id" value="<?= admin_h($item["id"]) ?>">
        <input type="hidden" name="action" value="delete">
        <button class="admin-btn-ghost" type="submit">Elimina</button>
      </form>
    </div>
  </article>
<?php } ?>
<?php
admin_layout("Attività", ob_get_clean(), ["nav" => true, "wide" => true]);
