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

// AJAX Profile Update - No page reload
document.getElementById("profileForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append("update_profile", "1");

  const msgEl = document.getElementById("profileMsg");
  msgEl.style.display = "none";

  fetch("profile_change_pass.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.text())
    .then((msg) => {
      if (msg.trim() === "success") {
        msgEl.className = "msg success";
        msgEl.textContent = "Profile updated successfully.";
        msgEl.style.display = "block";

        // Auto-dismiss after 4 seconds
        setTimeout(() => {
          msgEl.style.transition =
            "opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease";
          msgEl.style.opacity = "0";
          msgEl.style.maxHeight = "0";
          msgEl.style.margin = "0";
          setTimeout(() => {
            msgEl.style.display = "none";
            msgEl.style.transition = "";
            msgEl.style.opacity = "";
            msgEl.style.maxHeight = "";
            msgEl.style.margin = "";
          }, 500);
        }, 4000);
      } else if (msg.trim() === "No changes detected") {
        msgEl.className = "msg";
        msgEl.style.color = "#856404";
        msgEl.style.backgroundColor = "#fff3cd";
        msgEl.style.borderColor = "#ffeeba";
        msgEl.textContent = "No changes made to update.";
        msgEl.style.display = "block";

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
          msgEl.style.transition = "opacity 0.45s ease";
          msgEl.style.opacity = "0";
          setTimeout(() => {
            msgEl.style.display = "none";
            msgEl.style.transition = "";
            msgEl.style.opacity = "";
          }, 500);
        }, 3000);
      } else {
        msgEl.className = "msg error";
        msgEl.textContent = msg;
        msgEl.style.display = "block";
      }
    })
    .catch((err) => {
      msgEl.className = "msg error";
      msgEl.textContent = "Failed to update profile. Please try again.";
      msgEl.style.display = "block";
    });
});

// Order modal logic
document.addEventListener("click", function (e) {
  if (e.target && e.target.matches(".view-order")) {
    e.preventDefault();
    const id = e.target.dataset.order;
    const body = new URLSearchParams();
    body.append("action", "get_order_details");
    body.append("order_id", id);
    fetch("profile_change_pass.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body.toString(),
    })
      .then((res) => {
        if (!res.ok) throw new Error("Could not fetch order");
        return res.text();
      })
      .then((html) => {
        document.getElementById("orderModalBody").innerHTML = html;
        document.getElementById("orderModal").style.display = "flex";
      })
      .catch((err) => {
        document.getElementById("orderModalBody").innerHTML =
          '<div class="msg error">' + err.message + "</div>";
        document.getElementById("orderModal").style.display = "flex";
      });
  }
});

function closeOrderModal() {
  document.getElementById("orderModal").style.display = "none";
}

// close on overlay click
document.getElementById("orderModal").addEventListener("click", function (e) {
  if (e.target === this) closeOrderModal();
});

// AJAX Date Search - No page reload
document.querySelector(".search-form").addEventListener("submit", function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  const params = new URLSearchParams(formData);

  fetch("profile_change_pass.php?action=get_orders&" + params.toString())
    .then((res) => res.json())
    .then((data) => {
      // Update summary stats
      document.querySelectorAll(".profile-card")[1].innerHTML =
        '<h3>My Orders</h3><form method="get" class="search-form" style="margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;"><label style="display:flex;gap:6px;align-items:center">Start Date: <input type="date" name="start_date" value="' +
        (formData.get("start_date") || "") +
        '"></label><label style="display:flex;gap:6px;align-items:center">End Date: <input type="date" name="end_date" value="' +
        (formData.get("end_date") || "") +
        '"></label><div style="margin-left:auto"><button type="submit" class="btn btn-primary">Search</button><a href="profile.php" class="btn btn-secondary" style="margin-left:8px;">Reset</a></div></form><div style="margin-bottom:12px;padding:10px;border-radius:8px;background:#f4f9f8;border:1px solid #e6f3f1;display:flex;gap:12px;align-items:center;"><div><strong>Total orders:</strong> ' +
        data.totalOrders +
        "</div><div><strong>Total amount:</strong> Rs. " +
        data.totalAmount +
        '</div></div><div style="overflow-x:auto"><table class="table table-bordered table-striped"><thead class="table-dark"><tr><th>S.N.</th><th>Order No</th><th>Order Items</th><th>Payment Status</th><th>Order Status</th><th>Date</th></tr></thead><tbody>' +
        data.tableHtml +
        "</tbody></table></div>";

      // Update URL without reload
      const startDate = formData.get("start_date");
      const endDate = formData.get("end_date");
      let newUrl = "profile.php";
      if (startDate || endDate) {
        newUrl += "?";
        if (startDate) newUrl += "start_date=" + startDate + "&";
        if (endDate) newUrl += "end_date=" + endDate;
        newUrl = newUrl.replace(/&$/, "");
      }
      history.pushState({}, "", newUrl);
    })
    .catch((err) => {
      console.error("Search failed:", err);
    });
});
