/**
 * Alexandria Library Management System
 *
 * @file Modal and Toast utilities - reusable confirm dialog and toast notifications
 */
(function () {
    window.showToast = function (message, type) {
        try {
            type = type || 'success';
            var container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
                document.body.appendChild(container);
            }

            var icons = { success: '\u2713', error: '\u2717', warning: '\u26A0' };
            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.style.cssText = 'pointer-events:auto;display:flex;align-items:center;gap:12px;min-width:300px;max-width:420px;padding:14px 20px;border-radius:12px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,0.12);border-left:5px solid ' + (type === 'success' ? '#2d6a4f' : type === 'error' ? '#c1292e' : '#e09f3e') + ';font-size:0.875rem;color:#1e1e1e;animation:toastSlideIn 0.35s ease-out;';

            var iconSpan = document.createElement('span');
            iconSpan.className = 'toast-icon';
            iconSpan.textContent = icons[type] || '';
            iconSpan.style.cssText = 'font-size:1.3rem;flex-shrink:0;line-height:1;color:' + (type === 'success' ? '#2d6a4f' : type === 'error' ? '#c1292e' : '#e09f3e') + ';';

            var msgSpan = document.createElement('span');
            msgSpan.className = 'toast-message';
            msgSpan.textContent = message;
            msgSpan.style.cssText = 'flex:1;line-height:1.4;';

            var closeBtn = document.createElement('button');
            closeBtn.className = 'toast-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = 'flex-shrink:0;background:none;border:none;color:#6b6b6b;font-size:1.1rem;cursor:pointer;padding:0 4px;line-height:1;';
            closeBtn.onclick = function () {
                toast.style.animation = 'toastSlideOut 0.3s ease-in forwards';
                setTimeout(function () { if (toast.parentNode) toast.remove(); }, 300);
            };

            toast.appendChild(iconSpan);
            toast.appendChild(msgSpan);
            toast.appendChild(closeBtn);
            container.appendChild(toast);

            setTimeout(function () {
                toast.style.animation = 'toastSlideOut 0.3s ease-in forwards';
                setTimeout(function () { if (toast.parentNode) toast.remove(); }, 300);
            }, 4000);

            var style = document.getElementById('toast-keyframes');
            if (!style) {
                style = document.createElement('style');
                style.id = 'toast-keyframes';
                style.textContent = '@keyframes toastSlideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes toastSlideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}';
                document.head.appendChild(style);
            }
        } catch (e) {
            console.error('Toast error:', e);
            alert(message);
        }
    };

    window.showConfirm = function (message) {
        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML =
                '<div class="confirm-dialog">' +
                    '<p>' + message + '</p>' +
                    '<div class="confirm-actions">' +
                        '<button class="btn btn-secondary" id="confirm-cancel">Annulla</button>' +
                        '<button class="btn btn-danger" id="confirm-ok">Conferma</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(overlay);

            document.getElementById('confirm-ok').addEventListener('click', function () {
                overlay.classList.add('confirm-hiding');
                setTimeout(function () { overlay.remove(); resolve(true); }, 150);
            });
            document.getElementById('confirm-cancel').addEventListener('click', function () {
                overlay.classList.add('confirm-hiding');
                setTimeout(function () { overlay.remove(); resolve(false); }, 150);
            });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    overlay.classList.add('confirm-hiding');
                    setTimeout(function () { overlay.remove(); resolve(false); }, 150);
                }
            });
        });
    };
})();