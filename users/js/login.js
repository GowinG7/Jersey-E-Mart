$("#loginForm").on("submit", function (e) {
  var username = $("#username").val();
  var password = $("#password").val();
  var isValid = true;

  // if username name or password is empty
  if (!username || !password) {
    $("#errorMessage").text("Username and password are required.").show();
    isValid = false;
  }

  if (password.length < 8) {
    $("#errorMessage").text("Password must be at least 8 characters.").show();
    isValid = false;
  }

  if (!isValid) {
    e.preventDefault(); // Stop form submission
    setTimeout(() => $("#errorMessage").fadeOut("slow"), 2000);
  }
});