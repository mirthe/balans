<form class="category filter" method="get">
    <input type="hidden" name="jaar" value="<?= escapeHtml($selectedYear) ?>">
    <?php if ($selectedMonth !== null): ?>
        <input type="hidden" name="maand" value="<?= escapeHtml($selectedMonth) ?>">
    <?php endif; ?>
    <?php if (isset($selectedSearch) && $selectedSearch !== ''): ?>
        <input type="hidden" name="zoek" value="<?= escapeHtml($selectedSearch) ?>">
    <?php endif; ?>
    <label for="categorie">Categorie:</label>
    <select id="categorie" name="categorie">
        <option value="">Alle categorieën</option>
        <?php foreach ($categoryOptions as $category): ?>
            <option value="<?= escapeHtml($category) ?>"<?= $selectedCategory === $category ? ' selected' : '' ?>>
                <?= escapeHtml($category) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</form>