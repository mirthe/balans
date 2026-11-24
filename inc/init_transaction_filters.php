<?php
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
