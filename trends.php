<?php
include 'inc/init.php';
$selectedMonth = null;
$rows = $yearRows;
include 'inc/init_trends.php';

$monthlyTotalsCaption = 'Maandtotalen voor ' . $selectedYear;

$plotLeft = 76;
$plotTop = 24;
$plotWidth = 740;
$plotHeight = 250;
$plotBottom = $plotTop + $plotHeight;
$monthGroupWidth = $plotWidth / 12;
$barWidth = 16;
$barGap = 5;
$formatSvgNumber = static fn(float $value): string => number_format($value, 2, '.', '');
?>
<!doctype html>
<html lang="nl">
<head>
    <?php include 'inc/header.php' ?>
    <link rel="stylesheet" href="css/listing.css">
    <link rel="stylesheet" href="css/trends.css">
</head>
<body>
    <header>
        <h1>Trends per maand</h1>
        <?php include 'inc/nav.php' ?>
        <?php include 'inc/sel_year.php' ?>
    </header>
    <div class="filters">
        <?php include 'inc/sel_cat.php' ?>
        <?php include 'inc/search.php' ?>
    </div>
    <main>
        <section class="trend-panel" aria-labelledby="trends-heading">
            <h2 id="trends-heading">Inkomsten en uitgaven per maand</h2>
            <p>Vergelijk de maandtotalen voor <?= escapeHtml($selectedYear) ?>.</p>
            <p>Het saldo is inkomsten min uitgaven en komt overeen met het totaal in het transactieoverzicht.</p>
            <?php if ($rows === []): ?>
                <p role="status">Geen transacties gevonden voor deze selectie.</p>
            <?php else: ?>
                <div class="trend-chart-frame">
                <svg class="trend-chart" viewBox="0 0 840 330" role="img" aria-labelledby="trend-chart-title trend-chart-description">
                    <title id="trend-chart-title">Inkomsten en uitgaven per maand in <?= escapeHtml($selectedYear) ?></title>
                    <desc id="trend-chart-description">Staafdiagram met de inkomsten en uitgaven van januari tot en met december. De tabel onder de grafiek bevat de exacte maandtotalen.</desc>
                    <?php for ($tick = 0; $tick <= 4; $tick++): ?>
                        <?php
                        $tickY = $plotBottom - ($plotHeight * $tick / 4);
                        $tickCents = $chartStepCents * $tick;
                        ?>
                        <line class="trend-gridline" x1="<?= $plotLeft ?>" y1="<?= $formatSvgNumber($tickY) ?>" x2="<?= $plotLeft + $plotWidth ?>" y2="<?= $formatSvgNumber($tickY) ?>"></line>
                        <text class="trend-axis-label" x="4" y="<?= $formatSvgNumber($tickY + 4) ?>">€ <?= escapeHtml($formatAxisAmount($tickCents)) ?></text>
                    <?php endfor; ?>
                    <?php foreach ($monthLabels as $monthIndex => $monthLabel): ?>
                        <?php
                        $monthNumber = $monthIndex + 1;
                        $totals = $monthlyTotals[$monthNumber];
                        $groupX = $plotLeft + ($monthIndex * $monthGroupWidth);
                        $barsWidth = (2 * $barWidth) + $barGap;
                        $barStartX = $groupX + (($monthGroupWidth - $barsWidth) / 2);
                        $expenseHeight = $totals['expenses'] / $chartScaleCents * $plotHeight;
                        $incomeHeight = $totals['income'] / $chartScaleCents * $plotHeight;
                        ?>
                        <?php if ($totals['expenses'] > 0): ?>
                            <rect class="trend-expense" x="<?= $formatSvgNumber($barStartX) ?>" y="<?= $formatSvgNumber($plotBottom - $expenseHeight) ?>" width="<?= $barWidth ?>" height="<?= $formatSvgNumber($expenseHeight) ?>" rx="2">
                                <title><?= escapeHtml($monthLabel . ': uitgaven ' . $formatAmount(-$totals['expenses'])) ?></title>
                            </rect>
                        <?php endif; ?>
                        <?php if ($totals['income'] > 0): ?>
                            <rect class="trend-income" x="<?= $formatSvgNumber($barStartX + $barWidth + $barGap) ?>" y="<?= $formatSvgNumber($plotBottom - $incomeHeight) ?>" width="<?= $barWidth ?>" height="<?= $formatSvgNumber($incomeHeight) ?>" rx="2">
                                <title><?= escapeHtml($monthLabel . ': inkomsten ' . $formatAmount($totals['income'])) ?></title>
                            </rect>
                        <?php endif; ?>
                        <text class="trend-month-label" x="<?= $formatSvgNumber($groupX + ($monthGroupWidth / 2)) ?>" y="303" text-anchor="middle"><?= escapeHtml($monthLabel) ?></text>
                    <?php endforeach; ?>
                </svg>
                <ul class="trend-legend" aria-label="Legenda">
                    <li><span class="trend-swatch trend-expense" aria-hidden="true"></span> Uitgaven</li>
                    <li><span class="trend-swatch trend-income" aria-hidden="true"></span> Inkomsten</li>
                </ul>
                </div>
            <?php endif; ?>
            <?php if ($rows !== []): ?>
                <section class="trend-table-panel" aria-labelledby="monthly-totals-heading">
                    <h2 id="monthly-totals-heading">Maandtotalen</h2>
                    <div class="trends-table">
                        <table>
                            <caption>
                                <?= escapeHtml($monthlyTotalsCaption) ?>
                                <?php if ($selectedCategory !== null): ?>
                                    | categorie:
                                    <?php if (isset($categoryIcons[$selectedCategory])): ?>
                                        <i class="fa-solid <?= escapeHtml($categoryIcons[$selectedCategory]) ?> category-icon" aria-hidden="true"></i>
                                    <?php endif; ?>
                                    <?= escapeHtml($selectedCategory) ?>
                                <?php endif; ?>
                                <?php if ($selectedSearch !== ''): ?>
                                    | zoekterm: <?= escapeHtml($selectedSearch) ?>
                                <?php endif; ?>
                            </caption>
                            <thead>
                                <tr>
                                    <th scope="col">Maand</th>
                                    <th scope="col">Uitgaven</th>
                                    <th scope="col">Inkomsten</th>
                                    <th scope="col">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthLabels as $monthIndex => $monthLabel): ?>
                                    <?php $totals = $monthlyTotals[$monthIndex + 1]; ?>
                                    <?php $netTotal = $totals['income'] - $totals['expenses']; ?>
                                    <?php
                                    $transactionQuery = [
                                        'jaar' => $selectedYear,
                                        'maand' => sprintf('%02d', $monthIndex + 1),
                                    ];
                                    if ($selectedCategory !== null) {
                                        $transactionQuery['categorie'] = $selectedCategory;
                                    }
                                    if ($selectedSearch !== '') {
                                        $transactionQuery['zoek'] = $selectedSearch;
                                    }
                                    ?>
                                    <tr>
                                        <th scope="row">
                                            <a href="index.php?<?= escapeHtml(http_build_query($transactionQuery)) ?>">
                                                <?= escapeHtml(ucfirst($monthLabel)) ?>
                                            </a>
                                        </th>
                                        <td class="amount expense"><?= escapeHtml($formatAmount(-$totals['expenses'])) ?></td>
                                        <td class="amount income"><?= escapeHtml($formatAmount($totals['income'])) ?></td>
                                        <td class="amount"><?= escapeHtml($formatAmount($netTotal)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
