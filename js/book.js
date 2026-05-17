/**
 * Alexandria Library Management System
 *
 * @file Book detail page — modal dialog, premium slider, infinite scroll reviews
 */

(function () {
    document.addEventListener("DOMContentLoaded", function () {
        var modal = document.querySelector("#modal");
        var openModal = document.querySelector(".open-button");
        var closeModal = document.querySelector(".close-button");

        if (modal && openModal) {
            openModal.addEventListener("click", function () {
                modal.showModal();
            });
        }

        if (modal && closeModal) {
            closeModal.addEventListener("click", function () {
                modal.close();
            });
        }

        var slider = document.getElementById('slider');
        var sliderValue = document.getElementById('sliderValue');

        if (slider && sliderValue) {
            slider.addEventListener('input', function () {
                sliderValue.textContent = slider.value;
            });
        }

        var container = document.getElementById('reviews-container');
        var trigger = document.getElementById('scroll-trigger');

        if (!trigger || !container) return;

        var spinner = trigger.querySelector('.spinner-border');
        var bookId = container.dataset.bookId;

        var offset = 5;
        var isFetching = false;
        var allLoaded = false;

        var observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting && !isFetching && !allLoaded) {
                loadMoreReviews();
            }
        }, {
            root: container,
            threshold: 0.5
        });

        observer.observe(trigger);

        function loadMoreReviews() {
            isFetching = true;
            if (spinner) spinner.classList.remove('d-none');

            var fetchUrl = '../api/reviews.php?id=' + encodeURIComponent(bookId) + '&offset=' + encodeURIComponent(offset);

            fetch(fetchUrl)
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    isFetching = false;
                    var cleanHtml = html.trim();

                    if (cleanHtml === '') {
                        allLoaded = true;
                        observer.disconnect();
                        trigger.innerHTML = '<div class="py-3 text-muted small fw-bold">Hai raggiunto la fine delle recensioni.</div>';
                    } else {
                        if (spinner) spinner.classList.add('d-none');
                        trigger.insertAdjacentHTML('beforebegin', cleanHtml);
                        offset += 5;

                        setTimeout(function () {
                            var rect = trigger.getBoundingClientRect();
                            var contRect = container.getBoundingClientRect();
                            if (rect.bottom <= contRect.bottom && !allLoaded) {
                                loadMoreReviews();
                            }
                        }, 100);
                    }
                })
                .catch(function (error) {
                    console.error("Errore AJAX:", error);
                    isFetching = false;
                    if (spinner) spinner.classList.add('d-none');
                });
        }
    });
})();
