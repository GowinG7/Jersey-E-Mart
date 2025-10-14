$(document).ready(function () {
  // New password validation
  $("#new_password").on("blur", function () {
    var new_password = $(this).val();
    //multiple errors dekhauna xa so array used grney
    var errors = [];

    if (new_password.length < 8) {
      errors.push("Password must be at least 8 characters long.");
    }
    if (!/[A-Z]/.test(new_password))
      errors.push("Password must contain at least one uppercase letter");

    if (!/[a-z]/.test(new_password))
      errors.push("Password must contain at least one lowercase letter");

    if (!/[0-9]/.test(new_password))
      errors.push("Password must contain at least one digit");

    if (!/\W/.test(new_password))
      errors.push("Password must contain atleast one special character");

    if (errors.length > 0) {
      $("#pass_error").html(errors.join("<br>")).show();
    } else {
      $("#pass_error").hide();
    }
  });

  // Confirm password validation
  $("#confirm_password").on("blur", function () {
    var confirm_password = $(this).val();
    var new_password = $("#new_password").val(); // re-get value here

    if (confirm_password !== new_password) {
      $("#cpass_error").text("Passwords do not match.").show();
    } else {
      $("#cpass_error").hide();
    }
  });
});
