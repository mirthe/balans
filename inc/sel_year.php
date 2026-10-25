<ul class="year filter" aria-label="Filter transacties op jaar">
    <?php foreach ($years as $year): ?>
        <?php
        $yearQuery = ['jaar' => (string) $year];
        if ($selectedMonth !== null) {
            $yearQuery['maand'] = $selectedMonth;
        }
        if (isset($selectedCategory) && $selectedCategory !== null) {
            $yearQuery['categorie'] = $selectedCategory;
        }
        if (isset($selectedSearch) && $selectedSearch !== '') {
            $yearQuery['zoek'] = $selectedSearch;
        }
        ?>
        <li>
            <a href="?<?= escapeHtml(http_build_query($yearQuery)) ?>"<?= (string) $year === $selectedYear ? ' aria-current="page"' : '' ?>><?= escapeHtml((string) $year) ?></a>
        </li>
    <?php endforeach; ?>
</ul>