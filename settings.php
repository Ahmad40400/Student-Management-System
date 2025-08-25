<?php
// settings.php
include 'db_connect.php';
requireAuth();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf_token)) {
        die("CSRF token validation failed");
    }
    
    // For demonstration - in a real system, you'd save these to a database table
    $_SESSION['success'] = "Settings updated successfully!";
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - Student Management System</title>
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
      --sidebar-width: 250px;
      --header-height: 70px;
    }
    
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
    
    /* Settings specific styles */
    .settings-nav .nav-link {
      padding: 12px 20px;
      color: #495057;
      border-radius: 8px;
      margin-bottom: 5px;
    }
    
    .settings-nav .nav-link.active {
      background-color: #e9ecef;
      color: var(--primary);
      font-weight: 600;
    }
    
    .settings-nav .nav-link i {
      width: 24px;
      text-align: center;
      margin-right: 10px;
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
        <h4 class="mb-0">System Settings</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Settings</li>
          </ol>
        </nav>
      </div>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
      <?php endif; ?>

      <div class="row">
        <div class="col-md-3">
          <div class="card">
            <div class="card-body p-0">
              <nav class="settings-nav">
                <div class="nav nav-pills flex-column" id="settingsTabs" role="tablist">
                  <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab">
                    <i class="fas fa-cog"></i> General Settings
                  </button>
                  <button class="nav-link" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#appearance" type="button" role="tab">
                    <i class="fas fa-paint-brush"></i> Appearance
                  </button>
                  <button class="nav-link" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab">
                    <i class="fas fa-bell"></i> Notifications
                  </button>
                  <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                    <i class="fas fa-shield-alt"></i> Security
                  </button>
                  <button class="nav-link" id="backup-tab" data-bs-toggle="pill" data-bs-target="#backup" type="button" role="tab">
                    <i class="fas fa-database"></i> Backup & Restore
                  </button>
                </div>
              </nav>
            </div>
          </div>
        </div>
        
        <div class="col-md-9">
          <div class="tab-content" id="settingsTabsContent">
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">School/Institution Name</label>
                        <input type="text" class="form-control" value="EduManage Academy">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="admin@edumanage.edu">
                      </div>
                    </div>
                    
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" value="+1 (555) 123-4567">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Timezone</label>
                        <select class="form-select">
                          <option selected>(UTC-05:00) Eastern Time</option>
                          <option>(UTC-06:00) Central Time</option>
                          <option>(UTC-07:00) Mountain Time</option>
                          <option>(UTC-08:00) Pacific Time</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Address</label>
                      <textarea class="form-control" rows="2">123 Education Street, Knowledge City</textarea>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Date Format</label>
                      <select class="form-select">
                        <option>MM/DD/YYYY</option>
                        <option>DD/MM/YYYY</option>
                        <option>YYYY-MM-DD</option>
                      </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                  </form>
                </div>
              </div>
            </div>
            
            <!-- Appearance Tab -->
            <div class="tab-pane fade" id="appearance" role="tabpanel">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Appearance Settings</h5>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <div class="row mb-4">
                      <div class="col-md-6">
                        <label class="form-label">Theme Color</label>
                        <div class="d-flex gap-2">
                          <div class="color-option rounded-circle" style="background-color: #4361ee; width: 40px; height: 40px; cursor: pointer;" data-color="#4361ee"></div>
                          <div class="color-option rounded-circle" style="background-color: #4f46e5; width: 40px; height: 40px; cursor: pointer;" data-color="#4f46e5"></div>
                          <div class="color-option rounded-circle" style="background-color: #7c3aed; width: 40px; height: 40px; cursor: pointer;" data-color="#7c3aed"></div>
                          <div class="color-option rounded-circle" style="background-color: #059669; width: 40px; height: 40px; cursor: pointer;" data-color="#059669"></div>
                          <div class="color-option rounded-circle" style="background-color: #dc2626; width: 40px; height: 40px; cursor: pointer;" data-color="#dc2626"></div>
                        </div>
                        <input type="hidden" name="theme_color" id="themeColor" value="#4361ee">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Sidebar Style</label>
                        <select class="form-select">
                          <option>Default</option>
                          <option>Compact</option>
                          <option>Icons Only</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Logo</label>
                      <input type="file" class="form-control">
                      <div class="form-text">Recommended size: 180x60 pixels</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                      <label class="form-check-label" for="darkModeSwitch">Enable Dark Mode</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="fixedHeaderSwitch" checked>
                      <label class="form-check-label" for="fixedHeaderSwitch">Fixed Header</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Appearance Settings</button>
                  </form>
                </div>
              </div>
            </div>
            
            <!-- Notifications Tab -->
            <div class="tab-pane fade" id="notifications" role="tabpanel">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Notification Settings</h5>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <h6 class="mb-3">Email Notifications</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                      <label class="form-check-label" for="emailNotifications">Enable Email Notifications</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="newStudentEmail" checked>
                      <label class="form-check-label" for="newStudentEmail">New Student Registration</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="attendanceEmail" checked>
                      <label class="form-check-label" for="attendanceEmail">Daily Attendance Reports</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="gradeEmail">
                      <label class="form-check-label" for="gradeEmail">Grade Updates</label>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">System Notifications</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="browserNotifications" checked>
                      <label class="form-check-label" for="browserNotifications">Browser Notifications</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="lowStorageAlert" checked>
                      <label class="form-check-label" for="lowStorageAlert">Low Storage Alerts</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="systemUpdates" checked>
                      <label class="form-check-label" for="systemUpdates">System Update Notifications</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                  </form>
                </div>
              </div>
            </div>
            
            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Security Settings</h5>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Session Timeout</label>
                        <select class="form-select">
                          <option>15 minutes</option>
                          <option>30 minutes</option>
                          <option selected>1 hour</option>
                          <option>2 hours</option>
                          <option>4 hours</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Password Policy</label>
                        <select class="form-select">
                          <option>Low (6+ characters)</option>
                          <option selected>Medium (8+ characters with letters and numbers)</option>
                          <option>High (10+ characters with uppercase, lowercase, numbers and symbols)</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="twoFactorAuth">
                      <label class="form-check-label" for="twoFactorAuth">Enable Two-Factor Authentication</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="loginAttempts" checked>
                      <label class="form-check-label" for="loginAttempts">Limit Login Attempts (5 attempts)</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="ipWhitelist">
                      <label class="form-check-label" for="ipWhitelist">IP Whitelisting</label>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Change Password</h6>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control">
                      </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Security Settings</button>
                  </form>
                </div>
              </div>
            </div>
            
            <!-- Backup & Restore Tab -->
            <div class="tab-pane fade" id="backup" role="tabpanel">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Backup & Restore</h5>
                </div>
                <div class="card-body">
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="card bg-light">
                        <div class="card-body text-center">
                          <i class="fas fa-download fa-2x text-primary mb-3"></i>
                          <h5>Backup Database</h5>
                          <p class="text-muted">Create a backup of your current database</p>
                          <button class="btn btn-outline-primary">Download Backup</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card bg-light">
                        <div class="card-body text-center">
                          <i class="fas fa-upload fa-2x text-success mb-3"></i>
                          <h5>Restore Database</h5>
                          <p class="text-muted">Restore from a previous backup file</p>
                          <div class="d-flex gap-2 justify-content-center">
                            <input type="file" class="d-none" id="backupFile">
                            <button class="btn btn-outline-success" onclick="document.getElementById('backupFile').click()">Choose File</button>
                            <button class="btn btn-success">Restore</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <h6 class="mb-3">Automatic Backups</h6>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Backup Frequency</label>
                      <select class="form-select">
                        <option>Daily</option>
                        <option selected>Weekly</option>
                        <option>Monthly</option>
                        <option>Never</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Backup Retention</label>
                      <select class="form-select">
                        <option>Keep last 7 backups</option>
                        <option selected>Keep last 30 backups</option>
                        <option>Keep all backups</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="autoBackup" checked>
                    <label class="form-check-label" for="autoBackup">Enable Automatic Backups</label>
                  </div>
                  
                  <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Last backup: October 15, 2023 02:30 AM
                  </div>
                  
                  <button type="button" class="btn btn-primary">Save Backup Settings</button>
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
      
      // Theme color selection
      document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
          document.querySelectorAll('.color-option').forEach(opt => {
            opt.style.border = 'none';
          });
          this.style.border = '3px solid #495057';
          document.getElementById('themeColor').value = this.getAttribute('data-color');
        });
      });
      
      // Initialize the first color option as selected
      document.querySelector('.color-option').style.border = '3px solid #495057';
    });
  </script>
</body>
</html>