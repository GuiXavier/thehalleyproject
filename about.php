<?php
require_once __DIR__ . '/forum/includes/config.php';
$page_title = t("About The Halley Project | Mission & Vision", "Sobre o The Halley Project | Missão e Visão");
$active_page = 'about';
$extra_css = '
<style>
    .about-hero { text-align: center; padding: 3rem 2rem; max-width: 800px; margin: 0 auto; }
    .mission-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 3rem 0; }
    .mission-card { background: rgba(30, 58, 138, 0.1); border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem; text-align: center; backdrop-filter: blur(10px); }
    .mission-icon { font-size: 3rem; margin-bottom: 1rem; }
    .contact-section { background: rgba(139, 92, 246, 0.1); border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem; margin: 3rem 0; text-align: center; }
    .contact-methods { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin-top: 2rem; }
    .contact-button { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: rgba(30, 58, 138, 0.2); border: 1px solid var(--border-color); border-radius: 2rem; color: var(--text-primary); text-decoration: none; transition: all 0.3s; }
    .contact-button:hover { background: rgba(6, 182, 212, 0.2); border-color: var(--accent-cyan); transform: translateY(-2px); }
    .support-section { background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(139, 92, 246, 0.1)); border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem; margin: 3rem 0; }
    .crypto-addresses { display: grid; gap: 1rem; margin-top: 2rem; }
    .crypto-item { background: rgba(15, 23, 42, 0.5); padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--border-color); word-break: break-all; font-family: monospace; font-size: 0.9rem; }
    .crypto-label { color: var(--accent-cyan); font-weight: 600; margin-bottom: 0.5rem; font-family: var(--font-primary); }
    .roadmap { margin: 3rem 0; }
    .roadmap-item { padding: 1.5rem; margin: 1rem 0; background: rgba(30, 58, 138, 0.05); border-left: 3px solid var(--accent-purple); border-radius: 0 0.5rem 0.5rem 0; }
    .roadmap-item h3 { color: var(--accent-cyan); margin-bottom: 0.5rem; }
</style>';

require_once __DIR__ . '/includes/header.php';
?>

    <!-- About Hero -->
    <section class="about-hero">
        <h1><?= t("About The Halley Project", "Sobre o The Halley Project") ?></h1>
        <p class="hero-subtitle"><?= t(
            "Building the definitive resource for Halley's Comet enthusiasts worldwide",
            "Construindo o recurso definitivo para entusiastas do Cometa Halley em todo o mundo"
        ) ?></p>
    </section>

    <!-- Main Content -->
    <article class="article-container">
        <div class="article-content">
            <h2><?= t("Our Mission", "Nossa Missão") ?></h2>
            <p>
                <?= t(
                    "The Halley Project was created with a simple yet ambitious goal: to become the world's premier destination for accurate, accessible, and engaging information about Halley's Comet. We believe that this celestial phenomenon, which has captivated humanity for millennia, deserves a dedicated platform that combines scientific accuracy with educational accessibility.",
                    "O The Halley Project foi criado com um objetivo simples, mas ambicioso: tornar-se o principal destino mundial para informações precisas, acessíveis e envolventes sobre o Cometa Halley. Acreditamos que este fenômeno celeste, que cativou a humanidade por milênios, merece uma plataforma dedicada que combine precisão científica com acessibilidade educacional."
                ) ?>
            </p>
            
            <div class="mission-cards">
                <div class="mission-card">
                    <div class="mission-icon">🎯</div>
                    <h3><?= t("Accuracy First", "Precisão em Primeiro Lugar") ?></h3>
                    <p><?= t("Every piece of information is carefully researched and verified against scientific sources", "Cada informação é cuidadosamente pesquisada e verificada com fontes científicas") ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">🌍</div>
                    <h3><?= t("Global Access", "Acesso Global") ?></h3>
                    <p><?= t("Multi-language support to reach astronomy enthusiasts worldwide", "Suporte multilíngue para alcançar entusiastas de astronomia em todo o mundo") ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">📚</div>
                    <h3><?= t("Educational Focus", "Foco Educacional") ?></h3>
                    <p><?= t("Making complex astronomical concepts accessible to everyone", "Tornando conceitos astronômicos complexos acessíveis a todos") ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">🚀</div>
                    <h3><?= t("Future Ready", "Pronto para o Futuro") ?></h3>
                    <p><?= t("Building tools and resources for the 2061 return", "Construindo ferramentas e recursos para o retorno de 2061") ?></p>
                </div>
            </div>

            <h2><?= t("Open Source & Community Driven", "Código Aberto & Impulsionado pela Comunidade") ?></h2>
            <p>
                <?= t(
                    "The Halley Project is an open-source initiative. We believe that knowledge about our universe should be freely accessible to all. Our codebase is publicly available, and we welcome contributions from developers, astronomers, educators, and enthusiasts worldwide.",
                    "O The Halley Project é uma iniciativa de código aberto. Acreditamos que o conhecimento sobre nosso universo deve ser livremente acessível a todos. Nosso código é publicamente disponível, e acolhemos contribuições de desenvolvedores, astrônomos, educadores e entusiastas de todo o mundo."
                ) ?>
            </p>

            <div class="roadmap">
                <h2><?= t("Project Roadmap", "Roteiro do Projeto") ?></h2>
                <div class="roadmap-item">
                    <h3><?= t("Phase 1: Foundation (Current)", "Fase 1: Fundação (Atual)") ?></h3>
                    <p><?= t("Core website with countdown, historical information, observation guides, and community forum", "Site principal com contagem regressiva, informações históricas, guias de observação e fórum da comunidade") ?></p>
                </div>
                <div class="roadmap-item">
                    <h3><?= t("Phase 2: Expansion (2025-2026)", "Fase 2: Expansão (2025-2026)") ?></h3>
                    <p><?= t("Additional languages, interactive tools, and real-time comet position tracking", "Idiomas adicionais, ferramentas interativas e rastreamento de posição do cometa em tempo real") ?></p>
                </div>
                <div class="roadmap-item">
                    <h3><?= t("Phase 3: Coming soon", "Fase 3: Em breve") ?></h3>
                    <p><?= t("Coming soon", "Em breve") ?></p>
                </div>
                <div class="roadmap-item">
                    <h3><?= t("Phase 5: The Return (2061)", "Fase 5: O Retorno (2061)") ?></h3>
                    <p><?= t("Real-time coverage, community observations database, and historical documentation of the event", "Cobertura em tempo real, banco de dados de observações da comunidade e documentação histórica do evento") ?></p>
                </div>
            </div>

            <!-- Contact -->
            <div class="contact-section">
                <h2><?= t("Get Involved", "Participe") ?></h2>
                <p><?= t("Join us in building the ultimate resource for Halley's Comet", "Junte-se a nós na construção do recurso definitivo sobre o Cometa Halley") ?></p>
                <div class="contact-methods">
                    <a href="mailto:halleyproject@protonmail.com" class="contact-button">📧 Email</a>
                    <a href="https://github.com/GuiXavier/thehalleyproject" class="contact-button" target="_blank">💻 GitHub</a>
                    <a href="/forum/" class="contact-button">💬 <?= t("Forum", "Fórum") ?></a>
                </div>
            </div>

            <!-- Support -->
            <div class="support-section">
                <h2><?= t("Support The Project", "Apoie o Projeto") ?></h2>
                <p>
                    <?= t(
                        "The Halley Project is a labor of love, maintained by volunteers passionate about astronomy and education. Your support helps us maintain servers, develop new features, and keep this resource free for everyone.",
                        "O The Halley Project é um trabalho de amor, mantido por voluntários apaixonados por astronomia e educação. Seu apoio nos ajuda a manter servidores, desenvolver novos recursos e manter este recurso gratuito para todos."
                    ) ?>
                </p>
                <div class="crypto-addresses">
                    <div class="crypto-item" style="opacity: 0.5;">
                        <div class="crypto-label">Bitcoin (BTC)</div>
                        <div><?= t("Address coming soon...", "Endereço em breve...") ?></div>
                    </div>
                    <div class="crypto-item" style="opacity: 0.5;">
                        <div class="crypto-label">Monero (XMR)</div>
                        <div><?= t("Address coming soon...", "Endereço em breve...") ?></div>
                    </div>
                </div>
            </div>

            <h2><?= t("Looking to the Future", "Olhando para o Futuro") ?></h2>
            <p>
                <?= t(
                    "While 2061 may seem far away, the journey to that momentous return is just as important as the destination. Every day, we're working to ensure that when Halley's Comet graces our skies again, humanity will be ready with the knowledge, tools, and community to fully appreciate this cosmic visitor.",
                    "Embora 2061 possa parecer distante, a jornada até esse retorno memorável é tão importante quanto o destino. Todos os dias, trabalhamos para garantir que, quando o Cometa Halley adornar nossos céus novamente, a humanidade esteja pronta com o conhecimento, as ferramentas e a comunidade para apreciar plenamente esse visitante cósmico."
                ) ?>
            </p>
        </div>

        <a href="/" class="back-button">
            <span>←</span>
            <?= t("Back to Home", "Voltar ao Início") ?>
        </a>
    </article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
