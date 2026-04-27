document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-terminated');
    const container = document.getElementById('terminate-container');
    console.log(btn);

    btn.addEventListener('click', async () => {
        const idUtente = btn.getAttribute('data-id-utente');
        console.log(idUtente);
        btn.disabled = true;
        btn.textContent = "Caricamento...";

        try {
            const formData = new FormData();
            formData.append('idUtente', idUtente);

            const response = await fetch('get_terminate.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log("response =",response)
           if (data.length === 0) {
                container.innerHTML = "<div class='alert alert-info'>Nessuna prenotazione terminata trovata.</div>";
            } else {
                let html = "<h3>Prenotazioni Terminate</h3>";
                
                data.forEach(row => {
                    // Definiamo row-specific variables qui dentro
                    let ritardo2 = "";
                    let ritardo1 = "";
                    
                    if (row.FinePrestito > row.FineAttesa) {
                        ritardo2 = "<span>fine prestito: " + row.FinePrestito + "</span><br>";
                        ritardo1 = "con ritardo";
                    }

                    html += `
                    <div class='book-container'>
                        <div class='book-link'>
                            <img src='../../img/books/${row.Copertina}' class='book-cover' width='160px'>
                            <div class='book-section'>
                                <h3>${row.Nome}</h3>
                                <div class='info-release'>
                                    <h5>${row.Autore}</h5> 
                                    <h5>${row.CasaEditrice}</h5> 
                                    <h5>Stato:</h5> 
                                    <h5 class='status' style='color: #686868'>Terminata ${ritardo1}</h5>
                                </div>
                                <span>inizio prestito: ${row.InizioPrestito}</span><br>
                                <span>fine prestito: ${row.FinePrestito}</span><br>
                                ${ritardo2}
                                <form action='dettaglioPrenotazione.php?id=${row.idPrenotazione}' method='post'>
                                    <button name='apri-dettaglio' style='margin-top: 20px; width: 20vh;'>Vedi Dettagli</button>
                                </form>
                            </div>
                        </div>
                    </div>`;
                });
                container.innerHTML = html;
            }
            btn.style.display = 'none'; // Nascondo il pulsante dopo il caricamento
        } catch (error) {
            console.error("Errore:", error);
            alert("Impossibile caricare le prenotazioni terminate.");
            btn.disabled = false;
            btn.textContent = "Riprova";
        }
    });
});