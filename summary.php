<?php
include 'inc/init.php';
include 'inc/init_summary.php';
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
                                <th scope="col" aria-sort="<?= $sortBy === 'categorie' ? 'ascending' : 'none' ?>">
                                    <span class="summary-sort-header">Categorie <a class="summary-sort-link" href="<?= escapeHtml($sortUrl('categorie')) ?>" aria-label="Sorteer categorie oplopend"><i class="fa-solid fa-sort" aria-hidden="true"></i></a></span>
                                </th>
                                <th scope="col" aria-sort="<?= $sortBy === 'uitgaven' ? 'descending' : 'none' ?>">
                                    <span class="summary-sort-header">Uitgaven <a class="summary-sort-link" href="<?= escapeHtml($sortUrl('uitgaven')) ?>" aria-label="Sorteer uitgaven aflopend"><i class="fa-solid fa-sort" aria-hidden="true"></i></a></span>
                                </th>
                                <th scope="col" aria-sort="<?= $sortBy === 'inkomsten' ? 'descending' : 'none' ?>">
                                    <span class="summary-sort-header">Inkomsten <a class="summary-sort-link" href="<?= escapeHtml($sortUrl('inkomsten')) ?>" aria-label="Sorteer inkomsten aflopend"><i class="fa-solid fa-sort" aria-hidden="true"></i></a></span>
                                </th>
                                <th scope="col" aria-sort="<?= $sortBy === 'totaal' ? 'descending' : 'none' ?>">
                                    <span class="summary-sort-header">Totaal <a class="summary-sort-link" href="<?= escapeHtml($sortUrl('totaal')) ?>" aria-label="Sorteer totaal aflopend"><i class="fa-solid fa-sort" aria-hidden="true"></i></a></span>
                                </th>
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
                                    <td class="amount"><?= escapeHtml($formatAmount($totals['income'] - $totals['expenses'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if ($categoryTotals !== []): ?>
                            <tfoot>
                                <tr>
                                    <th scope="row">Totaal</th>
                                    <th class="amount"><?= escapeHtml($formatAmount(-$grandExpenses)) ?></th>
                                    <th class="amount"><?= escapeHtml($formatAmount($grandIncome)) ?></th>
                                    <th class="amount"><?= escapeHtml($formatAmount($grandIncome - $grandExpenses)) ?></th>
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