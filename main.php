<?php
require_once 'config.php';
require_once 'functions.php';

// Set default values
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$tasksPerPage = 5;
$currentFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'created_at-desc';

// Parse sort parameters
list($sortField, $sortDirection) = explode('-', $sortBy);

// Fetch tasks with filtering, sorting, and pagination
$totalTasks = getTasksCount($conn, $currentFilter, $searchQuery);
$totalPages = ceil($totalTasks / $tasksPerPage);
$tasks = getFilteredTasks($conn, $currentFilter, $searchQuery, $sortField, $sortDirection, $currentPage, $tasksPerPage);

// Get category counts
$categoryCounts = getCategoryCounts($conn);
$priorityCounts = getPriorityCounts($conn);

// Get pending and completed counts
$pendingCount = getStatusCount($conn, 'pending');
$completedCount = getStatusCount($conn, 'completed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <button class="dark-mode" id="darkModeToggle">
                <i class="fas fa-moon"></i>
            </button>
            
            <header>
                <h1>To-Do List Form</h1>
                <p class="app-description">Organize your life, one task at a time</p>
            </header>
            
            <div class="main-content">
                <!-- Statistics -->
                <div class="categories">
                    <div class="category <?php echo $currentFilter == 'all' ? 'active' : ''; ?>" data-filter="all">
                        <span><i class="fas fa-tasks"></i></span>
                        <h3>All Tasks</h3>
                        <p><?php echo $totalTasks; ?></p>
                    </div>
                    <div class="category <?php echo $currentFilter == 'pending' ? 'active' : ''; ?>" data-filter="pending">
                        <span><i class="fas fa-clock"></i></span>
                        <h3>Pending</h3>
                        <p><?php echo $pendingCount; ?></p>
                    </div>
                    <div class="category <?php echo $currentFilter == 'completed' ? 'active' : ''; ?>" data-filter="completed">
                        <span><i class="fas fa-check-circle"></i></span>
                        <h3>Completed</h3>
                        <p><?php echo $completedCount; ?></p>
                    </div>
                    <div class="category <?php echo $currentFilter == 'priority-high' ? 'active' : ''; ?>" data-filter="priority-high">
                        <span><i class="fas fa-exclamation-circle"></i></span>
                        <h3>High Priority</h3>
                        <p><?php echo isset($priorityCounts['high']) ? $priorityCounts['high'] : 0; ?></p>
                    </div>
                </div>
            
            <!-- Add Task Form -->
            <form class="task-form" method="POST" action="process.php">
                    <div class="task-input-group">
                        <input type="text" name="task" placeholder="What needs to be done?" required>
                        <div class="task-form-options">
                            <div class="form-group priority-group" data-priority="medium" id="priorityGroup">
                                <label for="priority">Priority:</label>
                                <select name="priority" id="priority" onchange="updatePriorityIndicator(this.value)">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="category">Category:</label>
                                <select name="category" id="category">
                                    <option value="personal">Personal</option>
                                    <option value="work">Work</option>
                                    <option value="shopping">Shopping</option>
                                    <option value="health">Health</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="due_date">Due Date:</label>
                                <input type="date" name="due_date" id="due_date">
                            </div>
                        </div>
                    </div>
                <button type="submit" name="add_task">Add Task</button>
            </form>
            
                <!-- Search and Filter -->
                <div class="task-controls">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Search tasks..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="filter-options">
                        <button class="filter-btn <?php echo $currentFilter == 'all' ? 'active' : ''; ?>" data-filter="all">
                            <i class="fas fa-tasks"></i> <span>All</span>
                        </button>
                        <button class="filter-btn <?php echo $currentFilter == 'pending' ? 'active' : ''; ?>" data-filter="pending">
                            <i class="fas fa-clock"></i> <span>Pending</span>
                        </button>
                        <button class="filter-btn <?php echo $currentFilter == 'completed' ? 'active' : ''; ?>" data-filter="completed">
                            <i class="fas fa-check-circle"></i> <span>Completed</span>
                        </button>
                    </div>
                </div>
                
                <!-- Sorting -->
                <div class="sort-options">
                    <span class="sort-label">
                        <i class="fas fa-sort-amount-down"></i>
                        Sort by:
                    </span>
                    <div class="sort-select-wrapper">
                        <select id="sortSelect" class="sort-select">
                            <option value="created_at-desc" <?php echo $sortBy == 'created_at-desc' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="created_at-asc" <?php echo $sortBy == 'created_at-asc' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="priority-desc" <?php echo $sortBy == 'priority-desc' ? 'selected' : ''; ?>>Priority (High-Low)</option>
                            <option value="priority-asc" <?php echo $sortBy == 'priority-asc' ? 'selected' : ''; ?>>Priority (Low-High)</option>
                            <option value="due_date-asc" <?php echo $sortBy == 'due_date-asc' ? 'selected' : ''; ?>>Due Date (Earliest)</option>
                            <option value="due_date-desc" <?php echo $sortBy == 'due_date-desc' ? 'selected' : ''; ?>>Due Date (Latest)</option>
                        </select>
                    </div>
                </div>
            
            <!-- Task List -->
                <?php if (count($tasks) > 0): ?>
                <ul class="task-list">
                    <?php foreach ($tasks as $task): ?>
                            <li class="task-item <?php echo $task['status'] == 'completed' ? 'completed' : ''; ?> priority-<?php echo htmlspecialchars($task['priority']); ?>">
                                <div class="task-content">
                                    <div class="task-text" id="text-<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['task']); ?></div>
                                    <div class="task-details">
                                        <?php if (!empty($task['due_date']) && $task['due_date'] != '0000-00-00'): ?>
                                        <div class="task-date" data-date="<?php echo $task['due_date']; ?>">
                                            <i class="far fa-calendar"></i> 
                                            <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="task-priority">
                                            <span class="task-priority-indicator"></span>
                                            <?php echo ucfirst(htmlspecialchars($task['priority'])); ?>
                                        </div>
                                        <div class="task-category">
                                            <span class="category-indicator category-<?php echo htmlspecialchars($task['category']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($task['category'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            
                            <!-- Edit Form (initially hidden) -->
                            <form class="edit-form" id="edit-form-<?php echo $task['id']; ?>" method="POST" action="process.php">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="text" name="task_text" value="<?php echo htmlspecialchars($task['task']); ?>" required>
                                <div class="form-group priority-group" id="edit-form-<?php echo $task['id']; ?>-priority-group" data-priority="<?php echo $task['priority']; ?>">
                                    <label for="priority-<?php echo $task['id']; ?>">Priority:</label>
                                    <select name="priority" id="priority-<?php echo $task['id']; ?>" onchange="updateEditPriorityIndicator(this, '<?php echo $task['id']; ?>')">
                                        <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="category-<?php echo $task['id']; ?>">Category:</label>
                                    <select name="category" id="category-<?php echo $task['id']; ?>">
                                        <option value="personal" <?php echo $task['category'] == 'personal' ? 'selected' : ''; ?>>Personal</option>
                                        <option value="work" <?php echo $task['category'] == 'work' ? 'selected' : ''; ?>>Work</option>
                                        <option value="shopping" <?php echo $task['category'] == 'shopping' ? 'selected' : ''; ?>>Shopping</option>
                                        <option value="health" <?php echo $task['category'] == 'health' ? 'selected' : ''; ?>>Health</option>
                                        <option value="other" <?php echo $task['category'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="due_date-<?php echo $task['id']; ?>">Due Date:</label>
                                    <input type="date" id="due_date-<?php echo $task['id']; ?>" name="due_date" value="<?php echo !empty($task['due_date']) && $task['due_date'] != '0000-00-00' ? $task['due_date'] : ''; ?>">
                                </div>
                                <div class="edit-form-actions">
                                    <button type="submit" name="edit_task" class="btn btn-edit">
                                        <i class="fas fa-save btn-icon"></i> <span class="btn-text">Save</span>
                                    </button>
                                    <button type="button" onclick="toggleEditForm(<?php echo $task['id']; ?>)" class="btn btn-delete">
                                        <i class="fas fa-times btn-icon"></i> <span class="btn-text">Cancel</span>
                                    </button>
                                </div>
                            </form>
                            
                            <div class="task-actions" id="actions-<?php echo $task['id']; ?>">
                                    <button onclick="toggleEditForm(<?php echo $task['id']; ?>)" class="btn btn-edit">
                                        <i class="fas fa-edit btn-icon"></i> <span class="btn-text">Edit</span>
                                    </button>
                                
                                <form method="POST" action="process.php" style="display: inline;">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-complete">
                                            <i class="fas <?php echo $task['status'] == 'pending' ? 'fa-check' : 'fa-undo'; ?> btn-icon"></i>
                                            <span class="btn-text"><?php echo $task['status'] == 'pending' ? 'Complete' : 'Reopen'; ?></span>
                                    </button>
                                </form>
                                
                                <form method="POST" action="process.php" style="display: inline;" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" name="delete_task" class="btn btn-delete">
                                            <i class="fas fa-trash btn-icon"></i> <span class="btn-text">Delete</span>
                                        </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo ($currentPage - 1); ?>&filter=<?php echo $currentFilter; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>" class="pagination-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&filter=<?php echo $currentFilter; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>" 
                               class="pagination-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo ($currentPage + 1); ?>&filter=<?php echo $currentFilter; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>" class="pagination-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-tasks">
                        <?php if (!empty($searchQuery)): ?>
                            <i class="fas fa-search-minus"></i>
                            <p>No tasks found matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                            <button id="clearSearch" class="btn">Clear Search</button>
                        <?php elseif ($currentFilter != 'all'): ?>
                            <i class="fas fa-filter"></i>
                            <p>No <?php echo $currentFilter; ?> tasks found</p>
                            <button data-filter="all" class="filter-btn btn">Show All Tasks</button>
            <?php else: ?>
                            <i class="fas fa-tasks"></i>
                            <p>Your task list is empty. Add your first task above!</p>
                        <?php endif; ?>
                    </div>
            <?php endif; ?>
            </div>
        </div>
        
        <!-- Clock display -->
        <div class="current-time">
            <i class="fas fa-clock"></i>
            <span id="current-time-display"></span>
            <button id="toggle-clock" class="toggle-clock-btn" title="Hide clock">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Show clock button (initially hidden) -->
        <button id="show-clock" class="clock-show-btn" title="Show clock">
            <i class="fas fa-clock"></i>
        </button>
        
        <footer class="main-footer">
            <div class="footer-content">
                <div class="footer-copyright">
                    <p>&copy; 2025 Task Manager | Made by <a href="https://github.com/hyperionzs">Anggra.Dev</a></p>
                </div>
                <div class="footer-social">
                    <a href="https://github.com/hyperionzs" target="_blank"><i class="fab fa-github"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </footer>
    </div>
    
    <script>
        // Toggle dark mode
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        
        // Check for saved theme preference or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            body.classList.add('dark');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark');
            
            // Update icon
            if (body.classList.contains('dark')) {
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem('theme', 'dark');
            } else {
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem('theme', 'light');
            }
        });
        
        // Task edit form toggle
        function toggleEditForm(taskId) {
            const textElement = document.getElementById(`text-${taskId}`);
            const actionsElement = document.getElementById(`actions-${taskId}`);
            const formElement = document.getElementById(`edit-form-${taskId}`);
            
            if (formElement.style.display === 'none' || formElement.style.display === '') {
                formElement.style.display = 'flex';
                textElement.parentElement.style.display = 'none';
                actionsElement.style.display = 'none';
            } else {
                formElement.style.display = 'none';
                textElement.parentElement.style.display = 'flex';
                actionsElement.style.display = 'flex';
            }
        }
        
        // Delete confirmation
        function confirmDelete() {
            return confirm('Are you sure you want to delete this task?');
        }
        
        // Filter, search and sort functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Category filter
            const categoryBtns = document.querySelectorAll('.category');
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    window.location.href = `?filter=${filter}&search=${encodeURIComponent('<?php echo $searchQuery; ?>')}&sort=<?php echo $sortBy; ?>`;
                });
            });
            
            // Filter buttons
            const filterBtns = document.querySelectorAll('.filter-btn');
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    window.location.href = `?filter=${filter}&search=${encodeURIComponent('<?php echo $searchQuery; ?>')}&sort=<?php echo $sortBy; ?>`;
                });
            });
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchValue = this.value;
                    window.location.href = `?filter=<?php echo $currentFilter; ?>&search=${encodeURIComponent(searchValue)}&sort=<?php echo $sortBy; ?>`;
                }, 500);
            });
            
            // Clear search
            const clearSearchBtn = document.getElementById('clearSearch');
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    window.location.href = `?filter=<?php echo $currentFilter; ?>&sort=<?php echo $sortBy; ?>`;
                });
            }
            
            // Sort select enhancement
            const sortSelect = document.getElementById('sortSelect');
            const sortSelectWrapper = document.querySelector('.sort-select-wrapper');
            
            // Change wrapper appearance based on selected option
            function updateSortIndicator() {
                const selectedOption = sortSelect.value;
                
                // Remove any existing classes
                sortSelectWrapper.classList.remove('sort-newest', 'sort-oldest', 'sort-priority-high', 'sort-priority-low', 'sort-date-early', 'sort-date-late');
                
                // Add appropriate class based on selection
                if (selectedOption === 'created_at-desc') {
                    sortSelectWrapper.classList.add('sort-newest');
                } else if (selectedOption === 'created_at-asc') {
                    sortSelectWrapper.classList.add('sort-oldest');
                } else if (selectedOption === 'priority-desc') {
                    sortSelectWrapper.classList.add('sort-priority-high');
                } else if (selectedOption === 'priority-asc') {
                    sortSelectWrapper.classList.add('sort-priority-low');
                } else if (selectedOption === 'due_date-asc') {
                    sortSelectWrapper.classList.add('sort-date-early');
                } else if (selectedOption === 'due_date-desc') {
                    sortSelectWrapper.classList.add('sort-date-late');
                }
            }
            
            // Initial update
            updateSortIndicator();
            
            // Update on change
            sortSelect.addEventListener('change', function() {
                updateSortIndicator();
                // Redirect to apply sorting
                window.location.href = `?filter=<?php echo $currentFilter; ?>&search=${encodeURIComponent('<?php echo $searchQuery; ?>')}&sort=${this.value}&page=<?php echo $currentPage; ?>`;
            });
            
            // Update priority indicator color
            function updatePriorityIndicator(priority) {
                const priorityGroup = document.getElementById('priorityGroup');
                priorityGroup.setAttribute('data-priority', priority);
            }
            
            // Update priority indicator for edit forms
            function updateEditPriorityIndicator(selectElement, taskId) {
                const priority = selectElement.value;
                const priorityGroup = document.getElementById(`edit-form-${taskId}-priority-group`);
                if (priorityGroup) {
                    priorityGroup.setAttribute('data-priority', priority);
                }
            }
            
            // Initialize priority indicators for edit forms
            function initPriorityIndicators() {
                const editForms = document.querySelectorAll('.edit-form');
                
                editForms.forEach(form => {
                    const prioritySelect = form.querySelector('select[name="priority"]');
                    const formId = form.id;
                    
                    if (prioritySelect) {
                        // Add onchange event to update indicator
                        prioritySelect.addEventListener('change', function() {
                            const editFormPriorityGroup = document.getElementById(formId + '-priority-group');
                            if (editFormPriorityGroup) {
                                editFormPriorityGroup.setAttribute('data-priority', this.value);
                            }
                        });
                        
                        // Initialize with current value
                        const currentPriority = prioritySelect.value;
                        const editFormPriorityGroup = document.getElementById(formId + '-priority-group');
                        if (editFormPriorityGroup) {
                            editFormPriorityGroup.setAttribute('data-priority', currentPriority);
                        }
                    }
                });
            }
            
            // Call on page load
            initPriorityIndicators();
        });
        
        // Real-time date updater function
        function updateTaskDates() {
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set to beginning of day for date comparison
            
            // Get all task date elements
            const taskDateElements = document.querySelectorAll('.task-date');
            
            taskDateElements.forEach(dateElement => {
                const dateText = dateElement.getAttribute('data-date');
                if (!dateText) return;
                
                const dueDate = new Date(dateText);
                dueDate.setHours(0, 0, 0, 0); // Set to beginning of day for comparison
                
                // Calculate days difference
                const diffTime = dueDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                // Get the status label element
                let statusElement = dateElement.querySelector('.date-status');
                if (!statusElement) {
                    statusElement = document.createElement('span');
                    statusElement.className = 'date-status';
                    dateElement.appendChild(statusElement);
                }
                
                // Clear existing classes
                statusElement.classList.remove('overdue', 'today', 'tomorrow');
                
                // Update status based on difference
                if (diffDays < 0) {
                    statusElement.textContent = ' (Overdue)';
                    statusElement.classList.add('overdue');
                } else if (diffDays === 0) {
                    statusElement.textContent = ' (Today)';
                    statusElement.classList.add('today');
                } else if (diffDays === 1) {
                    statusElement.textContent = ' (Tomorrow)';
                    statusElement.classList.add('tomorrow');
                } else {
                    statusElement.textContent = '';
                }
            });
        }
        
        // Update dates initially
        updateTaskDates();
        
        // Update dates every minute
        setInterval(updateTaskDates, 60000);
        
        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeDisplay = document.getElementById('current-time-display');
            
            // Format date as a more compact format: Jan 1, 2023 • 11:59 PM
            const dateOptions = { 
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            };
            
            const timeOptions = {
                hour: '2-digit', 
                minute: '2-digit'
            };
            
            const dateStr = now.toLocaleDateString('en-US', dateOptions);
            const timeStr = now.toLocaleTimeString('en-US', timeOptions);
            
            timeDisplay.textContent = dateStr + ' • ' + timeStr;
        }
        
        // Update the clock immediately
        updateClock();
        
        // Update the clock every second
        setInterval(updateClock, 1000);
        
        // Toggle clock visibility
        const clockElement = document.querySelector('.current-time');
        const toggleClockBtn = document.getElementById('toggle-clock');
        const showClockBtn = document.getElementById('show-clock');
        
        // Check local storage for clock visibility preference
        const clockVisible = localStorage.getItem('clockVisible') !== 'false';
        if (!clockVisible) {
            clockElement.classList.add('hidden');
            showClockBtn.classList.add('visible');
        }
        
        toggleClockBtn.addEventListener('click', () => {
            clockElement.classList.add('hidden');
            showClockBtn.classList.add('visible');
            localStorage.setItem('clockVisible', 'false');
        });
        
        showClockBtn.addEventListener('click', () => {
            clockElement.classList.remove('hidden');
            showClockBtn.classList.remove('visible');
            localStorage.setItem('clockVisible', 'true');
        });
    </script>
</body>
</html>