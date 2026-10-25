<form class="search-filter" method="get">
    <input type="hidden" name="jaar" value="<?= escapeHtml($selectedYear) ?>">
    <?php if ($selectedMonth !== null): ?>
        <input type="hidden" name="maand" value="<?= escapeHtml($selectedMonth) ?>">
    <?php endif; ?>
    <?php if ($selectedCategory !== null): ?>
        <input type="hidden" name="categorie" value="<?= escapeHtml($selectedCategory) ?>">
    <?php endif; ?>
    <label for="zoek">Zoeken:</label>
    <input type="search" id="zoek" name="zoek" value="<?= escapeHtml($selectedSearch) ?>" placeholder="Naam, omschrijving of transactienummer">
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</form>