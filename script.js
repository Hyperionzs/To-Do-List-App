// Enhanced script.js
function toggleEditForm(id) {
    const textElement = document.getElementById(`text-${id}`);
    const formElement = document.getElementById(`edit-form-${id}`);
    const actionsElement = document.getElementById(`actions-${id}`);
    
    if (formElement.style.display === 'flex') {
        formElement.style.display = 'none';
        textElement.style.display = 'block';
        actionsElement.style.display = 'flex';
    } else {
        formElement.style.display = 'flex';
        textElement.style.display = 'none';
        actionsElement.style.display = 'none';
        
        // Focus on the input field
        const inputField = formElement.querySelector('input[type="text"]');
        inputField.focus();
        
        // Place cursor at the end of the text
        const inputLength = inputField.value.length;
        inputField.setSelectionRange(inputLength, inputLength);
    }
}

function confirmDelete() {
    return confirm('Are you sure you want to delete this task?');
}

// Add animation when tasks are added
document.addEventListener('DOMContentLoaded', function() {
    const taskItems = document.querySelectorAll('.task-item');
    
    taskItems.forEach((item, index) => {
        // Stagger the animations
        item.style.animationDelay = `${index * 0.05}s`;
    });
});