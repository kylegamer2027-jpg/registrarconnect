
</main>

<?php if (function_exists('current_user') && current_user()): ?>
    <footer class="footer">
        <div class="footer-brand">
            <span class="footer-brand-icon">▤</span>
            <div>
                <b>Registrar's Office</b>
                <small>Making academic services simpler.</small>
            </div>
        </div>

        <div class="footer-meta">
            <span>Olongapo City, Zambales</span>
            <span class="footer-separator">•</span>
            <span>© <?= date('Y') ?> CELTECH College</span>
        </div>
    </footer>
<?php endif; ?>

</body>
</html>
