/**
 * The Halley Project - Stars Background Effect
 * Creates an animated starfield background
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        starCount: 150,
        minSize: 1,
        maxSize: 3,
        minDuration: 2,
        maxDuration: 5,
        containerClass: 'stars',
        starClass: 'star'
    };

    // Create a single star element
    function createStar() {
        const star = document.createElement('div');
        star.className = CONFIG.starClass;
        
        // Random position
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        
        // Random size
        const size = Math.random() * (CONFIG.maxSize - CONFIG.minSize) + CONFIG.minSize;
        star.style.width = size + 'px';
        star.style.height = size + 'px';
        
        // Random animation duration
        const duration = Math.random() * (CONFIG.maxDuration - CONFIG.minDuration) + CONFIG.minDuration;
        star.style.animationDuration = duration + 's';
        
        // Random animation delay
        star.style.animationDelay = Math.random() * CONFIG.maxDuration + 's';
        
        // Random opacity
        star.style.opacity = Math.random() * 0.8 + 0.2;
        
        return star;
    }

    // Create shooting star effect
    function createShootingStar() {
        const shootingStar = document.createElement('div');
        shootingStar.className = 'shooting-star';
        
        // Random starting position
        const startX = Math.random() * 100;
        const startY = Math.random() * 50; // Upper half of screen
        
        shootingStar.style.left = startX + '%';
        shootingStar.style.top = startY + '%';
        
        // Add CSS for shooting star
        const style = document.createElement('style');
        style.textContent = `
            .shooting-star {
                position: absolute;
                width: 2px;
                height: 2px;
                background: linear-gradient(90deg, transparent, #fff, transparent);
                animation: shootingAnimation 1s linear forwards;
                pointer-events: none;
            }
            
            @keyframes shootingAnimation {
                from {
                    transform: translateX(0) translateY(0);
                    opacity: 1;
                    width: 2px;
                }
                to {
                    transform: translateX(200px) translateY(100px);
                    opacity: 0;
                    width: 80px;
                }
            }
        `;
        
        // Add style if not already added
        if (!document.querySelector('style[data-shooting-star]')) {
            style.setAttribute('data-shooting-star', 'true');
            document.head.appendChild(style);
        }
        
        return shootingStar;
    }

    // Initialize starfield
    function initStars() {
        // Check if container already exists
        let container = document.querySelector('.' + CONFIG.containerClass);
        
        if (!container) {
            // Create container
            container = document.createElement('div');
            container.className = CONFIG.containerClass;
            document.body.insertBefore(container, document.body.firstChild);
        }
        
        // Clear existing stars (in case of re-initialization)
        container.innerHTML = '';
        
        // Create stars
        for (let i = 0; i < CONFIG.starCount; i++) {
            const star = createStar();
            container.appendChild(star);
        }
        
        // Occasionally create shooting stars
        if (Math.random() > 0.7) { // 30% chance
            setInterval(() => {
                if (Math.random() > 0.8) { // 20% chance every interval
                    const shootingStar = createShootingStar();
                    container.appendChild(shootingStar);
                    
                    // Remove shooting star after animation
                    setTimeout(() => {
                        shootingStar.remove();
                    }, 1000);
                }
            }, 5000); // Check every 5 seconds
        }
    }

    // Performance optimization: reduce stars on mobile
    function optimizeForDevice() {
        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        const isLowPerformance = navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4;
        
        if (isMobile || isLowPerformance) {
            CONFIG.starCount = Math.floor(CONFIG.starCount * 0.5); // Reduce by 50%
        }
    }

    // Handle visibility change to pause/resume animations
    function handleVisibilityChange() {
        const container = document.querySelector('.' + CONFIG.containerClass);
        if (!container) return;
        
        if (document.hidden) {
            container.style.animationPlayState = 'paused';
        } else {
            container.style.animationPlayState = 'running';
        }
    }

    // Add parallax effect on mouse move (desktop only)
    function addParallaxEffect() {
        if (window.matchMedia('(min-width: 768px)').matches) {
            let mouseX = 0;
            let mouseY = 0;
            let parallaxX = 0;
            let parallaxY = 0;
            
            document.addEventListener('mousemove', (e) => {
                mouseX = (e.clientX / window.innerWidth - 0.5) * 20;
                mouseY = (e.clientY / window.innerHeight - 0.5) * 20;
            });
            
            function updateParallax() {
                parallaxX += (mouseX - parallaxX) * 0.1;
                parallaxY += (mouseY - parallaxY) * 0.1;
                
                const container = document.querySelector('.' + CONFIG.containerClass);
                if (container) {
                    container.style.transform = `translate(${parallaxX}px, ${parallaxY}px)`;
                }
                
                requestAnimationFrame(updateParallax);
            }
            
            updateParallax();
        }
    }

    // Public API
    window.HalleyStars = {
        init: initStars,
        createStar: createStar,
        createShootingStar: createShootingStar
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            optimizeForDevice();
            initStars();
            addParallaxEffect();
            
            // Handle visibility changes
            document.addEventListener('visibilitychange', handleVisibilityChange);
        });
    } else {
        optimizeForDevice();
        initStars();
        addParallaxEffect();
        document.addEventListener('visibilitychange', handleVisibilityChange);
    }

})();