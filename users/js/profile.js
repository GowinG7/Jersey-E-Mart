function openModal() {
  document.getElementById("passwordModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("passwordModal").style.display = "none";
}

// Helper to show messages in the password modal. Success messages auto-dismiss after 4s.
function showModalMsg(type, text) {
  const el = document.getElementById("modalMsg");
  // Reset styles
  el.style.transition = "";
  el.style.opacity = "";
  el.style.maxHeight = "";
  el.style.margin = "";

  el.className = "msg " + (type === "success" ? "success" : "error");
  el.innerText = text;

  if (type === "success") {
    // Auto-dismiss after 4 seconds with a short fade/collapse
    setTimeout(() => {
      el.style.transition =
        "opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease";
      el.style.opacity = "0";
      el.style.maxHeight = "0";
      el.style.margin = "0";
      setTimeout(() => {
        el.className = "msg";
        el.innerText = "";
        el.style.transition = "";
        el.style.opacity = "";
        el.style.maxHeight = "";
        el.style.margin = "";
      }, 500);
    }, 4000);
  }
}

document
  .getElementById("changePasswordForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("change_password_modal.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((msg) => {
        if (msg.trim() === "success") {
          showModalMsg("success", "Password changed successfully.");
          this.reset();
        } else {
          showModalMsg("error", msg);
        }
      });
  });

// Auto-dismiss profile update success messages after 4 seconds
(function () {
  try {
    const profileSuccessMsgs = document.querySelectorAll(
      ".profile-card .msg.success"
    );
    profileSuccessMsgs.forEach((el) => {
      if (el.textContent.trim()) {
        setTimeout(() => {
          el.style.transition =
            "opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease";
          el.style.opacity = "0";
          el.style.maxHeight = "0";
          el.style.margin = "0";
          setTimeout(() => el.remove(), 500);
        }, 4000);
      }
    });
  } catch (e) {
    // fail silently
    console.warn("Auto-dismiss script error", e);
  }
})();

// Auto-dismiss error messages after 6 seconds
(function () {
  const profileErrorMsgs = document.querySelectorAll(
    ".profile-card .msg.error"
  );
  profileErrorMsgs.forEach((el) => {
    if (el.textContent.trim()) {
      setTimeout(() => {
        el.style.transition =
          "opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease";
        el.style.opacity = "0";
        el.style.maxHeight = "0";
        el.style.margin = "0";
        setTimeout(() => el.remove(), 500);
      }, 6000);
    }
  });
})();

// Confirm before profile update
(function () {
  const profileForm = document.getElementById("profileForm");
  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      const confirmed = confirm(
        "Are you sure you want to change your profile details? This will change your account information."
      );
      if (!confirmed) {
        e.preventDefault();
      }
    });
  }
})();
