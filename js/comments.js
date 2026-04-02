/**
 * Comments interaction - add, edit, delete, reply
 */

document.addEventListener('DOMContentLoaded', function() {

    // Handle comment form submission
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'comment-form') {
            e.preventDefault();
            addComment(e.target);
        }
        if (e.target.classList.contains('reply-form')) {
            e.preventDefault();
            addReply(e.target);
        }
        if (e.target.classList.contains('edit-comment-form')) {
            e.preventDefault();
            editComment(e.target);
        }
    });

    // Handle reply button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-reply')) {
            e.preventDefault();
            const commentId = e.target.dataset.commentId;
            const postId = e.target.dataset.postId;
            toggleReplyForm(commentId, postId);
        }
        
        if (e.target.classList.contains('btn-edit')) {
            e.preventDefault();
            const commentId = e.target.dataset.commentId;
            toggleEditForm(commentId);
        }
        
        if (e.target.classList.contains('btn-delete')) {
            e.preventDefault();
            if (confirm('Delete this comment?')) {
                deleteComment(e.target.dataset.commentId);
            }
        }
        
        if (e.target.classList.contains('btn-cancel')) {
            e.target.closest('.reply-form, .edit-comment-form')?.remove();
        }
    });
});

function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

function addComment(form) {
    const postId = form.querySelector('input[name="post_id"]').value;
    const content = form.querySelector('textarea[name="content"]').value.trim();
    const csrfToken = getCSRFToken();
    
    if (!content) return;

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Posting...';

    fetch('api.php?action=add_comment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId + '&content=' + encodeURIComponent(content) + '&csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            form.querySelector('textarea').value = '';
            appendComment(data.comment, form);
        } else {
            alert('Error: ' + data.error);
        }
        btn.disabled = false;
        btn.textContent = 'Post Comment';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to post comment');
        btn.disabled = false;
        btn.textContent = 'Post Comment';
    });
}

function addReply(form) {
    const parentCommentId = form.querySelector('input[name="parent_comment_id"]').value;
    const postId = form.querySelector('input[name="post_id"]').value;
    const content = form.querySelector('textarea').value.trim();
    const csrfToken = getCSRFToken();
    
    if (!content) return;

    fetch('api.php?action=add_reply', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'parent_comment_id=' + parentCommentId + '&post_id=' + postId + '&content=' + encodeURIComponent(content) + '&csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            form.remove();
            appendReply(data.reply, parentCommentId);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to post reply');
    });
}

function editComment(form) {
    const commentId = form.querySelector('input[name="comment_id"]').value;
    const content = form.querySelector('textarea').value.trim();
    const csrfToken = getCSRFToken();
    
    if (!content) return;

    fetch('api.php?action=edit_comment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'comment_id=' + commentId + '&content=' + encodeURIComponent(content) + '&csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to edit comment');
    });
}

function deleteComment(commentId) {
    const csrfToken = getCSRFToken();
    fetch('api.php?action=delete_comment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'comment_id=' + commentId + '&csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('[data-comment-id="' + commentId + '"]')?.remove();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete comment');
    });
}

function toggleReplyForm(commentId, postId) {
    const container = document.querySelector('[data-comment-id="' + commentId + '"]');
    if (!container) return;

    let replyForm = container.querySelector('.reply-form');
    if (replyForm) {
        replyForm.remove();
        return;
    }

    // Create reply form
    replyForm = document.createElement('form');
    replyForm.className = 'reply-form';
    replyForm.innerHTML = `
        <input type="hidden" name="parent_comment_id" value="${commentId}">
        <input type="hidden" name="post_id" value="${postId}">
        <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 6px;">
            <textarea name="content" placeholder="Write a reply..." 
                      style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; 
                             font-family: Arial; resize: none; min-height: 60px;"></textarea>
            <div style="margin-top: 10px; display: flex; gap: 10px;">
                <button type="submit" style="background: #F86015; color: white; border: none; 
                        padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    Reply
                </button>
                <button type="button" class="btn-cancel" style="background: #ccc; color: #333; border: none; 
                        padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(replyForm);
}

function toggleEditForm(commentId) {
    const container = document.querySelector('[data-comment-id="' + commentId + '"]');
    if (!container) return;

    let editForm = container.querySelector('.edit-comment-form');
    if (editForm) {
        editForm.remove();
        return;
    }

    // Get current content
    const currentContent = container.querySelector('[data-comment-content]').textContent;

    editForm = document.createElement('form');
    editForm.className = 'edit-comment-form';
    editForm.innerHTML = `
        <input type="hidden" name="comment_id" value="${commentId}">
        <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 6px;">
            <textarea name="content" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; 
                      font-family: Arial; resize: none; min-height: 60px;">${currentContent}</textarea>
            <div style="margin-top: 10px; display: flex; gap: 10px;">
                <button type="submit" style="background: #F86015; color: white; border: none; 
                        padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    Update
                </button>
                <button type="button" class="btn-cancel" style="background: #ccc; color: #333; border: none; 
                        padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(editForm);
}

function appendComment(comment, form) {
    const commentHtml = `
        <div data-comment-id="${comment.id}" style="background: white; padding: 15px; border-radius: 8px; 
                                                   border-left: 3px solid #F86015; margin-bottom: 15px;">
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <img src="${comment.avatar_url || 'https://via.placeholder.com/40'}" alt="avatar" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <strong style="color: #333;">${comment.display_name}</strong>
                        <small style="color: #999;">@${comment.username}</small>
                    </div>
                    <p data-comment-content style="margin: 0 0 10px 0; color: #666; line-height: 1.5;">${comment.content}</p>
                    <small style="color: #999;">${new Date(comment.created_at).toLocaleString()}</small>
                    <div style="margin-top: 8px; display: flex; gap: 15px;">
                        <button class="btn-reply" data-comment-id="${comment.id}" data-post-id="${comment.post_id}" 
                                style="background: none; border: none; color: #F86015; cursor: pointer; font-size: 12px; font-weight: bold;">
                            ↩️ Reply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Insert after the form
    form.insertAdjacentHTML('afterend', commentHtml);
}

function appendReply(reply, parentCommentId) {
    const container = document.querySelector('[data-comment-id="' + parentCommentId + '"]');
    if (container) {
        let repliesContainer = container.querySelector('.replies-container');
        if (!repliesContainer) {
            repliesContainer = document.createElement('div');
            repliesContainer.className = 'replies-container';
            repliesContainer.style = 'margin-left: 50px; margin-top: 10px;';
            container.appendChild(repliesContainer);
        }

        const replyHtml = `
            <div data-comment-id="${reply.id}" style="background: #f9f9f9; padding: 12px; border-radius: 6px; margin-bottom: 10px;">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <img src="${reply.avatar_url || 'https://via.placeholder.com/35'}" alt="avatar" 
                         style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px;">
                            <strong style="color: #333;">${reply.display_name}</strong>
                            <small style="color: #999;">@${reply.username}</small>
                        </div>
                        <p data-comment-content style="margin: 0; color: #666; font-size: 13px; line-height: 1.4;">${reply.content}</p>
                        <small style="color: #999; font-size: 11px;">${new Date(reply.created_at).toLocaleString()}</small>
                    </div>
                </div>
            </div>
        `;

        repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
    }
}
