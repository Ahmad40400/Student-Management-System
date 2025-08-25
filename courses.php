<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code']; // ADD THIS LINE
    
    try {
        $sql = "INSERT INTO courses (course_name, course_code) VALUES (:course_name, :course_code)"; // MODIFY THIS LINE
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'course_name' => $course_name,
            'course_code' => $course_code // ADD THIS LINE
        ]);
        
        // Refresh page to show new course
        header("Location: courses.php");
        exit();
    } catch(PDOException $e) {
        $error = "Course already exists or invalid input. Error: " . $e->getMessage(); // MODIFY THIS LINE
    }
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];
    
    // Check if any students are enrolled in this course
    $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE course = (SELECT course_name FROM courses WHERE id = ?)");
    $check->execute([$course_id]);
    $studentCount = $check->fetchColumn();
    
    if ($studentCount > 0) {
        $error = "Cannot delete course. There are students enrolled in this course.";
    } else {
        $delete = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $delete->execute([$course_id]);
        header("Location: courses.php");
        exit();
    }
}

$courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --info: #4895ef;
      --warning: #f72585;
      --danger: #e63946;
      --light: #f8f9fa;
      --dark: #212529;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fb;
      color: #495057;
    }
    
    .list-group-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
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
  </style>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  
  <div id="content">
    <?php include 'header.php'; ?>
    
    <div id="main-content">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Manage Courses</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Courses</li>
          </ol>
        </nav>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Add New Course</h5>
            </div>
            <div class="card-body">
              <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
              <?php endif; ?>
              
              <!-- MODIFY THE FORM TO INCLUDE COURSE_CODE FIELD -->
<form method="POST">
    <div class="mb-3">
        <label for="course_name" class="form-label">Course Name</label>
        <input type="text" name="course_name" class="form-control" placeholder="Enter course name" required>
    </div>
    <div class="mb-3">
        <label for="course_code" class="form-label">Course Code</label>
        <input type="text" name="course_code" class="form-control" placeholder="Enter course code" required>
    </div>
    <button type="submit" class="btn btn-success">Add Course</button>
</form>
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">All Courses</h5>
              <span class="badge bg-primary"><?= count($courses) ?> courses</span>
            </div>
            <div class="card-body p-0">
           <?php if (count($courses) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Code</th> <!-- ADD THIS COLUMN HEADER -->
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= $c['course_code'] ?></td> <!-- ADD THIS CELL -->
                        <td><?= $c['course_name'] ?></td>
                        <td>
                            <a href="courses.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to delete this course?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
              <?php else: ?>
                <div class="text-center py-4 text-muted">
                  <i class="fas fa-book fa-2x mb-2"></i>
                  <p>No courses found</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
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