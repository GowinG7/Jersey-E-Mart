$(document).ready(function () {
  // Live validation
  $("#full_name").on("blur", function () {
    var fullName = $(this).val();
    if (!/^[A-Za-z]+( [A-Za-z]+)*$/.test(fullName)) {
      $("#full_name_error")
        .text(
          "Name should only contain letters, and spaces are allowed between words but not at the start."
        )
        .show();
    } else {
      $("#full_name_error").hide();
    }
  });

  $("#username").on("blur", function () {
    var username = $(this).val();
    if (!/^[a-zA-Z0-9_@]+$/.test(username)) {
      $("#username_error")
        .text(
          "Username can only contain letters, numbers, underscores, and the @ symbol."
        )
        .show();
    } else {
      $("#username_error").hide();
    }
  });

  $("#email").on("blur", function () {
    var email = $(this).val();
    if (!/^[a-z0-9.]+@(gmail|yahoo|outlook)\.com$/.test(email)) {
      $("#email_error")
        .text(
          "Email must contain only letters (a-z), numbers (0-9), and periods (.) and must end with @gmail.com, @yahoo.com, or @outlook.com."
        )
        .show();
    } else {
      $("#email_error").hide();
    }
  });

  $("#pass").on("blur", function () {
    var pass = $(this).val();
    if (pass.length < 8) {
      $("#pass_error")
        .text("Password must be at least 8 characters long.")
        .show();
    } else {
      $("#pass_error").hide();
    }
  });

  $("#answer").on("blur", function () {
    var answer = $(this).val().trim();
    if (answer === "") {
      $("#answer_error").text("Security answer cannot be empty.").show();
    } else {
      $("#answer_error").hide();
    }
  });

  $("#cpass").on("blur", function () {
    var cpass = $(this).val();
    if (cpass !== $("#pass").val()) {
      $("#cpass_error").text("Passwords do not match.").show();
    } else {
      $("#cpass_error").hide();
    }
  });

  // Auto-hide success and error messages
  if ($("#successMessage").length) {
    setTimeout(() => $("#successMessage").fadeOut("slow"), 2000);
  }
  if ($("#errorMessage").length) {
    setTimeout(() => $("#errorMessage").fadeOut("slow"), 2000);
  }
});
