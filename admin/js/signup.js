$(document).ready(function () {
  // Username validation
  $("#uname").on("blur", function () {
    var uname = $(this).val();
    if (!/^[a-zA-Z0-9_@]+$/.test(uname)) {
      $("#uname_error")
        .text(
          "Username can only contain letters, numbers, underscores, and the @ symbol."
        )
        .show();
    } else {
      $("#uname_error").hide();
    }
  });

  // Password validation
  $("#pass").on("blur", function () {
    var pass = $(this).val();
    //multiple errors dekhauna xa so array used grney
    var errors = [];

    if (pass.length < 8) {
      errors.push("Password must be at least 8 characters long.");
    }
    if (!/[A-Z]/.test(pass)) {
      errors.push("Password must contain at least one uppercase letter.");
    }
    if (!/[a-z]/.test(pass)) {
      errors.push("Password must contain at least one lowercase letter.");
    }
    if (!/[0-9]/.test(pass)) {
      errors.push("Password must contain at least one digit.");
    }
    if (!/[!@#$%^&*(),.?\":{}|<>]/.test(pass)) {
      errors.push("Password must contain at least one special character.");
    }

    if (errors.length > 0) {
      $("#pass_error").html(errors.join("<br>")).show();
    } else {
      $("#pass_error").hide();
    }
  });
});
