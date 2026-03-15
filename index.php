<?php
$page_title = t(
    "Halley's Comet Countdown - Next Return in 2061 | The Halley Project",
    "Contagem Regressiva do Cometa Halley - Próximo Retorno em 2061 | The Halley Project"
);
$page_description = t(
    "Track the real-time countdown to Halley's Comet next appearance in 2061. The definitive source for information about the most famous comet in history.",
    "Acompanhe a contagem regressiva em tempo real para o retorno do Cometa Halley em 2061. A fonte definitiva de informações sobre o cometa mais famoso da história."
);
$active_page = 'home';
$extra_scripts = '<script src="/js/countdown.js" defer></script>';

require_once __DIR__ . '/includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <h1><?= t("Halley's Comet", "Cometa Halley") ?></h1>
        <p class="hero-subtitle"><?= t(
            "The most famous comet returns on July 28, 2061",
            "O cometa mais famoso retorna em 28 de julho de 2061"
        ) ?></p>

        <!-- Countdown -->
        <div class="countdown-container">
            <div class="countdown-label"><?= t("Next Perihelion: July 28, 2061", "Próximo Periélio: 28 de Julho de 2061") ?></div>
            <div id="countdown">
                <noscript>
                    <p><?= t("Please enable JavaScript to see the live countdown.", "Ative o JavaScript para ver a contagem regressiva.") ?></p>
                </noscript>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
        <!-- Featured Content -->
        <div class="featured">
            <h2><?= t("Why Halley's Comet Matters", "Por que o Cometa Halley é Importante") ?></h2>
            <p>
                <?= t(
                    "Halley's Comet is not just a celestial spectacle—it's a bridge between past and future, connecting generations through shared wonder. Its predictable return every 76 years makes it unique among comets, allowing humanity to anticipate and prepare for its arrival.",
                    "O Cometa Halley não é apenas um espetáculo celeste — é uma ponte entre passado e futuro, conectando gerações através da admiração compartilhada. Seu retorno previsível a cada 76 anos o torna único entre os cometas, permitindo que a humanidade antecipe e se prepare para sua chegada."
                ) ?>
            </p>
            
            <div class="comet-image">
                <img src="/images/halley.jpg" alt="<?= t("Halley's Comet photographed in 1986", "Cometa Halley fotografado em 1986") ?>" loading="lazy">
                <div class="image-caption">
                    <?= t(
                        "Halley's Comet captured during its 1986 approach • Credit: NASA/JPL",
                        "Cometa Halley capturado durante sua aproximação em 1986 • Crédito: NASA/JPL"
                    ) ?>
                </div>
            </div>
            
            <p>
                <?= t(
                    "The next perihelion in 2061 will be a defining moment for a new generation of observers. Many people alive today will witness this once or twice in their lifetime event, making it a truly special astronomical phenomenon that transcends scientific interest to become a shared human experience.",
                    "O próximo periélio em 2061 será um momento marcante para uma nova geração de observadores. Muitas pessoas vivas hoje testemunharão este evento único em suas vidas, tornando-o um fenômeno astronômico verdadeiramente especial que transcende o interesse científico para se tornar uma experiência humana compartilhada."
                ) ?>
            </p>
            
            <a href="/articles/history.php" class="cta-button"><?= t("Explore the Complete Guide", "Explore o Guia Completo") ?></a>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
