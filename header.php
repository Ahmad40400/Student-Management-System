<header id="header">
    <button type="button" id="sidebarCollapse" class="btn btn-primary">
        <i class="fas fa-bars"></i>
        <span>Menu</span>
    </button>
    
    <div class="d-flex align-items-center">
        <!-- <div class="dropdown me-3">
            <a href="#" class="dropdown-toggle text-dark" role="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                <li><a class="dropdown-item" href="index.php">New student registered</a></li>
                <li><a class="dropdown-item" href="courses.php">Course updated</a></li>
                <li><a class="dropdown-item" href="#">System update available</a></li>
            </ul>
        </div> -->
        
        <div class="dropdown">
            <a href="#" class="dropdown-toggle d-flex align-items-center text-dark text-decoration-none" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4361ee&color=fff" alt="Admin" width="32" height="32" class="rounded-circle me-2">
                <span>Admin User</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Logout</a></li>
            </ul>
        </div>
    </div>
</header>