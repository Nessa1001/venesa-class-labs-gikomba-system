(() => {
    const formatMoney = (value) => `KSh ${Number(value || 0).toFixed(2)}`;

    const showToast = (message, type = "success") => {
        const toast = document.createElement("div");
        toast.className = `app-toast app-toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add("show");
        });

        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 250);
        }, 1800);
    };

    const searchInput = document.getElementById("liveSearchInput");
    const resultsBox = document.getElementById("liveSearchResults");
    let debounceTimer;

    const hideResults = () => {
        if (!resultsBox) {
            return;
        }
        resultsBox.classList.add("d-none");
        resultsBox.innerHTML = "";
    };

    const renderResults = (items) => {
        if (!resultsBox) {
            return;
        }

        if (!items.length) {
            resultsBox.innerHTML = '<div class="p-3 text-muted">No matching items</div>';
            resultsBox.classList.remove("d-none");
            return;
        }

        resultsBox.innerHTML = items.map((item) => {
            const amount = Number(item.discount_price || item.price || 0).toFixed(2);
            return `
                <a class="live-search-item" href="index.php?page=product&id=${item.id}">
                    <span>${item.name}</span>
                    <strong>KES ${amount}</strong>
                </a>
            `;
        }).join("");

        resultsBox.classList.remove("d-none");
    };

    if (searchInput && resultsBox) {
        searchInput.addEventListener("input", () => {
            const term = searchInput.value.trim();

            clearTimeout(debounceTimer);
            if (term.length < 2) {
                hideResults();
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`public/api/live-search.php?q=${encodeURIComponent(term)}`);
                    const data = await response.json();
                    renderResults(Array.isArray(data) ? data : []);
                } catch (error) {
                    hideResults();
                }
            }, 250);
        });

        document.addEventListener("click", (event) => {
            if (!resultsBox.contains(event.target) && event.target !== searchInput) {
                hideResults();
            }
        });
    }

    const paymentMethod = document.getElementById("paymentMethod");
    const mpesaPanel = document.getElementById("mpesaPanel");
    const cardPanel = document.getElementById("cardPanel");

    const syncPanels = () => {
        if (!paymentMethod || !mpesaPanel || !cardPanel) {
            return;
        }
        if (paymentMethod.value === "mpesa") {
            mpesaPanel.classList.remove("d-none");
            cardPanel.classList.add("d-none");
        } else {
            cardPanel.classList.remove("d-none");
            mpesaPanel.classList.add("d-none");
        }
    };

    if (paymentMethod) {
        paymentMethod.addEventListener("change", syncPanels);
        syncPanels();
    }

    const shopGrid = document.getElementById("shopProductsGrid");
    const productSearchInput = document.getElementById("productSearchInput");
    const categoryFilterSelect = document.getElementById("categoryFilterSelect");
    const priceFilterSelect = document.getElementById("priceFilterSelect");
    const clearFiltersBtn = document.getElementById("clearFiltersBtn");
    const shopNoResults = document.getElementById("shopNoResults");

    const applyShopFilters = () => {
        if (!shopGrid) {
            return;
        }

        const searchTerm = (productSearchInput?.value || "").trim().toLowerCase();
        const category = categoryFilterSelect?.value || "all";
        const priceRange = priceFilterSelect?.value || "all";

        let visible = 0;
        const cards = shopGrid.querySelectorAll(".product-card");

        cards.forEach((card) => {
            const name = card.dataset.name || "";
            const productCategory = card.dataset.category || "";
            const price = Number(card.dataset.price || 0);

            let nameMatch = true;
            let categoryMatch = true;
            let priceMatch = true;

            if (searchTerm.length) {
                nameMatch = name.includes(searchTerm);
            }

            if (category !== "all") {
                categoryMatch = productCategory.includes(category);
            }

            if (priceRange !== "all") {
                const [min, max] = priceRange.split("-").map(Number);
                priceMatch = price >= min && price <= max;
            }

            const show = nameMatch && categoryMatch && priceMatch;
            card.closest(".col-sm-6")?.classList.toggle("d-none", !show);
            if (show) {
                visible += 1;
            }
        });

        if (shopNoResults) {
            shopNoResults.classList.toggle("d-none", visible > 0);
        }
    };

    [productSearchInput, categoryFilterSelect, priceFilterSelect].forEach((element) => {
        element?.addEventListener("input", applyShopFilters);
        element?.addEventListener("change", applyShopFilters);
    });

    clearFiltersBtn?.addEventListener("click", () => {
        if (productSearchInput) productSearchInput.value = "";
        if (categoryFilterSelect) categoryFilterSelect.value = "all";
        if (priceFilterSelect) priceFilterSelect.value = "all";
        applyShopFilters();
    });

    const cartCount = document.getElementById("cartCount");
    document.querySelectorAll(".add-to-cart-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const idField = form.querySelector('input[name="product_id"]');
            const quantityField = form.querySelector('input[name="quantity"]');
            const productName = form.dataset.productName || "Product";
            const productId = Number(idField?.value || 0);
            const quantity = Number(quantityField?.value || 0);

            if (!productId || quantity < 1) {
                event.preventDefault();
                showToast("Invalid product details. Please try again.", "error");
                return;
            }

            if (cartCount) {
                const currentCount = Number(cartCount.textContent || "0");
                cartCount.textContent = String(currentCount + quantity);
            }

            showToast(`${productName} added to cart!`);
        });
    });

    const cartRows = document.querySelectorAll(".cart-item-row");
    const subtotalElement = document.getElementById("cartSubtotal");
    const shippingElement = document.getElementById("cartShipping");
    const vatElement = document.getElementById("cartVat");
    const totalElement = document.getElementById("cartTotal");

    const updateCartTotals = () => {
        if (!cartRows.length || !subtotalElement || !shippingElement || !vatElement || !totalElement) {
            return;
        }

        let subtotal = 0;

        cartRows.forEach((row) => {
            const price = Number(row.dataset.unitPrice || 0);
            const qtyInput = row.querySelector(".cart-qty-input");
            const quantity = Math.max(1, Number(qtyInput?.value || 1));
            const line = price * quantity;
            subtotal += line;

            const lineTarget = row.querySelector(".item-subtotal");
            if (lineTarget) {
                lineTarget.textContent = formatMoney(line);
            }
        });

        const shipping = Number(shippingElement.dataset.value || 0);
        const vatRate = Number(vatElement.dataset.rate || 0.16);
        const vat = subtotal * vatRate;
        const total = subtotal + shipping + vat;

        subtotalElement.textContent = formatMoney(subtotal);
        vatElement.textContent = formatMoney(vat);
        totalElement.textContent = formatMoney(total);
    };

    document.querySelectorAll(".qty-btn").forEach((button) => {
        button.addEventListener("click", () => {
            const form = button.closest(".cart-update-form");
            const input = form?.querySelector(".cart-qty-input");
            if (!input) {
                return;
            }

            const currentValue = Math.max(1, Number(input.value || 1));
            input.value = button.dataset.action === "plus" ? String(currentValue + 1) : String(Math.max(1, currentValue - 1));
            updateCartTotals();
        });
    });

    document.querySelectorAll(".cart-qty-input").forEach((input) => {
        input.addEventListener("input", updateCartTotals);
    });

    document.querySelectorAll(".remove-item-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const confirmed = window.confirm("Remove this item from cart?");
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll(".admin-danger-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            const message = form.dataset.confirm || "Are you sure you want to continue?";
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    updateCartTotals();

    const clearValidationErrors = (form) => {
        form.querySelectorAll(".field-error").forEach((item) => item.remove());
        form.querySelectorAll(".is-invalid").forEach((field) => field.classList.remove("is-invalid"));
    };

    const showFieldError = (field, message) => {
        field.classList.add("is-invalid");
        const error = document.createElement("small");
        error.className = "field-error text-danger d-block mt-1";
        error.textContent = message;
        field.insertAdjacentElement("afterend", error);
    };

    const isEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    const isKenyaPhone = (value) => /^(\+254|0)\d{9}$/.test(value);

    const validateRegister = (form) => {
        const firstName = form.querySelector('[name="first_name"]');
        const lastName = form.querySelector('[name="last_name"]');
        const phone = form.querySelector('[name="phone"]');
        const email = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        const confirm = form.querySelector('[name="confirm_password"]');
        let valid = true;

        if (!firstName.value.trim()) {
            showFieldError(firstName, "Please enter your full name.");
            valid = false;
        }
        if (!lastName.value.trim()) {
            showFieldError(lastName, "Please enter your last name.");
            valid = false;
        }
        if (!isEmail(email.value.trim())) {
            showFieldError(email, "Please enter a valid email address.");
            valid = false;
        }
        if (!isKenyaPhone(phone.value.trim())) {
            showFieldError(phone, "Please enter a valid phone number.");
            valid = false;
        }
        if (password.value.length < 8) {
            showFieldError(password, "Password must be at least 8 characters.");
            valid = false;
        }
        if (confirm.value !== password.value) {
            showFieldError(confirm, "Passwords do not match.");
            valid = false;
        }

        return valid;
    };

    const validateLogin = (form) => {
        const email = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        let valid = true;

        if (!email.value.trim()) {
            showFieldError(email, "Please enter your email.");
            valid = false;
        } else if (!isEmail(email.value.trim())) {
            showFieldError(email, "Please enter a valid email address.");
            valid = false;
        }

        if (!password.value.trim()) {
            showFieldError(password, "Please enter your password.");
            valid = false;
        }

        return valid;
    };

    const validateAdminLogin = (form) => {
        const login = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        let valid = true;

        if (!login.value.trim()) {
            showFieldError(login, "Please enter your email or username.");
            valid = false;
        }

        if (!password.value.trim()) {
            showFieldError(password, "Please enter your password.");
            valid = false;
        }

        return valid;
    };

    const validateFeedback = (form) => {
        const name = form.querySelector('[name="name"]');
        const email = form.querySelector('[name="email"]');
        const rating = form.querySelector('[name="rating"]');
        const message = form.querySelector('[name="message"]');
        let valid = true;

        if (!name.value.trim()) {
            showFieldError(name, "Please enter your full name.");
            valid = false;
        }
        if (!isEmail(email.value.trim())) {
            showFieldError(email, "Please enter a valid email address.");
            valid = false;
        }
        if (!rating.value) {
            showFieldError(rating, "Please select a rating.");
            valid = false;
        }
        if (!message.value.trim()) {
            showFieldError(message, "Please enter your feedback message.");
            valid = false;
        }

        return valid;
    };

    const validateCheckout = (form) => {
        const requiredFields = ["customer_name", "phone", "email", "county", "town", "street", "house_number"];
        let valid = true;

        requiredFields.forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field && !field.value.trim()) {
                showFieldError(field, "This field is required.");
                valid = false;
            }
        });

        const email = form.querySelector('[name="email"]');
        const phone = form.querySelector('[name="phone"]');
        if (email && !isEmail(email.value.trim())) {
            showFieldError(email, "Please enter a valid email address.");
            valid = false;
        }
        if (phone && !isKenyaPhone(phone.value.trim())) {
            showFieldError(phone, "Please enter a valid phone number.");
            valid = false;
        }

        return valid;
    };

    const validatePayment = (form) => {
        const method = form.querySelector('[name="payment_method"]');
        const mpesaPhone = form.querySelector('[name="mpesa_phone"]');
        let valid = true;

        if (!method.value) {
            showFieldError(method, "Please select a payment method.");
            valid = false;
        }

        if (method.value === "mpesa" && !isKenyaPhone(mpesaPhone.value.trim())) {
            showFieldError(mpesaPhone, "Please enter a valid M-Pesa number.");
            valid = false;
        }

        return valid;
    };

    const validateAdminCustomerEdit = (form) => {
        const firstName = form.querySelector('[name="first_name"]');
        const lastName = form.querySelector('[name="last_name"]');
        const email = form.querySelector('[name="email"]');
        const phone = form.querySelector('[name="phone"]');
        let valid = true;

        if (!firstName.value.trim()) {
            showFieldError(firstName, "Please enter first name.");
            valid = false;
        }
        if (!lastName.value.trim()) {
            showFieldError(lastName, "Please enter last name.");
            valid = false;
        }
        if (!isEmail(email.value.trim())) {
            showFieldError(email, "Please enter a valid email address.");
            valid = false;
        }
        if (!isKenyaPhone(phone.value.trim())) {
            showFieldError(phone, "Please enter a valid phone number.");
            valid = false;
        }

        return valid;
    };

    const validateAdminProductForm = (form) => {
        const name = form.querySelector('[name="name"]');
        const category = form.querySelector('[name="category_id"]');
        const price = form.querySelector('[name="price"]');
        const stock = form.querySelector('[name="stock"]');
        const condition = form.querySelector('[name="item_condition"]');
        let valid = true;

        if (!name.value.trim()) {
            showFieldError(name, "Please enter product name.");
            valid = false;
        }
        if (!category.value.trim()) {
            showFieldError(category, "Please select a category.");
            valid = false;
        }
        if (Number(price.value || 0) <= 0) {
            showFieldError(price, "Please enter a valid product price.");
            valid = false;
        }
        if (Number(stock.value || -1) < 0) {
            showFieldError(stock, "Stock cannot be negative.");
            valid = false;
        }
        if (!condition.value.trim()) {
            showFieldError(condition, "Please enter product condition.");
            valid = false;
        }

        return valid;
    };

    const validateAdminCategoryForm = (form) => {
        const name = form.querySelector('[name="name"]');
        let valid = true;

        if (!name.value.trim()) {
            showFieldError(name, "Please enter category name.");
            valid = false;
        }

        return valid;
    };

    document.querySelectorAll("form[data-validate]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            clearValidationErrors(form);
            const type = form.dataset.validate;
            let valid = true;

            if (type === "register") valid = validateRegister(form);
            if (type === "login") valid = validateLogin(form);
            if (type === "admin-login") valid = validateAdminLogin(form);
            if (type === "feedback") valid = validateFeedback(form);
            if (type === "checkout") valid = validateCheckout(form);
            if (type === "payment") valid = validatePayment(form);
            if (type === "admin-customer-edit") valid = validateAdminCustomerEdit(form);
            if (type === "admin-product-form") valid = validateAdminProductForm(form);
            if (type === "admin-category-form") valid = validateAdminCategoryForm(form);

            if (!valid) {
                event.preventDefault();
                showToast("Please fix the highlighted fields.", "error");
            }
        });
    });
})();
