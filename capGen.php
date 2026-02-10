<?php
// -----------------------------
// capGen.php - Version 1.1
// -----------------------------

function fetchHtml($url) {
    return @file_get_contents($url);
}

function getBaseUrl($url) {
    $p = parse_url($url);
    return $p['scheme'] . '://' . $p['host'];
}

function extractInternalPages($html, $baseUrl) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $pages = [$baseUrl];
    foreach ($dom->getElementsByTagName('a') as $a) {
        $href = $a->getAttribute('href');
        if (!$href) continue;

        if (str_starts_with($href, '/')) {
            $pages[] = $baseUrl . $href;
        }
    }
    return array_unique($pages);
}

function extractImages($html, $baseUrl) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $images = [];
    foreach ($dom->getElementsByTagName('img') as $img) {
        $src = $img->getAttribute('src');
        if (!$src) continue;

        if (str_starts_with($src, '/')) {
            $src = $baseUrl . $src;
        }

        $images[] = [
            'src' => $src,
            'file' => basename(parse_url($src, PHP_URL_PATH)),
            'w' => (int)$img->getAttribute('width'),
            'h' => (int)$img->getAttribute('height')
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
// Handle request
// -----------------------------
$url = $_GET['url'] ?? '';
$startSize = (int)($_GET['startSize'] ?? 100);

$results = [];

if ($url) {
    $baseUrl = getBaseUrl($url);
    $html = fetchHtml($url);

    if ($html) {
        $pages = extractInternalPages($html, $baseUrl);

        foreach ($pages as $page) {
            $pageHtml = fetchHtml($page);
            if (!$pageHtml) continue;

            $imgs = extractImages($pageHtml, $baseUrl);
            $imgs = filterImages($imgs, $startSize);

            foreach ($imgs as $img) {
                $results[] = $img;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>capGen.php v1.1</title>
<style>
body {
    background:#111;
    color:#eee;
    font-family:Arial, sans-serif;
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
    text-align:center;
    pointer-events:none;
}
.filename {
    font-size:12px;
    color:#aaa;
    margin-top:4px;
}
</style>
</head>
<body>

<h1>capGen.php – Version 1.1</h1>

<form method="get">
    URL:
    <input type="text" name="url" size="50" value="<?= htmlspecialchars($url) ?>">
    startSize:
    <input type="number" name="startSize" value="<?= $startSize ?>">
    <button>Run</button>
</form>

<div class="canvas">
<?php foreach ($results as $img): 
    if ($img['w'] <= 0 || $img['h'] <= 0) continue;
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
