<?php
include 'inc/init.php';

$categoriesFile = __DIR__ . '/csv/cats.csv';
$categoriesHandle = fopen($categoriesFile, 'r');
if ($categoriesHandle === false) {
    throw new RuntimeException('Het categorie-bestand kon niet worden geopend.');
}

$categoryIcons = [];
while (($categoryRow = fgetcsv($categoriesHandle, 0, ';', '"', '')) !== false) {
    $category = trim($categoryRow[0] ?? '', " \t\n\r\0\x0B'");
    $icon = trim($categoryRow[1] ?? '');
    if ($category !== '' && preg_match('/^fa-[a-z0-9-]+$/D', $icon) === 1) {
        $categoryIcons[$category] = $icon;
    }
}
fclose($categoriesHandle);

$categoryTotals = [];
$grandExpenses = 0;
$grandIncome = 0;
foreach ($rows as $row) {
    $category = trim($row[19] ?? '', " \t\n\r\0\x0B'");
    if ($category === '') {
        $category = 'Geen categorie';
    }

    $amount = $row[10] ?? '';
    if (preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/D', $amount, $matches) !== 1) {
        throw new RuntimeException('Ongeldig bedrag aangetroffen in het transactiebestand.');
    }

    $cents = ((int) $matches[2] * 100) + (int) str_pad($matches[3] ?? '', 2, '0');
    if ($matches[1] === '-') {
        $cents *= -1;
    }

    if (!isset($categoryTotals[$category])) {
        $categoryTotals[$category] = ['expenses' => 0, 'income' => 0];
    }

    if ($cents < 0) {
        $categoryTotals[$category]['expenses'] += abs($cents);
        $grandExpenses += abs($cents);
    } else {
        $categoryTotals[$category]['income'] += $cents;
        $grandIncome += $cents;
    }
}
ksort($categoryTotals, SORT_NATURAL | SORT_FLAG_CASE);

$formatAmount = static function (int $cents): string {
    $absoluteCents = abs($cents);
    $sign = $cents < 0 ? '-' : '';
    $wholeEuros = number_format(intdiv($absoluteCents, 100), 0, ',', '.');
    $fractionalCents = str_pad((string) ($absoluteCents % 100), 2, '0', STR_PAD_LEFT);

    return $sign . $wholeEuros . ',' . $fractionalCents;
};

$rankedCategories = array_filter($categoryTotals, static function (array $totals): bool {
    return $totals['expenses'] > 0;
});
uasort($rankedCategories, static function (array $left, array $right): int {
    return $right['expenses'] <=> $left['expenses'];
});
$chartCategories = array_slice($rankedCategories, 0, 10, true);
$otherExpenses = array_sum(array_column(array_slice($rankedCategories, 10, null, true), 'expenses'));

$chartColors = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#9c755f'];
$chartSegments = [];
$colorIndex = 0;
foreach ($chartCategories as $category => $totals) {
    if ($totals['expenses'] > 0) {
        $chartSegments[] = [
            'category' => $category,
            'amount' => $totals['expenses'],
            'color' => $chartColors[$colorIndex % count($chartColors)],
        ];
        $colorIndex++;
    }
}

if ($otherExpenses > 0) {
    $chartSegments[] = [
        'category' => 'Overig',
        'amount' => $otherExpenses,
        'color' => $chartColors[$colorIndex % count($chartColors)],
    ];
}

$chartTotal = $grandExpenses;
$chartStartAngle = -90;
?>
<!doctype html>
<html lang="nl">
<head>
    <?php include 'inc/header.php' ?>
    <link rel="stylesheet" href="css/summary.css">
</head>
<body>
    <header>
        <h1>Totalen per categorie</h1>
        <?php include 'inc/nav.php' ?>
        <?php include 'inc/sel_year.php' ?>
        <?php include 'inc/sel_month.php' ?>
    </header>
    <main>
        <div class="summary-grid">
            <section class="summary-panel" aria-labelledby="category-totals-heading">
                <div class="table-container summary-table">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Categorie</th>
                                <th scope="col">Uitgaven</th>
                                <th scope="col">Inkomsten</th>
                                <th scope="col">Totaal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoryTotals as $category => $totals): ?>
                                <tr>
                                    <?php
                                    $categoryQuery = ['jaar' => $selectedYear];
                                    if ($selectedMonth !== null) {
                                        $categoryQuery['maand'] = $selectedMonth;
                                    }
                                    $categoryQuery['categorie'] = $category;
                                    ?>
                                    <td>
                                        <a href="index.php?<?= escapeHtml(http_build_query($categoryQuery)) ?>">
                                            <?php if (isset($categoryIcons[$category])): ?>
                                                <i class="fa-solid <?= escapeHtml($categoryIcons[$category]) ?> category-icon" aria-hidden="true"></i>
                                            <?php endif; ?>
                                            <?= escapeHtml($category) ?>
                                        </a>
                                    </td>
                                    <td class="amount"><?= escapeHtml($formatAmount(-$totals['expenses'])) ?></td>
                                    <td class="amount"><?= escapeHtml($formatAmount($totals['income'])) ?></td>
                                    <td class="amount"><?= escapeHtml($formatAmount($totals['expenses'] - $totals['income'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if ($categoryTotals !== []): ?>
                            <tfoot>
                                <tr>
                                    <th scope="row">Totaal</th>
                                    <th class="amount"><?= escapeHtml($formatAmount(-$grandExpenses)) ?></th>
                                    <th class="amount"><?= escapeHtml($formatAmount($grandIncome)) ?></th>
                                    <th class="amount"><?= escapeHtml($formatAmount($grandExpenses - $grandIncome)) ?></th>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </section>
            <?php include 'chart.php' ?>
        </div>
    </main>
</body>
</html>