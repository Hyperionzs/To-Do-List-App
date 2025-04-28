<?php
require_once 'config.php';
require_once 'functions.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new task
    if (isset($_POST['add_task'])) {
        $task = $_POST['task'];
        $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';
        $category = isset($_POST['category']) ? $_POST['category'] : 'personal';
        $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        
        addTaskEnhanced($conn, $task, $priority, $category, $due_date);
        
        // Preserve filter parameters
        $redirect_url = buildRedirectUrl();        
        header("Location: $redirect_url");
        exit();
    }
    
    // Delete task
    if (isset($_POST['delete_task'])) {
        deleteTask($conn, $_POST['task_id']);
        
        // Preserve filter parameters
        $redirect_url = buildRedirectUrl();        
        header("Location: $redirect_url");
        exit();
    }
    
    // Update task status
    if (isset($_POST['toggle_status'])) {
        toggleTaskStatus($conn, $_POST['task_id']);
        
        // Preserve filter parameters
        $redirect_url = buildRedirectUrl();        
        header("Location: $redirect_url");
        exit();
    }
    
    // Edit task
    if (isset($_POST['edit_task'])) {
        $task_id = $_POST['task_id'];
        $task_text = $_POST['task_text'];
        $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';
        $category = isset($_POST['category']) ? $_POST['category'] : 'personal';
        $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        
        editTaskEnhanced($conn, $task_id, $task_text, $priority, $category, $due_date);
        
        // Preserve filter parameters
        $redirect_url = buildRedirectUrl();        
        header("Location: $redirect_url");
        exit();
    }
}

// If script is accessed directly (not through a form submission), redirect to main page
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: main.php");
    exit();
}

// Helper function to build redirect URL with preserved parameters
function buildRedirectUrl() {
    // Get filter parameters from the HTTP referer URL if present
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $params = [];
    
    if (!empty($referer) && strpos($referer, '?') !== false) {
        $query = parse_url($referer, PHP_URL_QUERY);
        parse_str($query, $refParams);
        
        if (isset($refParams['filter'])) {
            $params[] = "filter=" . urlencode($refParams['filter']);
        }
        
        if (isset($refParams['search'])) {
            $params[] = "search=" . urlencode($refParams['search']);
        }
        
        if (isset($refParams['sort'])) {
            $params[] = "sort=" . urlencode($refParams['sort']);
        }
        
        if (isset($refParams['page'])) {
            $params[] = "page=" . urlencode($refParams['page']);
        }
    }
    
    $redirect_url = "main.php";
    if (!empty($params)) {
        $redirect_url .= "?" . implode("&", $params);
    }
    
    return $redirect_url;
}
?>
