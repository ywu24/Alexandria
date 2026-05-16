/**
 * Alexandria Library Management System
 *
 * @file Homepage — hero slider autoplay and stat counter animations
 */
(function () {
    localStorage.removeItem('reinvio_count');
    localStorage.removeItem('reg_form_nome');
    localStorage.removeItem('reg_form_cognome');
    localStorage.removeItem('reg_form_email');
    localStorage.removeItem('log_form_email');

    var sliderInterval;

    function Check_next() {
        var wanted = document.getElementsByName("slider");
        for (var i = 0; i < wanted.length; ++i) {
            if (wanted[i].checked === true) {
                if (i === wanted.length - 1) {
                    wanted[0].checked = true;
                } else {
                    wanted[i + 1].checked = true;
                }
                break;
            }
        }
    }

    function resetSliderInterval() {
        clearInterval(sliderInterval);
        sliderInterval = setInterval(function () {
            Check_next();
        }, 5000);
    }

    function initSlider() {
        sliderInterval = setInterval(function () {
            Check_next();
        }, 5000);

        var sliderInputs = document.querySelectorAll('input[name="slider"]');
        sliderInputs.forEach(function (input) {
            input.addEventListener('change', resetSliderInterval);
        });

        var sliderDots = document.querySelectorAll('.slider-dots .dot');
        sliderDots.forEach(function (dot) {
            dot.addEventListener('click', resetSliderInterval);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSlider);
    } else {
        initSlider();
    }

    function animateCounter(el, target, duration) {
        var start = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var current = Math.floor(progress * target);
            el.textContent = current;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                el.textContent = target;
            }
        }

        window.requestAnimationFrame(step);
    }

    function initStatCounters() {
        var counters = document.querySelectorAll('.stat-number[data-target]');
        if (!counters.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var target = parseInt(el.getAttribute('data-target'), 10);
                    if (!isNaN(target)) {
                        animateCounter(el, target, 1500);
                    }
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function (counter) {
            observer.observe(counter);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStatCounters);
    } else {
        initStatCounters();
    }
})();
