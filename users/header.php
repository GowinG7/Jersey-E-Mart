<?php
require_once "../shared/dbconnect.php";
include_once "../shared/commonlinks.php";

// fetch cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $res = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = $user_id");
  if ($row = mysqli_fetch_assoc($res)) {
    $cart_count = intval($row['total_items']);
  }
}
?>

<style>
  /* Navbar links */
  #nav-bar .navbar-nav .nav-link {
    color: white;
    font-weight: 600;
  }

  #nav-bar .navbar-nav .nav-link:hover,
  #nav-bar .navbar-nav .nav-link.active {
    color: #312f26ff;
    /* highlight on hover */
  }

  /* Right-side pill buttons (Cart, Profile, Login, Register, Logout) */
  .navbar-pill {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(4px);
    border-radius: 50px;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    padding: 0.35rem 0.8rem;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
  }

  .navbar-pill i {
    margin-right: 0.6rem;
    font-size: 1.1rem;
  }

  /* Hover effect like logout button */
  .navbar-pill:hover {
    background: white !important;
    color: black !important;
  }

  /* Cart badge */
  .cart-badge {
    font-size: 0.65rem;
    padding: 2px 5px;
    border-radius: 50%;
    background: white;
    color: black;
    line-height: 1;
    position: absolute;
    top: -5px;
    right: -10px;
  }
</style>

<nav id="nav-bar" class="navbar navbar-expand-lg shadow-sm" style="background-color: rgba(71, 145, 137, 1)">
  <div class="container-fluid px-lg-5">
    <img src="images/logo.png" alt="Logo" class="ms-2 rounded-circle"
      style="height:50px; width:100px; margin-right:20px;">

    <!-- Mobile Toggle -->
    <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-semibold">
        <li class="nav-item"><a class="nav-link me-3" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="jersey.php">Jerseys</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">Kits</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="Contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">About</a></li>
      </ul>

      <!-- Right-Side Buttons -->
      <div class="d-flex align-items-center gap-2 position-relative">

        <?php if (isset($_SESSION['user_id'])): ?>

          <!-- Cart Icon -->
          <a href="displaycart.php" class="navbar-pill position-relative">
            <i class="bi bi-cart"></i>
            <span>Cart</span>
            <?php if ($cart_count > 0): ?>
              <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </a>

          <!-- Profile Badge -->
          <a href="#" class="navbar-pill">
            <i class="bi bi-person-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['nam']); ?></span>
          </a>

          <!-- Logout Button -->
          <a href="loginsignup/logout.php" class="navbar-pill">Logout</a>

        <?php else: ?>

          <a href="loginsignup/login.php" class="navbar-pill">Login</a>
          <a href="loginsignup/signup.php" class="navbar-pill">Register</a>

        <?php endif; ?>

      </div>
    </div>
  </div>
</nav>