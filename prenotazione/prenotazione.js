
document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', async function (event) {

        //1. "GESTISCI"
        if (event.target.classList.contains('btn-gestisci')) {
            console.log("gestisci");
            const idPrenotazione = event.target.getAttribute('data-id');
            console.log("Apertura dettaglio per ID:", idPrenotazione);


            window.location.href = `../dashboard/dashboardUtenti/dettaglioPrenotazione.php?id=${idPrenotazione}`;
        }

        // 2."ELIMINA" 


        const deleteBtn = event.target.closest('.delete-button');
        console.log("delete" + deleteBtn);
        if (deleteBtn) {
            console.log("elimina");
            const idPrenotazione = deleteBtn.getAttribute('data-id');

            console.log("Eliminazione confermata per ID:", idPrenotazione);

            try {
                // Prepariamo i dati da inviare
                const formData = new FormData();
                console.log(idPrenotazione);
                formData.append('id', idPrenotazione)
                formData.append('delete', '1');
                const response = await fetch('eliminaPrenotazione.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();
                console.log(result);

                // Procediamo solo se la risposta contiene "ok"
                if (result.includes("ok")) {
                    let row = deleteBtn.closest('.book-container');
                    console.log(row);
                    row.remove();
                    showMessage(result.slice(2));
                }
                else {
                    showMessage(result, "errore");
                }
            } catch (error) {
                showMessage("Errore: " + error, "errore");
            }
        }

        // 4. TERMINATE
        const btn = event.target.closest("#load-terminated");
        console.log(btn);
        if (btn) {
            const container = document.getElementById('terminate-container');
            const email = btn.getAttribute('data-id-utente');
            console.log(email);
            btn.disabled = true;
            btn.textContent = "Caricamento...";

            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('../dashboard/dashboardUtenti/get_terminate.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                console.log("response =", response)
                if (data.length === 0) {
                    container.innerHTML = "<div class='alert alert-info'>Nessuna prenotazione terminata trovata.</div>";
                } else {
                    let html = "<h3>Prenotazioni Terminate</h3>";

                    data.forEach(row => {
                        // Definiamo row-specific variables qui dentro
                        let ritardo2 = "";
                        let ritardo1 = "";

                        if (row.FinePrestito > row.FineAttesa) {
                            ritardo2 = "<span>scadenza prestito: " + row.FineAttesa + "</span><br>";
                            ritardo1 = "con ritardo";
                        }
                        

                        html += `
                        <div class='book-container'>
                            <div class='book-link'>
                                <img src='../img/books/${row.Copertina}' class='book-cover' width='160px'>
                                <div class='book-section'>
                                    <div class='info-title'><h3 class='trunctitle'>${row.Nome}</h3> <h6>ISBN: ${row.ISBN}</h6></div>
                                    <div class='info-release'>
                                        <h5>${row.Autore}</h5> 
                                        <h5>${row.CasaEditrice}</h5> 
                                        <h5>Stato:</h5> 
                                        <h5 class='status' style='color: #686868'>Terminata ${ritardo1}</h5>
                                    </div>
                                    <span>inizio prestito: ${row.InizioPrestito}</span><br>
                                    <span>fine prestito: ${row.FinePrestito}</span><br>
                                    ${ritardo2}`;
                        if (row.recensito == 0) {

                            html += ` <div class='container-pulsanti'>
                                        <a href="../recensione/recensione.php?id=${row.idOpera}" class="btn btn-warning">
                                        Recensisci
                                        </a>
                                    </div>`;
                        }
                        html += `
                                   
                                </div >
                            </div >
                        </div > `;
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
        }
    })
});
function showMessage(text, type = "successo") {
    const div = document.getElementById("messages");

    const p = document.createElement("p");
    p.textContent = text;

    p.classList.add(type);

    div.appendChild(p);

    // sparisce dopo 5 secondi 
    setTimeout(() => {
        p.remove();
    }, 4000);
}