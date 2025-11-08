<?php
include_once "../shared/commonlinks.php";
?>

<!-- navbar styles: change nav <li> links to white on hover/focus -->
<style>
  #nav-bar .navbar-nav .nav-link:hover {
    color: #0808107d;
  }
</style>

<nav id="nav-bar" class="navbar navbar-expand-lg shadow-sm" style="background-color: rgba(71, 145, 137, 1);">
  <div class="container-fluid px-lg-5">
    <!-- Brand and Logo -->
    <a  class="navbar-brand ps-3 fw-bold fs-3 rounded shadow-lg d-flex align-items-center" style="background-color: rgba(59, 150, 141, 1)">
      Jersey E-Mart 
      <img src="images/logo.png" alt="Logo" class="ms-2 rounded-circle" style="height:50px; width:50px;">
    </a>
    <!-- Mobile Toggle -->
    <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-semibold">
        <li class="nav-item"><a class="nav-link me-3 active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">Jerseys</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">Kits</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">Contact</a></li>
        <li class="nav-item"><a class="nav-link me-3" href="#">About</a></li>
      </ul>
      <!-- Right-Side Buttons -->
      <div class="d-flex">
        <a href="loginsignup/login.php" class="btn btn-light shadow me-lg-3 me-2 mt-1">Login</a>
        <a href="loginsignup/signup.php" class="btn btn-dark shadow mt-1">Register</a>
      </div>
    </div>
  </div>
</nav>