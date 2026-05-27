document.addEventListener('DOMContentLoaded', function() {
    // Configuración inicial
    const ajaxurl = window.tamarindNotifications?.ajaxurl || '/wp-admin/admin-ajax.php';
    const nonce = window.tamarindNotifications?.nonce || '';
    
    // Delegación de eventos para el acordeón
    document.querySelector('.tm-notifications-accordion')?.addEventListener('click', function(e) {
        const header = e.target.closest('.accordion-header');
        if (!header) return;
        
        const notificationId = header.dataset.id;
        const accordionItem = header.closest('.accordion-item');
        
        // Solo proceder si no está marcado como leído
        if (!accordionItem.classList.contains('is-read')) {
            // Marcar como leído via AJAX
            markNotificationAsRead(notificationId, accordionItem);
        }
    });
    
    // Función para hacer la petición AJAX
    function markNotificationAsRead(notificationId, element) {
        const formData = new FormData();
        formData.append('action', 'tamarind_mark_notification_read');
        formData.append('notification_id', notificationId);
        formData.append('security', nonce);
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.add('is-read');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});