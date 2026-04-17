document.addEventListener("click", async(e) =>{ 
    
    if (e.target.classList.contains("save")){   //pulsante per Salva
        
        const button = e.target;

        // trovo la riga (td)
        const row = button.closest("tr");
        const cell = button.closest("td");

        // prendo input dentro la stessa riga
        const inputs = cell.getElementsByTagName("input");
        for (input of inputs){

            let copie = input.value;
            if(copie<0){
                //showMessage("ERRORE: il numero inserito deve essere positivo o al più uguale zero!", "errore");
                //return;
            }
            let isbn = row.dataset.isbn;

            if (!confirm("Confermi aggiornamento copie?")) return;

            const formData = new FormData();
            formData.append("isbn", isbn);
            formData.append("copie", copie);

            const response = await fetch("updateCopie.php", {
                method: "POST",
                body: formData
            });

            const result = await response.text();

            console.log(result)
            if(result.includes("ignora")){
               
            }
            else if(result.includes("ok")){
                showMessage(result.slice(2));
                const btnEspandi = document.querySelector(`.btn-espandi[data-isbn="${isbn}"]`);
                if(btnEspandi) {
                    btnEspandi.click();
                    btnEspandi.click();
                }
            }
          
            else{
                 showMessage(result, "errore");
            }
        }
    } 
    else if(e.target.classList.contains("delete")) { //pulsante per Elimina (elimina_copie)
        
        const button = e.target;
        const row  = button.closest("tr");
        const id =row.dataset.id;
        const formData = new FormData();
        formData.append("id", id);
        console.log(id);
        const response = await fetch("elimina_copia.php", {
            method: "POST",
            body: formData
        });
        const result = await response.text();
        console.log(result)
            if(result.includes("ignora")){
               
            }
            else if(result.includes("ok")){
                showMessage(result.slice(2));
                const btnEspandi = document.querySelector(`.btn-espandi[data-isbn="${isbn}"]`);
                if(btnEspandi) {
                    btnEspandi.click();
                    btnEspandi.click();
                }
            }
          
            else{
                 showMessage(result, "errore");
            }
    } 
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

document.addEventListener('DOMContentLoaded', () => {
    // Rendiamo la funzione di callback 'async' per poter usare 'await'
    document.querySelector('table').addEventListener('click', async function eventHandler(e) {
        
        if (e.target && e.target.classList.contains('btn-espandi')) {
            const btn = e.target;
            const isbn = btn.getAttribute('data-isbn');
            const targetRow = document.getElementById(`row-details-${isbn}`);
            const contentDiv = document.getElementById(`content-${isbn}`);

            // Toggle visibilità
            if (targetRow.style.display === 'table-row') {
                targetRow.style.display = 'none';
                btn.textContent = '+';
                btn.classList.replace('btn-danger', 'btn-info');
            } else {
                btn.textContent = '...';
                
                try {
                    const formData = new FormData();
                    formData.append("isbn", isbn);
               
                    // Chiamata Fetch con metodo POST
                    const response = await fetch(`get_copie.php`, {
                        method: "POST",
                        body: formData
                    });

                    if (!response.ok) throw new Error("Errore di rete");

                    const data = await response.json(); 

                    console.log(data); //////////////////////
                    // Passiamo sia il contenitore che i dati
                    renderCopie(contentDiv, data, e);
                    
                    targetRow.style.display = 'table-row';
                    btn.textContent = '-';
                    btn.classList.replace('btn-info', 'btn-danger');
                } catch (error) {
                    console.error("Errore:", error);
                    contentDiv.innerHTML = "<p class='text-danger'>Impossibile caricare le copie.</p>";
                    btn.textContent = '+';
                }
            }
        }
    }); // Chiusura corretta dell'EventListener
});
function renderCopie(container, copie, e) {
    // Svuotiamo il contenitore dal testo "Caricamento..."
    container.innerHTML = "";

    if (copie.length === 0) {
        container.innerHTML = "<div class='alert alert-info'>Nessuna copia disponibile per questo volume.</div>";
        return;
    }

    // Costruiamo la tabella
    let html = `
        <table class="table table-sm table-striped bg-white border m-0">
            <thead class="thead-light">
                <tr>
                    <th>ID Copia</th>
                    <th>Stato</th>
                    <th class="text-right">Azioni</th>
                </tr>
            </thead>
            <tbody>`;


for (let copia of copie) {
    console.log("copia : [" + copia.idCopia + " " + copia.Stato + "]");
    
    let statoTesto = 'Non Disponibile';
    let badgeClass = 'badge-danger';
    let btnClass = 'btn-outline-secondary';
    let action = "eliminaLibo.php?"+copia.idCopia;
    let dettagli = ""
    let disabled = "";

    if (copia.Stato == '1') {
        // Caso: DISPONIBILE (Può essere eliminato)
        badgeClass = 'badge-success';
        statoTesto = 'Disponibile';
        btnClass = 'btn-outline-danger  delete';
        action = "";
         
        
    } else {
        // Caso: IN PRESTITO (Mostra errore al click)
        disabled = "disabled";
        action = "showMessage('Non puoi eliminare libri attualmente in prestito!', 'errore'); eventHandler(e);";
       
        dettagli = '<button class="btn btn-sm btn-warning" onclick=>' +
                        '<i class="fas fa-exclamation-triangle"></i> Dettagli' +
                    '</button> ';
    }

    // Costruiamo la riga
    html += '<tr data-id="' + copia.idCopia+'" >' +
                '<td><strong>#' + copia.idCopia + '</strong></td>' +
                '<td><span class="badge ' + badgeClass + '">' + statoTesto + '</span></td>' +
                '<td class="text-right">' +
                    // Pulsante Segnala (Giallo)
                    dettagli+
                    // Pulsante Elimina (Dinamico)
                    '<button class="btn btn-sm  ' + btnClass + '" ' + disabled +'  onclick="' + action + '">' +
                        '<i class="fas fa-trash"></i> Elimina' +
                    '</button>' +
                '</td>' +
            '</tr>';
}

html += '</tbody></table>';
container.innerHTML = html;
};