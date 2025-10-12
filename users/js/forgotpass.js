 $(document).ready(function () {
    function isValidUsername(uname) {
      return /^[a-zA-Z0-9_@]+$/.test(uname) || /^[a-z0-9.]+@(gmail\.com|yahoo\.com|outlook\.com)$/i.test(uname);
    }

    function isValidPassword(pwd) {
      return pwd.length >= 8;
    }

    function showError(input, message, className) {
      $("." + className).remove();
      input.after('<span class="' + className + '" style="color:red;">' + message + '</span>');
    }

    function clearError(className) {
      $("." + className).remove();
    }

    $("#uname").on("input", function () {
      let uname = $(this).val().trim();
      if (uname && !isValidUsername(uname)) {
        showError($(this), "Enter a valid username or email.", "uname-error");
      } else {
        clearError("uname-error");
      }
    });

    $("#new_password").on("input", function () {
      let pwd = $(this).val().trim();
      if (!isValidPassword(pwd)) {
        showError($(this), "Password must be at least 8 characters.", "password-error");
      } else {
        clearError("password-error");
      }
      $("#confirm_password").trigger("input"); // recheck match
    });

    $("#confirm_password").on("input", function () {
      let pwd = $("#new_password").val().trim();
      let confirm = $(this).val().trim();
      if (confirm && pwd !== confirm) {
        showError($(this), "Passwords do not match.", "confirm-password-error");
      } else {
        clearError("confirm-password-error");
      }
    });

    $("form").on("submit", function (e) {
      $("#uname, #new_password, #confirm_password").trigger("input");
      if ($(".uname-error, .password-error, .confirm-password-error").length > 0) {
        e.preventDefault();
      }
    });

    setTimeout(() => $(".message").fadeOut("slow"), 3000);
 });