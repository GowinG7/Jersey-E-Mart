(function () {
  const STORAGE_KEY = "jem_guest_cart";

  function safeParse(json) {
    try {
      const parsed = JSON.parse(json);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function normalizeNumber(value, fallback) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
  }

  function getCart() {
    return safeParse(localStorage.getItem(STORAGE_KEY));
  }

  function saveCart(items) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    document.dispatchEvent(new CustomEvent("guest-cart-updated", { detail: { items: items } }));
  }

  function clearCart() {
    localStorage.removeItem(STORAGE_KEY);
    document.dispatchEvent(new CustomEvent("guest-cart-updated", { detail: { items: [] } }));
  }

  function getItemKey(item) {
    return [
      normalizeNumber(item.product_id, 0),
      (item.size || "").trim().toUpperCase(),
      (item.quality || "").trim(),
      (item.print_name || "").trim(),
      (item.print_number || "").toString().trim()
    ].join("||");
  }

  function addItem(rawItem) {
    const item = {
      product_id: normalizeNumber(rawItem.product_id, 0),
      pname: (rawItem.pname || "").trim(),
      category: (rawItem.category || "").trim(),
      image: (rawItem.image || "").trim(),
      base_price: normalizeNumber(rawItem.base_price, 0),
      discount: normalizeNumber(rawItem.discount, 0),
      price_after_discount: normalizeNumber(rawItem.price_after_discount, 0),
      size: (rawItem.size || "").trim().toUpperCase(),
      quality: (rawItem.quality || "").trim(),
      print_name: (rawItem.print_name || "").trim(),
      print_number: (rawItem.print_number || "").toString().trim(),
      quantity: Math.max(1, parseInt(rawItem.quantity, 10) || 1),
      max_stock: Math.max(0, parseInt(rawItem.max_stock, 10) || 0)
    };

    if (!item.product_id || !item.size) {
      return false;
    }

    const items = getCart();
    const key = getItemKey(item);
    const existingIndex = items.findIndex(function (it) {
      return getItemKey(it) === key;
    });

    if (existingIndex >= 0) {
      const current = items[existingIndex];
      const nextQty = (parseInt(current.quantity, 10) || 1) + item.quantity;
      const cap = item.max_stock > 0 ? item.max_stock : nextQty;
      items[existingIndex].quantity = Math.min(nextQty, cap);
      items[existingIndex].max_stock = item.max_stock;
      items[existingIndex].price_after_discount = item.price_after_discount;
      items[existingIndex].base_price = item.base_price;
      items[existingIndex].discount = item.discount;
      items[existingIndex].pname = item.pname;
      items[existingIndex].category = item.category;
      items[existingIndex].image = item.image;
    } else {
      if (item.max_stock > 0) {
        item.quantity = Math.min(item.quantity, item.max_stock);
      }
      items.push(item);
    }

    saveCart(items);
    return true;
  }

  function removeItem(index) {
    const items = getCart();
    if (index < 0 || index >= items.length) {
      return;
    }
    items.splice(index, 1);
    saveCart(items);
  }

  function updateQuantity(index, quantity) {
    const items = getCart();
    if (index < 0 || index >= items.length) {
      return;
    }

    const requested = Math.max(1, parseInt(quantity, 10) || 1);
    const maxStock = Math.max(0, parseInt(items[index].max_stock, 10) || 0);
    items[index].quantity = maxStock > 0 ? Math.min(requested, maxStock) : requested;
    saveCart(items);
  }

  function getCount() {
    return getCart().reduce(function (sum, item) {
      return sum + (parseInt(item.quantity, 10) || 0);
    }, 0);
  }

  window.guestCart = {
    storageKey: STORAGE_KEY,
    getCart: getCart,
    saveCart: saveCart,
    clearCart: clearCart,
    addItem: addItem,
    removeItem: removeItem,
    updateQuantity: updateQuantity,
    getCount: getCount
  };
})();
