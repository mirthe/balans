<?php
include __DIR__ . '/init_transaction_filters.php';

$monthLabels = [
    'jan', 'feb', 'mrt', 'apr', 'mei', 'jun',
    'jul', 'aug', 'sep', 'okt', 'nov', 'dec',
];
$monthlyTotals = array_fill(1, 12, ['expenses' => 0, 'income' => 0]);

foreach ($rows as $row) {
    $date = $row[0] ?? '';
    if (
        preg_match('/^(\d{2})-(\d{2})-(\d{4})$/D', $date, $dateMatches) !== 1
        || !checkdate((int) $dateMatches[2], (int) $dateMatches[1], (int) $dateMatches[3])
        || (int) $dateMatches[3] !== (int) $selectedYear
    ) {
        throw new RuntimeException('Ongeldige datum aangetroffen in het transactiebestand.');
    }

    $amount = $row[10] ?? '';
    if (preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/D', $amount, $amountMatches) !== 1) {
        throw new RuntimeException('Ongeldig bedrag aangetroffen in het transactiebestand.');
    }

    $cents = ((int) $amountMatches[2] * 100)
        + (int) str_pad($amountMatches[3] ?? '', 2, '0');
    if ($amountMatches[1] === '-') {
        $monthlyTotals[(int) $dateMatches[2]]['expenses'] += $cents;
    } else {
        $monthlyTotals[(int) $dateMatches[2]]['income'] += $cents;
    }
}

$largestMonthlyTotal = 0;
foreach ($monthlyTotals as $totals) {
    $largestMonthlyTotal = max($largestMonthlyTotal, $totals['expenses'], $totals['income']);
}
$chartStepEuros = max(1, (int) ceil(($largestMonthlyTotal / 100) / 4));
$chartScaleCents = $chartStepEuros * 4 * 100;

$formatAmount = static function (int $cents): string {
    $absoluteCents = abs($cents);
    $sign = $cents < 0 ? '-' : '';
    $wholeEuros = number_format(intdiv($absoluteCents, 100), 0, ',', '.');
    $fractionalCents = str_pad((string) ($absoluteCents % 100), 2, '0', STR_PAD_LEFT);

    return $sign . $wholeEuros . ',' . $fractionalCents;
};
