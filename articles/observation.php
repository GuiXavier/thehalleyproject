<?php
$page_title = t("How to Observe Halley's Comet | The Halley Project", "Como Observar o Cometa Halley | The Halley Project");
$active_page = 'observation';

require_once __DIR__ . '/../includes/header.php';
?>

    <article class="article-container">
        <header class="article-header">
            <h1><?= t("How to Observe Halley's Comet", "Como Observar o Cometa Halley") ?></h1>
            <div class="article-meta">
                <span>📅 <?= t("Updated: January 2025", "Atualizado: Janeiro 2025") ?></span>
                <span>⏱️ <?= t("8 min read", "8 min de leitura") ?></span>
            </div>
        </header>

        <div class="article-content">
            <p>
                <?= t(
                    "Halley's Comet will return to our skies in 2061, offering a spectacular observation opportunity for observers worldwide. This comprehensive guide will help you prepare and maximize your viewing experience of this once-in-a-lifetime celestial event.",
                    "O Cometa Halley retornará aos nossos céus em 2061, oferecendo uma oportunidade espetacular de observação para observadores em todo o mundo. Este guia completo ajudará você a se preparar e maximizar sua experiência de visualização deste evento celestial único na vida."
                ) ?>
            </p>

            <h2><?= t("When to Observe", "Quando Observar") ?></h2>
            <p>
                <?= t(
                    "The comet will reach perihelion (closest approach to the Sun) on <strong>July 28, 2061</strong>. However, the best observation periods will be:",
                    "O cometa alcançará o periélio (aproximação mais próxima do Sol) em <strong>28 de julho de 2061</strong>. No entanto, os melhores períodos de observação serão:"
                ) ?>
            </p>
            <ul>
                <li><strong><?= t("May - June 2061:", "Maio - Junho 2061:") ?></strong> <?= t("Pre-perihelion viewing as the comet brightens", "Visualização pré-periélio enquanto o cometa brilha") ?></li>
                <li><strong><?= t("August - September 2061:", "Agosto - Setembro 2061:") ?></strong> <?= t("Post-perihelion viewing with a fully developed tail", "Visualização pós-periélio com uma cauda totalmente desenvolvida") ?></li>
                <li><strong><?= t("Peak brightness:", "Brilho máximo:") ?></strong> <?= t("Expected around late July to early August 2061", "Esperado por volta do final de julho ao início de agosto de 2061") ?></li>
            </ul>

            <h2><?= t("Ideal Observation Conditions", "Condições Ideais de Observação") ?></h2>
            
            <h3><?= t("Location Requirements", "Requisitos de Localização") ?></h3>
            <ul>
                <li><strong><?= t("Dark skies:", "Céus escuros:") ?></strong> <?= t("Get away from city lights. Light pollution significantly reduces visibility", "Afaste-se das luzes da cidade. A poluição luminosa reduz significativamente a visibilidade") ?></li>
                <li><strong><?= t("Altitude:", "Altitude:") ?></strong> <?= t("Higher elevations offer clearer views with less atmospheric interference", "Altitudes mais elevadas oferecem vistas mais claras com menos interferência atmosférica") ?></li>
                <li><strong><?= t("Clear horizons:", "Horizontes limpos:") ?></strong> <?= t("Choose locations with unobstructed views, especially to the east and west", "Escolha locais com vistas desobstruídas, especialmente para o leste e oeste") ?></li>
            </ul>

            <h3><?= t("Best Time of Night", "Melhor Horário da Noite") ?></h3>
            <ul>
                <li><?= t("Before perihelion: Best viewed in the pre-dawn hours (3-5 AM local time)", "Antes do periélio: Melhor visualizado nas horas antes do amanhecer (3-5h horário local)") ?></li>
                <li><?= t("After perihelion: Evening observations after sunset will be favorable", "Após o periélio: Observações noturnas após o pôr do sol serão favoráveis") ?></li>
                <li><?= t("Allow 20-30 minutes for your eyes to adapt to darkness", "Permita 20-30 minutos para seus olhos se adaptarem à escuridão") ?></li>
            </ul>

            <h2><?= t("Equipment Guide", "Guia de Equipamentos") ?></h2>
            
            <h3><?= t("Naked Eye Observation", "Observação a Olho Nu") ?></h3>
            <p><?= t("Halley's Comet will be visible without any equipment during its peak. You will be able to see:", "O Cometa Halley será visível sem qualquer equipamento durante seu pico. Você poderá ver:") ?></p>
            <ul>
                <li><?= t("The bright nucleus (head) of the comet", "O núcleo brilhante (cabeça) do cometa") ?></li>
                <li><?= t("A visible tail extending several degrees across the sky", "Uma cauda visível estendendo-se vários graus pelo céu") ?></li>
                <li><?= t("Possible color variations (greenish head, whitish tail)", "Possíveis variações de cor (cabeça esverdeada, cauda esbranquiçada)") ?></li>
            </ul>

            <h3><?= t("Binoculars (Recommended)", "Binóculos (Recomendado)") ?></h3>
            <ul>
                <li><strong><?= t("Recommended specs:", "Especificações recomendadas:") ?></strong> <?= t("7×50 or 10×50 binoculars", "Binóculos 7×50 ou 10×50") ?></li>
                <li><strong><?= t("Benefits:", "Benefícios:") ?></strong> <?= t("Reveals more tail structure and coma details", "Revela mais estrutura da cauda e detalhes na coma") ?></li>
                <li><strong><?= t("Wide field:", "Campo amplo:") ?></strong> <?= t("Better for tracking the comet's movement", "Melhor para rastrear o movimento do cometa") ?></li>
            </ul>

            <h3><?= t("Telescopes", "Telescópios") ?></h3>
            <ul>
                <li><strong><?= t("Small telescopes (60-80mm):", "Telescópios pequenos (60-80mm):") ?></strong> <?= t("Good for overall comet structure", "Bom para estrutura geral do cometa") ?></li>
                <li><strong><?= t("Medium telescopes (100-200mm):", "Telescópios médios (100-200mm):") ?></strong> <?= t("Can reveal jets and tail details", "Podem revelar jatos e detalhes da cauda") ?></li>
                <li><strong><?= t("Use low magnification:", "Use baixa ampliação:") ?></strong> <?= t("20-40x is ideal for the full comet", "20-40x é ideal para o cometa completo") ?></li>
            </ul>

            <h2><?= t("Photography Tips", "Dicas de Fotografia") ?></h2>
            <ul>
                <li><strong><?= t("Camera:", "Câmera:") ?></strong> <?= t("DSLR or mirrorless with manual controls", "DSLR ou mirrorless com controles manuais") ?></li>
                <li><strong><?= t("Lens:", "Lente:") ?></strong> <?= t("Wide angle (14-35mm) for landscape shots, telephoto (200mm+) for close-ups", "Grande angular (14-35mm) para fotos de paisagem, telefoto (200mm+) para close-ups") ?></li>
                <li><strong><?= t("Tripod:", "Tripé:") ?></strong> <?= t("Essential for stability during long exposures", "Essencial para estabilidade durante longas exposições") ?></li>
                <li><strong><?= t("Settings:", "Configurações:") ?></strong> <?= t("ISO 400-1600, 10-30 second exposures", "ISO 400-1600, exposições de 10-30 segundos") ?></li>
            </ul>

            <h2><?= t("Best Locations in Brazil", "Melhores Locais no Brasil") ?></h2>
            <ul>
                <li><strong>Chapada dos Veadeiros, GO:</strong> <?= t("Dark sky and high altitude", "Céu escuro e altitude elevada") ?></li>
                <li><strong><?= t("Atacama Desert (Chile - nearby):", "Deserto do Atacama (Chile - próximo):") ?></strong> <?= t("One of the cleanest skies in the world", "Um dos céus mais limpos do mundo") ?></li>
                <li><strong>Serra da Canastra, MG:</strong> <?= t("Low light pollution and good infrastructure", "Pouca poluição luminosa e boa infraestrutura") ?></li>
                <li><strong>Chapada Diamantina, BA:</strong> <?= t("Excellent dark sky conditions", "Excelentes condições de céu escuro") ?></li>
            </ul>

            <div class="featured" style="margin-top: 2rem;">
                <h3><?= t("Essential Checklist for Comet Observation", "Itens Essenciais para Observação do Cometa") ?></h3>
                <ul style="text-align: left;">
                    <li>☑️ <?= t("Red flashlight (preserves night vision)", "Lanterna vermelha (preserva a visão noturna)") ?></li>
                    <li>☑️ <?= t("Star charts or astronomy apps", "Mapas celestes ou aplicativos de astronomia") ?></li>
                    <li>☑️ <?= t("Comfortable chair or blanket", "Cadeira confortável ou cobertor") ?></li>
                    <li>☑️ <?= t("Warm clothing (nights can be cold)", "Roupas quentes (noites podem ser frias)") ?></li>
                    <li>☑️ <?= t("Binoculars or telescope", "Binóculos ou telescópio") ?></li>
                    <li>☑️ <?= t("Camera and tripod (for photography)", "Câmera e tripé (para fotografia)") ?></li>
                    <li>☑️ <?= t("Snacks and water", "Lanches e água") ?></li>
                    <li>☑️ <?= t("Observation notebook", "Caderno para observações") ?></li>
                </ul>
            </div>

            <h2><?= t("Scientific Observation", "Observação Científica") ?></h2>
            <p><?= t("Amateur astronomers can contribute valuable scientific data:", "Astrônomos amadores podem contribuir com dados científicos valiosos:") ?></p>
            <ul>
                <li><strong><?= t("Magnitude estimates:", "Estimativas de magnitude:") ?></strong> <?= t("Track the comet's brightness changes", "Acompanhe as mudanças de brilho do cometa") ?></li>
                <li><strong><?= t("Tail measurements:", "Medições da cauda:") ?></strong> <?= t("Document the tail's length and position angle", "Documente o comprimento e ângulo de posição da cauda") ?></li>
                <li><strong><?= t("Drawings:", "Desenhos:") ?></strong> <?= t("Visual records complement photography", "Registros visuais complementam a fotografia") ?></li>
                <li><strong><?= t("Report observations:", "Relatar observações:") ?></strong> <?= t("Submit to organizations like the International Comet Quarterly", "Envie para organizações como o International Comet Quarterly") ?></li>
            </ul>

            <blockquote>
                <?= t(
                    "\"For most people, Halley's Comet is a once or twice in a lifetime event. Take the time to appreciate not just the spectacle, but the profound connection with the cosmos it represents.\"",
                    "\"Para a maioria das pessoas, o Cometa Halley é um evento único ou duas vezes na vida. Reserve um tempo para apreciar não apenas o espetáculo, mas a profunda conexão com o cosmos que ele representa.\""
                ) ?>
            </blockquote>
        </div>

        <a href="/" class="back-button">
            <span>←</span>
            <?= t("Back to Home", "Voltar ao Início") ?>
        </a>
    </article>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
