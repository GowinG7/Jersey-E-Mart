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

  $("#phone").on("blur", function () {
    var phone = $(this).val().trim();
    if (phone.length < 10) {
      $("#phone_error").text("Phone number must be at least 10 digits").show();
    } else {
      $("#phone_error").hide();
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

  // $("#pass").on("blur", function () {
  //   var pass = $(this).val();
  //   if (pass.length < 8) {
  //     $("#pass_error")
  //       .text("Password must be at least 8 characters long.")
  //       .show();
  //   } else {
  //     $("#pass_error").hide();
  //   }
  // });

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

  $("#cpass").on("blur", function () {
    var cpass = $(this).val();
    if (cpass !== $("#pass").val()) {
      $("#cpass_error").text("Passwords do not match.").show();
    } else {
      $("#cpass_error").hide();
    }
  });

});
