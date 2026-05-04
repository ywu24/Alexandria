document.addEventListener('DOMContentLoaded', function() {
    // Selezioniamo il form all'interno della card "Security"
    // Usiamo l'attributo action o l'ID della sezione per mirare al form corretto
    const passwordForm = document.querySelector('#password-reset form');

    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            // Recuperiamo i valori dei campi
            const newPassword = passwordForm.querySelector('input[name="new_password"]').value;
            const confirmPassword = passwordForm.querySelector('input[name="confirm_password"]').value;
            const currentPassword = passwordForm.querySelector('input[name="current_password"]').value;



            // 2. Controllo coincidenza Nuova Password e Conferma
            if (newPassword !== confirmPassword) {
                // Blocchiamo l'invio
                e.preventDefault();
                
                showMessage("La nuova password e la password di conferma non coincidono.", "errore");
                
                // Opzionale: Focus sul campo conferma e pulizia
                passwordForm.querySelector('input[name="confirm_password"]').value = "";
                passwordForm.querySelector('input[name="confirm_password"]').focus();
            }
            
            // 3. Controllo lunghezza minima (opzionale, basato sui tuoi standard)
            else if (newPassword.length < 8) {
                e.preventDefault();
                showMessage("La nuova password deve essere lunga almeno 8 caratteri.", "errore");
            }
        });
    }
});

// Funzione aggiuntiva per la conferma eliminazione account (richiamata dal tuo onsubmit)
function confirmDelete() {
    return confirm("Sei assolutamente sicuro? Questa azione è irreversibile e cancellerà tutti i tuoi dati.");
}
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