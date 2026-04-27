const modal = document.querySelector("#modal");
            const openModal = document.querySelector(".open-button");
            const closeModal = document.querySelector(".close-button");
            try{
            openModal.addEventListener("click", () => {
                modal.showModal();
            });
            } catch{}
            try{
            closeModal.addEventListener("click", () => {
                modal.close();
            });
            } catch{}

document.getElementById('bookings').addEventListener('click', async (e) => {
    // Verifichiamo che l'elemento cliccato sia un bottone con classe 'prenotazione'
    
    const btn = e.target.closest('.prenotazione');
    if (!btn) return;
    
    // Determiniamo l'azione in base alla classe
    let azione = '';
    if (btn.classList.contains('conferma')) azione = 'conferma';
    else if (btn.classList.contains('termina')) azione = 'termina';
    else if (btn.classList.contains('elimina')) azione = 'elimina';
    //console.log("azione=",azione);
    let id = btn.dataset.id;
    console.log(id);
    if (!azione) return;

    try {
        // Prepariamo i dati da inviare
        const formData = new FormData();
        formData.append(azione, 1); 
        formData.append('id', id )

        const response = await fetch('handlePrenotazione.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.text();
        console.log("result= ",result);

        // Procediamo solo se la risposta contiene "ok"
        if (result.includes("ok")) {
            
            if (azione === 'conferma') {
             
               window.location.href = window.location.pathname + window.location.search;
               //showMessage(result, "successo");

            } else if (azione === 'termina') {
               window.location.href = window.location.pathname + window.location.search;
                //showMessage(result, "successo");

            } else if (azione === 'elimina') {
                let idUtente = parseInt(result.trim().split(" ")[1], 10);
                console.log(idUtente);
               window.location.href = "dettaglioUtente.php?id="+idUtente+ "&eliminato=1";
                               
            }
        }
        else{
            showMessage(result, "errore");
        }
    } catch (error) {
        console.error("Errore durante la fetch:", error);
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