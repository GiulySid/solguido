<?php

require dirname(__DIR__) . "/includes/data.php";

$posts = data_bacheca_posts(null);
$before = count($posts);
$posts[] = [
    "id" => "test-pending",
    "autore" => "TestUser",
    "testo" => "prova pending",
    "immagine" => "",
    "immagineThumb" => "",
    "createdAt" => date("c"),
    "status" => "pending",
    "fromGuido" => false,
];
$ok = data_write("bacheca", ["posts" => array_values($posts)]);
$after = data_bacheca_posts(["pending"]);
echo "write=" . ($ok ? "ok" : "FAIL") . " before={$before} pending=" . count($after) . "\n";
if (!$ok) {
    $path = data_path("bacheca");
    $tmp = $path . ".tmp";
    echo "path writable=" . (is_writable(dirname($path)) ? "yes" : "no") . "\n";
    echo "file exists=" . (is_file($path) ? "yes" : "no") . "\n";
    echo "tmp exists=" . (is_file($tmp) ? "yes" : "no") . "\n";
}
