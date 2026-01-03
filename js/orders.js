
let orders = JSON.parse(localStorage.getItem('orders')) || [];
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');
    
    if (!session) {
        showNotification('Please login to view your orders', 'error');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 1500);
        return;
    }

    displayOrders();
    
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            displayOrders();
        });
    });
});

function displayOrders() {
    const ordersContainer = document.getElementById('ordersContainer');
    const noOrders = document.getElementById('noOrders');
    const ordersCount = document.getElementById('ordersCount');
    
    const session = JSON.parse(localStorage.getItem('userSession') || sessionStorage.getItem('userSession') || 'null');
    const userEmail = session && session.email ? session.email : null;

    let userOrders = orders;
    if (userEmail) {
        userOrders = orders.filter(o => o.userEmail === userEmail);
    } else {
        userOrders = [];
    }

    let filteredOrders = userOrders;
    if (currentFilter !== 'all') {
        filteredOrders = userOrders.filter(order => order.status === currentFilter);
    }
    
    filteredOrders.sort((a, b) => new Date(b.date) - new Date(a.date));
    
    if (filteredOrders.length === 0) {
        ordersContainer.innerHTML = '';
        noOrders.style.display = 'block';
        ordersCount.textContent = '0 Orders';
        return;
    }
    
    noOrders.style.display = 'none';
    ordersCount.textContent = `${filteredOrders.length} ${filteredOrders.length === 1 ? 'Order' : 'Orders'}`;
    
    const placeholderUrl = 'https://via.placeholder.com/80x100/F5F5DC/3E3E3E?text=Product';
    
    ordersContainer.innerHTML = filteredOrders.map(order => {
        const orderDate = new Date(order.date);
        const formattedDate = orderDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        return `
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-info-item">
                            <span class="order-info-label">Order ID</span>
                            <span class="order-info-value">${order.orderId}</span>
                        </div>
                        <div class="order-info-item">
                            <span class="order-info-label">Date</span>
                            <span class="order-info-value">${formattedDate}</span>
                        </div>
                        <div class="order-info-item">
                            <span class="order-info-label">Items</span>
                            <span class="order-info-value">${order.items.length} ${order.items.length === 1 ? 'Item' : 'Items'}</span>
                        </div>
                    </div>
                    <div class="order-status status-${order.status.toLowerCase()}">${order.status}</div>
                </div>
                
                <div class="order-body">
                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item">
                                <div class="order-item-image">
                                    <img src="${item.image}" alt="${item.name}" onerror="this.src='${placeholderUrl}'">
                                </div>
                                <div class="order-item-details">
                                    <div class="order-item-name">${item.name}</div>
                                    <div class="order-item-info">
                                        ${item.size ? `Size: ${item.size} | ` : ''}
                                        ${item.color ? `Color: ${item.color} | ` : ''}
                                        Qty: ${item.quantity}
                                    </div>
                                </div>
                                <div class="order-item-price">PKR ${(item.price * item.quantity).toLocaleString()}</div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">
                            Total: <span class="order-total-amount">PKR ${order.total.toLocaleString()}</span>
                        </div>
                        <div class="order-actions">
                            <button class="order-btn track-btn" onclick="trackOrder('${order.orderId}')">
                                <i class="fas fa-map-marker-alt"></i> Track Order
                            </button>
                            <button class="order-btn reorder-btn" onclick="reorder('${order.orderId}')">
                                <i class="fas fa-redo"></i> Reorder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function trackOrder(orderId) {
    const order = orders.find(o => o.orderId === orderId);
    
    if (!order) return;
    
    const statuses = ['Processing', 'Shipped', 'Delivered'];
    const currentIndex = statuses.indexOf(order.status);
    
    let trackingHTML = `
        <div style="background: white; padding: 30px; border-radius: 15px; max-width: 600px; margin: 20px auto; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <h2 style="color: var(--text-dark); margin-bottom: 20px;">Order Tracking - ${orderId}</h2>
            <div class="tracking-timeline">
    `;
    
    statuses.forEach((status, index) => {
        const isActive = index <= currentIndex;
        const icon = index === 0 ? 'fa-receipt' : index === 1 ? 'fa-truck' : 'fa-check-circle';
        
        trackingHTML += `
            <div class="timeline-item">
                <div class="timeline-icon ${isActive ? 'active' : ''}">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="timeline-content">
                    <h4>${status}</h4>
                    <p>${isActive ? 'Completed' : 'Pending'}</p>
                </div>
            </div>
        `;
    });
    
    trackingHTML += `
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="width: 100%; padding: 15px; background: var(--accent-beige); border: none; border-radius: 25px; font-weight: 600; cursor: pointer; margin-top: 20px;">Close</button>
        </div>
    `;
    
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 10000; overflow-y: auto; padding: 20px;';
    overlay.innerHTML = trackingHTML;
    document.body.appendChild(overlay);
    
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
}

function reorder(orderId) {
    const order = orders.find(o => o.orderId === orderId);
    
    if (!order) return;
    
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    order.items.forEach(item => {
        const existingItem = cart.find(cartItem => 
            cartItem.id === item.id && 
            cartItem.size === item.size && 
            cartItem.color === item.color
        );
        
        if (existingItem) {
            existingItem.quantity += item.quantity;
        } else {
            cart.push({...item});
        }
    });
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    
    showNotification('Items added to cart!', 'success');
    
    setTimeout(() => {
        window.location.href = 'cart.html';
    }, 1500);
}

function simulateOrderUpdates() {
    orders.forEach(order => {
        if (order.status === 'Processing') {
            if (Math.random() < 0.3) {
                order.status = 'Shipped';
            }
        } else if (order.status === 'Shipped') {
            if (Math.random() < 0.2) {
                order.status = 'Delivered';
            }
        }
    });
    
    localStorage.setItem('orders', JSON.stringify(orders));
}

simulateOrderUpdates();
