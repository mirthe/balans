<?php
$categoriesFile = dirname(__DIR__) . '/csv/cats.csv';
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

$sortBy = $_GET['sorteer'] ?? 'categorie';
$sortDirections = [
    'categorie' => 'ascending',
    'uitgaven' => 'descending',
    'inkomsten' => 'descending',
    'totaal' => 'descending',
];
if (!is_string($sortBy) || !isset($sortDirections[$sortBy])) {
    http_response_code(400);
    exit('Ongeldige sorteerkolom.');
}

$sortValues = $categoryTotals;
uksort($categoryTotals, static function (string $leftCategory, string $rightCategory) use ($sortBy, $sortValues): int {
    if ($sortBy === 'categorie') {
        return strnatcasecmp($leftCategory, $rightCategory);
    }

    $leftValue = match ($sortBy) {
        'uitgaven' => $sortValues[$leftCategory]['expenses'],
        'inkomsten' => $sortValues[$leftCategory]['income'],
        'totaal' => $sortValues[$leftCategory]['income'] - $sortValues[$leftCategory]['expenses'],
    };
    $rightValue = match ($sortBy) {
        'uitgaven' => $sortValues[$rightCategory]['expenses'],
        'inkomsten' => $sortValues[$rightCategory]['income'],
        'totaal' => $sortValues[$rightCategory]['income'] - $sortValues[$rightCategory]['expenses'],
    };

    return $rightValue <=> $leftValue;
});

$sortUrl = static function (string $column) use ($selectedYear, $selectedMonth): string {
    $query = ['jaar' => $selectedYear, 'sorteer' => $column];
    if ($selectedMonth !== null) {
        $query['maand'] = $selectedMonth;
    }

    return '?' . http_build_query($query);
};

$chartTotal = $grandExpenses;
$chartStartAngle = -90;
