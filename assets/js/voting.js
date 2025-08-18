// Shiksha Mitra - Educational Community Platform - Enhanced Voting & Interaction System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize voting buttons
    initializeVoting();
});

function initializeVoting() {
    // Add event listeners to all vote buttons
    document.querySelectorAll('.vote-btn').forEach(button => {
        button.addEventListener('click', handleVote);
    });
}

function handleVote(event) {
    event.preventDefault();
    
    const button = event.currentTarget;
    const issueId = button.dataset.issueId;
    const voteType = button.dataset.voteType;
    
    // Prevent multiple clicks
    if (button.classList.contains('loading')) {
        return;
    }
    
    // Add loading state
    button.classList.add('loading');
    
    // Make AJAX request
    fetch('../api/vote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            issue_id: issueId,
            vote_type: voteType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update vote count
            updateVoteCount(issueId, data.new_vote_count);
            
            // Update button states
            updateVoteButtons(issueId, voteType);
            
            // Add success feedback animation
            button.classList.add('success-feedback');
            setTimeout(() => button.classList.remove('success-feedback'), 600);
            
            // Show success message (English only)
            showMessage('Vote recorded successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to record vote', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while voting', 'error');
    })
    .finally(() => {
        // Remove loading state
        button.classList.remove('loading');
    });
}

function updateVoteCount(issueId, newCount) {
    // Find all vote count elements for this issue
    const voteCounts = document.querySelectorAll(`[data-issue-id="${issueId}"] .vote-count, .vote-count`);
    
    voteCounts.forEach(countElement => {
        // Update the number
        countElement.textContent = newCount;
        
        // Update styling based on vote count
        countElement.className = 'vote-count';
        if (newCount > 0) {
            countElement.classList.add('text-success');
        } else if (newCount < 0) {
            countElement.classList.add('text-danger');
        } else {
            countElement.classList.add('text-muted');
        }
    });
}

function updateVoteButtons(issueId, voteType) {
    // Find all vote buttons for this issue
    const upvoteBtn = document.querySelector(`[data-issue-id="${issueId}"].upvote-btn`);
    const downvoteBtn = document.querySelector(`[data-issue-id="${issueId}"].downvote-btn`);
    
    if (upvoteBtn && downvoteBtn) {
        // Remove active state from both buttons
        upvoteBtn.classList.remove('active');
        downvoteBtn.classList.remove('active');
        
        // Add active state to the clicked button
        if (voteType === 'upvote') {
            upvoteBtn.classList.add('active');
        } else if (voteType === 'downvote') {
            downvoteBtn.classList.add('active');
        }
    }
}

function showMessage(message, type) {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    messageDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    
    // Add appropriate icon based on message type
    const icon = getMessageIcon(type);
    
    messageDiv.innerHTML = `
        <i class="${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add animation class
    messageDiv.classList.add('slide-in');
    
    // Add to page
    document.body.appendChild(messageDiv);
    
    // Auto-remove after 4 seconds (shorter for better UX)
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.classList.add('fade-out');
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 300);
        }
    }, 4000);
}

function getMessageIcon(type) {
    switch(type) {
        case 'success': return 'fas fa-check-circle';
        case 'error': 
        case 'danger': return 'fas fa-exclamation-triangle';
        case 'warning': return 'fas fa-exclamation-circle';
        case 'info': return 'fas fa-info-circle';
        default: return 'fas fa-info-circle';
    }
}

// Enhanced interactive UI elements
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects for vote buttons
    document.querySelectorAll('.vote-btn').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.transition = 'all 0.2s ease';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Add click animation
        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1.1)';
            }, 100);
        });
    });
    
    // Add form validation feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showMessage('Please fill all required fields', 'warning');
            }
        });
    });
    
    // Add real-time validation for email fields
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
                showMessage('Please enter a valid email address', 'warning');
            } else if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // Add loading animation to buttons on form submit
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        const form = button.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                button.disabled = true;
            });
        }
    });
});

// Enhanced keyboard navigation and accessibility
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' || event.key === ' ') {
        const focusedElement = document.activeElement;
        if (focusedElement.classList.contains('vote-btn')) {
            event.preventDefault();
            focusedElement.click();
        }
    }
});

// Add fade-out animation class to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    .fade-out {
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }
    
    .slide-in {
        animation: slideInFromRight 0.4s ease-out;
    }
    
    @keyframes slideInFromRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25) !important;
    }
    
    .is-valid {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25) !important;
    }
`;
document.head.appendChild(style);

// Add page load animation
window.addEventListener('load', function() {
    document.body.style.opacity = '0';
    document.body.style.transform = 'translateY(20px)';
    document.body.style.transition = 'all 0.5s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
        document.body.style.transform = 'translateY(0)';
    }, 100);
});
