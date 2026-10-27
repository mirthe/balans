<?php
function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$currentYear = (int) date('Y');
$yearFiles = glob(dirname(__DIR__) . '/csv/[0-9][0-9][0-9][0-9].csv');
$years = array_map(
    static fn(string $yearFile): int => (int) basename($yearFile, '.csv'),
    $yearFiles === false ? [] : $yearFiles
);
rsort($years, SORT_NUMERIC);
$selectedYear = $_GET['jaar'] ?? ($years[0] ?? (string) $currentYear);
if (
    !is_string($selectedYear)
    || preg_match('/^\d{4}$/', $selectedYear) !== 1
    || !in_array((int) $selectedYear, $years, true)
) {
    http_response_code(400);
    exit('Ongeldig jaar. Kies een beschikbaar jaar.');
}

$months = [];
for ($month = 1; $month <= 12; $month++) {
    $monthNumber = sprintf('%02d', $month);
    $months[$monthNumber] = date('F', mktime(0, 0, 0, $month, 1));
}

$selectedMonth = $_GET['maand'] ?? null;
if ($selectedMonth !== null && (!is_string($selectedMonth) || !isset($months[$selectedMonth]))) {
    http_response_code(400);
    exit('Ongeldige maand. Kies een maand van 01 tot en met 12.');
}

$file = dirname(__DIR__) . '/csv/' . $selectedYear . '.csv';
$fileAvailable = is_file($file);
$handle = $fileAvailable ? fopen($file, 'r') : false;

if ($fileAvailable && $handle === false) {
    throw new RuntimeException('Het transactiebestand kon niet worden geopend.');
}

$yearRows = [];
if ($handle !== false) {
    while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
        if (isset($row[0])) {
            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
        }
        $yearRows[] = $row;
    }
    fclose($handle);
}

$rows = $yearRows;
if ($selectedMonth !== null) {
    $rows = array_values(array_filter($rows, static function (array $row) use ($selectedMonth): bool {
        return isset($row[0])
            && preg_match('/^\d{2}-(\d{2})-\d{4}$/', $row[0], $matches) === 1
            && $matches[1] === $selectedMonth;
    }));
}
