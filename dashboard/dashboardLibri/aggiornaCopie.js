document.addEventListener("click", async(e) =>{
    
    if (e.target.classList.contains("save")){

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
            }
          
            else{
                 showMessage(result, "errore");
            }
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
    }, 5000);
}