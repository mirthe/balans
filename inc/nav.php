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
    <li><a href="index.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-table fa-xl""></i></a></li>
    <li><a href="summary.php?<?= escapeHtml($navigationQueryString) ?>"<?= $currentPage === 'summary.php' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-chart-pie fa-xl"></i></a></li>
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