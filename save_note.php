<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Gebruik POST om een notitie op te slaan.');
}

$csrfToken = $_POST['csrf_token'] ?? null;
if (
    !is_string($csrfToken)
    || !isset($_SESSION['csrf_token'])
    || !is_string($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    http_response_code(403);
    exit('Ongeldige aanvraag. Vernieuw de transactielijst en probeer opnieuw.');
}

$currentYear = (int) date('Y');
$year = $_POST['jaar'] ?? null;
$submittedNotes = $_POST['notities'] ?? null;
if (
    !is_string($year)
    || !preg_match('/^\d{4}$/', $year)
    || (int) $year < 2010
    || (int) $year > $currentYear
    || !is_array($submittedNotes)
    || $submittedNotes === []
) {
    http_response_code(400);
    exit('Ongeldige notitiegegevens.');
}
$transactionNumbers = [];
foreach ($submittedNotes as $key => $note) {
    if (!is_string($key) || preg_match('/^t(.+)$/D', $key, $matches) !== 1 || !is_string($note)) {
        http_response_code(400);
        exit('Ongeldige notitiegegevens.');
    }
    $transactionNumbers[] = $matches[1];
}

$month = $_POST['maand'] ?? null;
if ($month !== null && (!is_string($month) || !preg_match('/^(0[1-9]|1[0-2])$/', $month))) {
    http_response_code(400);
    exit('Ongeldige maand.');
}

$category = $_POST['categorie'] ?? null;
if ($category !== null && !is_string($category)) {
    http_response_code(400);
    exit('Ongeldige categorie.');
}

$search = $_POST['zoek'] ?? null;
if ($search !== null && !is_string($search)) {
    http_response_code(400);
    exit('Ongeldige zoekterm.');
}

$transactionFile = __DIR__ . '/csv/' . $year . '.csv';
$transactionHandle = fopen($transactionFile, 'r');
if ($transactionHandle === false) {
    http_response_code(404);
    exit('Het transactiebestand kon niet worden geopend.');
}

$transactionNumbersInFile = [];
while (($row = fgetcsv($transactionHandle, 0, ',', '"', '')) !== false) {
    if (isset($row[15])) {
        $transactionNumbersInFile[$row[15]] = true;
    }
}
fclose($transactionHandle);

foreach ($transactionNumbers as $transactionNumber) {
    if (!isset($transactionNumbersInFile[$transactionNumber])) {
        http_response_code(400);
        exit('Transactie niet gevonden in het gekozen jaar.');
    }
}

$notesFile = __DIR__ . '/csv/' . $year . '_tags.csv';
$notesHandle = fopen($notesFile, 'c+');
if ($notesHandle === false) {
    error_log('Kan notitiebestand niet openen: ' . $notesFile);
    http_response_code(500);
    exit('Notitie kon niet worden opgeslagen.');
}

if (!flock($notesHandle, LOCK_EX)) {
    fclose($notesHandle);
    error_log('Kan notitiebestand niet vergrendelen: ' . $notesFile);
    http_response_code(500);
    exit('Notitie kon niet worden opgeslagen.');
}

$notes = [];
while (($row = fgetcsv($notesHandle, 0, ',', '"', '')) !== false) {
    if (isset($row[0], $row[1])) {
        $notes[$row[0]] = $row[1];
    }
}

foreach ($submittedNotes as $key => $note) {
    $transactionNumber = substr($key, 1);
    if ($note === '') {
        unset($notes[$transactionNumber]);
    } else {
        $notes[$transactionNumber] = $note;
    }
}

rewind($notesHandle);
if (!ftruncate($notesHandle, 0)) {
    flock($notesHandle, LOCK_UN);
    fclose($notesHandle);
    error_log('Kan notitiebestand niet leegmaken: ' . $notesFile);
    http_response_code(500);
    exit('Notitie kon niet worden opgeslagen.');
}

$writeFailed = false;
foreach ($notes as $savedTransactionNumber => $savedNote) {
    if (fputcsv($notesHandle, [$savedTransactionNumber, $savedNote], ',', '"', '') === false) {
        $writeFailed = true;
        break;
    }
}

if (!$writeFailed && !fflush($notesHandle)) {
    $writeFailed = true;
}
flock($notesHandle, LOCK_UN);
fclose($notesHandle);

if ($writeFailed) {
    error_log('Kan notitie niet wegschrijven naar: ' . $notesFile);
    http_response_code(500);
    exit('Notitie kon niet worden opgeslagen.');
}

$redirectQuery = ['jaar' => $year];
if ($month !== null) {
    $redirectQuery['maand'] = $month;
}
if ($category !== null && $category !== '') {
    $redirectQuery['categorie'] = $category;
}
if ($search !== null && $search !== '') {
    $redirectQuery['zoek'] = $search;
}

header('Location: index.php?' . http_build_query($redirectQuery), true, 303);
exit;