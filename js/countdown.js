/**
 * The Halley Project - Countdown System
 * Accurate countdown to Halley's Comet return in 2061
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        targetDate: new Date('2061-07-28T00:00:00Z'),
        updateInterval: 1000,
        language: document.documentElement.lang || 'en'
    };

    // Language strings
    const TRANSLATIONS = {
        'en': {
            years: 'Years',
            days: 'Days',
            hours: 'Hours',
            minutes: 'Minutes',
            seconds: 'Seconds',
            arrived: "Halley's Comet has arrived!"
        },
        'pt-BR': {
            years: 'Anos',
            days: 'Dias',
            hours: 'Horas',
            minutes: 'Minutos',
            seconds: 'Segundos',
            arrived: 'O Cometa Halley chegou!'
        },
        'pt': {
            years: 'Anos',
            days: 'Dias',
            hours: 'Horas',
            minutes: 'Minutos',
            seconds: 'Segundos',
            arrived: 'O Cometa Halley chegou!'
        }
    };

    // Get translation based on current language
    function getTranslation(key) {
        const lang = CONFIG.language;
        return (TRANSLATIONS[lang] && TRANSLATIONS[lang][key]) || TRANSLATIONS['en'][key];
    }

    // Calculate time difference
    function calculateTimeDifference() {
        const now = new Date().getTime();
        const target = CONFIG.targetDate.getTime();
        const difference = target - now;

        if (difference <= 0) {
            return {
                years: 0,
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
                isArrived: true
            };
        }

        const seconds = Math.floor(difference / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        const years = Math.floor(days / 365.25);

        return {
            years: years,
            days: Math.floor(days % 365.25),
            hours: hours % 24,
            minutes: minutes % 60,
            seconds: seconds % 60,
            isArrived: false
        };
    }

    // Format number with leading zero if needed
    function formatNumber(num, digits = 2) {
        return num.toString().padStart(digits, '0');
    }

    // Create countdown HTML structure
    function createCountdownStructure() {
        const container = document.getElementById('countdown');
        if (!container) return null;

        // Clear existing content
        container.innerHTML = '';

        // Create units
        const units = ['years', 'days', 'hours', 'minutes', 'seconds'];
        
        units.forEach(unit => {
            const unitDiv = document.createElement('div');
            unitDiv.className = 'countdown-unit';
            
            const valueDiv = document.createElement('div');
            valueDiv.className = 'countdown-value';
            valueDiv.id = unit;
            valueDiv.textContent = '00';
            
            const labelDiv = document.createElement('div');
            labelDiv.className = 'countdown-label-unit';
            labelDiv.textContent = getTranslation(unit);
            
            unitDiv.appendChild(valueDiv);
            unitDiv.appendChild(labelDiv);
            container.appendChild(unitDiv);
        });

        return container;
    }

    // Update countdown display
    function updateCountdown() {
        const time = calculateTimeDifference();
        
        if (time.isArrived) {
            const container = document.getElementById('countdown');
            if (container) {
                container.innerHTML = `<div class="countdown-arrived">${getTranslation('arrived')}</div>`;
            }
            return false; // Stop updating
        }

        // Update each unit
        const yearsEl = document.getElementById('years');
        const daysEl = document.getElementById('days');
        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        const secondsEl = document.getElementById('seconds');

        if (yearsEl) yearsEl.textContent = time.years;
        if (daysEl) daysEl.textContent = time.days;
        if (hoursEl) hoursEl.textContent = formatNumber(time.hours);
        if (minutesEl) minutesEl.textContent = formatNumber(time.minutes);
        if (secondsEl) secondsEl.textContent = formatNumber(time.seconds);

        return true; // Continue updating
    }

    // Add smooth number transition effect
    function animateValue(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        
        if (currentValue !== newValue) {
            element.style.transform = 'scale(1.1)';
            element.textContent = formatNumber(newValue);
            
            setTimeout(() => {
                element.style.transform = 'scale(1)';
            }, 200);
        }
    }

    // Initialize countdown
    function initCountdown() {
        // Create structure if not exists
        const container = document.getElementById('countdown');
        if (!container) {
            console.warn('Countdown container not found');
            return;
        }

        // Check if structure needs to be created
        if (!container.querySelector('.countdown-unit')) {
            createCountdownStructure();
        }

        // Initial update
        updateCountdown();

        // Set up interval
        const intervalId = setInterval(() => {
            const shouldContinue = updateCountdown();
            if (!shouldContinue) {
                clearInterval(intervalId);
            }
        }, CONFIG.updateInterval);

        // Clean up on page unload
        window.addEventListener('beforeunload', () => {
            clearInterval(intervalId);
        });
    }

    // Public API
    window.HalleyCountdown = {
        init: initCountdown,
        update: updateCountdown,
        getTimeRemaining: calculateTimeDifference
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCountdown);
    } else {
        initCountdown();
    }

})();