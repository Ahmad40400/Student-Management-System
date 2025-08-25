<?php
include 'db_connect.php';
requireAuth();

// Get report parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'students';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

// Get courses for dropdown
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll();

// Generate reports based on type
$report_data = [];
$report_title = '';

switch ($report_type) {
    case 'students':
        $report_title = 'Student Report';
        $query = "SELECT * FROM students WHERE 1=1";
        $params = [];
        
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND created_at BETWEEN ? AND ?";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        }
        
        if (!empty($course_id)) {
            $query .= " AND course = (SELECT course_name FROM courses WHERE id = ?)";
            $params[] = $course_id;
        }
        
        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $report_data = $stmt->fetchAll();
        break;
        
    case 'attendance':
        $report_title = 'Attendance Report';
        $query = "SELECT a.*, s.full_name, c.course_name 
                 FROM attendance a 
                 JOIN students s ON a.student_id = s.id 
                 JOIN courses c ON a.course_id = c.id 
                 WHERE 1=1";
        $params = [];
        
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND a.date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        if (!empty($course_id)) {
            $query .= " AND a.course_id = ?";
            $params[] = $course_id;
        }
        
        $query .= " ORDER BY a.date DESC, s.full_name";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $report_data = $stmt->fetchAll();
        break;
        
    case 'grades':
        $report_title = 'Grades Report';
        $query = "SELECT g.*, s.full_name, c.course_name 
                 FROM grades g 
                 JOIN students s ON g.student_id = s.id 
                 JOIN courses c ON g.course_id = c.id 
                 WHERE 1=1";
        $params = [];
        
        if (!empty($course_id)) {
            $query .= " AND g.course_id = ?";
            $params[] = $course_id;
        }
        
        $query .= " ORDER BY c.course_name, s.full_name";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $report_data = $stmt->fetchAll();
        break;
        
    case 'course_stats':
        $report_title = 'Course Statistics';
        $query = "SELECT c.course_name, 
                         COUNT(DISTINCT s.id) as student_count,
                         AVG(g.grade) as avg_grade,
                         COUNT(DISTINCT a.id) as attendance_count
                 FROM courses c
                 LEFT JOIN students s ON c.course_name = s.course
                 LEFT JOIN grades g ON c.id = g.course_id
                 LEFT JOIN attendance a ON c.id = a.course_id
                 GROUP BY c.id
                 ORDER BY c.course_name";
        $stmt = $pdo->query($query);
        $report_data = $stmt->fetchAll();
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports</title>
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
    
    .stats-card {
      text-align: center;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .stats-card i {
      font-size: 2rem;
      margin-bottom: 10px;
    }
    
    .stats-card h3 {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 5px;
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
        <h4 class="mb-0">Reports</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
          </ol>
        </nav>
      </div>

      <div class="row mb-4">
        <div class="col-md-3">
          <div class="stats-card bg-primary text-white">
            <i class="fas fa-users"></i>
            <h3><?= $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() ?></h3>
            <p>Total Students</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card bg-success text-white">
            <i class="fas fa-book"></i>
            <h3><?= $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn() ?></h3>
            <p>Total Courses</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card bg-info text-white">
            <i class="fas fa-user-check"></i>
            <h3><?= $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'Present'")->fetchColumn() ?></h3>
            <p>Today's Attendance</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card bg-warning text-white">
            <i class="fas fa-chart-line"></i>
            <h3><?= round($pdo->query("SELECT AVG(grade) FROM grades")->fetchColumn(), 1) ?></h3>
            <p>Average Grade</p>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Generate Report</h5>
        </div>
        <div class="card-body">
          <form method="GET" class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Report Type</label>
              <select name="report_type" class="form-select" required>
                <option value="students" <?= $report_type == 'students' ? 'selected' : '' ?>>Student Report</option>
                <option value="attendance" <?= $report_type == 'attendance' ? 'selected' : '' ?>>Attendance Report</option>
                <option value="grades" <?= $report_type == 'grades' ? 'selected' : '' ?>>Grades Report</option>
                <option value="course_stats" <?= $report_type == 'course_stats' ? 'selected' : '' ?>>Course Statistics</option>
              </select>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" 
                     <?= $report_type == 'course_stats' ? 'disabled' : '' ?>>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" 
                     <?= $report_type == 'course_stats' ? 'disabled' : '' ?>>
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Course</label>
              <select name="course_id" class="form-select">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $course_id == $c['id'] ? 'selected' : '' ?>>
                    <?= $c['course_name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-12">
              <button type="submit" class="btn btn-primary">Generate Report</button>
              <?php if (!empty($report_data)): ?>
                <a href="export.php?type=<?= $report_type ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&course_id=<?= $course_id ?>" 
                   class="btn btn-success ms-2">
                  <i class="fas fa-download"></i> Export
                </a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <?php if (!empty($report_data)): ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><?= $report_title ?></h5>
          <span class="badge bg-primary"><?= count($report_data) ?> records</span>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <?php if ($report_type == 'students'): ?>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Created</th>
                  <?php elseif ($report_type == 'attendance'): ?>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Notes</th>
                  <?php elseif ($report_type == 'grades'): ?>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Grade</th>
                    <th>Semester</th>
                  <?php elseif ($report_type == 'course_stats'): ?>
                    <th>Course</th>
                    <th>Students</th>
                    <th>Avg Grade</th>
                    <th>Attendance</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($report_data as $row): ?>
                  <tr>
                    <?php if ($report_type == 'students'): ?>
                      <td><?= $row['id'] ?></td>
                      <td><?= htmlspecialchars($row['full_name']) ?></td>
                      <td><?= htmlspecialchars($row['email']) ?></td>
                      <td><?= htmlspecialchars($row['phone']) ?></td>
                      <td><?= htmlspecialchars($row['course']) ?></td>
                      <td><span class="badge bg-<?= $row['status'] == 'Active' ? 'success' : 'warning' ?>"><?= $row['status'] ?></span></td>
                      <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                    <?php elseif ($report_type == 'attendance'): ?>
                      <td><?= date('M j, Y', strtotime($row['date'])) ?></td>
                      <td><?= htmlspecialchars($row['full_name']) ?></td>
                      <td><?= htmlspecialchars($row['course_name']) ?></td>
                      <td><span class="badge bg-<?= $row['status'] == 'Present' ? 'success' : ($row['status'] == 'Absent' ? 'danger' : 'warning') ?>"><?= $row['status'] ?></span></td>
                      <td><?= htmlspecialchars($row['notes']) ?></td>
                    <?php elseif ($report_type == 'grades'): ?>
                      <td><?= htmlspecialchars($row['full_name']) ?></td>
                      <td><?= htmlspecialchars($row['course_name']) ?></td>
                      <td><span class="badge bg-<?= $row['grade'] >= 90 ? 'success' : ($row['grade'] >= 70 ? 'warning' : 'danger') ?>"><?= $row['grade'] ?></span></td>
                      <td><?= $row['semester'] ?></td>
                    <?php elseif ($report_type == 'course_stats'): ?>
                      <td><?= htmlspecialchars($row['course_name']) ?></td>
                      <td><?= $row['student_count'] ?></td>
                      <td><?= $row['avg_grade'] ? round($row['avg_grade'], 1) : 'N/A' ?></td>
                      <td><?= $row['attendance_count'] ?></td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>
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
      
      // Disable date fields for course stats
      document.querySelector('select[name="report_type"]').addEventListener('change', function() {
        const isCourseStats = this.value === 'course_stats';
        document.querySelector('input[name="start_date"]').disabled = isCourseStats;
        document.querySelector('input[name="end_date"]').disabled = isCourseStats;
      });
    });
  </script>
</body>
</html>