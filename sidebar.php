<?php
// Sidebar navigation items
$navItems = [
    ['title' => 'Dashboard', 'icon' => 'fas fa-home', 'link' => 'index.php'],
    ['title' => 'Add Student', 'icon' => 'fas fa-user-plus', 'link' => 'add-students.php'],
    ['title' => 'View Students', 'icon' => 'fas fa-users', 'link' => 'view-students.php'],
    ['title' => 'Courses', 'icon' => 'fas fa-book', 'link' => 'courses.php'],
    ['title' => 'Profile', 'icon' => 'fas fa-user-circle', 'link' => 'profile.php'],
    ['title' => 'Settings', 'icon' => 'fas fa-cog', 'link' => 'settings.php'],
    ['title' => 'Logout', 'icon' => 'fas fa-sign-out-alt', 'link' => 'logout.php']
];

// Get current page to set active class
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar">
    <div class="sidebar-header">
        <h3 class="mb-0">EduManage</h3>
        <p class="text-muted mb-0">Student System</p>
    </div>

    <ul class="list-unstyled components">
        <?php foreach ($navItems as $item): 
            $isActive = ($currentPage === $item['link']) ? 'active' : '';
        ?>
            <li class="<?= $isActive ?>">
                <a href="<?= $item['link'] ?>">
                    <i class="<?= $item['icon'] ?>"></i> 
                    <?= $item['title'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>