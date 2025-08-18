// Community Issue Voting Platform - Voting System

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
            
            // Show success message
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
    messageDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    messageDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(messageDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}

// Add smooth animations for vote buttons
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects
    document.querySelectorAll('.vote-btn').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Handle keyboard navigation for accessibility
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' || event.key === ' ') {
        const focusedElement = document.activeElement;
        if (focusedElement.classList.contains('vote-btn')) {
            event.preventDefault();
            focusedElement.click();
        }
    }
});
