document.addEventListener("DOMContentLoaded", function() {
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
        root: container, // Specifica che lo scroll è dentro il div
        threshold: 0.5   
    }); 

    observer.observe(trigger);

    function loadMoreReviews() {
        isFetching = true;
        if(spinner) spinner.classList.remove('d-none'); 

        const fetchUrl = `?id=${bookId}&ajax_reviews=1&offset=${offset}`;

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                isFetching = false;
                const cleanHtml = html.trim();

                // Caso: Non ci sono più recensioni
                if (cleanHtml === '') {
                    allLoaded = true;
                    observer.disconnect(); // Smette di osservare immediatamente
                    
                    // Trasforma il trigger nel messaggio di fine
                    trigger.innerHTML = '<div class="py-3 text-muted small fw-bold">Hai raggiunto la fine delle recensioni.</div>';
                } else {
                    // Nascondi spinner
                    if(spinner) spinner.classList.add('d-none');
                    
                    // Inserisce le nuove card PRIMA dello spinner (lo spinge giù)
                    trigger.insertAdjacentHTML('beforebegin', cleanHtml);
                    
                    offset += 5;

                    // Controllo di sicurezza: se dopo il caricamento il trigger è ancora visibile
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
                if(spinner) spinner.classList.add('d-none');
            });
    }
});