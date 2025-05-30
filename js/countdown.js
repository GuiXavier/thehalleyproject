document.addEventListener('DOMContentLoaded', () => {
    const countdownElement = document.getElementById('countdown');

    // Só executa o script se o elemento #countdown existir na página
    if (countdownElement) {
        const targetDate = new Date("2061-07-28T00:00:00Z").getTime();

        // Pega os textos traduzidos dos atributos data-* do elemento HTML
        const textYears = countdownElement.getAttribute('data-text-years') || 'years';
        const textDays = countdownElement.getAttribute('data-text-days') || 'days';
        const textHours = countdownElement.getAttribute('data-text-hours') || 'h';
        const textMinutes = countdownElement.getAttribute('data-text-minutes') || 'm';
        const textSeconds = countdownElement.getAttribute('data-text-seconds') || 's';
        const textArrived = countdownElement.getAttribute('data-text-arrived') || "The event has started!"; // Mensagem padrão

        let countdownInterval; // Variável para guardar o ID do intervalo

        function updateCountdown() {
            const now = new Date().getTime();
            let diff = targetDate - now;

            if (diff <= 0) {
                countdownElement.innerHTML = textArrived;
                clearInterval(countdownInterval); // Para o contador quando chega a zero
                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const totalMinutes = Math.floor(totalSeconds / 60);
            const totalHours = Math.floor(totalMinutes / 60);
            const totalDays = Math.floor(totalHours / 24);

            const years = Math.floor(totalDays / 365.25);
            const days = Math.floor(totalDays % 365.25); // Dias restantes no ano atual da contagem
            const hours = totalHours % 24;
            const minutes = totalMinutes % 60;
            const seconds = totalSeconds % 60;

            // Monta a string usando os textos dos atributos data-*
            countdownElement.innerHTML =
                `${years} ${textYears}, ${days} ${textDays}, ${hours}${textHours} ${minutes}${textMinutes} ${seconds}${textSeconds}`;
        }

        updateCountdown(); // Chama a função uma vez imediatamente para mostrar o tempo sem esperar 1 segundo
        countdownInterval = setInterval(updateCountdown, 1000); // Atualiza a cada segundo
    }
});