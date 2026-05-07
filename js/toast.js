/**
 * Toast Notification System
 * Automatically checks for 'msg' or 'error' in the URL and displays a toast.
 */
function showToast(message, type = 'success') {
    if (!message) return;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notif ${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${decodeURIComponent(message.replace(/\+/g, ' '))}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 5s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 500);
    }, 5000);
}

// Auto-init on page load
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const error = urlParams.get('error');

    if (msg) showToast(msg, 'success');
    if (error) showToast(error, 'error');
});
