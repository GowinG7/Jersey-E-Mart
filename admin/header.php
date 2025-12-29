<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<style>
    :root {
        --admin-green: #1c6059;
        --admin-green-dark: #164a44;
        --admin-sidebar-width: 260px; /* changeable width for sidebar */
        --admin-topbar-height: 70px; /* consistent topbar height */
        --admin-z-topbar: 9999;
        --admin-z-sidebar: 1020;
        --admin-seam-color: rgba(255,255,255,0.08);
    }

    /* ================= HEADER ================= */
    .admin-header {
        height: var(--admin-topbar-height);
        background: var(--admin-green-dark);
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        z-index: var(--admin-z-topbar);
        display: flex;
        align-items: center;
        padding: 0 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        border-bottom: 1px solid rgba(255,255,255,0.02);
    }

    .admin-header > .container-fluid { padding-left: 1.25rem; padding-right: 1.25rem; }

    @media (min-width: 992px) {
        .admin-header > .container-fluid { padding-left: calc(var(--admin-sidebar-width) + 1.5rem); padding-right: 1.5rem; }
        .admin-header::before {
            content: "";
            position: absolute;
            left: var(--admin-sidebar-width);
            top: 0;
            height: var(--admin-topbar-height);
            width: 1px;
            background: var(--admin-seam-color);
            z-index: calc(var(--admin-z-topbar) + 1);
            pointer-events: none;
        }
    }

    .admin-header .navbar-brand { line-height: 1; font-size: 1.25rem; color: #fff; font-weight: 700; }
    .admin-header .ms-auto { display: flex; align-items: center; gap: 0.5rem; }

    /* ================= SIDEBAR ================= */
    .admin-sidebar {
        width: var(--admin-sidebar-width);
        background: var(--admin-green-dark);
        height: calc(100vh - var(--admin-topbar-height));
        position: fixed;
        top: var(--admin-topbar-height); /* start below topbar */
        left: 0;
        padding-top: 8px; /* small pad instead of using topbar padding */
        display: flex;
        flex-direction: column;
        z-index: var(--admin-z-sidebar);
        overflow-y: auto; /* allow scrolling when links exceed height */
        box-shadow: 1px 0 6px rgba(0,0,0,0.08);
        border-right: 1px solid var(--admin-seam-color);
    }

    .admin-sidebar .nav-link { color: #fff; padding: 12px 18px; font-weight: 500; border-left: 4px solid transparent; }
    .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.12); border-left-color: rgba(255,255,255,0.12); font-weight: 600; }
    .admin-sidebar .nav-item.mt-auto { margin-top: auto; }

    /* ================= CONTENT ================= */
    .admin-content { padding-top: calc(var(--admin-topbar-height) + 8px); }
    @media (min-width: 992px) { .admin-content { margin-left: var(--admin-sidebar-width); width: calc(100% - var(--admin-sidebar-width)); padding-right: 1.25rem; } }

    /* ================= MOBILE MENU ================= */
    .mobile-menu { background: var(--admin-green-dark); }
    .mobile-menu .nav-link { color: #fff; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.15); }
    .mobile-menu .nav-link.active { background: rgba(255,255,255,0.2); font-weight: 600; }
</style>

<!-- ================= DESKTOP SIDEBAR ================= -->
<div class="admin-sidebar d-none d-lg-flex flex-column">
    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
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
            <a class="nav-link text-warning" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<!-- ================= HEADER BAR ================= -->
<nav class="navbar admin-header admin-topbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <!-- Brand for mobile -->
        <a class="navbar-brand text-white fw-bold d-lg-none" href="dashboard.php">Jersey E-Mart</a>
        <!-- Brand for desktop -->
        <a class="navbar-brand text-white fw-bold d-none d-lg-block" href="dashboard.php">Jersey E-Mart</a>

        <!-- Hamburger (mobile / tablet) -->
        <button class="navbar-toggler shadow-none text-white d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminMobileMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Desktop logout -->
        <div class="ms-auto d-none d-lg-flex">
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- ================= MOBILE / TABLET MENU ================= -->
<div class="collapse d-lg-none" id="adminMobileMenu">
    <div class="mobile-menu">
        <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
        <a class="nav-link <?= $currentPage == 'orders.php' ? 'active' : '' ?>" href="orders.php">Orders</a>
        <a class="nav-link <?= $currentPage == 'users.php' ? 'active' : '' ?>" href="users.php">Users</a>
        <a class="nav-link <?= $currentPage == 'jersies.php' ? 'active' : '' ?>" href="jersies.php">Jerseys</a>
        <a class="nav-link <?= $currentPage == 'carousel.php' ? 'active' : '' ?>" href="carousel.php">Carousel</a>
        <a class="nav-link text-warning" href="logout.php">Logout</a>
    </div>
</div>

<!-- ================= CONTENT AREA ================= -->
<div class="admin-content"></div>