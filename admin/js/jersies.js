// Its purpose is to store the current product ID when you open the Size Modal,
// so that adding, updating, or deleting sizes knows which product it belongs to.
let PROD = 0;

//  FORM VALIDATION
function validateJerseyForm(form) {
  const name = form.querySelector('input[name="j_name"]').value.trim();
  const category = form.querySelector('select[name="category"]').value;
  const quality = form.querySelector('select[name="quality"]').value;
  const price = form.querySelector('input[name="price"]').value.trim();
  const discount = form.querySelector('input[name="discount"]').value.trim();
  const imageInput = form.querySelector('input[name="image"]');

  if (name.length < 3) {
    alert("Jersey name must be at least 3 characters.");
    return false;
  }
  if (!category) {
    alert("Please select a category.");
    return false;
  }
  if (!quality) {
    alert("Please select jersey quality.");
    return false;
  }
  if (!price || isNaN(price) || Number(price) <= 0) {
    alert("Please enter a valid price.");
    return false;
  }
  if (
    discount &&
    (isNaN(discount) || Number(discount) < 0 || Number(discount) > 100)
  ) {
    alert("Discount must be between 0 and 100.");
    return false;
  }

  if (imageInput && imageInput.files.length > 0) {
    const file = imageInput.files[0];
    const allowed = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    if (!allowed.includes(file.type)) {
      alert("Invalid image type. Only JPG, PNG, GIF, WEBP allowed.");
      return false;
    }
    if (file.size > 2 * 1024 * 1024) {
      alert("Image size must be less than 2MB.");
      return false;
    }
  }
  return true;
}

// Attach validation to both Add/Edit forms
document.querySelectorAll("#addModal form, #editModal form").forEach((f) => {
  f.onsubmit = () => validateJerseyForm(f);
});

//  SEARCH FUNCTIONALITY
const searchBox = document.getElementById("searchBox");
const tableBody = document.querySelector("table tbody");
searchBox.addEventListener("keyup", () => {
  const filter = searchBox.value.toLowerCase();
  tableBody.querySelectorAll("tr").forEach((row) => {
    const name = row.cells[1].textContent.toLowerCase();
    const category = row.cells[2].textContent.toLowerCase();
    const country = row.cells[3].textContent.toLowerCase();
    row.style.display =
      name.includes(filter) ||
      category.includes(filter) ||
      country.includes(filter)
        ? ""
        : "none";
  });
});

//  SIZE MODAL FUNCTIONS
function openSizeModal(id, name) {
  PROD = id;
  document.getElementById("sizeTitle").innerHTML = "Sizes — " + name;
  document.getElementById("sizesList").innerHTML =
    "<div class='text-muted'>Loading sizes...</div>";

  fetch("jersies.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "fetch_sizes=1&pid=" + id,
  })
    .then((r) => (r.ok ? r.text() : Promise.reject("Failed to fetch")))
    .then((d) => (document.getElementById("sizesList").innerHTML = d))
    .catch((err) => {
      console.error(err);
      document.getElementById("sizesList").innerHTML =
        "<span class='text-danger'>Failed to load sizes</span>";
    })
    .finally(() =>
      new bootstrap.Modal(document.getElementById("sizeModal")).show()
    );
}

function saveSize() {
  const sizeSelect = document.getElementById("sizeSelect");
  const stockInput = document.getElementById("stockInput");
  const size = sizeSelect.value.trim();
  const stock = stockInput.value.trim();

  if (!size) {
    alert("Select a size.");
    return;
  }
  if (!stock || isNaN(stock) || Number(stock) < 0) {
    alert("Enter valid stock.");
    return;
  }

  fetch("jersies.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `add_size=1&product_id=${PROD}&size=${size}&stock=${stock}`,
  }).then(() => location.reload());
}

function updateStock(id, val) {
  if (!val || isNaN(val) || Number(val) < 0) {
    alert("Stock must be a positive number.");
    return;
  }
  fetch("jersies.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `update_stock=1&id=${id}&stock=${val}`,
  })
    .then((r) => r.text())
    .then((res) => console.log("Stock updated:", res))
    .catch((err) => console.error(err));
}

function deleteSize(id) {
  if (!confirm("Remove this size?")) return;
  fetch("jersies.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `del_size=${id}`,
  }).then(() => location.reload());
}

//  EDIT PRODUCT MODAL
function openEditModal(id) {
  fetch(location.pathname, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `fetch_product=1&id=${id}`,
  })
    .then(async (r) => {
      if (!r.ok) throw new Error("Failed to fetch product");
      const ct = r.headers.get("Content-Type") || "";
      return ct.includes("application/json")
        ? r.json()
        : Promise.reject("Invalid response");
    })
    .then((d) => {
      if (!d || !d.id) throw new Error("Product data missing");
      document.getElementById("edit_id").value = d.id;
      document.getElementById("edit_name").value = d.j_name;
      document.getElementById("edit_country").value = d.country;
      document.getElementById("edit_price").value = d.price;
      document.getElementById("edit_discount").value = d.discount;
      document.getElementById("edit_description").value = d.description;

      // Category select
      const cat = document.getElementById("edit_category");
      if (d.category && ![...cat.options].some((o) => o.value === d.category)) {
        cat.add(new Option(d.category, d.category, true, true), cat.options[0]);
      }
      cat.value = d.category || "";

      // Quality select
      const qual = document.getElementById("edit_quality");
      if (d.quality && ![...qual.options].some((o) => o.value === d.quality)) {
        qual.add(new Option(d.quality, d.quality, true, true), qual.options[0]);
      }
      qual.value = d.quality || "";

      new bootstrap.Modal(document.getElementById("editModal")).show();
    })
    .catch((err) => {
      console.error(err);
      alert("Failed to open edit modal.");
    });
}

//  BIND MANAGE BUTTONS
document.querySelectorAll(".btn-manage").forEach((btn) => {
  btn.addEventListener("click", () =>
    openSizeModal(btn.dataset.pid, btn.dataset.name)
  );
});
