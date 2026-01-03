document.addEventListener('DOMContentLoaded', function() {
    const lastOrderId = localStorage.getItem('lastOrderId');
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const session = JSON.parse(localStorage.getItem('userSession') || sessionStorage.getItem('userSession') || 'null');
    const userEmail = session && session.email ? session.email : null;

    let order = null;
    if (lastOrderId) {
        order = orders.find(o => o.orderId === lastOrderId && (!userEmail || o.userEmail === userEmail));
    }
    if (!order && userEmail) {
        const userOrders = orders.filter(o => o.userEmail === userEmail);
        order = userOrders[userOrders.length - 1] || null;
    }
    if (!order) order = orders[orders.length - 1] || null;

    const container = document.getElementById('confirmationDetails');
    if (!order) {
        container.innerHTML = '<p>No recent order found.</p>';
        return;
    }

    const orderDate = new Date(order.date).toLocaleString();

    container.innerHTML = `
        <div class="confirmation-success-banner">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="success-title">Order Placed Successfully!</h2>
            <p class="success-message">Thank you for your order. We've received your order and will process it shortly.</p>
        </div>
        
        <div class="confirmation-card">
            <div class="order-header">
                <div class="order-info-grid">
                    <div class="info-item">
                        <i class="fas fa-receipt"></i>
                        <div>
                            <span class="info-label">Order ID</span>
                            <span class="info-value">${order.orderId}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <span class="info-label">Order Date</span>
                            <span class="info-value">${orderDate}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <span class="info-label">Payment Method</span>
                            <span class="info-value">${order.paymentMethod || 'Cash on Delivery'}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="order-items-section">
                <h3 class="section-heading"><i class="fas fa-box-open"></i> Order Items</h3>
                <div class="confirmation-items">
                    ${order.items.map(item => `
                        <div class="confirmation-item">
                            <img src="${item.image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/80x100/F5F5DC/3E3E3E?text=Product'">
                            <div class="confirmation-item-info">
                                <div class="confirmation-item-name">${item.name}</div>
                                <div class="confirmation-item-meta">
                                    <span class="item-quantity">Qty: ${item.quantity}</span>
                                    <span class="item-unit-price">@ PKR ${item.price.toLocaleString()}</span>
                                </div>
                            </div>
                            <div class="confirmation-item-price">PKR ${ (item.price * item.quantity).toLocaleString() }</div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="order-summary-section">
                <h3 class="section-heading"><i class="fas fa-file-invoice-dollar"></i> Order Summary</h3>
                <div class="confirmation-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>PKR ${order.subtotal.toLocaleString()}</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="${order.shipping === 0 ? 'text-success' : ''}">${order.shipping === 0 ? 'FREE' : 'PKR ' + order.shipping.toLocaleString()}</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax</span>
                        <span>PKR ${order.tax.toLocaleString()}</span>
                    </div>
                    ${order.discount ? `<div class="summary-row discount-row">
                        <span><i class="fas fa-tag"></i> Discount</span>
                        <span class="text-success">-PKR ${order.discount.toLocaleString()}</span>
                    </div>` : ''}
                    <div class="summary-divider"></div>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span>PKR ${order.total.toLocaleString()}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
});