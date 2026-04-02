/**
 * Messages/Inbox interaction
 */

function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Handle conversation item clicks in inbox
    const inboxItems = document.querySelectorAll('[data-user-id]');
    inboxItems.forEach(item => {
        item.addEventListener('click', function() {
            window.location = '?conversation&user=' + this.dataset.userId;
        });
    });

    // Handle message form submission in conversation
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const receiverId = document.querySelector('input[name="receiver_id"]').value;
            const messageInput = document.getElementById('message-input');
            const content = messageInput.value.trim();
            
            if (!content) return;

            fetch('api.php?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'receiver_id=' + receiverId + '&content=' + encodeURIComponent(content) + '&csrf_token=' + encodeURIComponent(getCSRFToken())
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input
                    messageInput.value = '';
                    
                    // Reload messages
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send message');
            });
        });
    }

    // Auto-expand conversation items
    const inboxList = document.querySelectorAll('[data-inbox-item]');
    inboxList.forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.inboxItem;
            window.location = '?conversation&user=' + userId;
        });
    });
});
