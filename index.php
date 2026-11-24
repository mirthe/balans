<?php
session_start();
include 'inc/init.php';
include 'inc/init_index.php';
?>
<!doctype html>
<html lang="nl">
<head>
    <?php include 'inc/header.php' ?>
    <link rel="stylesheet" href="css/listing.css">
</head>
<body>
    <header>
        <h1>Transacties</h1>
        <?php include 'inc/nav.php' ?>
        <?php include 'inc/sel_year.php' ?>
        <?php include 'inc/sel_month.php' ?>
    </header>
    <div class="filters">
        <?php include 'inc/sel_cat.php' ?>
        <?php include 'inc/search.php' ?>
    </div>
    <main>
        <div class="table-container">
                <form class="notes-form" method="post" action="save_note.php">
                <?php include 'inc/init_form.php' ?>
                <table>
                <thead>
                    <tr>
                        <?php foreach ($visibleColumns as $index): ?>
                            <?php if ($index === 0): ?>
                                <th scope="col" aria-sort="<?= $sortDirection === 'datum-oplopend' ? 'ascending' : ($sortDirection === 'datum-aflopend' ? 'descending' : 'none') ?>">
                                    <span class="date-sort-header">
                                        <?= escapeHtml($headers[$index]) ?>
                                        <a class="date-sort-link" href="<?= escapeHtml($dateSortUrl) ?>" aria-label="<?= escapeHtml($dateSortLabel) ?>">
                                            <i class="fa-solid <?= escapeHtml($dateSortIcon) ?>" aria-hidden="true"></i>
                                        </a>
                                    </span>
                                </th>
                            <?php else: ?>
                                <th><?= escapeHtml($headers[$index]) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($visibleColumns as $index): ?>
                                <?php
                                $transactionNumber = $row[15] ?? '';
                                $value = $index === 20
                                    ? ($notes[$transactionNumber] ?? '')
                                    : ($row[$index] ?? '');
                                if ($index === 19) {
                                    $value = trim($value, " \t\n\r\0\x0B'");
                                }
                                $class = '';
                                if ($index === 10) {
                                    $class = 'amount';
                                    if (is_numeric($value) && (float) $value < 0) {
                                        $class .= ' expense';
                                    } elseif (is_numeric($value) && (float) $value > 0) {
                                        $class .= ' income';
                                    }
                                } elseif ($index === 17) {
                                    $class = 'description';
                                } elseif ($index === 20) {
                                    $class = 'note';
                                } elseif ($index === 21) {
                                    $class = 'counterparty';
                                }
                                ?>
                                <td class="<?= $class ?>">
                                    <?php if ($index === 19): ?>
                                        <?php
                                        $categoryQuery = ['jaar' => $selectedYear];
                                        if ($selectedMonth !== null) {
                                            $categoryQuery['maand'] = $selectedMonth;
                                        }
                                        $categoryQuery['categorie'] = $value;
                                        if ($selectedSearch !== '') {
                                            $categoryQuery['zoek'] = $selectedSearch;
                                        }
                                        ?>
                                        <a href="?<?= escapeHtml(http_build_query($categoryQuery)) ?>">
                                            <?php if (isset($categoryIcons[$value])): ?>
                                                <i class="fa-solid <?= escapeHtml($categoryIcons[$value]) ?> category-icon" aria-hidden="true"></i>
                                            <?php endif; ?>
                                            <?= escapeHtml($value) ?>
                                        </a>
                                    <?php elseif ($index === 20): ?>
                                        <div class="note-editor">
                                            <input type="text" name="notities[t<?= escapeHtml($transactionNumber) ?>]" value="<?= escapeHtml($value) ?>" title="Tags spatie-gescheiden invoeren" aria-label="Spatie-gescheiden tags voor transactie <?= escapeHtml($transactionNumber) ?>">
                                            <button type="submit" aria-label="Alle notities opslaan"><i class="fa fa-save"></i></button>
                                        </div>
                                        <small class="transaction-number"><?= escapeHtml($transactionNumber) ?></small>
                                    <?php elseif ($index === 21): ?>
                                        <?php if (!empty($row[3])): ?>
                                            <?= escapeHtml($row[3]) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($row[3]) && !empty($row[2])): ?><br><?php endif; ?>
                                        <?php if (!empty($row[2])): ?>
                                            <?= escapeHtml($row[2]) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?= escapeHtml($index === 10 ? number_format((float) $value, 2, ',', '.') : $value) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" colspan="4">Totaal</th>
                        <td class="notes-submit-cell">
                            <button type="submit"><i class="fa fa-save"></i> Alles opslaan</button>
                        </td>
                        <td class="amount"><?= escapeHtml($totalAmount) ?></td>
                    </tr>
                </tfoot>
                </table>
            </form>
        </div>
    </main>
</body>
</html>