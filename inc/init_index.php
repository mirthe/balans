<?php

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include __DIR__ . '/init_transaction_filters.php';

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

$headers[] = 'Tags';
$headers[21] = 'Naam / tegenrekening';
$visibleColumns = [0, 19, 21, 17, 20, 10];

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
