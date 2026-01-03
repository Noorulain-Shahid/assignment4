
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = parseInt(urlParams.get('id'));
    
    if (!productId || !window.allProducts) {
        window.location.href = 'products.html';
        return;
    }
    
    const product = window.allProducts.find(p => p.id === productId);
    
    if (!product) {
        window.location.href = 'products.html';
        return;
    }
    
    displayProductDetails(product);
    displayRelatedProducts(product.category, product.id);
});

function displayProductDetails(product) {
    const wrapper = document.getElementById('productDetailsWrapper');
    
    const section = document.querySelector('.product-details-section');
    if (section) {
        section.setAttribute('data-product-id', product.id);
    }
    
    const placeholderUrl = `https://via.placeholder.com/600x800/F5F5DC/3E3E3E?text=${encodeURIComponent(product.name)}`;
    
    wrapper.innerHTML = `
        <div class="product-images">
            <div class="main-image">
                <img src="${product.image}" alt="${product.name}" id="mainImage" onerror="this.src='${placeholderUrl}'">
            </div>
            <div class="thumbnail-images">
                <div class="thumbnail active" onclick="changeImage('${product.image}', this)">
                    <img src="${product.image}" alt="View 1" onerror="this.src='${placeholderUrl}'">
                </div>
                <div class="thumbnail" onclick="changeImage('${product.image}', this)">
                    <img src="${product.image}" alt="View 2" onerror="this.src='${placeholderUrl}'">
                </div>
                <div class="thumbnail" onclick="changeImage('${product.image}', this)">
                    <img src="${product.image}" alt="View 3" onerror="this.src='${placeholderUrl}'">
                </div>
                <div class="thumbnail" onclick="changeImage('${product.image}', this)">
                    <img src="${product.image}" alt="View 4" onerror="this.src='${placeholderUrl}'">
                </div>
            </div>
        </div>
        
        <div class="product-info-detailed">
            <span class="product-category">${product.category}</span>
            <h1 class="product-title">${product.name}</h1>
            
            <div class="product-rating">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <span class="rating-text">(4.5) 128 Reviews</span>
            </div>
            
            <div class="product-price-detailed">PKR ${product.price.toLocaleString()}</div>
            
            <p class="product-description">${product.description}</p>
            
            <div class="size-selection">
                <h3>Select Size:</h3>
                <div class="size-options" id="sizeOptions">
                    ${product.sizes.map(size => `
                        <button class="size-option" onclick="selectSize(this)">${size}</button>
                    `).join('')}
                </div>
            </div>
            
            <div class="color-selection">
                <h3>Select Color:</h3>
                <div class="color-options" id="colorOptions">
                    ${product.colors.map(color => `
                        <button class="color-option" onclick="selectColor(this)">${color}</button>
                    `).join('')}
                </div>
            </div>
            
            <div class="quantity-selection">
                <h3>Quantity:</h3>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                    <span class="quantity-display" id="quantityDisplay">1</span>
                    <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                </div>
            </div>
            
            <div class="action-buttons">
                <button class="add-to-cart-large" onclick="addToCartDetailed(${product.id})">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
                <button class="wishlist-large" onclick="toggleWishlistDetailed(this)">
                    <i class="far fa-heart"></i> Add to Wishlist
                </button>
            </div>
            
            <div class="product-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Free Shipping</h4>
                        <p>On orders over PKR 15,000</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Easy Returns</h4>
                        <p>30-day return policy</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Premium Quality</h4>
                        <p>100% authentic products</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-text">
                        <h4>24/7 Support</h4>
                        <p>Always here to help</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

let relatedCarouselPosition = 0;

function displayRelatedProducts(category, currentProductId) {
    const relatedContainer = document.getElementById('relatedProducts');
    const related = window.allProducts.filter(p => p.category === category && p.id !== currentProductId).slice(0, 12);
    
    relatedContainer.innerHTML = related.map(product => {
        const placeholderUrl = `https://via.placeholder.com/400x500/F5F5DC/3E3E3E?text=${encodeURIComponent(product.name)}`;
        return `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}" onerror="this.src='${placeholderUrl}'">
                    <button class="wishlist-btn" onclick="toggleWishlist(this)"><i class="far fa-heart"></i></button>
                </div>
                <div class="product-info">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-price">PKR ${product.price.toLocaleString()}</p>
                    <div class="product-actions">
                        <button class="add-to-cart-btn" onclick="addToCart(${product.id})">Add to Cart</button>
                        <a href="product-details.html?id=${product.id}" class="view-details-btn">View Details</a>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function moveRelatedCarousel(direction) {
    const carousel = document.querySelector('.related-carousel');
    const cards = carousel.querySelectorAll('.product-card');
    
    if (cards.length === 0) return;
    
    let visibleCards = 4;
    
    if (window.innerWidth <= 480) {
        visibleCards = 1;
    } else if (window.innerWidth <= 768) {
        visibleCards = 2;
    } else if (window.innerWidth <= 1200) {
        visibleCards = 3;
    }
    
    const containerWidth = carousel.parentElement.offsetWidth;
    
    const maxSlides = Math.ceil(cards.length / visibleCards);
    let currentSlide = Math.round(-relatedCarouselPosition / containerWidth);
    
    currentSlide += direction;
    
    if (currentSlide < 0) {
        currentSlide = 0;
    } else if (currentSlide >= maxSlides) {
        currentSlide = maxSlides - 1;
    }
    
    relatedCarouselPosition = -(currentSlide * containerWidth);
    carousel.style.transform = `translateX(${relatedCarouselPosition}px)`;
}

        const product = allProducts.find(p => p.id === productId);
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const size = document.getElementById('sizeSelect')?.value || '';
        const color = document.getElementById('colorSelect')?.value || '';

        if (!product) {
            showNotification('Product not found!', 'error');
            return;
        }

        const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');

        if (session) {
            try {
                await addToCartAPI(productId, quantity, size, color);
                await updateCartCount();
                showNotification(`${product.name} added to cart!`, 'success');
                return;
            } catch (err) {
                console.error('Add to cart detailed (API) error:', err);
                showNotification('Could not add to cart. Please try again.', 'error');
                return;
            }
        }

        const existingItem = cart.find(item => item.id === productId && item.size === size && item.color === color);
    
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image_url || product.image,
                size: size,
                color: color,
                quantity: quantity
            });
        }
    
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        showNotification(`${product.name} added to cart!`, 'success');
    document.getElementById('quantityDisplay').textContent = quantity;
}

function decreaseQuantity() {
    if (quantity > 1) {
        quantity--;
        document.getElementById('quantityDisplay').textContent = quantity;
    }
}

function addToCartDetailed(productId) {
    if (!selectedSize) {
        showNotification('Please select a size!', 'error');
        return;
    }
    
    if (!selectedColor) {
        showNotification('Please select a color!', 'error');
        return;
    }
    
    const product = window.allProducts.find(p => p.id === productId);
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    const cartItemKey = `${productId}-${selectedSize}-${selectedColor}`;
    const existingItem = cart.find(item => 
        item.id === productId && item.size === selectedSize && item.color === selectedColor
    );
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: quantity,
            size: selectedSize,
            color: selectedColor
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showNotification(`${quantity} ${product.name} added to cart!`, 'success');
    
    quantity = 1;
    document.getElementById('quantityDisplay').textContent = '1';
}

function toggleWishlistDetailed(btn) {
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.style.backgroundColor = 'var(--light-beige)';
        showNotification('Added to wishlist!', 'success');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        btn.style.backgroundColor = '';
        showNotification('Removed from wishlist!', 'success');
    }
}
