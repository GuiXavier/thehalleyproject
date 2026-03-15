<?php
$page_title = t("History of Halley's Comet | The Halley Project", "História do Cometa Halley | The Halley Project");
$active_page = 'history';

require_once __DIR__ . '/../includes/header.php';
?>

    <!-- Article Container -->
    <article class="article-container">
        <header class="article-header">
            <h1><?= t("History of Halley's Comet", "História do Cometa Halley") ?></h1>
            <div class="article-meta">
                <span>📅 <?= t("Updated: January 2025", "Atualizado: Janeiro 2025") ?></span>
                <span>⏱️ <?= t("10 min read", "10 min de leitura") ?></span>
            </div>
        </header>

        <div class="article-content">
            <p>
                <?= t(
                    "Halley's Comet, officially designated 1P/Halley, is the most famous of all periodic comets and has been observed by humanity for over two millennia. Its regular appearances have marked significant moments in human history and helped advance our understanding of the cosmos.",
                    "O Cometa Halley, oficialmente designado 1P/Halley, é o mais famoso de todos os cometas periódicos e tem sido observado pela humanidade por mais de dois milênios. Suas aparições regulares marcaram momentos significativos na história humana e ajudaram a avançar nossa compreensão do cosmos."
                ) ?>
            </p>

            <h2><?= t("Ancient Observations", "Observações Antigas") ?></h2>
            <p>
                <?= t(
                    "The first confirmed observation of Halley's Comet dates back to 240 BC, recorded by Chinese astronomers in the chronicles <em>Shih Chi</em> and <em>Wen Hsien T'ung K'ao</em>. They described it as a \"broom star\" sweeping across the sky.",
                    "A primeira observação confirmada do Cometa Halley data de 240 a.C., registrada por astrônomos chineses nas crônicas <em>Shih Chi</em> e <em>Wen Hsien T'ung K'ao</em>. Eles o descreveram como uma \"estrela vassoura\" varrendo o céu."
                ) ?>
            </p>
            <p><?= t("Throughout antiquity, the comet appeared regularly in historical records:", "Ao longo da antiguidade, o cometa apareceu regularmente em registros históricos:") ?></p>
            <ul>
                <li><strong><?= t("164 BC:", "164 a.C.:") ?></strong> <?= t("Babylonian astronomers recorded the comet on clay tablets", "Astrônomos babilônicos registraram o cometa em tábuas de argila") ?></li>
                <li><strong><?= t("12 BC:", "12 a.C.:") ?></strong> <?= t("Chinese records note a \"broom star\" visible for 56 days", "Registros chineses notam uma \"estrela vassoura\" visível por 56 dias") ?></li>
                <li><strong><?= t("66 AD:", "66 d.C.:") ?></strong> <?= t("Jewish historian Josephus described it as \"a star resembling a sword\" over Jerusalem", "O historiador judeu Josefo o descreveu como \"uma estrela semelhante a uma espada\" sobre Jerusalém") ?></li>
                <li><strong><?= t("451 AD:", "451 d.C.:") ?></strong> <?= t("Appeared during the defeat of Attila the Hun in Europe", "Apareceu durante a derrota de Átila, o Huno, na Europa") ?></li>
            </ul>

            <h2><?= t("The Norman Conquest - 1066", "A Conquista Normanda - 1066") ?></h2>
            <p>
                <?= t(
                    "One of Halley's Comet's most famous appearances occurred in 1066, shortly before the Battle of Hastings. The comet is immortalized in the Bayeux Tapestry, where it is depicted as an omen. King Harold II of England saw it as a bad sign, while William the Conqueror interpreted it as a favorable portent for his invasion.",
                    "Uma das aparições mais famosas do Cometa Halley ocorreu em 1066, pouco antes da Batalha de Hastings. O cometa está imortalizado na Tapeçaria de Bayeux, onde é retratado como um presságio. O Rei Harold II da Inglaterra o viu como um mau sinal, enquanto Guilherme, o Conquistador, interpretou-o como um portento favorável para sua invasão."
                ) ?>
            </p>
            
            <blockquote>
                <?= t(
                    "\"You came, didn't you? You came, source of tears for many mothers. It has been a long time since I saw you; but as I see you now, you are much more terrible.\"",
                    "\"Você veio, não é? Você veio, fonte de lágrimas para muitas mães. Faz tempo que não o via; mas como o vejo agora, você é muito mais terrível.\""
                ) ?>
                <cite>— <?= t("Eilmer of Malmesbury, 1066", "Eilmer de Malmesbury, 1066") ?></cite>
            </blockquote>

            <h2><?= t("Edmund Halley's Discovery", "A Descoberta de Edmund Halley") ?></h2>
            <p>
                <?= t(
                    "The comet is named after English astronomer Edmund Halley (1656-1742), who was the first to recognize that comets could be periodic. Using Newton's newly formulated laws of motion and gravitation, Halley calculated the orbits of 24 comets observed between 1337 and 1698.",
                    "O cometa recebeu o nome do astrônomo inglês Edmund Halley (1656-1742), que foi o primeiro a reconhecer que os cometas poderiam ser periódicos. Usando as leis do movimento e gravitação recém-formuladas por Newton, Halley calculou as órbitas de 24 cometas observados entre 1337 e 1698."
                ) ?>
            </p>
            <p>
                <?= t(
                    "He noted that the comets of 1531, 1607, and 1682 had remarkably similar orbital elements and concluded they were the same object returning approximately every 76 years. In 1705, he published his findings and boldly predicted the comet would return in 1758.",
                    "Ele notou que os cometas de 1531, 1607 e 1682 tinham elementos orbitais notavelmente similares e concluiu que eram o mesmo objeto retornando aproximadamente a cada 76 anos. Em 1705, ele publicou suas descobertas e audaciosamente previu que o cometa retornaria em 1758."
                ) ?>
            </p>
            <p>
                <?= t(
                    "Halley died in 1742, but his prediction proved correct when the comet was recovered on Christmas Day 1758 by German farmer and amateur astronomer Johann Georg Palitzsch. This successful prediction was a triumph for Newtonian physics.",
                    "Halley morreu em 1742, mas sua previsão provou-se correta quando o cometa foi recuperado no dia de Natal de 1758 pelo fazendeiro alemão e astrônomo amador Johann Georg Palitzsch. Esta previsão bem-sucedida foi um triunfo para a física newtoniana."
                ) ?>
            </p>

            <h2><?= t("The 1910 Panic", "O Pânico de 1910") ?></h2>
            <p>
                <?= t(
                    "The 1910 return of Halley's Comet caused worldwide panic when spectroscopic analysis revealed the presence of cyanogen gas in its tail. As Earth was expected to pass through the tail, sensationalist newspapers predicted mass poisoning, despite scientists' assurances that the gas was too diffuse to pose any threat.",
                    "O retorno de 1910 do Cometa Halley causou pânico mundial quando a análise espectroscópica revelou a presença de gás cianogênio em sua cauda. Como a Terra deveria passar pela cauda, jornais sensacionalistas previram envenenamento em massa, apesar das garantias dos cientistas de que o gás era muito difuso para representar qualquer ameaça."
                ) ?>
            </p>

            <h2><?= t("The Space Age Encounter - 1986", "O Encontro da Era Espacial - 1986") ?></h2>
            <p>
                <?= t(
                    "The 1986 return marked the first time spacecraft could study a comet up close. An international fleet of spacecraft, nicknamed the \"Halley Armada\", was launched to intercept the comet:",
                    "O retorno de 1986 marcou a primeira vez que naves espaciais puderam estudar um cometa de perto. Uma frota internacional de espaçonaves, apelidada de \"Armada Halley\", foi lançada para interceptar o cometa:"
                ) ?>
            </p>
            <ul>
                <li><strong>Giotto (ESA):</strong> <?= t("Passed within 596 km of the nucleus", "Passou a 596 km do núcleo") ?></li>
                <li><strong>Vega 1 & 2 (<?= t("USSR", "URSS") ?>):</strong> <?= t("Flew at distances of 8,890 km and 8,030 km", "Voaram a distâncias de 8.890 km e 8.030 km") ?></li>
                <li><strong>Suisei & Sakigake (<?= t("Japan", "Japão") ?>):</strong> <?= t("Studied the comet from greater distances", "Estudaram o cometa de distâncias maiores") ?></li>
                <li><strong>ICE (<?= t("USA", "EUA") ?>):</strong> <?= t("Observed from 28 million km away", "Observou de 28 milhões de km de distância") ?></li>
            </ul>
            <p>
                <?= t(
                    "These missions revealed that Halley's nucleus is a dark, peanut-shaped object about 15 km long and 8 km wide, composed of ice and dust — confirming Fred Whipple's \"dirty snowball\" hypothesis.",
                    "Essas missões revelaram que o núcleo de Halley é um objeto escuro em forma de amendoim com cerca de 15 km de comprimento e 8 km de largura, composto de gelo e poeira — confirmando a hipótese da \"bola de neve suja\" de Fred Whipple."
                ) ?>
            </p>

            <h2><?= t("Cultural Impact", "Impacto Cultural") ?></h2>
            <ul>
                <li><?= t("Italian artist Giotto di Bondone included it as the Star of Bethlehem in his 1305 fresco \"Adoration of the Magi\"", "O artista italiano Giotto di Bondone o incluiu como a Estrela de Belém em seu afresco de 1305 \"Adoração dos Magos\"") ?></li>
                <li><?= t("Mark Twain was born during the 1835 appearance and correctly predicted he would die with its return in 1910", "Mark Twain nasceu durante a aparição de 1835 e previu corretamente que morreria com seu retorno em 1910") ?></li>
                <li><?= t("The comet has inspired countless works of literature, music, and art throughout history", "O cometa inspirou inúmeras obras de literatura, música e arte ao longo da história") ?></li>
            </ul>

            <h2><?= t("Next Return: 2061", "Próximo Retorno: 2061") ?></h2>
            <p>
                <?= t(
                    "Halley's Comet will reach perihelion on July 28, 2061. This return is expected to be particularly favorable for observers, as the comet will pass relatively close to Earth and will be well positioned for viewing in both hemispheres.",
                    "O Cometa Halley alcançará o periélio em 28 de julho de 2061. Espera-se que este retorno seja particularmente favorável para observadores, pois o cometa passará relativamente perto da Terra e estará bem posicionado para visualização em ambos os hemisférios."
                ) ?>
            </p>

            <div class="featured" style="margin-top: 3rem;">
                <h3><?= t("Quick Facts about Halley's Comet", "Fatos Rápidos sobre o Cometa Halley") ?></h3>
                <ul>
                    <li><?= t("Official designation: 1P/Halley", "Designação oficial: 1P/Halley") ?></li>
                    <li><?= t("Orbital period: 75-76 years", "Período orbital: 75-76 anos") ?></li>
                    <li><?= t("Last perihelion: February 9, 1986", "Último periélio: 9 de fevereiro de 1986") ?></li>
                    <li><?= t("Next perihelion: July 28, 2061", "Próximo periélio: 28 de julho de 2061") ?></li>
                    <li><?= t("Nucleus size: 15 × 8 km", "Tamanho do núcleo: 15 × 8 km") ?></li>
                    <li><?= t("First recorded observation: 240 BC", "Primeira observação registrada: 240 a.C.") ?></li>
                </ul>
            </div>
        </div>

        <a href="/" class="back-button">
            <span>←</span>
            <?= t("Back to Home", "Voltar ao Início") ?>
        </a>
    </article>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
