<section class="summary-panel chart-panel" aria-labelledby="expenses-chart-heading">
    <?php if ($chartTotal > 0): ?>
        <svg class="pie-chart" viewBox="0 0 200 200" role="img" aria-labelledby="pie-chart-title pie-chart-description">
            <title id="pie-chart-title">Verdeling van uitgaven per categorie</title>
            <desc id="pie-chart-description">De tien grootste categorieën en overige uitgaven, totaal <?= escapeHtml($formatAmount($chartTotal)) ?>.</desc>
            <?php if (count($chartSegments) === 1): ?>
                <circle cx="100" cy="100" r="90" fill="<?= escapeHtml($chartSegments[0]['color']) ?>">
                    <title><?= escapeHtml($chartSegments[0]['category'] . ': ' . $formatAmount($chartSegments[0]['amount'])) ?></title>
                </circle>
            <?php else: ?>
                <?php foreach ($chartSegments as $segment): ?>
                    <?php
                    $sliceAngle = 360 * $segment['amount'] / $chartTotal;
                    $endAngle = $chartStartAngle + $sliceAngle;
                    $startRadians = deg2rad($chartStartAngle);
                    $endRadians = deg2rad($endAngle);
                    $startX = 100 + 90 * cos($startRadians);
                    $startY = 100 + 90 * sin($startRadians);
                    $endX = 100 + 90 * cos($endRadians);
                    $endY = 100 + 90 * sin($endRadians);
                    $largeArc = $sliceAngle > 180 ? 1 : 0;
                    ?>
                    <path d="M 100 100 L <?= $startX ?> <?= $startY ?> A 90 90 0 <?= $largeArc ?> 1 <?= $endX ?> <?= $endY ?> Z" fill="<?= escapeHtml($segment['color']) ?>">
                        <title><?= escapeHtml($segment['category'] . ': ' . $formatAmount($segment['amount'])) ?></title>
                    </path>
                    <?php $chartStartAngle = $endAngle; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </svg>
        <ul class="chart-legend">
            <?php foreach ($chartSegments as $segment): ?>
                <li>
                    <span class="legend-label">
                        <span class="legend-swatch" style="background-color: <?= escapeHtml($segment['color']) ?>" aria-hidden="true"></span>
                        <?= escapeHtml($segment['category']) ?>
                        <?php if (isset($categoryIcons[$segment['category']])): ?>
                            <i class="fa-solid <?= escapeHtml($categoryIcons[$segment['category']]) ?> category-icon" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                    <span><?= escapeHtml($formatAmount($segment['amount'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Geen uitgaven gevonden voor deze selectie.</p>
    <?php endif; ?>
</section>