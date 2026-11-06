<?php

$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "placeholders";
if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    fwrite(STDERR, "Cannot create placeholders dir\n");
    exit(1);
}

function make_gradient(string $path, int $w, int $h, array $stops): void {
    $im = imagecreatetruecolor($w, $h);
    imagealphablending($im, true);
    for ($y = 0; $y < $h; $y++) {
        $t = $y / max(1, $h - 1);
        $seg = $t * (count($stops) - 1);
        $i = (int) floor($seg);
        $f = $seg - $i;
        if ($i >= count($stops) - 1) {
            $i = count($stops) - 2;
            $f = 1.0;
        }
        $a = $stops[$i];
        $b = $stops[$i + 1];
        $r = (int) round($a[0] + ($b[0] - $a[0]) * $f);
        $g = (int) round($a[1] + ($b[1] - $a[1]) * $f);
        $bl = (int) round($a[2] + ($b[2] - $a[2]) * $f);
        $col = imagecolorallocate($im, $r, $g, $bl);
        imageline($im, 0, $y, $w - 1, $y, $col);
    }
    for ($n = 0; $n < 5; $n++) {
        $cx = (int) ($w * (0.15 + $n * 0.18));
        $cy = (int) ($h * (0.25 + ($n % 3) * 0.2));
        $rad = (int) ($w * (0.18 + ($n % 2) * 0.08));
        $color = imagecolorallocatealpha($im, 255, 230, 190, 100);
        imagefilledellipse($im, $cx, $cy, $rad, $rad, $color);
    }
    imagejpeg($im, $path, 88);
    imagedestroy($im);
}

make_gradient($dir . DIRECTORY_SEPARATOR . "home-bg-1.jpg", 1920, 1080, [
    [31, 78, 107], [61, 120, 140], [242, 166, 90], [252, 236, 210],
]);
make_gradient($dir . DIRECTORY_SEPARATOR . "home-bg-3.jpg", 1920, 1080, [
    [36, 52, 71], [70, 110, 130], [228, 90, 70], [245, 210, 170],
]);

$bg2 = $dir . DIRECTORY_SEPARATOR . "home-bg-2.jpg";
$asset = "C:\\Users\\giuli\\.cursor\\projects\\c-Users-giuli-OneDrive-Documenti-Progetti-solguido\\assets\\home-bg-2.jpg";
if (is_file($asset)) {
    copy($asset, $bg2);
} elseif (!is_file($bg2)) {
    make_gradient($bg2, 1920, 1080, [
        [47, 122, 123], [226, 106, 93], [247, 239, 230],
    ]);
}

echo "placeholders ready\n";
