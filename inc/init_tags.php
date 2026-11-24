<?php
$transactionNumbers = [];
foreach ($rows as $row) {
    if (isset($row[15]) && $row[15] !== '') {
        $transactionNumbers[$row[15]] = true;
    }
}

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

$tagCounts = [];
$tagLabels = [];
foreach ($notes as $transactionNumber => $note) {
    if (!isset($transactionNumbers[$transactionNumber])) {
        continue;
    }

    $tags = preg_split('/\s+/u', trim($note), -1, PREG_SPLIT_NO_EMPTY);
    if ($tags === false) {
        throw new RuntimeException('Tags in het notitiebestand konden niet worden gelezen.');
    }

    foreach ($tags as $tag) {
        $tagKey = function_exists('mb_strtolower')
            ? mb_strtolower($tag, 'UTF-8')
            : strtolower($tag);
        $tagCounts[$tagKey] = ($tagCounts[$tagKey] ?? 0) + 1;
        $tagLabels[$tagKey] ??= $tag;
    }
}

ksort($tagCounts, SORT_NATURAL | SORT_FLAG_CASE);
$maxTagCount = $tagCounts === [] ? 0 : max($tagCounts);
