<?php
$navigationQuery = ['jaar' => $selectedYear];
if ($selectedMonth !== null) {
    $navigationQuery['maand'] = $selectedMonth;
}
if (isset($selectedCategory) && $selectedCategory !== null) {
    $navigationQuery['categorie'] = $selectedCategory;
}
if (isset($selectedSearch) && $selectedSearch !== '') {
    $navigationQuery['zoek'] = $selectedSearch;
}
$navigationQueryString = http_build_query($navigationQuery);
$trendsNavigationQuery = ['jaar' => $selectedYear];
if (isset($selectedCategory) && $selectedCategory !== null) {
    $trendsNavigationQuery['categorie'] = $selectedCategory;
}
if (isset($selectedSearch) && $selectedSearch !== '') {
    $trendsNavigationQuery['zoek'] = $selectedSearch;
}
$trendsNavigationQueryString = http_build_query($trendsNavigationQuery);
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>

<nav>
<ul>
    <li><a href="index.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-table fa-xl""></i></a></li>
    <li><a href="summary.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'summary.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-chart-pie fa-xl"></i></a></li>
    <li><a href="trends.php?<?= escapeHtml($trendsNavigationQueryString) ?>"<?= $currentPage === 'trends.php' ? ' aria-current="page"' : '' ?> aria-label="Trends per maand"><i class="fa-solid fa-chart-simple fa-xl" aria-hidden="true"></i></a></li>
    <li><a href="tags.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'tags.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-tags fa-xl"></i></a></li>
    <li class="nav-last-link"><a href="about.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'about.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-circle-question fa-xl"></i></a></li>
    <li class="theme-toggle-item">
        <button class="theme-toggle" type="button" aria-label="Donkere modus inschakelen" aria-pressed="false">
            <i class="fa-solid fa-moon fa-xl theme-icon-moon" aria-hidden="true"></i>
            <i class="fa-solid fa-sun fa-xl theme-icon-sun" aria-hidden="true"></i>
        </button>
    </li>
</ul>
</nav>