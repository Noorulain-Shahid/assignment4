// API Base URL (root-level API scripts)
const API_BASE = '';

// Authentication Functions
async function login(username, password) {
    try {
        const response = await fetch(API_BASE + 'login_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('userSession', JSON.stringify(data.user));
            localStorage.setItem('sessionToken', data.session_token);
            return data;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
}

async function signup(userData) {
    try {
        const response = await fetch(API_BASE + 'signup_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('userSession', JSON.stringify(data.user));
            localStorage.setItem('sessionToken', data.session_token);
            return data;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Signup error:', error);
        throw error;
    }
}

async function logout() {
    try {
        await fetch(API_BASE + 'logout.php', { method: 'POST' });
        localStorage.removeItem('userSession');
        localStorage.removeItem('sessionToken');
        sessionStorage.removeItem('userSession');
        window.location.href = 'index.php';
    } catch (error) {
        console.error('Logout error:', error);
    }
}

async function checkAuth() {
    try {
        const response = await fetch(API_BASE + 'check_auth.php');
        const data = await response.json();
        return data.authenticated;
    } catch (error) {
        console.error('Auth check error:', error);
        return false;
    }
}

// Product Functions
async function getProducts(params = {}) {
    try {
        const queryString = new URLSearchParams(params).toString();
        const response = await fetch(API_BASE + 'products_api.php?' + queryString);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get products error:', error);
        throw error;
    }
}

async function getProductDetail(id) {
    try {
        const response = await fetch(API_BASE + 'product_detail.php?id=' + id);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get product detail error:', error);
        throw error;
    }
}

async function getCategories() {
    try {
        const response = await fetch(API_BASE + 'categories.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get categories error:', error);
        throw error;
    }
}

// Cart Functions
async function getCart() {
    try {
        const response = await fetch(API_BASE + 'cart_api.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get cart error:', error);
        throw error;
    }
}

async function addToCartAPI(productId, quantity = 1, size = '', color = '') {
    try {
        const response = await fetch(API_BASE + 'cart_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity, size, color })
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Add to cart error:', error);
        throw error;
    }
}

async function updateCartItem(cartId, quantity) {
    try {
        const response = await fetch(API_BASE + 'cart_api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_id: cartId, quantity })
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Update cart error:', error);
        throw error;
    }
}

async function removeCartItem(cartId) {
    try {
        const response = await fetch(API_BASE + 'cart_api.php?cart_id=' + cartId, {
            method: 'DELETE'
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Remove cart item error:', error);
        throw error;
    }
}

// Order Functions
async function checkout(orderData) {
    try {
        const response = await fetch(API_BASE + 'checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Checkout error:', error);
        throw error;
    }
}

async function getOrders() {
    try {
        const response = await fetch(API_BASE + 'orders.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get orders error:', error);
        throw error;
    }
}

async function getOrderDetail(orderId) {
    try {
        const response = await fetch(API_BASE + 'order_detail.php?order_id=' + orderId);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get order detail error:', error);
        throw error;
    }
}

async function reorder(orderId) {
    try {
        const response = await fetch(API_BASE + 'reorder_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Reorder error:', error);
        throw error;
    }
}

// Profile Functions
async function getProfile() {
    try {
        const response = await fetch(API_BASE + 'profile.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Get profile error:', error);
        throw error;
    }
}

async function updateProfile(profileData) {
    try {
        const response = await fetch(API_BASE + 'update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(profileData)
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Update profile error:', error);
        throw error;
    }
}

// UI Helper Functions
function isLoggedIn() {
    const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');
    return session !== null;
}

function getCurrentUser() {
    const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');
    return session ? JSON.parse(session) : null;
}

function updateAuthUI() {
    const loginLink = document.querySelector('a[href="login.php"]');
    if (!loginLink) return;
    
    if (isLoggedIn()) {
        const user = getCurrentUser();
        loginLink.innerHTML = `<i class="fas fa-user"></i> ${user.username}`;
        loginLink.href = '#';
        loginLink.onclick = (e) => {
            e.preventDefault();
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-menu show position-absolute';
            dropdown.style.cssText = 'right: 0; top: 100%;';
            dropdown.innerHTML = `
                <a class="dropdown-item" href="#" onclick="window.location.href='orders.php'">My Orders</a>
                <a class="dropdown-item" href="#" onclick="logout()">Logout</a>
            `;
            loginLink.parentElement.style.position = 'relative';
            loginLink.parentElement.appendChild(dropdown);
            
            setTimeout(() => dropdown.remove(), 3000);
        };
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAuthUI();
});
