<?php
include 'inc/init.php';
include 'inc/init_tags.php';
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
        <h2>Tags</h2>
        <p>Tags zijn gerangschikt op gebruik binnen de gekozen periode. Klik op een tag om de bijbehorende transacties te bekijken.</p>
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