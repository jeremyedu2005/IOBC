/**
 * Following/Followers interaction
 */

function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Handle follow button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-follow')) {
            e.preventDefault();
            const button = e.target;
            const userId = button.dataset.user;
            
            if (!userId) return;

            fetch('api.php?action=follow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId + '&csrf_token=' + encodeURIComponent(getCSRFToken())
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = 'Following ✓';
                    button.style.background = '#03A272';
                    button.classList.remove('btn-follow');
                    button.classList.add('btn-unfollow');
                    
                    // Update class for unfollow
                    button.onclick = function() {
                        unfollowUser(button, userId);
                    };
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // Handle unfollow button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-unfollow')) {
            e.preventDefault();
            const button = e.target;
            const userId = button.dataset.user;
            unfollowUser(button, userId);
        }
    });
});

function unfollowUser(button, userId) {
    fetch('api.php?action=unfollow', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + userId + '&csrf_token=' + encodeURIComponent(getCSRFToken())
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.textContent = 'Follow';
            button.style.background = '#F86015';
            button.classList.remove('btn-unfollow');
            button.classList.add('btn-follow');
        }
    })
    .catch(error => console.error('Error:', error));
}
