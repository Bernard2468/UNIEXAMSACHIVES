{{-- Centralized Confirmation Modal Component --}}
<div id="confirmationModal" class="confirmation-modal" style="display: none;">
    <div class="confirmation-modal-overlay" onclick="closeConfirmationModal()"></div>
    <div class="confirmation-modal-content">
        <div class="confirmation-modal-header">
            <div class="confirmation-modal-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="confirmation-modal-title" id="confirmationModalTitle">Confirm Action</h3>
        </div>
        
        <div class="confirmation-modal-body">
            <p class="confirmation-modal-message" id="confirmationModalMessage">
                Are you sure you want to perform this action?
            </p>
            <div class="confirmation-modal-details" id="confirmationModalDetails" style="display: none;">
                <p class="confirmation-modal-subtitle" id="confirmationModalSubtitle"></p>
            </div>
        </div>
        
        <div class="confirmation-modal-footer">
            <button type="button" class="btn btn-outline-secondary confirmation-modal-cancel" onclick="closeConfirmationModal()">
                <i class="icofont-close"></i>
                Cancel
            </button>
            <button type="button" class="btn confirmation-modal-confirm" id="confirmationModalConfirmBtn" onclick="executeConfirmationAction()">
                <i class="icofont-check"></i>
                <span id="confirmationModalConfirmText">Confirm</span>
            </button>
        </div>
    </div>
</div>

<script>
// Global variables for confirmation modal
let confirmationCallback = null;
let confirmationData = null;

// Show confirmation modal
function showConfirmationModal(options) {
    const modal = document.getElementById('confirmationModal');
    const title = document.getElementById('confirmationModalTitle');
    const message = document.getElementById('confirmationModalMessage');
    const subtitle = document.getElementById('confirmationModalSubtitle');
    const details = document.getElementById('confirmationModalDetails');
    const confirmBtn = document.getElementById('confirmationModalConfirmBtn');
    const confirmText = document.getElementById('confirmationModalConfirmText');
    
    // Set modal content
    title.textContent = options.title || 'Confirm Action';
    message.textContent = options.message || 'Are you sure you want to perform this action?';
    
    // Set subtitle if provided
    if (options.subtitle) {
        subtitle.textContent = options.subtitle;
        details.style.display = 'block';
    } else {
        details.style.display = 'none';
    }
    
    // Set confirm button text and style
    confirmText.textContent = options.confirmText || 'Confirm';
    
    // Set button style based on type
    confirmBtn.className = 'btn confirmation-modal-confirm';
    if (options.type === 'danger') {
        confirmBtn.classList.add('btn-danger');
    } else if (options.type === 'warning') {
        confirmBtn.classList.add('btn-warning');
    } else if (options.type === 'success') {
        confirmBtn.classList.add('btn-success');
    } else {
        confirmBtn.classList.add('btn-primary');
    }
    
    // Store callback and data
    confirmationCallback = options.callback;
    confirmationData = options.data || null;
    
    // Show modal with animation
    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

// Close confirmation modal
function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        confirmationCallback = null;
        confirmationData = null;
    }, 300);
}

// Execute confirmation action
function executeConfirmationAction() {
    if (confirmationCallback && typeof confirmationCallback === 'function') {
        confirmationCallback(confirmationData);
    }
    closeConfirmationModal();
}

// Convenience functions for common confirmation types
function confirmDelete(message, callback, data = null) {
    showConfirmationModal({
        title: 'Delete Confirmation',
        message: message || 'Are you sure you want to delete this item?',
        subtitle: 'This action cannot be undone.',
        type: 'danger',
        confirmText: 'Delete',
        callback: callback,
        data: data
    });
}

function confirmAction(message, callback, data = null, options = {}) {
    showConfirmationModal({
        title: options.title || 'Confirm Action',
        message: message,
        subtitle: options.subtitle || null,
        type: options.type || 'primary',
        confirmText: options.confirmText || 'Confirm',
        callback: callback,
        data: data
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirmationModal();
    }
});
</script>
