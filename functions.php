<?php
function getTasks($conn) {
    $result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $tasks = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    return $tasks;
}

function addTask($conn, $task) {
    $task = $conn->real_escape_string($task);
    $sql = "INSERT INTO tasks (task) VALUES ('$task')";
    return $conn->query($sql);
}

function deleteTask($conn, $id) {
    $id = (int)$id;
    $sql = "DELETE FROM tasks WHERE id = $id";
    return $conn->query($sql);
}

function toggleTaskStatus($conn, $id) {
    $id = (int)$id;
    $sql = "UPDATE tasks SET status = IF(status = 'pending', 'completed', 'pending') WHERE id = $id";
    return $conn->query($sql);
}

function editTask($conn, $id, $task) {
    $id = (int)$id;
    $task = $conn->real_escape_string($task);
    $sql = "UPDATE tasks SET task = '$task' WHERE id = $id";
    return $conn->query($sql);
}

// New functions for enhanced functionality
function getTasksCount($conn, $filter = 'all', $searchQuery = '') {
    $whereClause = getWhereClause($filter, $searchQuery);
    $sql = "SELECT COUNT(*) as count FROM tasks $whereClause";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getStatusCount($conn, $status) {
    $status = $conn->real_escape_string($status);
    $sql = "SELECT COUNT(*) as count FROM tasks WHERE status = '$status'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getCategoryCounts($conn) {
    $sql = "SELECT category, COUNT(*) as count FROM tasks GROUP BY category";
    $result = $conn->query($sql);
    $counts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $counts[$row['category']] = $row['count'];
        }
    }
    return $counts;
}

function getPriorityCounts($conn) {
    $sql = "SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority";
    $result = $conn->query($sql);
    $counts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $counts[$row['priority']] = $row['count'];
        }
    }
    return $counts;
}

function getFilteredTasks($conn, $filter = 'all', $searchQuery = '', $sortField = 'created_at', $sortDirection = 'DESC', $page = 1, $perPage = 5) {
    $offset = ($page - 1) * $perPage;
    $whereClause = getWhereClause($filter, $searchQuery);
    
    // Validate sort field to prevent SQL injection
    $validSortFields = ['created_at', 'priority', 'due_date', 'task'];
    if (!in_array($sortField, $validSortFields)) {
        $sortField = 'created_at';
    }
    
    // Validate sort direction
    $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';
    
    // Handle priority sorting (convert 'low', 'medium', 'high' to sortable values)
    $orderBy = $sortField;
    if ($sortField === 'priority') {
        $orderBy = "CASE 
                    WHEN priority = 'high' THEN 1 
                    WHEN priority = 'medium' THEN 2 
                    WHEN priority = 'low' THEN 3 
                    ELSE 4 END";
    }
    
    $sql = "SELECT * FROM tasks 
            $whereClause 
            ORDER BY $orderBy $sortDirection 
            LIMIT $offset, $perPage";
    
    $result = $conn->query($sql);
    $tasks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    return $tasks;
}

function getWhereClause($filter, $searchQuery) {
    $conditions = [];
    
    // Apply filter
    if ($filter === 'pending') {
        $conditions[] = "status = 'pending'";
    } elseif ($filter === 'completed') {
        $conditions[] = "status = 'completed'";
    } elseif ($filter === 'priority-high') {
        $conditions[] = "priority = 'high'";
    } elseif ($filter === 'priority-medium') {
        $conditions[] = "priority = 'medium'";
    } elseif ($filter === 'priority-low') {
        $conditions[] = "priority = 'low'";
    }
    
    // Apply search
    if (!empty($searchQuery)) {
        $searchQuery = addslashes($searchQuery);
        $conditions[] = "(task LIKE '%$searchQuery%' OR category LIKE '%$searchQuery%')";
    }
    
    // Combine conditions
    if (count($conditions) > 0) {
        return "WHERE " . implode(" AND ", $conditions);
    }
    
    return "";
}

// Enhanced add task function with priority, category, and due date
function addTaskEnhanced($conn, $task, $priority = 'medium', $category = 'personal', $due_date = null) {
    $task = $conn->real_escape_string($task);
    $priority = $conn->real_escape_string($priority);
    $category = $conn->real_escape_string($category);
    
    if (empty($due_date)) {
        $sql = "INSERT INTO tasks (task, priority, category) VALUES ('$task', '$priority', '$category')";
    } else {
        $due_date = $conn->real_escape_string($due_date);
        $sql = "INSERT INTO tasks (task, priority, category, due_date) VALUES ('$task', '$priority', '$category', '$due_date')";
    }
    
    return $conn->query($sql);
}

// Enhanced edit task function with priority, category, and due date
function editTaskEnhanced($conn, $id, $task, $priority = 'medium', $category = 'personal', $due_date = null) {
    $id = (int)$id;
    $task = $conn->real_escape_string($task);
    $priority = $conn->real_escape_string($priority);
    $category = $conn->real_escape_string($category);
    
    if (empty($due_date)) {
        $sql = "UPDATE tasks SET task = '$task', priority = '$priority', category = '$category', due_date = NULL WHERE id = $id";
    } else {
        $due_date = $conn->real_escape_string($due_date);
        $sql = "UPDATE tasks SET task = '$task', priority = '$priority', category = '$category', due_date = '$due_date' WHERE id = $id";
    }
    
    return $conn->query($sql);
}
?>