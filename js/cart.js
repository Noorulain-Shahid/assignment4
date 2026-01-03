
if (typeof cart === 'undefined') {
    cart = JSON.parse(localStorage.getItem('cart')) || [];
} else {
    cart = JSON.parse(localStorage.getItem('cart')) || cart || [];
}
let discount = 0;

document.addEventListener('DOMContentLoaded', function() {
    displayCart();
    updateCartSummary();
    displayCartProducts();
    updateCheckoutState();
});

function updateCheckoutState() {
    const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');
    const noticeEl = document.getElementById('checkoutNotice');
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (!checkoutBtn) return;

    if (!session) {
        if (noticeEl) {
            noticeEl.innerHTML = `<div class="alert alert-info" role="status">You're not logged in — your cart is saved locally. <a href="login.html">Login</a> to proceed to checkout.</div>`;
        }
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-lock"></i> Login to Checkout';
        checkoutBtn.onclick = function() { window.location.href = 'login.html'; };
    } else {
        if (noticeEl) noticeEl.innerHTML = '';
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fas fa-lock"></i> Proceed to Checkout';
        checkoutBtn.onclick = proceedToCheckout;
    }
}

function displayCartProducts() {
    const container = document.getElementById('cartProducts');
    if (!container) return;

    const products = (typeof productsDB !== 'undefined') ? Object.values(productsDB) : [];
    if (products.length === 0) {
        container.innerHTML = '<p class="text-muted">No products available.</p>';
        return;
    }

    const placeholder = 'https://via.placeholder.com/220x260/F5F5DC/3E3E3E?text=Product';
    container.innerHTML = products.map(p => `
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card product-card h-100 border-0 shadow-sm">
                <div class="position-relative product-image">
                    <img src="${p.image}" class="card-img-top" alt="${p.name}" onerror="this.src='${placeholder}'">
                </div>
                <div class="card-body product-info">
                    <h5 class="card-title product-name">${p.name}</h5>
                    <p class="product-price fw-bold text-warning">PKR ${p.price.toLocaleString()}</p>
                    <div class="product-actions d-flex gap-2">
                        <button class="btn add-to-cart-btn" onclick="addToCart(${p.id}); displayCart(); updateCartSummary(); updateCartCount(); scheduleCartBadgeSync();">Add to Cart</button>
                        <a href="product-details.html?id=${p.id}" class="btn view-details-btn">View</a>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function displayCart() {
    const cartContainer = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const cartSummary = document.getElementById('cartSummary');
    
    if (cart.length === 0) {
        cartContainer.innerHTML = '';
        emptyCart.style.display = 'block';
        cartSummary.style.display = 'none';
        return;
    }
    
    emptyCart.style.display = 'none';
    cartSummary.style.display = 'block';
    
    const placeholderUrl = 'https://via.placeholder.com/120x150/F5F5DC/3E3E3E?text=Product';

    function resolveImage(item) {
        let src = (item && item.image) ? item.image : null;
        if (!src && window.productsDB && item && item.id && productsDB[item.id]) {
            src = productsDB[item.id].image;
        }
        if (!src) return placeholderUrl;

        try {
            src = String(src).trim();
            src = src.replace(/\\/g, '/');
            src = src.replace(/\/\/+/, '/');
        } catch (e) {}

        try {
            if (!/^(https?:|data:|\/)/i.test(src)) {
                const parts = src.split('/').map(p => encodeURIComponent(p));
                src = parts.join('/');
            } else {
                src = encodeURI(src);
            }
        } catch (e) {
            try { src = encodeURI(src); } catch (err) { src = placeholderUrl; }
        }

        return src || placeholderUrl;
    }

    cartContainer.innerHTML = cart.map((item, index) => {
        const imgSrc = resolveImage(item);
        return `
        <div class="cart-item">
            <div class="cart-item-image">
                <img src="${imgSrc}" alt="${item.name}" onerror="this.src='${placeholderUrl}'">
            </div>
            <div class="cart-item-details">
                <div class="cart-item-header">
                    <div>
                        <h3 class="cart-item-name">${item.name}</h3>
                        <div class="cart-item-info">
                            ${item.size ? `<span><i class="fas fa-tag"></i> Size: ${item.size}</span>` : ''}
                            ${item.color ? `<span><i class="fas fa-palette"></i> Color: ${item.color}</span>` : ''}
                        </div>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="cart-item-footer">
                    <div class="quantity-controls-cart">
                        <button class="quantity-btn-cart" onclick="updateQuantity(${index}, -1)">-</button>
                        <span class="quantity-display-cart">${item.quantity}</span>
                        <button class="quantity-btn-cart" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                    <div class="cart-item-price">PKR ${(item.price * item.quantity).toLocaleString()}</div>
                </div>
            </div>
        </div>
    `}).join('');

    populateCartDebug();
}

function populateCartDebug() {
    const debugSection = document.getElementById('cartDebug');
    const debugList = document.getElementById('cartDebugList');
    if (!debugSection || !debugList) return;
    const show = localStorage.getItem('showCartDebug') === '1';
    debugSection.style.display = show ? 'block' : 'none';
    if (!show) return;

    debugList.innerHTML = '';
    const items = JSON.parse(localStorage.getItem('cart') || '[]');
    if (!items || items.length === 0) {
        debugList.innerHTML = '<p class="text-muted">Cart is empty.</p>';
        return;
    }

    items.forEach((it, idx) => {
        const resolved = (it && it.image) ? it.image : (window.productsDB && productsDB[it.id] && productsDB[it.id].image) || '';
        const resolvedUrl = (function() {
            try { return encodeURI(resolved.trim().replace(/\\/g, '/')); } catch (e) { return resolved; }
        })();

        const row = document.createElement('div');
        row.className = 'cart-debug-row';
        row.innerHTML = `
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
                <div style="width:60px;height:80px;overflow:hidden;border:1px solid #eee;">
                    <img src="${resolvedUrl}" style="width:100%;height:100%;object-fit:cover;" onerror="this.dataset.status='error'" onload="this.dataset.status='ok'">
                </div>
                <div style="flex:1">
                    <div><strong>${it.name || ('id:'+it.id)}</strong></div>
                    <div class="text-muted">Resolved: <code>${resolvedUrl || '(none)'}</code></div>
                    <div class="text-muted" id="cartDebugStatus${idx}">Checking…</div>
                </div>
            </div>
        `;
        debugList.appendChild(row);

        const img = new Image();
        img.onload = () => { const s = document.getElementById('cartDebugStatus'+idx); if (s) s.textContent = 'OK'; };
        img.onerror = () => { const s = document.getElementById('cartDebugStatus'+idx); if (s) s.textContent = 'ERROR (file not found or blocked)'; };
        img.src = resolvedUrl;
    });
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    
    if (cart[index].quantity <= 0) {
        removeFromCart(index);
        return;
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
    updateCartSummary();
    updateCartCount();
}

function removeFromCart(index) {
    const itemName = cart[index].name;
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
    updateCartSummary();
    updateCartCount();
    showNotification(`${itemName} removed from cart`, 'success');
}

function clearCart() {
    if (!cart || cart.length === 0) {
        showNotification('Your cart is already empty', 'error');
        return;
    }

    const confirmClear = confirm('Are you sure you want to remove all items from your cart?');
    if (!confirmClear) return;

    cart = [];
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
    updateCartSummary();
    updateCartCount();
    try {
        const badge = document.getElementById('cartCount');
        if (badge) badge.textContent = '0';
    } catch (e) {}
    showNotification('All items removed from cart', 'success');
}

function updateCartSummary() {
    const summaryItemsContainer = document.getElementById('summaryItems');
    if (summaryItemsContainer) {
        if (cart.length === 0) {
            summaryItemsContainer.innerHTML = '<p class="muted">No items in cart</p>';
        } else {
            const maxShow = 4;
            const itemsToShow = cart.slice(0, maxShow);
            const moreCount = cart.length - itemsToShow.length;
            const placeholderUrl = 'https://via.placeholder.com/60x80/F5F5DC/3E3E3E?text=Prod';
            summaryItemsContainer.innerHTML = itemsToShow.map(it => {
                let s = it.image || (window.productsDB && productsDB[it.id] && productsDB[it.id].image) || placeholderUrl;
                try { s = encodeURI(s); } catch (e) {}
                return `
                <div class="summary-item">
                    <img src="${s}" alt="${it.name}" onerror="this.src='${placeholderUrl}'">
                    <div class="summary-item-info">
                        <div class="summary-item-name">${it.name}</div>
                        <div class="summary-item-qty">Qty: ${it.quantity}</div>
                    </div>
                    <div class="summary-item-price">PKR ${(it.price * it.quantity).toLocaleString()}</div>
                </div>
            `}).join('') + (moreCount > 0 ? `<div class="summary-more">+${moreCount} more</div>` : '');
        }
    }

    if (cart.length === 0) return;
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    const shipping = subtotal >= 15000 ? 0 : 500;
    
    const tax = subtotal * 0.1;
    
    const total = subtotal + shipping + tax - discount;
    
    document.getElementById('subtotal').textContent = `PKR ${subtotal.toLocaleString()}`;
    document.getElementById('shipping').textContent = shipping === 0 ? 'FREE' : `PKR ${shipping.toLocaleString()}`;
    document.getElementById('tax').textContent = `PKR ${tax.toLocaleString()}`;
    document.getElementById('total').textContent = `PKR ${total.toLocaleString()}`;
    
    if (discount > 0) {
        document.getElementById('discountRow').style.display = 'flex';
        document.getElementById('discount').textContent = `-PKR ${discount.toLocaleString()}`;
    }
}

function applyPromoCode() {
    const promoInput = document.getElementById('promoCode');
    const code = promoInput.value.trim().toUpperCase();
    
    const promoCodes = {
        'WELCOME10': 10,
        'SAVE20': 20,
        'TRENDY15': 15
    };
    
    if (code === '') {
        showNotification('Please enter a promo code', 'error');
        return;
    }
    
    if (promoCodes[code]) {
        discount = promoCodes[code];
        updateCartSummary();
        showNotification(`Promo code applied! You saved PKR ${discount.toLocaleString()}`, 'success');
        promoInput.value = '';
        promoInput.disabled = true;
    } else {
        showNotification('Invalid promo code', 'error');
    }
}

function proceedToCheckout() {
    if (cart.length === 0) {
        showNotification('Your cart is empty!', 'error');
        return;
    }
    
    const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');
    
    if (!session) {
        showNotification('Please login to proceed to checkout', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1500);
        return;
    }
    
    const selectedPayment = document.querySelector('input[name="payment"]:checked');
    if (!selectedPayment) {
        showNotification('Please select a payment method', 'error');
        return;
    }
    
    const checkoutBtn = document.querySelector('.checkout-btn');
    const originalText = checkoutBtn.innerHTML;
    checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    checkoutBtn.disabled = true;
    
    setTimeout(() => {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = subtotal >= 15000 ? 0 : 500;
        const tax = subtotal * 0.1;
        const total = subtotal + shipping + tax - discount;
        
    const paymentEl = document.querySelector('input[name="payment"]:checked');
    const paymentMethod = paymentEl ? paymentEl.value : null;

        const session = JSON.parse(localStorage.getItem('userSession') || sessionStorage.getItem('userSession') || 'null');
        const order = {
            orderId: 'ORD-' + Date.now(),
            items: [...cart],
            subtotal,
            shipping,
            tax,
            discount,
            total,
            paymentMethod,
            date: new Date().toISOString(),
            status: 'Processing',
            userEmail: session && session.email ? session.email : 'guest'
        };
        
        const orders = JSON.parse(localStorage.getItem('orders')) || [];
        orders.push(order);
        localStorage.setItem('orders', JSON.stringify(orders));
    localStorage.setItem('lastOrderId', order.orderId);
        
        cart = [];
        localStorage.setItem('cart', JSON.stringify(cart));
        
        showNotification('Order placed successfully!', 'success');
        
        setTimeout(() => {
            window.location.href = 'order-confirmation.html';
        }, 1200);
    }, 2000);
}
