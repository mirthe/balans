<?php
include 'inc/init.php';

$transactionNumbers = [];
foreach ($rows as $row) {
    if (isset($row[15]) && $row[15] !== '') {
        $transactionNumbers[$row[15]] = true;
    }
}

$notes = [];
$notesFile = __DIR__ . '/csv/' . $selectedYear . '_tags.csv';
if (is_file($notesFile)) {
    $notesHandle = fopen($notesFile, 'r');
    if ($notesHandle === false) {
        throw new RuntimeException('Het notitiebestand kon niet worden geopend.');
    }

    while (($noteRow = fgetcsv($notesHandle, 0, ',', '"', '')) !== false) {
        if (isset($noteRow[0], $noteRow[1])) {
            $notes[$noteRow[0]] = $noteRow[1];
        }
    }
    fclose($notesHandle);
}

$tagCounts = [];
$tagLabels = [];
foreach ($notes as $transactionNumber => $note) {
    if (!isset($transactionNumbers[$transactionNumber])) {
        continue;
    }

    $tags = preg_split('/\s+/u', trim($note), -1, PREG_SPLIT_NO_EMPTY);
    if ($tags === false) {
        throw new RuntimeException('Tags in het notitiebestand konden niet worden gelezen.');
    }

    foreach ($tags as $tag) {
        $tagKey = function_exists('mb_strtolower')
            ? mb_strtolower($tag, 'UTF-8')
            : strtolower($tag);
        $tagCounts[$tagKey] = ($tagCounts[$tagKey] ?? 0) + 1;
        $tagLabels[$tagKey] ??= $tag;
    }
}

ksort($tagCounts, SORT_NATURAL | SORT_FLAG_CASE);
$maxTagCount = $tagCounts === [] ? 0 : max($tagCounts);
?>
<!doctype html>
<html lang="nl">
<head>
    <?php include 'inc/header.php' ?>
    <link rel="stylesheet" href="css/tags.css">
    </style>
</head>
<body>
    <header>
        <h1>Tagcloud</h1>
        <?php include 'inc/nav.php' ?>
        <?php include 'inc/sel_year.php' ?>
        <?php include 'inc/sel_month.php' ?>
    </header>
    <main class="narrow">
        <?php if (!$fileAvailable): ?>
            <p role="status">Het bestand voor <?= escapeHtml($selectedYear) ?> is nog niet beschikbaar.</p>
        <?php elseif ($tagCounts === []): ?>
            <p>Geen tags gevonden voor deze selectie.</p>
        <?php else: ?>
            <ul class="tag-cloud" aria-label="Tags, gesorteerd op gebruik">
                <?php foreach ($tagCounts as $tagKey => $count): ?>
                    <?php
                    $fontSize = $maxTagCount === 1
                        ? 1.5
                        : 0.9 + (($count - 1) / ($maxTagCount - 1)) * 1.1;
                    $tagQuery = ['jaar' => $selectedYear, 'zoek' => $tagLabels[$tagKey]];
                    if ($selectedMonth !== null) {
                        $tagQuery['maand'] = $selectedMonth;
                    }
                    ?>
                    <li>
                        <a href="index.php?<?= escapeHtml(http_build_query($tagQuery)) ?>" style="font-size: <?= number_format($fontSize, 2, '.', '') ?>rem">
                            <?= escapeHtml($tagLabels[$tagKey]) ?>
                            <span class="tag-count"><?= escapeHtml((string) $count) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>

</body>
</html>