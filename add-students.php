<?php
include 'db_connect.php';

// Get courses for dropdown
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $status = $_POST['status'];

    $sql = "INSERT INTO students (full_name, email, phone, course, status) 
            VALUES (:name, :email, :phone, :course, :status)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name, 
        'email' => $email, 
        'phone' => $phone, 
        'course' => $course,
        'status' => $status
    ]);

    header("Location: view-students.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Student</title>
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
    
    .container {
      max-width: 800px;
    }
    
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 12px 15px;
      border: 1px solid #e1e5eb;
    }
    
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
      border-color: var(--primary);
    }
    
    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .btn-primary:hover {
      background-color: var(--secondary);
      border-color: var(--secondary);
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
        <h4 class="mb-0">Add Student</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Student</li>
          </ol>
        </nav>
      </div>

      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Student Information</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Pending">Pending</option>
                </select>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="form-label">Course</label>
              <select name="course" class="form-select" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c['course_name'] ?>"><?= $c['course_name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Save Student</button>
              <a href="view-students.php" class="btn btn-outline-secondary">Cancel</a>
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