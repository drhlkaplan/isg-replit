
</main>

<?php
$_layoutFirmId = $_SESSION['user']['firm_id'] ?? 0;
$_layoutFirm = $_layoutFirmId ? (\ISG\DB::getInstance())->fetch('SELECT footer_text, name FROM firms WHERE id = ?', [$_layoutFirmId]) : null;
$_footerText = $_layoutFirm['footer_text'] ?? '';
?>
<footer class="isg-footer mt-auto py-3 <?= isset($_SESSION['user']) ? '' : 'd-none' ?>">
    <div class="container text-center">
        <?php if ($_footerText): ?>
        <small class="text-muted"><?= htmlspecialchars($_footerText) ?></small>
        <?php else: ?>
        <small class="text-muted">&copy; <?= date('Y') ?> İSG Eğitim Platformu — ISG Akademi</small>
        <?php endif; ?>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
