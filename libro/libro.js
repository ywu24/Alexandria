document.addEventListener("DOMContentLoaded", function() {
    // --- Modal logic ---
    const modal = document.querySelector("#modal");
    const openModal = document.querySelector(".open-button");
    const closeModal = document.querySelector(".close-button");

    if (modal && openModal) {
        openModal.addEventListener("click", () => {
            modal.showModal();
        });
    }

    if (modal && closeModal) {
        closeModal.addEventListener("click", () => {
            modal.close();
        });
    }

    // --- Premium slider logic ---
    const slider = document.getElementById('slider');
    const sliderValue = document.getElementById('sliderValue');

    if (slider && sliderValue) {
        slider.addEventListener('input', function() {
            sliderValue.textContent = slider.value;
        });
    }

    // --- Infinite scroll reviews ---
    const container = document.getElementById('reviews-container');
    const trigger = document.getElementById('scroll-trigger');

    if (!trigger || !container) return;

    const spinner = trigger.querySelector('.spinner-border');
    const bookId = container.dataset.bookId;

    let offset = 5;
    let isFetching = false;
    let allLoaded = false;

    const observer = new IntersectionObserver((entries) => {
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

        const fetchUrl = `../api/reviews.php?id=${bookId}&offset=${offset}`;

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                isFetching = false;
                const cleanHtml = html.trim();

                if (cleanHtml === '') {
                    allLoaded = true;
                    observer.disconnect();
                    trigger.innerHTML = '<div class="py-3 text-muted small fw-bold">Hai raggiunto la fine delle recensioni.</div>';
                } else {
                    if (spinner) spinner.classList.add('d-none');
                    trigger.insertAdjacentHTML('beforebegin', cleanHtml);
                    offset += 5;

                    setTimeout(() => {
                        const rect = trigger.getBoundingClientRect();
                        const contRect = container.getBoundingClientRect();
                        if (rect.bottom <= contRect.bottom && !allLoaded) {
                            loadMoreReviews();
                        }
                    }, 100);
                }
            })
            .catch(error => {
                console.error("Errore AJAX:", error);
                isFetching = false;
                if (spinner) spinner.classList.add('d-none');
            });
    }
});
