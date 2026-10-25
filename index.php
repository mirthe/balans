<?php
session_start();
include 'inc/init.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categoriesFile = __DIR__ . '/csv/cats.csv';
$categoriesHandle = fopen($categoriesFile, 'r');
if ($categoriesHandle === false) {
    throw new RuntimeException('Het categorie-bestand kon niet worden geopend.');
}

$categories = [];
$categoryIcons = [];
while (($categoryRow = fgetcsv($categoriesHandle, 0, ';', '"', '')) !== false) {
    $category = trim($categoryRow[0] ?? '', " \t\n\r\0\x0B'");
    if ($category !== '') {
        $categories[] = $category;
        $icon = trim($categoryRow[1] ?? '');
        if (preg_match('/^fa-[a-z0-9-]+$/D', $icon) === 1) {
            $categoryIcons[$category] = $icon;
        }
    }
}
fclose($categoriesHandle);

$selectedCategory = $_GET['categorie'] ?? null;
if ($selectedCategory === '') {
    $selectedCategory = null;
}
if ($selectedCategory !== null && (!is_string($selectedCategory) || !in_array($selectedCategory, $categories, true))) {
    http_response_code(400);
    exit('Ongeldige categorie.');
}

$selectedSearch = $_GET['zoek'] ?? '';
if (!is_string($selectedSearch)) {
    http_response_code(400);
    exit('Ongeldige zoekterm.');
}
$selectedSearch = trim($selectedSearch);

$headers = [
    'Datum',
    'Rekening',
    'Tegenrekening',
    'Naam tegenrekening',
    'Extra veld 5',
    'Extra veld 6',
    'Extra veld 7',
    'Valuta saldo',
    'Saldo',
    'Valuta transactie',
    'Bedrag',
    'Boekingsdatum',
    'Valutadatum',
    'Transactiecode',
    'Transactietype',
    'Transactienr',
    'Kenmerk',
    'Omschrijving',
    'Volgnummer',
    'Categorie',
];

$usedCategories = [];
foreach ($yearRows as $row) {
    $category = trim($row[19] ?? '', " \t\n\r\0\x0B'");
    if ($category !== '') {
        $usedCategories[$category] = true;
    }
}
$categoryOptions = array_values(array_filter(
    $categories,
    static fn(string $category): bool => isset($usedCategories[$category])
));

$notes = [];
$notesFile = __DIR__ . '/notes/' . $selectedYear . '_notes.csv';
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

$headers[] = 'Notitie';
$headers[21] = 'Naam / tegenrekening';
$visibleColumns = [0, 19, 21, 17, 20, 10];

if ($selectedCategory !== null) {
    $rows = array_values(array_filter($rows, static function (array $row) use ($selectedCategory): bool {
        return trim($row[19] ?? '', " \t\n\r\0\x0B'") === $selectedCategory;
    }));
}

if ($selectedSearch !== '') {
    $rows = array_values(array_filter($rows, static function (array $row) use ($selectedSearch, $notes): bool {
        $searchableValues = [$row[3] ?? '', $row[2] ?? '', $row[17] ?? '', $row[15] ?? ''];
        $transactionNumber = $row[15] ?? '';
        $searchableValues[] = $notes[$transactionNumber] ?? '';
        foreach ($searchableValues as $value) {
            $match = function_exists('mb_stripos')
                ? mb_stripos($value, $selectedSearch, 0, 'UTF-8')
                : stripos($value, $selectedSearch);
            if ($match !== false) {
                return true;
            }
        }

        return false;
    }));
}

$totalAmountCents = 0;
foreach ($rows as $row) {
    $amount = $row[10] ?? '';
    if (preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/D', $amount, $matches) !== 1) {
        throw new RuntimeException('Ongeldig bedrag aangetroffen in het transactiebestand.');
    }

    $cents = ((int) $matches[2] * 100) + (int) str_pad($matches[3] ?? '', 2, '0');
    $totalAmountCents += $matches[1] === '-' ? -$cents : $cents;
}

$absoluteTotalCents = abs($totalAmountCents);
$totalAmount = ($totalAmountCents < 0 ? '-' : '')
    . '€ '
    . number_format(intdiv($absoluteTotalCents, 100), 0, ',', '.')
    . ','
    . str_pad((string) ($absoluteTotalCents % 100), 2, '0', STR_PAD_LEFT);
?>
<!doctype html>
<html lang="nl">
<head>
    <?php include 'inc/header.php' ?>
    <link rel="stylesheet" href="css/index.css">
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
                            <th><?= escapeHtml($headers[$index]) ?></th>
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
                                            <input type="text" name="notities[t<?= escapeHtml($transactionNumber) ?>]" value="<?= escapeHtml($value) ?>" aria-label="Notitie voor transactie <?= escapeHtml($transactionNumber) ?>">
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
                                        <?= escapeHtml($value) ?>
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