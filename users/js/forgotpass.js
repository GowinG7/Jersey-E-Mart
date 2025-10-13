$(document).ready(function () {
  // Username validation (can be normal username or email)
  $("#uname").on("blur", function () {
    var uname = $(this).val().trim();
    if (
      !/^[a-zA-Z0-9_@]+$/.test(uname) && // username pattern
      !/^[a-z0-9.]+@(gmail|yahoo|outlook)\.com$/.test(uname) // email pattern
    ) {
      $("#uname_error").text("Enter a valid username or email.").show();
    } else {
      $("#uname_error").hide();
    }
  });

  // New password validation
  $("#new_password").on("blur", function () {
    var pwd = $(this).val().trim();
    if (pwd.length < 8) {
      $("#password_error").text("Password must be at least 8 characters long.").show();
    } else {
      $("#password_error").hide();
    }
  });

  // Confirm password validation
  $("#confirm_password").on("blur", function () {
    var pwd = $("#new_password").val().trim();
    var cpass = $(this).val().trim();
    if (cpass !== pwd) {
      $("#cpass_error").text("Passwords do not match.").show();
    } else {
      $("#cpass_error").hide();
    }
  });

 
});
