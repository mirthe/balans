<ul class="month filter" aria-label="Filter transacties op maand">
    <?php
    $allMonthsQuery = ['jaar' => $selectedYear];
    if (isset($selectedCategory) && $selectedCategory !== null) {
        $allMonthsQuery['categorie'] = $selectedCategory;
    }
    if (isset($selectedSearch) && $selectedSearch !== '') {
        $allMonthsQuery['zoek'] = $selectedSearch;
    }
    ?>
    <li><a href="?<?= escapeHtml(http_build_query($allMonthsQuery)) ?>"<?= $selectedMonth === null ? ' aria-current="page"' : '' ?>>Jaaroverzicht</a></li>
    <?php foreach ($months as $monthNumber => $monthName): ?>
        <?php
        $monthQuery = ['jaar' => $selectedYear, 'maand' => $monthNumber];
        if (isset($selectedCategory) && $selectedCategory !== null) {
            $monthQuery['categorie'] = $selectedCategory;
        }
        if (isset($selectedSearch) && $selectedSearch !== '') {
            $monthQuery['zoek'] = $selectedSearch;
        }
        ?>
        <li>
            <a href="?<?= escapeHtml(http_build_query($monthQuery)) ?>"<?= $selectedMonth === $monthNumber ? ' aria-current="page"' : '' ?>><?= escapeHtml(strtolower(substr($monthName, 0, 3))) ?></a>
        </li>
    <?php endforeach; ?>
</ul>