/**
 * Alexandria Library Management System
 *
 * @file Profile edit page — password confirmation, file input label, account deletion
 */

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var passwordForm = document.querySelector('#password-reset form');

        if (passwordForm) {
            passwordForm.addEventListener('submit', function (e) {
                var newPassword = passwordForm.querySelector('input[name="new_password"]').value;
                var confirmPassword = passwordForm.querySelector('input[name="confirm_password"]').value;

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    showMessage("La nuova password e la password di conferma non coincidono.", "errore");
                    passwordForm.querySelector('input[name="confirm_password"]').value = "";
                    passwordForm.querySelector('input[name="confirm_password"]').focus();
                } else if (newPassword.length < 8) {
                    e.preventDefault();
                    showMessage("La nuova password deve essere lunga almeno 8 caratteri.", "errore");
                }
            });
        }

        document.querySelectorAll('.custom-file-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var fileName = this.value.split("\\").pop();
                var label = this.nextElementSibling;
                if (label) {
                    label.classList.add('selected');
                    label.textContent = fileName;
                }
            });
        });
    });

    window.confirmDelete = function () {
        return confirm("Sei assolutamente sicuro? Questa azione è irreversibile e cancellerà tutti i tuoi dati.");
    };
})();
