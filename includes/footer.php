    <!-- Footer -->
    <footer>
        <p>&copy; 2026 The Halley Project &bull; <?= t('Your definitive source for Halley\'s Comet information', 'Sua fonte definitiva de informações sobre o Cometa Halley') ?></p>
        <p><?= t('Created with passion for astronomy and education', 'Criado com paixão por astronomia e educação') ?></p>
    </footer>

    <!-- Scripts -->
    <script src="/js/stars.js" defer></script>
    <?= $extra_scripts ?? '' ?>
    <script>
    document.querySelectorAll('.flash').forEach(el => {
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 5000);
    });
    </script>
</body>
</html>
