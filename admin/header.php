<?php
// Get the current page filename so it can be used to make active muni ko ma used baxa
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!-- TOP BAR -->
<div class="admin-topbar">
    <h3 class="mb-0 h-font"><u>Jersey E-mart</u></h3> <!-- Hamburger (Visible only on tablet/mobile) --> <button
        class="mobile-toggle d-lg-none" onclick="openSidebar()"> <i class="bi bi-list"
            style="font-size: 30px; color:white;"></i> </button> <a href="logout.php"
        class="btn btn-light btn-sm d-none d-lg-block">Log Out</a>
</div>
<!-- SIDEBAR -->
<div id="adminSidebar" class="admin-sidebar">

    <!-- Close Button (only mobile/tablet) -->
    <button class="close-sidebar d-lg-none" onclick="closeSidebar()">
        <i class="bi bi-x-lg"></i>
    </button>

    <h4 class="mt-2 text-light text-center">ADMIN PANEL</h4>

    <ul class="nav flex-column mt-3 px-2">
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"
                href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'bookings.php' ? 'active' : '' ?>"
                href="bookings.php">Bookings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'users.php' ? 'active' : '' ?>" href="users.php">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'user_queries.php' ? 'active' : '' ?>"
                href="user_queries.php">User Queries</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'rooms.php' ? 'active' : '' ?>" href="jersies.php">Jersies</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'rooms.php' ? 'active' : '' ?>" href="kits.php">Kits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'carousel.php' ? 'active' : '' ?>"
                href="carousel.php">Carousel</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= $currentPage == 'settings.php' ? 'active' : '' ?>"
                href="settings.php">Settings</a>
        </li>
    </ul>
</div>

<style>
    /* Active link styling */
    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: bold;
        border-radius: 5px;
    }
</style>