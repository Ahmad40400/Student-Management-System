<?php
include 'db_connect.php';
requireAuth();

// Get courses for dropdown
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll();

$selected_course = isset($_GET['course_id']) ? $_GET['course_id'] : '';
$students = [];

if ($selected_course) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE course = (SELECT course_name FROM courses WHERE id = ?) ORDER BY full_name");
    $stmt->execute([$selected_course]);
    $students = $stmt->fetchAll();
}

// Process grade submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf_token)) {
        die("CSRF token validation failed");
    }
    
    $course_id = $_POST['course_id'];
    $semester = $_POST['semester'];
    
    foreach ($_POST['grades'] as $student_id => $grade) {
        if (!empty($grade)) {
            // Check if grade already exists for this student and course
            $check = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ? AND semester = ?");
            $check->execute([$student_id, $course_id, $semester]);
            
            if ($check->fetch()) {
                // Update existing grade
                $stmt = $pdo->prepare("UPDATE grades SET grade = ? WHERE student_id = ? AND course_id = ? AND semester = ?");
                $stmt->execute([$grade, $student_id, $course_id, $semester]);
            } else {
                // Insert new grade
                $stmt = $pdo->prepare("INSERT INTO grades (student_id, course_id, grade, semester) VALUES (?, ?, ?, ?)");
                $stmt->execute([$student_id, $course_id, $grade, $semester]);
            }
        }
    }
    
    $_SESSION['success'] = "Grades recorded successfully!";
    header("Location: add-grades.php?course_id=$course_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Grades</title>
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
        <h4 class="mb-0">Add Grades</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Grades</li>
          </ol>
        </nav>
      </div>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
      <?php endif; ?>

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Select Course</h5>
        </div>
        <div class="card-body">
          <form method="GET" class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Course</label>
              <select name="course_id" class="form-select" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $selected_course == $c['id'] ? 'selected' : '' ?>>
                    <?= $c['course_name'] ?> (<?= $c['course_code'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary">Load Students</button>
            </div>
          </form>
        </div>
      </div>

      <?php if ($selected_course && count($students) > 0): 
        // Get current semester (default to current year and season)
        $current_year = date('Y');
        $current_month = date('n');
        $semester = ($current_month >= 1 && $current_month <= 5) ? "Spring $current_year" : 
                   (($current_month >= 8 && $current_month <= 12) ? "Fall $current_year" : "Summer $current_year");
      ?>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Enter Grades</h5>
          <span class="badge bg-primary"><?= count($students) ?> students</span>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="course_id" value="<?= $selected_course ?>">
            
            <div class="row mb-4">
              <div class="col-md-6">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select" required>
                  <option value="Spring <?= $current_year ?>" <?= $semester == "Spring $current_year" ? 'selected' : '' ?>>Spring <?= $current_year ?></option>
                  <option value="Summer <?= $current_year ?>" <?= $semester == "Summer $current_year" ? 'selected' : '' ?>>Summer <?= $current_year ?></option>
                  <option value="Fall <?= $current_year ?>" <?= $semester == "Fall $current_year" ? 'selected' : '' ?>>Fall <?= $current_year ?></option>
                  <option value="Spring <?= $current_year-1 ?>" >Spring <?= $current_year-1 ?></option>
                  <option value="Fall <?= $current_year-1 ?>" >Fall <?= $current_year-1 ?></option>
                </select>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Grade (0-100)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $student): 
                    // Get existing grade if any
                    $existing_grade = '';
                    if (isset($_GET['semester'])) {
                      $stmt = $pdo->prepare("SELECT grade FROM grades WHERE student_id = ? AND course_id = ? AND semester = ?");
                      $stmt->execute([$student['id'], $selected_course, $_GET['semester']]);
                      $grade_row = $stmt->fetch();
                      $existing_grade = $grade_row ? $grade_row['grade'] : '';
                    }
                  ?>
                    <tr>
                      <td><?= $student['id'] ?></td>
                      <td><?= htmlspecialchars($student['full_name']) ?></td>
                      <td>
                        <input type="number" name="grades[<?= $student['id'] ?>]" class="form-control" 
                               min="0" max="100" step="0.01" value="<?= $existing_grade ?>"
                               placeholder="Enter grade">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary btn-lg">Save Grades</button>
            </div>
          </form>
        </div>
      </div>
      <?php elseif ($selected_course): ?>
        <div class="alert alert-info">
          No students found for this course.
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
    });
  </script>
</body>
</html>