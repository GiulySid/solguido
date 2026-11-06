<?php

const DATA_DIR = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "data";
const MEDIA_DIR = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "media";
const DATA_BACKUP_DIR = DATA_DIR . DIRECTORY_SEPARATOR . "backups";

const BACHECA_STATUS = [
    "pending" => "In attesa",
    "approved" => "Approvato",
    "hidden" => "Nascosto",
];

const GUIDO_FIRMA_DEFAULT = "🌈 🎵 🎺";

function data_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function site_base_path() {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $docRoot = str_replace("\\", "/", rtrim((string) ($_SERVER["DOCUMENT_ROOT"] ?? ""), "/"));
    $appRoot = str_replace("\\", "/", dirname(__DIR__));
    $docLen = strlen($docRoot);
    if ($docRoot !== "" && strncasecmp($appRoot, $docRoot, $docLen) === 0) {
        $rest = substr($appRoot, $docLen);
        if ($rest === "" || str_starts_with($rest, "/")) {
            $cached = trim($rest, "/");
            return $cached;
        }
    }

    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "/index.php");
    $dir = trim(dirname($script), "/");
    if ($dir === "admin" || str_ends_with("/" . $dir, "/admin")) {
        $dir = trim(dirname($dir), "/");
    }
    if ($dir === "" || $dir === ".") {
        $cached = "";
        return $cached;
    }
    $cached = $dir;
    return $cached;
}

function site_asset($path) {
    $path = ltrim(str_replace("\\", "/", (string) $path), "/");
    $base = site_base_path();
    return ($base === "" ? "" : "/" . $base) . "/" . $path;
}

function data_path($name) {
    return DATA_DIR . DIRECTORY_SEPARATOR . $name . ".json";
}

function data_default($name) {
    switch ($name) {
        case "sito":
            return [
                "nome" => "SolGuido",
                "tagline" => "Sottotitolo del sito.",
                "chiSiamo" => "",
                "cosaFacciamo" => "",
                "email" => "",
                "instagram" => "",
                "facebook" => "",
                "codiceFiscale" => "",
                "testo8x1000" => "",
                "firmaGuido" => GUIDO_FIRMA_DEFAULT,
            ];
        case "attivita":
            return ["items" => []];
        case "bacheca":
            return ["posts" => []];
        default:
            return [];
    }
}

function data_read($name) {
    $path = data_path($name);
    $defaults = data_default($name);
    if (!is_file($path)) {
        return $defaults;
    }
    $raw = file_get_contents($path);
    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        return $defaults;
    }
    return array_replace_recursive($defaults, $decoded);
}

function data_write($name, $payload) {
    if (!is_dir(DATA_DIR) && !@mkdir(DATA_DIR, 0755, true)) {
        return false;
    }
    if (!is_dir(DATA_BACKUP_DIR)) {
        @mkdir(DATA_BACKUP_DIR, 0755, true);
    }
    $path = data_path($name);
    if (is_file($path) && is_dir(DATA_BACKUP_DIR)) {
        $stamp = date("Ymd-His");
        @copy($path, DATA_BACKUP_DIR . DIRECTORY_SEPARATOR . $name . "-" . $stamp . ".json");
        $existing = glob(DATA_BACKUP_DIR . DIRECTORY_SEPARATOR . $name . "-*.json") ?: [];
        rsort($existing);
        foreach (array_slice($existing, 20) as $old) {
            @unlink($old);
        }
    }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $tmp = $path . ".tmp";
    if (file_put_contents($tmp, $json . "\n", LOCK_EX) === false) {
        return false;
    }
    // Windows: rename() fails if the destination already exists.
    if (is_file($path) && !@unlink($path)) {
        @unlink($tmp);
        return false;
    }
    if (@rename($tmp, $path)) {
        return true;
    }
    $copied = @copy($tmp, $path);
    @unlink($tmp);
    return (bool) $copied;
}

function data_site() {
    return data_read("sito");
}

function data_firma_guido() {
    $site = data_site();
    $firma = trim((string) ($site["firmaGuido"] ?? ""));
    return $firma !== "" ? $firma : GUIDO_FIRMA_DEFAULT;
}

function data_attivita($includeHidden = false) {
    $items = data_read("attivita")["items"] ?? [];
    if (!$includeHidden) {
        $items = array_values(array_filter($items, static function ($item) {
            return empty($item["hidden"]);
        }));
    }
    usort($items, static function ($a, $b) {
        $dateA = (string) ($a["data"] ?? "");
        $dateB = (string) ($b["data"] ?? "");
        if ($dateA !== $dateB) {
            return $dateB <=> $dateA;
        }
        return strcasecmp((string) ($a["titolo"] ?? ""), (string) ($b["titolo"] ?? ""));
    });
    return $items;
}

function data_attivita_item($id, $includeHidden = false) {
    foreach (data_attivita($includeHidden) as $item) {
        if (($item["id"] ?? "") === $id) {
            return $item;
        }
    }
    return null;
}

function data_attivita_upcoming($limit = 3) {
    $today = date("Y-m-d");
    $upcoming = [];
    foreach (data_attivita() as $item) {
        $day = (string) ($item["data"] ?? "");
        if ($day !== "" && $day >= $today) {
            $upcoming[] = $item;
        }
    }
    usort($upcoming, static function ($a, $b) {
        return ((string) ($a["data"] ?? "")) <=> ((string) ($b["data"] ?? ""));
    });
    if ($limit > 0) {
        return array_slice($upcoming, 0, $limit);
    }
    return $upcoming;
}

function data_bacheca_posts($statuses = null) {
    $posts = data_read("bacheca")["posts"] ?? [];
    if ($statuses !== null) {
        $allowed = array_fill_keys((array) $statuses, true);
        $posts = array_values(array_filter($posts, static function ($post) use ($allowed) {
            return isset($allowed[$post["status"] ?? ""]);
        }));
    }
    usort($posts, static function ($a, $b) {
        return ((string) ($a["createdAt"] ?? "")) <=> ((string) ($b["createdAt"] ?? ""));
    });
    return $posts;
}

function data_bacheca_post($id) {
    foreach (data_bacheca_posts(null) as $post) {
        if (($post["id"] ?? "") === $id) {
            return $post;
        }
    }
    return null;
}

function data_bacheca_pending_count() {
    return count(data_bacheca_posts(["pending"]));
}

function data_strlen($text) {
    $text = (string) $text;
    return function_exists("mb_strlen") ? mb_strlen($text) : strlen($text);
}

function data_ensure_guido_firma($text) {
    $text = rtrim((string) $text);
    $firma = data_firma_guido();
    if ($text === "") {
        return $firma;
    }
    if (str_contains($text, $firma)) {
        return $text;
    }
    return $text . "\n\n" . $firma;
}

function data_slugify($text) {
    $ascii = trim((string) $text);
    if ($ascii === "") {
        return "";
    }
    if (function_exists("iconv")) {
        $converted = @iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $ascii);
        if (is_string($converted) && $converted !== "") {
            $ascii = $converted;
        }
    }
    $ascii = strtolower($ascii);
    $ascii = str_replace(["'", "`", "\""], "", $ascii);
    $ascii = preg_replace("/[^a-z0-9]+/", "-", $ascii) ?? "";
    $ascii = preg_replace("/-+/", "-", $ascii) ?? "";
    return trim($ascii, "-");
}

function data_is_safe_id($id) {
    return is_string($id) && (bool) preg_match("/^[a-z0-9][a-z0-9\-]{0,80}$/", $id);
}

function data_unique_id($used, $base) {
    $base = data_slugify($base);
    if ($base === "" || !data_is_safe_id($base)) {
        $base = "item";
    }
    $candidate = $base;
    $n = 2;
    $set = array_fill_keys($used, true);
    while (isset($set[$candidate])) {
        $candidate = $base . "-" . $n;
        $n += 1;
        if ($n > 80) {
            return null;
        }
    }
    return $candidate;
}

function data_new_id($prefix = "post") {
    return $prefix . "-" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 6);
}

function data_asset($relative) {
    $relative = str_replace("\\", "/", (string) $relative);
    $relative = ltrim($relative, "/");
    if ($relative === "" || strpos($relative, "..") !== false) {
        return "";
    }
    $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relative);
    if (!is_file($abs)) {
        return "";
    }
    return $relative;
}

function data_format_date($iso) {
    $iso = trim((string) $iso);
    if ($iso === "") {
        return "";
    }
    $dt = date_create($iso);
    if (!$dt) {
        return $iso;
    }
    $months = [
        1 => "gennaio", 2 => "febbraio", 3 => "marzo", 4 => "aprile",
        5 => "maggio", 6 => "giugno", 7 => "luglio", 8 => "agosto",
        9 => "settembre", 10 => "ottobre", 11 => "novembre", 12 => "dicembre",
    ];
    $days = ["domenica", "lunedì", "martedì", "mercoledì", "giovedì", "venerdì", "sabato"];
    $month = $months[(int) $dt->format("n")] ?? $dt->format("m");
    $weekday = $days[(int) $dt->format("w")] ?? "";
    return $weekday . " " . $dt->format("j") . " " . $month . " " . $dt->format("Y");
}

function data_format_datetime($iso) {
    $iso = trim((string) $iso);
    if ($iso === "") {
        return "";
    }
    $dt = date_create($iso);
    if (!$dt) {
        return $iso;
    }
    return data_format_date($dt->format("Y-m-d")) . " · " . $dt->format("H:i");
}

function data_nl2p($text) {
    $text = trim((string) $text);
    if ($text === "") {
        return "";
    }
    $parts = preg_split("/\R{2,}/", $text) ?: [];
    $html = "";
    foreach ($parts as $part) {
        $html .= "<p>" . nl2br(data_h(trim($part))) . "</p>";
    }
    return $html;
}

function data_ensure_dir($relative) {
    $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relative);
    if (!is_dir($abs) && !@mkdir($abs, 0755, true)) {
        return null;
    }
    return $abs;
}

function data_image_from_upload($tmpPath) {
    if (!function_exists("imagecreatetruecolor")) {
        return [null, "GD non è disponibile su questo PHP."];
    }
    $info = @getimagesize($tmpPath);
    if (!$info) {
        return [null, "Il file non è un’immagine valida."];
    }
    switch ($info["mime"]) {
        case "image/jpeg":
            $src = @imagecreatefromjpeg($tmpPath);
            break;
        case "image/png":
            $src = @imagecreatefrompng($tmpPath);
            break;
        case "image/webp":
            $src = function_exists("imagecreatefromwebp") ? @imagecreatefromwebp($tmpPath) : null;
            break;
        case "image/gif":
            $src = @imagecreatefromgif($tmpPath);
            break;
        default:
            return [null, "Usa JPG, PNG, WEBP o GIF."];
    }
    if (!$src) {
        return [null, "Impossibile leggere l’immagine."];
    }
    return [$src, null];
}

function data_fit_image($src, $max) {
    $width = imagesx($src);
    $height = imagesy($src);
    if ($width < 1 || $height < 1) {
        return $src;
    }
    $scale = min(1, $max / max($width, $height));
    if ($scale >= 1) {
        return $src;
    }
    $newW = max(1, (int) round($width * $scale));
    $newH = max(1, (int) round($height * $scale));
    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
    imagedestroy($src);
    return $dst;
}

function data_clone_image($src) {
    $width = imagesx($src);
    $height = imagesy($src);
    $dst = imagecreatetruecolor($width, $height);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopy($dst, $src, 0, 0, 0, 0, $width, $height);
    return $dst;
}

function data_save_gd($image, $absPath) {
    $dir = dirname($absPath);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return false;
    }
    $ok = false;
    if (function_exists("imagewebp")) {
        $ok = imagewebp($image, $absPath, 82);
    } else {
        $absPath = preg_replace("/\\.webp$/i", ".jpg", $absPath);
        $ok = imagejpeg($image, $absPath, 85);
    }
    return $ok ? $absPath : false;
}

function data_store_image_raw($tmpPath, $relativeDir, $basename) {
    $info = @getimagesize($tmpPath);
    if (!$info) {
        return [null, "Il file non è un’immagine valida."];
    }
    $ext = match ($info["mime"] ?? "") {
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
        "image/gif" => "gif",
        default => "",
    };
    if ($ext === "") {
        return [null, "Usa JPG, PNG, WEBP o GIF."];
    }
    $dir = data_ensure_dir(trim($relativeDir, "/"));
    if ($dir === null) {
        return [null, "Impossibile creare la cartella media. Controlla i permessi."];
    }
    $fileName = $basename . "." . $ext;
    $relFull = trim($relativeDir, "/") . "/" . $fileName;
    $absFull = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relFull);
    if (!@copy($tmpPath, $absFull) && !@move_uploaded_file($tmpPath, $absFull)) {
        return [null, "Impossibile salvare l’immagine. Controlla i permessi di media/."];
    }
    return [[
        "src" => $relFull,
        "thumb" => $relFull,
    ], null];
}

function data_store_image($tmpPath, $relativeDir, $basename, $max = 1920, $thumbMax = 480) {
    if (!function_exists("imagecreatetruecolor")) {
        return data_store_image_raw($tmpPath, $relativeDir, $basename);
    }
    [$src, $error] = data_image_from_upload($tmpPath);
    if (!$src) {
        return [null, $error];
    }
    $full = data_fit_image($src, $max);
    $thumbSrc = data_fit_image(data_clone_image($full), $thumbMax);
    $fileName = $basename . ".webp";
    $relFull = trim($relativeDir, "/") . "/" . $fileName;
    $relThumb = trim($relativeDir, "/") . "/thumbs/" . $fileName;
    $absFull = data_save_gd($full, dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relFull));
    $absThumb = data_save_gd($thumbSrc, dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relThumb));
    imagedestroy($full);
    imagedestroy($thumbSrc);
    if (!$absFull) {
        return data_store_image_raw($tmpPath, $relativeDir, $basename);
    }
    $relFull = str_replace("\\", "/", substr($absFull, strlen(dirname(__DIR__) . DIRECTORY_SEPARATOR)));
    $relThumb = $absThumb
        ? str_replace("\\", "/", substr($absThumb, strlen(dirname(__DIR__) . DIRECTORY_SEPARATOR)))
        : $relFull;
    return [[
        "src" => $relFull,
        "thumb" => $relThumb,
    ], null];
}

function data_delete_rel($relative) {
    $relative = str_replace("\\", "/", (string) $relative);
    if ($relative === "" || strpos($relative, "..") !== false || strpos($relative, "media/") !== 0) {
        return;
    }
    $abs = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $relative);
    if (is_file($abs)) {
        @unlink($abs);
    }
}

function data_send_mail($to, $subject, $body, $replyTo = "") {
    $to = trim((string) $to);
    if ($to === "" || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $clean = static function ($value) {
        return str_replace(["\r", "\n", "%0a", "%0d", "%0A", "%0D"], "", $value);
    };
    $headers = [
        "From: " . $clean($to),
        "Content-Type: text/plain; charset=UTF-8",
        "X-Mailer: PHP/" . PHP_VERSION,
    ];
    if ($replyTo !== "" && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = "Reply-To: " . $clean($replyTo);
    }
    return @mail($to, "=?UTF-8?B?" . base64_encode($subject) . "?=", $body, implode("\r\n", $headers));
}
