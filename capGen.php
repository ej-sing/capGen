<?php
// -----------------------------
// capGen.php - Version 1.3
// Internal Use Mode
// -----------------------------

function fetchHtml($url) {
    return @file_get_contents($url);
}

function getBaseUrlFromServer() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'];
}

// -----------------------------
// Crawl internal pages (homepage only)
// -----------------------------
function extractInternalPages($html, $baseUrl) {

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $pages = [];
    $baseHost = parse_url($baseUrl, PHP_URL_HOST);
    $baseUrl  = rtrim($baseUrl, '/');

    foreach ($dom->getElementsByTagName('a') as $a) {
        $href = trim($a->getAttribute('href'));
        if ($href === '') continue;

        // ignore unwanted
        if (
            str_starts_with($href, '#') ||
            str_starts_with($href, 'mailto:') ||
            str_starts_with($href, 'javascript:') ||
            str_starts_with($href, 'tel:')
        ) {
            continue;
        }

        // absolute internal
        if (str_starts_with($href, 'http')) {
            $host = parse_url($href, PHP_URL_HOST);
            if ($host === $baseHost) {
                $pages[] = rtrim($href, '/');
            }
            continue;
        }

        // root relative
        if (str_starts_with($href, '/')) {
            $pages[] = $baseUrl . $href;
            continue;
        }

        // plain relative
        $pages[] = $baseUrl . '/' . ltrim($href, './');
    }

    // ensure homepage exists once
    array_unshift($pages, $baseUrl);

    return array_values(array_unique($pages));
}

// -----------------------------
// Extract images
// -----------------------------
function extractImages($html, $baseUrl) {

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $images = [];
    $baseUrl = rtrim($baseUrl, '/');

    foreach ($dom->getElementsByTagName('img') as $img) {

        $src = trim($img->getAttribute('src'));
        if ($src === '') continue;

        if (str_starts_with($src, '/')) {
            $src = $baseUrl . $src;
        }

        $w = (int)$img->getAttribute('width');
        $h = (int)$img->getAttribute('height');

        // skip unknown size (icons, lazy, svg etc.)
        if ($w <= 0 || $h <= 0) continue;

        $images[] = [
            'src'  => $src,
            'file' => basename(parse_url($src, PHP_URL_PATH)),
            'w'    => $w,
            'h'    => $h
        ];
    }

    return $images;
}

function filterImages($images, $startSize) {
    return array_filter($images, function ($img) use ($startSize) {
        return $img['w'] >= $startSize;
    });
}

// -----------------------------
// Context
// -----------------------------
$baseUrl   = getBaseUrlFromServer();
$startSize = (int)($_GET['startSize'] ?? 100);
$target    = $_GET['target'] ?? $baseUrl;

$pages   = [];
$results = [];

// Load homepage immediately
$homeHtml = fetchHtml($baseUrl);
if ($homeHtml) {
    $pages = extractInternalPages($homeHtml, $baseUrl);
}

// Analyze selected page
$pageHtml = fetchHtml($target);
if ($pageHtml) {
    $imgs    = extractImages($pageHtml, $baseUrl);
    $results = filterImages($imgs, $startSize);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>capGen.php v1.3</title>
<style>
body {
    background:#111;
    color:#eee;
    font-family:Arial, sans-serif;
}
form {
    margin-bottom:20px;
}
select {
    width:100%;
    max-width:700px;
}
.canvas {
    display:flex;
    flex-direction:column;
    gap:20px;
}
.mock {
    position:relative;
    border:2px solid #444;
    background:#1c1c1c;
}
.mock span {
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    color:red;
    font-weight:bold;
}
.filename {
    font-size:12px;
    color:#aaa;
    margin-top:4px;
}
</style>
</head>
<body>

<h1>capGen.php – Version 1.3 (Internal)</h1>

<form method="get">
    <div>
        Internal Page:<br>
        <select name="target">
            <?php foreach ($pages as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= $p === $target ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-top:10px;">
        startSize:
        <input type="number" name="startSize" value="<?= $startSize ?>">
        <button>Analyze</button>
    </div>
</form>

<div class="canvas">
<?php foreach ($results as $img):
    $mw = $img['w'] / 3;
    $mh = $img['h'] / 3;
?>
    <div>
        <div class="mock" style="width:<?= $mw ?>px;height:<?= $mh ?>px;">
            <span><?= $img['w'] ?> × <?= $img['h'] ?></span>
        </div>
        <div class="filename"><?= htmlspecialchars($img['file']) ?></div>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
