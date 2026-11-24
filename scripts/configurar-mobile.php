<?php
// Genera los recursos de Expo a partir del mismo PNG usado para la web.
$sourcePath = getenv('ICON_FILE');
$output = getenv('MOBILE_OUTPUT');
if (!extension_loaded('gd')) throw new RuntimeException('Para generar los iconos de la app móvil es necesaria la extensión GD de PHP.');
$source = @imagecreatefrompng($sourcePath);
if (!$source) throw new RuntimeException('No se pudo leer el PNG del icono.');
$width = imagesx($source);
$height = imagesy($source);
foreach (['icon.png' => [1024, 850, false], 'android-icon-foreground.png' => [1024, 620, true], 'splash-icon.png' => [1024, 800, false], 'favicon.png' => [64, 53, false]] as $name => [$size, $content, $transparent]) {
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $background = $transparent ? imagecolorallocatealpha($canvas, 0, 0, 0, 127) : imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $background);
    $scale = min($content / $width, $content / $height);
    $w = max(1, (int) round($width * $scale));
    $h = max(1, (int) round($height * $scale));
    if (!$transparent) imagealphablending($canvas, true);
    imagecopyresampled($canvas, $source, intdiv($size - $w, 2), intdiv($size - $h, 2), 0, 0, $w, $h, $width, $height);
    if (!imagepng($canvas, $output.'/'.$name)) throw new RuntimeException('No se pudo guardar '.$name);
    imagedestroy($canvas);
}
imagedestroy($source);
