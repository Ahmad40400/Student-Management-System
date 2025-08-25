<?php
// index.php - Enhanced with authentication and more features
include 'db_connect.php';
requireAuth();

// Get statistics
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// Get attendance statistics
$attendanceStats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
    FROM attendance 
    WHERE date = CURDATE()
")->fetch();
$attendancePercentage = $attendanceStats['total'] > 0 ? 
    round(($attendanceStats['present'] / $attendanceStats['total']) * 100) : 0;

// Get grade statistics
$gradeStats = $pdo->query("
    SELECT AVG(grade) as average_grade FROM grades
")->fetch();
$averageGrade = $gradeStats['average_grade'] ? round($gradeStats['average_grade'], 1) : 'N/A';

// Get recent students
$recentStudents = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll();

// Get upcoming events (placeholder - could be connected to a calendar system)
$events = [
    ['title' => 'Midterm Exams', 'date' => date('Y-m-d', strtotime('+5 days')), 'type' => 'exam'],
    ['title' => 'Faculty Meeting', 'date' => date('Y-m-d', strtotime('+2 days')), 'type' => 'meeting'],
    ['title' => 'Project Submission', 'date' => date('Y-m-d', strtotime('+7 days')), 'type' => 'deadline']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Student Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
  <div class="wrapper d-flex align-items-stretch">
    <!-- Include sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
      <!-- Include header -->
      <?php include 'header.php'; ?>

      <!-- Main Content -->
      <div id="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="mb-0">Dashboard</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </nav>
        </div>

        <!-- Stats Cards -->
        <div class="row">
          <div class="col-md-3">
            <div class="card stat-card">
              <div class="card-body">
                <i class="fas fa-users"></i>
                <h2><?= $totalStudents ?></h2>
                <p>Total Students</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card">
              <div class="card-body">
                <i class="fas fa-book"></i>
                <h2><?= $totalCourses ?></h2>
                <p>Courses</p>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card">
              <div class="card-body">
                <i class="fas fa-user-check"></i>
                <h2><?= $attendancePercentage ?>%</h2>
                <p>Today's Attendance</p>
                <div class="progress mt-2">
                  <div class="progress-bar bg-success" role="progressbar" 
                       style="width: <?= $attendancePercentage ?>%" 
                       aria-valuenow="<?= $attendancePercentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card">
              <div class="card-body">
                <i class="fas fa-graduation-cap"></i>
                <h2><?= $averageGrade ?></h2>
                <p>Average Grade</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Students & Upcoming Events -->
        <div class="row mt-4">
          <div class="col-md-8">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Students</h5>
                <a href="view-students.php" class="btn btn-sm btn-primary">View All</a>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($recentStudents as $student): 
                        $statusClass = $student['status'] == 'Active' ? 'badge bg-success' : 'badge bg-warning';
                      ?>
                        <tr>
                          <td><?= $student['id'] ?></td>
                          <td><?= htmlspecialchars($student['full_name']) ?></td>
                          <td><?= htmlspecialchars($student['email']) ?></td>
                          <td><?= htmlspecialchars($student['course']) ?></td>
                          <td><span class="<?= $statusClass ?>"><?= $student['status'] ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Upcoming Events</h5>
              </div>
              <div class="card-body">
                <div class="list-group list-group-flush">
                  <?php foreach ($events as $event): 
                    $daysLeft = round((strtotime($event['date']) - time()) / (60 * 60 * 24));
                    $badgeClass = $event['type'] == 'exam' ? 'bg-danger' : 
                                 ($event['type'] == 'meeting' ? 'bg-info' : 'bg-warning');
                  ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                      <div>
                        <h6 class="mb-1"><?= $event['title'] ?></h6>
                        <small class="text-muted"><?= date('M j, Y', strtotime($event['date'])) ?></small>
                      </div>
                      <span class="badge <?= $badgeClass ?> event-badge">
                        <?= $daysLeft > 0 ? "in $daysLeft days" : "Today" ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            
            <div class="card mt-4">
              <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="d-grid gap-2">
                  <a href="add-students.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Add New Student
                  </a>
                  <a href="take-attendance.php" class="btn btn-outline-primary">
                    <i class="fas fa-clipboard-check me-2"></i> Take Attendance
                  </a>
                  <a href="add-grades.php" class="btn btn-outline-primary">
                    <i class="fas fa-chart-line me-2"></i> Record Grades
                  </a>
                  <a href="reports.php" class="btn btn-outline-primary">
                    <i class="fas fa-chart-pie me-2"></i> Generate Reports
                  </a>
                </div>
              </div>
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