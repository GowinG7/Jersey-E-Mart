<?php
include_once "../shared/commonlinks.php";
?>

<style>
  #nav-bar .navbar-nav .nav-link:hover {
    color: #0808107d;
  }
</style>

<nav id="nav-bar" class="navbar navbar-expand-lg shadow-sm" style="background-color: rgba(71, 145, 137, 1);">
  <div class="container-fluid px-lg-5">
    <img src="images/logo.png" alt="Logo" class="ms-2 rounded-circle"
      style="height:50px; width:100px;margin-right:20px;">

    <!-- Mobile Toggle -->
    <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-semibold">
        <li class="nav-item"><a class="nav-link me-3 active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="jersey.php">Jerseys</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">Kits</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="Contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">About</a></li>
      </ul>

      <!-- Right-Side Buttons -->
      <!-- Right-Side Buttons -->
      <div class="d-flex align-items-center gap-3">

        <?php
        if (isset($_SESSION['user_id'])) {
          ?>

          <!-- Profile Badge -->
          <div class="d-flex align-items-center text-white fw-semibold px-3 py-1 rounded-pill"
            style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);">
            <i class="bi bi-person-circle me-2 fs-5"></i>
            <span style="font-size: 0.95rem;"><?php echo htmlspecialchars($_SESSION['nam']); ?></span>
          </div>

          <!-- Logout Button -->
          <a href="loginsignup/logout.php" class="btn btn-light fw-semibold px-3 py-1" style="border-radius: 50px;">
            Logout
          </a>

          <?php
        } else {
          ?>

          <a href="loginsignup/login.php" class="btn btn-light fw-semibold px-3 py-1"
            style="border-radius: 50px;">Login</a>

          <a href="loginsignup/signup.php" class="btn btn-dark fw-semibold px-3 py-1"
            style="border-radius: 50px;">Register</a>

          <?php
        }
        ?>

      </div>

    </div>
  </div>
</nav>