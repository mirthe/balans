<?php

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categoriesFile = dirname(__DIR__) . '/csv/cats.csv';
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

$sortDirection = $_GET['sorteer'] ?? null;
if (
    $sortDirection !== null
    && (!is_string($sortDirection) || !in_array($sortDirection, ['datum-oplopend', 'datum-aflopend'], true))
) {
    http_response_code(400);
    exit('Ongeldige sorteerrichting.');
}

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
$notesFile = dirname(__DIR__) . '/csv/' . $selectedYear . '_tags.csv';
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

$headers[] = 'Tags';
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

if ($sortDirection !== null) {
    $sortableRows = [];
    foreach ($rows as $position => $row) {
        $date = $row[0] ?? '';
        if (
            preg_match('/^(\d{2})-(\d{2})-(\d{4})$/D', $date, $matches) !== 1
            || !checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])
        ) {
            throw new RuntimeException('Ongeldige datum aangetroffen in het transactiebestand.');
        }

        $sortableRows[] = [
            'row' => $row,
            'date' => $matches[3] . $matches[2] . $matches[1],
            'position' => $position,
        ];
    }

    usort($sortableRows, static function (array $left, array $right) use ($sortDirection): int {
        $dateComparison = strcmp($left['date'], $right['date']);
        if ($sortDirection === 'datum-aflopend') {
            $dateComparison *= -1;
        }

        return $dateComparison !== 0
            ? $dateComparison
            : ($left['position'] <=> $right['position']);
    });
    $rows = array_map(static fn(array $sortableRow): array => $sortableRow['row'], $sortableRows);
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
    . number_format(intdiv($absoluteTotalCents, 100), 0, ',', '.')
    . ','
    . str_pad((string) ($absoluteTotalCents % 100), 2, '0', STR_PAD_LEFT);

$dateSortQuery = ['jaar' => $selectedYear];
if ($selectedMonth !== null) {
    $dateSortQuery['maand'] = $selectedMonth;
}
if ($selectedCategory !== null) {
    $dateSortQuery['categorie'] = $selectedCategory;
}
if ($selectedSearch !== '') {
    $dateSortQuery['zoek'] = $selectedSearch;
}
$nextSortDirection = $sortDirection === 'datum-oplopend' ? 'datum-aflopend' : 'datum-oplopend';
$dateSortQuery['sorteer'] = $nextSortDirection;
$dateSortUrl = '?' . http_build_query($dateSortQuery);
$dateSortLabel = $nextSortDirection === 'datum-oplopend'
    ? 'Sorteer datum oplopend'
    : 'Sorteer datum aflopend';
$dateSortIcon = match ($sortDirection) {
    'datum-oplopend' => 'fa-sort-up',
    'datum-aflopend' => 'fa-sort-down',
    default => 'fa-sort',
};
