<?php
session_start();
require_once("../shared/dbconnect.php");
include_once("../shared/commonlinks.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --admin-green-dark: #164a44;
        --admin-sidebar-width: 160px;
        /* desired width */
        --admin-topbar-height: 70px;
    }

    /*  TOPBAR  */
    .admin-header {
        height: var(--admin-topbar-height);
        background: var(--admin-green-dark);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1050;
        display: flex;
        align-items: center;
    }

    /*  SIDEBAR  */
    .admin-sidebar {
        border-top: 3px solid #0b1720ff;
        width: var(--admin-sidebar-width);
        background: var(--admin-green-dark);
        color: #ffffff;
    }

    .admin-sidebar .nav-link {
        color: #fff;
        padding: 12px 18px;
        font-weight: 500;
        border-left: 4px solid transparent;
    }

    .admin-sidebar .nav-link:hover,
    .admin-sidebar .nav-link.active {
        background: rgba(25, 132, 37, 0.12);
        border-left-color: rgba(54, 157, 58, 0.3);
        border: 1px solid whitesmoke;
    }

    /* Desktop sidebar (always visible) */
    @media (min-width: 992px) {
        .admin-sidebar {
            position: fixed;
            top: var(--admin-topbar-height);
            left: 0;
            height: calc(100vh - var(--admin-topbar-height));
            overflow-y: auto;
            z-index: 1030;
        }
    }

    /*  MOBILE OFFCANVAS SIDEBAR  */
    #adminSidebarMobile.offcanvas-start {
        border-top: 3px solid #0b1720ff;
        width: var(--admin-sidebar-width);
        background: var(--admin-green-dark);
    }

    #adminSidebarMobile .nav-link {
        color: #fff;
        border-left: 4px solid transparent;
    }

    #adminSidebarMobile .nav-link:hover,
    #adminSidebarMobile .nav-link.active {
        background: rgba(25, 132, 37, 0.12);
        border-left-color: rgba(54, 157, 58, 0.3);
        border: 1px solid whitesmoke;
    }

    /* Make mobile offcanvas appear below topbar */
    @media (max-width: 991px) {
        #adminSidebarMobile {
            margin-top: var(--admin-topbar-height);
            height: calc(100vh - var(--admin-topbar-height));
        }
    }

    /*  CONTENT  */
    .admin-content {
        margin-top: var(--admin-topbar-height);
        padding: 20px;
        background: #e5f0eb;
        min-height: calc(100vh - var(--admin-topbar-height));
    }

    @media (min-width: 992px) {
        .admin-content {
            margin-left: var(--admin-sidebar-width);
        }
    }
</style>

<!--  DESKTOP SIDEBAR  -->
<div class="admin-sidebar d-none d-lg-flex flex-column">
    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"
                href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage == 'orders.php' ? 'active' : '' ?>" href="orders.php">Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage == 'users.php' ? 'active' : '' ?>" href="users.php">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage == 'jersies.php' ? 'active' : '' ?>" href="jersies.php">Jerseys</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage == 'carousel.php' ? 'active' : '' ?>" href="carousel.php">Carousel</a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="user_queries.php">User Queries</a>
        </li>
    </ul>
</div>

<!--  MOBILE SIDEBAR (OFFCANVAS)  -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminSidebarMobile">
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"
                    href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'orders.php' ? 'active' : '' ?>" href="orders.php">Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'users.php' ? 'active' : '' ?>" href="users.php">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'jersies.php' ? 'active' : '' ?>" href="jersies.php">Jerseys</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'carousel.php' ? 'active' : '' ?>"
                    href="carousel.php">Carousel</a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="user_queries.php">User Queries</a>
            </li>
        </ul>
    </div>
</div>

<!--  TOPBAR  -->
<nav class="navbar navbar-dark admin-header">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <!-- Hamburger only on mobile -->
            <button class="btn btn-outline-light d-lg-none" data-bs-toggle="offcanvas"
                data-bs-target="#adminSidebarMobile">
                ☰
            </button>

            <span class="navbar-brand fw-bold mb-0">Admin Panel</span>
        </div>

        <!-- Logout always visible -->
        <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </div>
</nav>