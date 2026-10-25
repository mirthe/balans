
    <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="jaar" value="<?= escapeHtml($selectedYear) ?>">
    <?php if ($selectedMonth !== null): ?>
        <input type="hidden" name="maand" value="<?= escapeHtml($selectedMonth) ?>">
    <?php endif; ?>
    <?php if ($selectedCategory !== null): ?>
        <input type="hidden" name="categorie" value="<?= escapeHtml($selectedCategory) ?>">
    <?php endif; ?>
    <?php if ($selectedSearch !== ''): ?>
        <input type="hidden" name="zoek" value="<?= escapeHtml($selectedSearch) ?>">
    <?php endif; ?>