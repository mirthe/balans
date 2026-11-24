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
$rawChartStepCents = $largestMonthlyTotal / 4;
if ($rawChartStepCents <= 0) {
    $chartStepCents = 100;
} else {
    $stepMagnitude = 10 ** floor(log10($rawChartStepCents));
    $normalizedStep = $rawChartStepCents / $stepMagnitude;
    $stepMultiplier = match (true) {
        $normalizedStep <= 1 => 1,
        $normalizedStep <= 2 => 2,
        $normalizedStep <= 5 => 5,
        default => 10,
    };
    $chartStepCents = max(1, (int) ($stepMultiplier * $stepMagnitude));
}
$chartScaleCents = $chartStepCents * 4;

$formatAmount = static function (int $cents): string {
    $absoluteCents = abs($cents);
    $sign = $cents < 0 ? '-' : '';
    $wholeEuros = number_format(intdiv($absoluteCents, 100), 0, ',', '.');
    $fractionalCents = str_pad((string) ($absoluteCents % 100), 2, '0', STR_PAD_LEFT);

    return $sign . $wholeEuros . ',' . $fractionalCents;
};

$formatAxisAmount = static function (int $cents) use ($formatAmount): string {
    return $cents % 100 === 0
        ? number_format(intdiv($cents, 100), 0, ',', '.')
        : $formatAmount($cents);
};
