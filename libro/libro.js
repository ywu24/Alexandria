document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('reviews-container');
    const trigger = document.getElementById('scroll-trigger');
    
    if (!trigger || !container) return;

    const spinner = trigger.querySelector('.spinner-border');
    
    // Legge l'id del libro dall'attributo inserito in PHP
    const bookId = container.dataset.bookId;
    
    let offset = 5; // Salta le prime 5 già caricate
    let isFetching = false;
    let allLoaded = false;


    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isFetching && !allLoaded) {
            loadMoreReviews();
        }
    }, {
        rootMargin: "0px", // Non anticipare il caricamento
        threshold: 1 // Attende che il trigger sia effettivamente entrato nello schermo
        }); 

    observer.observe(trigger);

    function loadMoreReviews() {
        isFetching = true;
        if(spinner) spinner.classList.remove('d-none'); 

        // Recuperiamo il path corrente
        const currentPath = window.location.pathname; 

        // Costruiamo la URL per la chiamata AJAX che invierà i parametri alla pagina attuale
        const fetchUrl = `${currentPath}?id=${bookId}&ajax_reviews=1&offset=${offset}`;

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                if(spinner) spinner.classList.add('d-none');
                isFetching = false;

                // Se non c'è più codice HTML, abbiamo caricato tutte le recensioni
                if (html.trim() === '') {
                    allLoaded = true;
                    observer.disconnect();
                    trigger.innerHTML = '<span class="text-muted small mt-3 d-block">Hai raggiunto la fine delle recensioni.</span>';
                } else {
                    container.insertAdjacentHTML('beforeend', html);
                    offset += 5; // Prepara il limite per il prossimo scroll
                }
            })
            .catch(error => {
                console.error("Errore AJAX:", error);
                if(spinner) spinner.classList.add('d-none');
                isFetching = false;
            });
    }
});