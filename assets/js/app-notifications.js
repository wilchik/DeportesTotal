(function () {
    'use strict';
    var timeoutMs = 5000;

    function removeNotification(node) {
        if (!node || node.classList.contains('is-hiding')) return;
        node.classList.add('is-hiding');
        window.setTimeout(function () { if (node.parentNode) node.parentNode.removeChild(node); }, 260);
    }

    function scheduleNotification(node) {
        if (node.getAttribute('data-persistent') === 'true') return;
        window.setTimeout(function () { removeNotification(node); }, timeoutMs);
    }

    var stack = document.getElementById('appNotifications');
    if (stack) {
        Array.prototype.forEach.call(stack.querySelectorAll('[data-notification]'), scheduleNotification);
        stack.addEventListener('click', function (event) {
            var button = event.target.closest('.app-notification-close');
            if (button) removeNotification(button.closest('.app-notification'));
        });
    }

    window.DeportesTotalNotifications = {
        show: function (message, type, persistent) {
            stack = document.getElementById('appNotifications');
            if (!stack || !message) return null;
            type = type === 'error' ? 'error' : 'success';
            var duplicate = Array.prototype.find.call(stack.querySelectorAll('.app-notification'), function (item) {
                return item.classList.contains(type) && item.querySelector('.app-notification-message').textContent === String(message);
            });
            if (duplicate) return duplicate;

            var notification = document.createElement('div');
            notification.className = 'app-notification ' + type;
            notification.setAttribute('role', type === 'error' ? 'alert' : 'status');
            notification.setAttribute('data-notification', 'true');
            if (persistent) notification.setAttribute('data-persistent', 'true');
            var text = document.createElement('span');
            text.className = 'app-notification-message';
            text.textContent = String(message);
            var close = document.createElement('button');
            close.className = 'app-notification-close';
            close.type = 'button';
            close.setAttribute('aria-label', 'Cerrar notificación');
            close.textContent = '×';
            notification.appendChild(text);
            notification.appendChild(close);
            stack.appendChild(notification);
            scheduleNotification(notification);
            return notification;
        }
    };
})();
