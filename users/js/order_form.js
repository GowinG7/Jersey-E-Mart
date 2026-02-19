$(document).ready(function () {
  // ===============================
  // REGEX PATTERNS
  // ===============================
  var namePattern = /^[A-Za-z]+( [A-Za-z]+)*$/; // letters only, spaces allowed between words
  var phonePattern = /^[0-9]{10}$/; // exactly 10 digits
  var addressPattern = /^(?=.*[A-Za-z]).{5,}$/; // min 5 chars, at least 1 letter

  // ===============================
  // BLUR VALIDATIONS
  // ===============================

  // Full Name
  $("#name").on("blur", function () {
    var name = $(this).val().trim();
    if (!namePattern.test(name)) {
      $("#name_error")
        .text(
          "Name must contain only letters and spaces (no leading/trailing spaces).",
        )
        .show();
    } else {
      $("#name_error").hide();
    }
  });

  // Delivery Address
  $("#location").on("blur", function () {
    var location = $(this).val().trim();
    if (!addressPattern.test(location)) {
      $("#location_error")
        .text(
          "Enter a valid delivery address (minimum 5 characters, must include letters).",
        )
        .show();
    } else {
      $("#location_error").hide();
    }
  });

  // Contact Phone
  $("#contact").on("blur", function () {
    var contact = $(this).val().trim();
    if (!phonePattern.test(contact)) {
      $("#contact_error")
        .text("Phone number must be exactly 10 digits.")
        .show();
    } else {
      $("#contact_error").hide();
    }
  });

  // ===============================
  // FORM SUBMIT VALIDATION
  // ===============================
  $("#orderForm").on("submit", function (e) {
    var valid = true;

    var name = $("#name").val().trim();
    var location = $("#location").val().trim();
    var contact = $("#contact").val().trim();
    var payment = $("input[name='payment_option']:checked").val();

    // Full Name Check
    if (!namePattern.test(name)) {
      $("#name_error").text("Invalid full name.").show();
      valid = false;
    } else {
      $("#name_error").hide();
    }

    // Address Check
    if (!addressPattern.test(location)) {
      $("#location_error").text("Invalid delivery address.").show();
      valid = false;
    } else {
      $("#location_error").hide();
    }

    // Phone Check
    if (!phonePattern.test(contact)) {
      $("#contact_error")
        .text("Invalid phone number (must be exactly 10 digits).")
        .show();
      valid = false;
    } else {
      $("#contact_error").hide();
    }

    // Payment Check
    if (!payment) {
      $("#payment_error").text("Please select a payment option.").show();
      valid = false;
    } else {
      $("#payment_error").hide();
    }

    // Stop submission if invalid
    if (!valid) {
      e.preventDefault();
    }
  });
});
