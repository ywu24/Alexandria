localStorage.removeItem('reinvio_count');
localStorage.removeItem('reg_form_nome');
localStorage.removeItem('reg_form_cognome');
localStorage.removeItem('reg_form_email');
localStorage.removeItem('log_form_email');

function Check_next() {
    var wanted = document.getElementsByName("slider");
    for (var i = 0; i < wanted.length; ++i) {
        if (wanted[i].checked == true) {
            if (i == wanted.length - 1) {
                wanted[0].checked = true;
            } else {
                wanted[i + 1].checked = true;
            }
            break;
        }
    }
}

setInterval(function () {
    Check_next()
}, 5000)