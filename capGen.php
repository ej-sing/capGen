<?php
// -----------------------------
// capGen.php - Version 1
// Simulated Web Image Dimension Analyzer
// -----------------------------

function fetchHtml($url) {
    return @file_get_contents($url);
}

function extractImages($html) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $images = [];
    foreach ($dom->getElementsByTagName('img') as $img) {
        $src = $img->getAttribute('src');
        $width  = $img->getAttribute('width');
        $height = $img->getAttribute('height');

        $images[] = [
            'src' => $src,
            'width' => (int)$width,
            'height' => (int)$height
        ];
    }
    return $images;
}

function filterImages($images, $startSize) {
    return array_filter($images, function ($img) use ($startSize) {
        return $img['width'] >= $startSize;
    });
}

// -----------------------------
// Handle request
// -----------------------------
$url = $_GET['url'] ?? '';
$startSize = (int)($_GET['startSize'] ?? 100);

$images = [];
if ($url) {
    $html = fetchHtml($url);
    if ($html) {
        $images = filterImages(extractImages($html), $startSize);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>capGen.php – Version 1</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #111;
    color: #eee;
}
form {
    margin-bottom: 20px;
}
.canvas {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}
.box {
    position: relative;
    height: 160px;
    border: 2px solid #555;
    background: #1c1c1c;
}
.box span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: red;
    font-weight: bold;
}
</style>
</head>
<body>

<h1>capGen.php (Version 1)</h1>

<form method="get">
    URL:
    <input type="text" name="url" size="50" value="<?= htmlspecialchars($url) ?>">
    startSize:
    <input type="number" name="startSize" value="<?= $startSize ?>">
    <button type="submit">Analyze</button>
</form>

<div class="canvas">
<?php foreach ($images as $img): ?>
    <div class="box">
        <span><?= $img['width'] ?> × <?= $img['height'] ?></span>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
