<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();
admin_reject_if_post_too_large("attivita-edit.php");

$id = trim((string) ($_GET["id"] ?? ""));
$items = data_attivita(true);
$item = null;
$index = null;

if ($id !== "") {
    foreach ($items as $i => $row) {
        if (($row["id"] ?? "") === $id) {
            $item = $row;
            $index = $i;
            break;
        }
    }
    if ($item === null) {
        admin_flash("error", "Attività non trovata.");
        header("Location: attivita.php");
        exit;
    }
}

if ($item === null) {
    $item = [
        "id" => "",
        "titolo" => "",
        "data" => "",
        "ora" => "",
        "luogo" => "",
        "citta" => "",
        "testo" => "",
        "immagine" => "",
        "immagineThumb" => "",
        "hidden" => false,
    ];
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    admin_csrf_check();
    $item["titolo"] = trim((string) ($_POST["titolo"] ?? ""));
    $item["data"] = trim((string) ($_POST["data"] ?? ""));
    $item["ora"] = trim((string) ($_POST["ora"] ?? ""));
    $item["luogo"] = trim((string) ($_POST["luogo"] ?? ""));
    $item["citta"] = trim((string) ($_POST["citta"] ?? ""));
    $item["testo"] = trim((string) ($_POST["testo"] ?? ""));
    $item["hidden"] = !empty($_POST["hidden"]);

    if ($item["titolo"] === "") {
        $error = "Il titolo è obbligatorio.";
    } else {
        if ($index === null) {
            $used = [];
            foreach ($items as $row) {
                if (!empty($row["id"])) {
                    $used[] = $row["id"];
                }
            }
            $newId = data_unique_id($used, $item["titolo"]);
            if ($newId === null) {
                $error = "Impossibile generare un id.";
            } else {
                $item["id"] = $newId;
            }
        }

        if ($error === "" && !empty($_POST["remove_image"])) {
            data_delete_rel($item["immagine"] ?? "");
            data_delete_rel($item["immagineThumb"] ?? "");
            $item["immagine"] = "";
            $item["immagineThumb"] = "";
        }

        if ($error === "" && !empty($_FILES["immagine"])) {
            $basename = ($item["id"] ?: "attivita") . "-" . substr(bin2hex(random_bytes(3)), 0, 6);
            [$stored, $imgError] = admin_store_upload($_FILES["immagine"], "media/attivita", $basename);
            if ($imgError) {
                $error = $imgError;
            } elseif ($stored) {
                data_delete_rel($item["immagine"] ?? "");
                data_delete_rel($item["immagineThumb"] ?? "");
                $item["immagine"] = $stored["src"];
                $item["immagineThumb"] = $stored["thumb"];
            }
        }

        if ($error === "") {
            if ($index === null) {
                $items[] = $item;
            } else {
                $items[$index] = $item;
            }
            if (!data_write("attivita", ["items" => array_values($items)])) {
                $error = "Impossibile scrivere attivita.json.";
            } else {
                admin_flash("success", "Attività salvata.");
                header("Location: attivita-edit.php?id=" . rawurlencode($item["id"]));
                exit;
            }
        }
    }
}

$img = data_asset($item["immagineThumb"] ?: ($item["immagine"] ?? ""));

ob_start();
?>
<a class="admin-back" href="attivita.php">← Attività</a>
<h1 class="admin-title"><?= $index === null ? "Nuova attività" : "Modifica attività" ?></h1>
<?php if ($error) { ?>
  <p class="admin-flash admin-flash--error" role="alert"><?= admin_h($error) ?></p>
<?php } ?>
<form class="admin-form" method="post" enctype="multipart/form-data">
  <?= admin_csrf_field() ?>
  <label class="admin-field">
    <span>Titolo</span>
    <input type="text" name="titolo" required value="<?= admin_h($item["titolo"]) ?>">
  </label>
  <label class="admin-field">
    <span>Data</span>
    <input type="date" name="data" value="<?= admin_h($item["data"]) ?>">
  </label>
  <label class="admin-field">
    <span>Ora</span>
    <input type="text" name="ora" placeholder="11:00" value="<?= admin_h($item["ora"]) ?>">
  </label>
  <label class="admin-field">
    <span>Luogo</span>
    <input type="text" name="luogo" value="<?= admin_h($item["luogo"]) ?>">
  </label>
  <label class="admin-field">
    <span>Città</span>
    <input type="text" name="citta" value="<?= admin_h($item["citta"]) ?>">
  </label>
  <label class="admin-field">
    <span>Testo</span>
    <textarea name="testo"><?= admin_h($item["testo"]) ?></textarea>
  </label>
  <label class="admin-field">
    <span>Immagine</span>
    <input type="file" name="immagine" accept="image/jpeg,image/png,image/webp,image/gif">
    <span class="admin-help"><?= admin_h(admin_upload_limits_note()) ?></span>
  </label>
  <?php if ($img !== "") { ?>
    <div>
      <img class="admin-thumb" src="../<?= admin_h($img) ?>" alt="">
      <label class="admin-check">
        <input type="checkbox" name="remove_image" value="1">
        <span>Rimuovi immagine attuale</span>
      </label>
    </div>
  <?php } ?>
  <label class="admin-check">
    <input type="checkbox" name="hidden" value="1"<?= !empty($item["hidden"]) ? " checked" : "" ?>>
    <span>Nascosta sul sito pubblico</span>
  </label>
  <div class="admin-actions">
    <button class="admin-btn" type="submit">Salva</button>
  </div>
</form>
<?php
admin_layout($index === null ? "Nuova attività" : "Modifica attività", ob_get_clean(), ["nav" => true]);
