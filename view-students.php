<?php
// view-students.php - Enhanced with search and filtering
include 'db_connect.php';
requireAuth();

// Get filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? sanitizeInput($_GET['course']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Build query with filters
$query = "SELECT * FROM students WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($course_filter)) {
    $query .= " AND course = ?";
    $params[] = $course_filter;
}

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY id DESC";

// Get students
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get courses for filter dropdown
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Students - Student Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* ... (keep your existing styles) ... */


     :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --info: #4895ef;
      --warning: #f72585;
      --danger: #e63946;
      --light: #f8f9fa;
      --dark: #212529;
      --sidebar-width: 250px;
      --header-height: 70px;
    }
    
    /* ... (keep your existing styles) ... */
body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fb;
      color: #495057;
      overflow-x: hidden;
    }
    
    /* Sidebar styling */
    #sidebar {
      position: fixed;
      width: var(--sidebar-width);
      height: 100vh;
      background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      transition: all 0.3s;
      z-index: 1000;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    #sidebar .sidebar-header {
      padding: 20px;
      background: rgba(0, 0, 0, 0.1);
    }
    
    #sidebar ul.components {
      padding: 20px 0;
    }
    
    #sidebar ul li a {
      padding: 15px 25px;
      display: block;
      color: rgba(255, 255, 255, 0.9);
      text-decoration: none;
      transition: all 0.3s;
      font-size: 1rem;
    }
    
    #sidebar ul li a:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
    }
    
    #sidebar ul li a i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    #sidebar ul li.active > a {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }
    
    /* Content area */
    #content {
      width: calc(100% - var(--sidebar-width));
      margin-left: var(--sidebar-width);
      min-height: 100vh;
      transition: all 0.3s;
    }
    
    /* Header */
    #header {
      height: var(--header-height);
      padding: 0 20px;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    /* Main content */
    #main-content {
      padding: 30px;
    }
    
    /* Cards */
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 24px;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 20px;
      font-weight: 600;
      border-radius: 10px 10px 0 0 !important;
    }
    
    .card-body {
      padding: 25px;
    }
    
    /* Buttons */
    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .btn-primary:hover {
      background-color: var(--secondary);
      border-color: var(--secondary);
    }
    
    .btn-success {
      background-color: var(--success);
      border-color: var(--success);
    }
    
    /* Forms */
    .form-control, .form-select {
      border-radius: 8px;
      padding: 12px 15px;
      border: 1px solid #e1e5eb;
    }
    
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
      border-color: var(--primary);
    }
    
    /* Tables */
    .table {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
    }
    
    .table th {
      background-color: #f8f9fa;
      font-weight: 600;
      padding: 12px 15px;
      border-top: 1px solid #eaeaea;
    }
    
    .table td {
      padding: 12px 15px;
      vertical-align: middle;
      border-top: 1px solid #eaeaea;
    }
    
    .table tr:hover {
      background-color: rgba(67, 97, 238, 0.03);
    }
    
    /* Dashboard stats */
    .stat-card {
      text-align: center;
      padding: 20px;
    }
    
    .stat-card i {
      font-size: 2.5rem;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .stat-card h2 {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .stat-card p {
      color: #6c757d;
      margin-bottom: 0;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      #sidebar {
        margin-left: -var(--sidebar-width);
        width: var(--sidebar-width);
      }
      
      #sidebar.active {
        margin-left: 0;
      }
      
      #content {
        width: 100%;
        margin-left: 0;
      }
      
      #content.active {
        width: calc(100% - var(--sidebar-width));
        margin-left: var(--sidebar-width);
      }
      
      #sidebarCollapse span {
        display: none;
      }
    }













    
    .progress {
      height: 10px;
      border-radius: 5px;
    }
    
    .event-badge {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }
    
    .filter-card {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  
  <div id="content">
    <?php include 'header.php'; ?>
    
    <div id="main-content">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Student List</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Students</li>
          </ol>
        </nav>
      </div>

      <!-- Filters -->
      <div class="card filter-card">
        <div class="card-body">
          <form method="GET" class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Name, email or phone" value="<?= $search ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Course</label>
              <select name="course" class="form-select">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c['course_name'] ?>" <?= $course_filter == $c['course_name'] ? 'selected' : '' ?>>
                    <?= $c['course_name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Active" <?= $status_filter == 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-primary me-2">Filter</button>
              <a href="view-students.php" class="btn btn-outline-secondary">Reset</a>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">All Students (<?= count($students) ?>)</h5>
          <div>
            <a href="add-students.php" class="btn btn-primary btn-sm">
              <i class="fas fa-plus me-1"></i> Add New
            </a>
            <button class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#exportModal">
              <i class="fas fa-download me-1"></i> Export
            </button>
          </div>
        </div>
        <div class="card-body">
          <?php if (count($students) > 0): ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $student): 
                    $statusClass = $student['status'] == 'Active' ? 'bg-success' : 'bg-warning';
                  ?>
                    <tr>
                      <td><?= $student['id'] ?></td>
                      <td><?= htmlspecialchars($student['full_name']) ?></td>
                      <td><?= htmlspecialchars($student['email']) ?></td>
                      <td><?= $student['phone'] ? htmlspecialchars($student['phone']) : 'N/A' ?></td>
                      <td><?= htmlspecialchars($student['course']) ?></td>
                      <td><span class="badge <?= $statusClass ?>"><?= $student['status'] ?></span></td>
                      <td><?= date('M j, Y', strtotime($student['created_at'])) ?></td>
                      <td>
                        <div class="btn-group">
                          <a href="view-students.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="edit-student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                          </a>
                          <a href="delete-student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-danger" 
                             onclick="return confirm('Are you sure you want to delete this student?')">
                            <i class="fas fa-trash"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="fas fa-users fa-3x text-muted mb-3"></i>
              <h5>No students found</h5>
              <p class="text-muted"><?= (!empty($search) || !empty($course_filter) || !empty($status_filter)) ? 
                'Try adjusting your filters' : 'Get started by adding your first student' ?></p>
              <?php if (empty($search) && empty($course_filter) && empty($status_filter)): ?>
                <a href="add-students.php" class="btn btn-primary mt-2">
                  <i class="fas fa-plus me-1"></i> Add Student
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Export Modal -->
  <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Export Students</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="export-students.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="search" value="<?= $search ?>">
            <input type="hidden" name="course" value="<?= $course_filter ?>">
            <input type="hidden" name="status" value="<?= $status_filter ?>">
            
            <div class="mb-3">
              <label class="form-label">Format</label>
              <select name="format" class="form-select">
                <option value="csv">CSV</option>
                <option value="excel">Excel</option>
                <option value="pdf">PDF</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Columns to Include</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[]" value="id" checked id="colId">
                <label class="form-check-label" for="colId">ID</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[]" value="name" checked id="colName">
                <label class="form-check-label" for="colName">Name</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[]" value="email" checked id="colEmail">
                <label class="form-check-label" for="colEmail">Email</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[]" value="phone" id="colPhone">
                <label class="form-check-label" for="colPhone">Phone</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[]" value="course" checked id="colCourse">
                <label class="form-check-label" for="colCourse">Course</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="columns[]" value="status" checked id="colStatus">
                <label class="form-check-label" for="colStatus">Status</label>
              </div>
            </div>
            
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Export</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Sidebar toggle functionality
      document.getElementById('sidebarCollapse').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('content').classList.toggle('active');
      });
    });
  </script>
</body>
</html>