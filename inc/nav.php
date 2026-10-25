<?php
$navigationQuery = ['jaar' => $selectedYear];
if ($selectedMonth !== null) {
    $navigationQuery['maand'] = $selectedMonth;
}
if (isset($selectedCategory) && $selectedCategory !== null) {
    $navigationQuery['categorie'] = $selectedCategory;
}
$navigationQueryString = http_build_query($navigationQuery);
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>

<nav>
<ul>
    <li><a href="index.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>>Transacties</a></li>
    <li><a href="summary.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'summary.php' ? ' aria-current="page"' : '' ?>>Samenvatting</a></li>
    <li><a href="tags.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'tags.php' ? ' aria-current="page"' : '' ?>>Tags</a></li>
    <li><a href="about.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'about.php' ? ' aria-current="page"' : '' ?>>Over</a></li>
</ul>
<button class="theme-toggle" type="button" aria-label="Donkere modus inschakelen" aria-pressed="false">
    <i class="fa-solid fa-moon theme-icon-moon" aria-hidden="true"></i>
    <i class="fa-solid fa-sun theme-icon-sun" aria-hidden="true"></i>
</button>
</nav>