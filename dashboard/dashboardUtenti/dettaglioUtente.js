document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-terminated');
    const container = document.getElementById('terminate-container');
    console.log(btn);

    btn.addEventListener('click', async () => {
        const idUtente = btn.getAttribute('data-id-utente');
        console.log(idUtente);
        //if(!idUtente)idUtente=1;
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
                        ritardo2 = "<span>fine prestito: " + row.FinePrestito + "</span><br>";
                        ritardo1 = "con ritardo";
                    }

                    html += `
                    <div class='book-container' style='background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; border:1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
                        <div class='row' style='display: flex; align-items: stretch; flex-wrap: wrap;'>
                            
                            <!-- COLONNA SINISTRA: Info Libro (50%) -->
                            <div class='col-sm-6' style='display: flex; flex-direction: column; justify-content:建设; border-right: 1px solid #f0f0f0;'>
                                <div style='display: flex; gap: 15px;'>
                                    <img src='../../img/books/${row.Copertina}' class='book-cover' width='100px' style='object-fit: contain; border-radius: 4px;'>
                                    <div style='flex: 1;'>
                                        <h4 style='margin: 0 0 5px 0; color: #333; font-weight: bold;'>${row.Nome}</h4>
                                        <p class='text-muted' style='font-size: 13px; margin-bottom: 10px;'>${row.Autore}</p>
                                        
                                        <div style='margin-bottom: 15px;'>
                                            <span class="label" style="color: #686868; font-size: 10px; padding: 4px 8px;">Terminata</span>
                                            ${ritardo1 ? `<span class="label label-danger" style="font-size: 10px; margin-left:5px;">in Ritardo</span>` : ''}
                                        </div>

                                        <!-- Pulsante compatto -->
                                        <button class='btn btn-primary btn-sm' 
                                                style='padding: 5px 15px; font-weight: bold; border-radius: 20px; transition: all 0.2s;' 
                                                onclick='apriDettaglioPrenotazione(${row.idPrenotazione})'>
                                           Gestisci
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- COLONNA DESTRA: Dettagli (50%) -->
                            <div class='col-sm-6' id='dettaglio-content-${row.idPrenotazione}' style='display:none; padding-left: 25px;'>
                                
                            </div>
                            
                            <!-- Placeholder se il dettaglio è chiuso (opzionale, per non lasciare vuoto) -->
                            <div class='col-sm-6 text-center text-muted' id='placeholder-${row.idPrenotazione}' style='display: flex; align-items: center; justify-content: center; opacity: 0.5;'>
                                <small>Clicca su "Gestisci" per i dettagli</small>
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