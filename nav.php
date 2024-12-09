<?
session_start();
$_SESSION['email'] = $user['email'];
$_SESSION['name'] = $user['name'];
// print_r($_SESSION);
?>

<nav class="navbar navbar-expand-lg">
                <div class="container">
                    <a class="navbar-brand" href="index.html">
                        <i class="navbar-brand-icon bi-book me-2"></i>
                        <span>COA</span>
                    </a>

                    <div class="d-lg-none ms-auto me-3">
                        <a href="#" class="btn custom-btn custom-border-btn btn-naira btn-inverted">
                            <i class="btn-icon bi-cloud-download"></i>
                            <span>Memos</span>
                        </a>
                    </div>
    
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
    
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-lg-auto me-lg-4">


                        <li class="nav-item list-group">
                                <a class="nav-link" href=""><div class="digital-clock">00:00:00</div></a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link  <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : 'text-custom'; ?>" aria-current="true" href="index.php">Home</a>
                            </li>
    
                             <!-- Project List Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (basename($_SERVER['PHP_SELF']) == 'project.php' || basename($_SERVER['PHP_SELF']) == 'fulfilled_project.php') ? 'active' : 'text-custom'; ?>" href="#" id="projectDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Project List
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="projectDropdown">
                        <li><a class="dropdown-item" style="font-size: small" href="project.php">All Projects</a></li>
                        <li><a class="dropdown-item" style="font-size: small" href="fulfilled_project.php">Fulfilled Projects</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown-center d-flex align-content-center">
  <button class="text-white btn dropdown-toggle" style="font-size: small;" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
    Sections
  </button>
  <ul class="dropdown-menu dropend" aria-labelledby="dropdownMenuButton">
    <!-- Section A -->
    <li class="dropdown-submenu" style="font-size: small;">
      <a class="dropdown-item dropdown-toggle" href="#">Section A</a>
      <ul class="dropdown-menu dropdown-toggle-split" style="font-size: small;">
        <?php
        // Fetch users for Section A
        $query = "SELECT id, name FROM users WHERE section_id = '1'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<li><a class="dropdown-item" style="font-size: 10px;" href="project_task.php?user_id=' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a></li>';
            }
        } else {
            echo '<li><a class="dropdown-item" style="font-size: 10px;" href="#">No users in Section A</a></li>';
        }
        ?>
      </ul>
    </li>

    <!-- Section B -->
    <li class="dropdown-submenu" style="font-size: small;">
      <a class="dropdown-item dropdown-toggle" href="#">Section B</a>
      <ul class="dropdown-menu dropdown-toggle-split" style="font-size: small;">
        <?php
        // Fetch users for Section B
        $query = "SELECT id, name FROM users WHERE section_id = '2'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<li><a class="dropdown-item" style="font-size: 10px;" href="project_task.php?user_id=' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a></li>';
            }
        } else {
            echo '<li><a class="dropdown-item" style="font-size: 10px;" href="#">No users in Section B</a></li>';
        }
        ?>
      </ul>
    </li>
  </ul>
</li>






<!-- Reference Dropdown -->
<li class="nav-item d-flex dropdown align-content-center">
    <button class="btn text-white dropdown-toggle" style="font-size: small;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Reference
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" style="font-size: small;" data-bs-toggle="modal" data-bs-target="#directoryModal" href="#">Directory</a></li>
        <li><a class="dropdown-item" style="font-size: small;" data-bs-toggle="modal" data-bs-target="#encodingModal" href="#">Encoding</a></li>

        <!-- Management only for Admin -->
        <?php if (isset($_SESSION['user_email']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
    <li class="dropdown-submenu" style="font-size: small;">
        <a class="dropdown-item" href="#">Management</a>
        <ul class="dropdown-menu dropdown-toggle-split" style="font-size: small;">
            <li><a class="dropdown-item" style="font-size: small;" href="user_management.php">User Management</a></li>
            <li><a class="dropdown-item" style="font-size: small;" href="announcement_management.php">Announcement Management</a></li>
            <li><a class="dropdown-item" style="font-size: small;" data-bs-toggle="modal" data-bs-target="#logsModal" href="#">Activity Logs</a></li>
        </ul>
    </li>
<?php endif; ?>

    </ul>
</li>


                            
                            <li>
                            <div class="d-none d-lg-block mx-auto p-2">
                            <a href="#" class="btn custom-btn custom-border-btn btn-naira btn-inverted">
                                <i class="btn-icon bi-cloud-download"></i>
                                <span>Memos</span><!-- duplicated above one for mobile -->
                            </a>
                        </div>
                        </li>

                        <li class="nav-item dropdown-center">
    <?php if (isset($_SESSION['user_email'])): ?>
        <a class="nav-link dropdown-toggle d-inline-block text-truncate" style="max-width: 100px;" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo $_SESSION['user_email']; ?>
        </a>
        <ul class="dropdown-menu" aria-labelledby="userDropdown">
            <!-- Prevent the default behavior (link click) -->
            <li><a class="dropdown-item" onclick="confirmLogout(event); return false;" style="font-size: small;">Logout</a></li>
        </ul>
    <?php else: ?>
        <a class="nav-link pe-auto" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Sign In</a>
    <?php endif; ?>
</li>

                        </ul>

                       
                    </div>
                </div>
            </nav>