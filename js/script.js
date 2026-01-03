let cart = JSON.parse(localStorage.getItem('cart')) || [];

const productsDB = {
    1: { id: 1, name: 'Beanie Woolen Hat', price: 1050, image: 'images/winter hat girl.png', category: 'Accessories', description: 'A warm and stylish beanie cap to keep you cozy.' },
    2: { id: 2, name: 'Women\'s Hooded Trench Coat', price: 5000, image: 'images/red sweater for women.png', category: 'Outerwear', description: 'A stylish red trench coat with a hood for cold weather.' },
    3: { id: 3, name: 'Hat Scarf Set', price: 1520, image: 'images/winter hat black boy.png', category: 'Accessories', description: 'A matching set of a hat and scarf for a complete winter look.' },
    4: { id: 4, name: 'Cable-Knit Sweater', price: 5000, image: 'images/red sweater for boys.png', category: 'Sweaters', description: 'A classic red cable-knit sweater for a timeless winter style.' },
    9: { id: 9, name: 'Capreze Men Winter Jacket', price: 7500, image: 'images/black boy jacket.png', category: 'Outerwear', description: 'A durable and warm winter jacket for men.' },
    5: { id: 5, name: 'Pom Pom Hat and Gloves', price: 1500, image: 'images/winter hat red black kids.png', category: 'Accessories', description: 'A cute and warm hat and gloves set for kids.' },
    6: { id: 6, name: 'Striped Oversized Sweater', price: 4700, image: 'images/white and baige sweater girl.png', category: 'Sweaters', description: 'A comfy and trendy oversized sweater.' }
    ,
    7: { id: 7, name: 'White Hoodie', price: 3500, image: 'images/white hoodie.png', category: 'Hoodies', description: 'A classic white hoodie for everyday comfort.' },
    8: { id: 8, name: 'Sky Blue Sweatshirt (Boys)', price: 2400, image: 'images/sky blue sweatshirt for boys.png', category: 'Sweatshirts', description: 'Comfortable sky blue sweatshirt for boys.' }
};

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    syncLocalCartToServer();
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Added to wishlist!');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Removed from wishlist!');
            }
        });
    });

    renderUserAvatarInNavbar();
    attachCartLinkZeroing();
});

function getStoredSession() {
    try {
        return JSON.parse(localStorage.getItem('userSession') || sessionStorage.getItem('userSession') || 'null');
    } catch (e) {
        return null;
    }
}

async function syncLocalCartToServer() {
    const localCart = JSON.parse(localStorage.getItem('cart') || '[]');
    if (!localCart || localCart.length === 0) return;

    // Verify server session by probing cart API; if unauthenticated it returns 401
    let serverSessionActive = false;
    try {
        const probe = await getCart();
        if (probe && probe.success !== false) {
            serverSessionActive = true;
        }
    } catch (e) {
        serverSessionActive = false;
    }

    if (!serverSessionActive) return;

    const session = getStoredSession();
    const syncKey = session && session.id ? `synced-${session.id}` : null;
    if (syncKey && localStorage.getItem('cartSyncedFor') === syncKey) return;

    for (const item of localCart) {
        try {
            await addToCartAPI(item.id, item.quantity || 1, item.size || '', item.color || '');
        } catch (err) {
            console.error('Cart sync failed for item', item.id, err);
        }
    }

    localStorage.removeItem('cart');
    if (syncKey) localStorage.setItem('cartSyncedFor', syncKey);
    await updateCartCount(true);
}

function attachCartLinkZeroing() {
    try {
        const cartLinks = Array.from(document.querySelectorAll('a[href*="cart.php"]'));
        cartLinks.forEach(link => {
            link.addEventListener('click', () => {
                const badge = document.getElementById('cartCount');
                if (badge) badge.textContent = '0';
                try { scheduleCartBadgeSync(); } catch (e) {}
            });
        });
    } catch (e) {
        // ignore
    }
}

function scheduleCartBadgeSync() {
    const times = [0, 300, 800, 1500, 3000];
    times.forEach(delay => setTimeout(() => {
        try { updateCartCount(); } catch (e) { console.error('updateCartCount error', e); }
        try {
            const badge = document.getElementById('cartCount');
            if (badge) {
                const stored = JSON.parse(localStorage.getItem('cart') || '[]');
                const total = stored.reduce((s,i) => s + (i.quantity||0), 0);
                badge.textContent = total;
            }
        } catch (err) { /* ignore */ }
    }, delay));
}

document.addEventListener('DOMContentLoaded', scheduleCartBadgeSync);


function renderUserAvatarInNavbar() {
    try {
        const session = JSON.parse(localStorage.getItem('userSession') || sessionStorage.getItem('userSession') || 'null');
        if (!session || !session.email) return;

        const navbar = document.getElementById('navbar');
        if (!navbar) return;

        let loginLink = navbar.querySelector('a[href*="login.php"]');
        if (!loginLink) {
            loginLink = navbar.querySelector('a .fa-user') ? navbar.querySelector('a .fa-user').closest('a') : null;
        }

        const wrapper = document.createElement('a');
        wrapper.className = 'nav-link';
        wrapper.href = 'logout.php';
        wrapper.innerHTML = '<i class="fas fa-sign-out-alt"></i> Logout';

        if (loginLink && loginLink.parentElement) {
            loginLink.parentElement.replaceChild(wrapper, loginLink);
        } else {
            const navCollapse = navbar.querySelector('.collapse.navbar-collapse');
            if (navCollapse) navCollapse.appendChild(wrapper);
        }
    } catch (e) {
        console.error('Avatar render error', e);
    }
}

function md5(s) {
    function toBytes(str){
        const bytes = [];
        for (let i=0;i<str.length;i++){
            const code = str.charCodeAt(i);
            if (code < 0x80) bytes.push(code);
            else if (code < 0x800) { bytes.push(0xc0 | (code>>6), 0x80 | (code & 0x3f)); }
            else if (code < 0xd800 || code >= 0xe000) { bytes.push(0xe0 | (code>>12), 0x80 | ((code>>6)&0x3f), 0x80 | (code & 0x3f)); }
            else { i++; const code2 = str.charCodeAt(i); const val = 0x10000 + (((code & 0x3ff) << 10) | (code2 & 0x3ff)); bytes.push(0xf0 | (val>>18), 0x80 | ((val>>12)&0x3f), 0x80 | ((val>>6)&0x3f), 0x80 | (val & 0x3f)); }
        }
        return bytes;
    }

    function cmn(q, a, b, x, s, t) { a = (a + q + x + t) >>> 0; return (((a << s) | (a >>> (32 - s))) + b) >>> 0; }
    function ff(a,b,c,d,x,s,t){ return cmn((b & c) | ((~b) & d), a, b, x, s, t); }
    function gg(a,b,c,d,x,s,t){ return cmn((b & d) | (c & (~d)), a, b, x, s, t); }
    function hh(a,b,c,d,x,s,t){ return cmn(b ^ c ^ d, a, b, x, s, t); }
    function ii(a,b,c,d,x,s,t){ return cmn(c ^ (b | (~d)), a, b, x, s, t); }

    const x = toBytes(s);
    const len = x.length * 8;
    x.push(0x80);
    while ((x.length % 64) !== 56) x.push(0);
    for (let i = 0; i < 8; i++) x.push(len >>> (8 * i) & 0xFF);

    let a=1732584193, b=-271733879, c=-1732584194, d=271733878;

    for (let i = 0; i < x.length; i += 64) {
        const olda=a, oldb=b, oldc=c, oldd=d;
        const chunk = x.slice(i, i+64);
        const M = [];
        for (let j=0;j<64;j+=4) M.push(chunk[j] | (chunk[j+1]<<8) | (chunk[j+2]<<16) | (chunk[j+3]<<24));

        a = ff(a,b,c,d,M[0],7,-680876936);
        a = ff(a,b,c,d,M[1],12,-389564586);
        a = ff(a,b,c,d,M[2],17,606105819);
        a = ff(a,b,c,d,M[3],22,-1044525330);
        a = ff(a,b,c,d,M[4],7,-176418897);
        a = ff(a,b,c,d,M[5],12,1200080426);
        a = ff(a,b,c,d,M[6],17,-1473231341);
        a = ff(a,b,c,d,M[7],22,-45705983);
        a = ff(a,b,c,d,M[8],7,1770035416);
        a = ff(a,b,c,d,M[9],12,-1958414417);
        a = ff(a,b,c,d,M[10],17,-42063);
        a = ff(a,b,c,d,M[11],22,-1990404162);
        a = ff(a,b,c,d,M[12],7,1804603682);
        a = ff(a,b,c,d,M[13],12,-40341101);
        a = ff(a,b,c,d,M[14],17,-1502002290);
        a = ff(a,b,c,d,M[15],22,1236535329);

        b = gg(b,c,d,a,M[1],5,-165796510);
        b = gg(b,c,d,a,M[6],9,-1069501632);
        b = gg(b,c,d,a,M[11],14,643717713);
        b = gg(b,c,d,a,M[0],20,-373897302);
        b = gg(b,c,d,a,M[5],5,-701558691);
        b = gg(b,c,d,a,M[10],9,38016083);
        b = gg(b,c,d,a,M[15],14,-660478335);
        b = gg(b,c,d,a,M[4],20,-405537848);
        b = gg(b,c,d,a,M[9],5,568446438);
        b = gg(b,c,d,a,M[14],9,-1019803690);
        b = gg(b,c,d,a,M[3],14,-187363961);
        b = gg(b,c,d,a,M[8],20,1163531501);
        b = gg(b,c,d,a,M[13],5,-1444681467);
        b = gg(b,c,d,a,M[2],9,-51403784);
        b = gg(b,c,d,a,M[7],14,1735328473);
        b = gg(b,c,d,a,M[12],20,-1926607734);

        c = hh(c,d,a,b,M[5],4,-378558);
        c = hh(c,d,a,b,M[8],11,-2022574463);
        c = hh(c,d,a,b,M[11],16,1839030562);
        c = hh(c,d,a,b,M[14],23,-35309556);
        c = hh(c,d,a,b,M[1],4,-1530992060);
        c = hh(c,d,a,b,M[4],11,1272893353);
        c = hh(c,d,a,b,M[7],16,-155497632);
        c = hh(c,d,a,b,M[10],23,-1094730640);
        c = hh(c,d,a,b,M[13],4,681279174);
        c = hh(c,d,a,b,M[0],11,-358537222);
        c = hh(c,d,a,b,M[3],16,-722521979);
        c = hh(c,d,a,b,M[6],23,76029189);
        c = hh(c,d,a,b,M[9],4,-640364487);
        c = hh(c,d,a,b,M[12],11,-421815835);
        c = hh(c,d,a,b,M[15],16,530742520);
        c = hh(c,d,a,b,M[2],23,-995338651);

        d = ii(d,a,b,c,M[0],6,-198630844);
        d = ii(d,a,b,c,M[7],10,1126891415);
        d = ii(d,a,b,c,M[14],15,-1416354905);
        d = ii(d,a,b,c,M[5],21,-57434055);
        d = ii(d,a,b,c,M[12],6,1700485571);
        d = ii(d,a,b,c,M[3],10,-1894986606);
        d = ii(d,a,b,c,M[10],15,-1051523);
        d = ii(d,a,b,c,M[1],21,-2054922799);
        d = ii(d,a,b,c,M[8],6,1873313359);
        d = ii(d,a,b,c,M[15],10,-30611744);
        d = ii(d,a,b,c,M[6],15,-1560198380);
        d = ii(d,a,b,c,M[13],21,1309151649);
        d = ii(d,a,b,c,M[4],6,-145523070);
        d = ii(d,a,b,c,M[11],10,-1120210379);
        d = ii(d,a,b,c,M[2],15,718787259);
        d = ii(d,a,b,c,M[9],21,-343485551);

        a = (a + olda) & 0xFFFFFFFF;
        b = (b + oldb) & 0xFFFFFFFF;
        c = (c + oldc) & 0xFFFFFFFF;
        d = (d + oldd) & 0xFFFFFFFF;
    }

    function toHex(n){
        let s = '', j;
        for (j = 0; j < 4; j++) {
            s += ('0' + ((n >> (j * 8)) & 0xFF).toString(16)).slice(-2);
        }
        return s;
    }
    return toHex(a) + toHex(b) + toHex(c) + toHex(d);
}

function getGravatarUrl(email, size = 80) {
    const normalized = (email || '').trim().toLowerCase();
    const hash = md5(normalized);
    return `https://www.gravatar.com/avatar/${hash}?s=${size}`;
}

function getInitialsDataUrl(name) {
    const text = (name || '').trim();
    const initials = text.split(' ').filter(Boolean).map(n => n[0].toUpperCase()).slice(0,2).join('') || '?';
    const bg = '#D4C5B0';
    const fg = '#3E3E3E';
    const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80'><rect width='100%' height='100%' fill='${bg}'/><text x='50%' y='50%' dy='.35em' font-family='Helvetica, Arial, sans-serif' font-size='36' fill='${fg}' text-anchor='middle'>${initials}</text></svg>`;
    return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
}

async function addToCart(productId) {
    const product = (window.allProducts && window.allProducts.find(p => p.id === productId)) || productsDB[productId];
    if (!product) {
        showNotification('Product not found!', 'error');
        return;
    }

    // Always try server cart first; fall back to local if unauthenticated or server fails
    try {
        const apiResult = await addToCartAPI(productId, 1, '', '');
        if (apiResult && apiResult.success) {
            await updateCartCount(true);
            showNotification(`${product.name} added to cart!`, 'success');
            return;
        }
    } catch (err) {
        console.warn('Add to cart via API failed; falling back to local storage.', err);
    }

    // Fallback for guest or server failure: local cart
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: 1
        });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showNotification(`${product.name} added to cart!`, 'success');
}

async function updateCartCount(forceApi = false) {
    const cartCountElement = document.getElementById('cartCount');
    if (!cartCountElement) return;

    // Try backend count first (even if client session storage is missing)
    try {
        const data = await getCart();
        if (data && data.success) {
            const count = data.item_count ?? (data.cart_items ? data.cart_items.reduce((s, i) => s + (i.quantity || 0), 0) : 0);
            cartCountElement.textContent = count;
            return count;
        }
    } catch (e) {
        // Fall through to local storage
    }

    // Guest/local fallback
    try {
        const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = storedCart.reduce((sum, item) => sum + (item.quantity || 0), 0);
        cartCountElement.textContent = totalItems;
        return totalItems;
    } catch (e) {
        cartCountElement.textContent = '0';
        return 0;
    }
}

function showNotification(message, type = 'success') {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background-color: ${type === 'success' ? '#D4C5B0' : '#e74c3c'};
        color: ${type === 'success' ? '#3E3E3E' : '#fff'};
        padding: 15px 25px;
        border-radius: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .wishlist-btn.active {
        background-color: #ffe6e6 !important;
        color: #e74c3c !important;
    }
`;
document.head.appendChild(style);

window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 50) {
        navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
    } else {
        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    }
});

window.addEventListener('storage', function(e) {
    try {
        if (e.key === 'cart') {
            updateCartCount();
        }
        if (e.key === 'userSession' || e.key === 'userSession') {
            renderUserAvatarInNavbar();
            try { if (typeof updateCheckoutState === 'function') updateCheckoutState(); } catch (err) {}
            syncLocalCartToServer();
        }
    } catch (err) {
        console.error('storage event handler error', err);
    }
});

let currentSlide = 0;
let autoSlideInterval;

function moveCarousel(direction) {
    const carousel = document.querySelector('.products-carousel');
    if (!carousel) return;
    
    clearInterval(autoSlideInterval);
    
    const totalItems = carousel.children.length;
    const visibleCount = 4; // Show 4 cards at a time
    const maxSlide = Math.max(0, totalItems - visibleCount); // Don't show empty slots

    // Don't move if all items fit on one screen
    if (totalItems <= visibleCount) return;

    currentSlide += direction;

    if (currentSlide < 0) {
        currentSlide = maxSlide;
    } else if (currentSlide > maxSlide) {
        currentSlide = 0;
    }
    
    updateCarousel();
    
    startAutoSlide();
}

function updateCarousel() {
    const carousel = document.querySelector('.products-carousel');
    if (!carousel) return;
    
    // Move by individual card width (25%) instead of full viewport
    carousel.style.transform = `translateX(-${currentSlide * 25}%)`;
}

function startAutoSlide() {
    const carousel = document.querySelector('.products-carousel');
    if (!carousel) return;
    
    const totalItems = carousel.children.length;
    const visibleCount = 4;
    
    // Don't auto-slide if all items fit on one screen
    if (totalItems <= visibleCount) return;
    
    autoSlideInterval = setInterval(() => {
        const maxSlide = Math.max(0, totalItems - visibleCount);

        currentSlide++;
        if (currentSlide > maxSlide) {
            currentSlide = 0;
        }

        updateCarousel();
    }, 4000); // Slide every 4 seconds
}

if (document.querySelector('.products-carousel')) {
    const carousel = document.querySelector('.products-carousel');
    const totalItems = carousel.children.length;
    const visibleCount = 4;
    
    // Hide carousel controls if all items fit on one screen
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    
    if (totalItems <= visibleCount) {
        if (prevBtn) prevBtn.classList.add('hidden');
        if (nextBtn) nextBtn.classList.add('hidden');
    } else {
        if (prevBtn) prevBtn.classList.remove('hidden');
        if (nextBtn) nextBtn.classList.remove('hidden');
        startAutoSlide();
        
        const carouselWrapper = document.querySelector('.products-carousel-wrapper');
        if (carouselWrapper) {
            carouselWrapper.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
            });

            carouselWrapper.addEventListener('mouseleave', () => {
                startAutoSlide();
            });
        }
    }
}

let currentLeftSlide = 0;
let currentRightSlide = 0;

function startSideSlideshow(className, currentIndex) {
    const slideshow = document.querySelector(`.${className}`);
    if (!slideshow) return;
    
    const slides = slideshow.querySelectorAll('.side-slide');
    if (slides.length === 0) return;
    
    setInterval(() => {
        slides[currentIndex].classList.remove('active');
        
        currentIndex = (currentIndex + 1) % slides.length;
        
        slides[currentIndex].classList.add('active');
    }, 3500);
}

let currentHeroSlide = 0;

function startHeroSlideshow() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) return;
    
    setInterval(() => {
        slides[currentHeroSlide].classList.remove('active');
        
        currentHeroSlide = (currentHeroSlide + 1) % slides.length;
        
        slides[currentHeroSlide].classList.add('active');
    }, 3500); 
}

if (document.querySelector('.hero-slideshow')) {
    startHeroSlideshow();
}

if (document.querySelector('.left-slideshow')) {
    startSideSlideshow('left-slideshow', currentLeftSlide);
}
if (document.querySelector('.right-slideshow')) {
    startSideSlideshow('right-slideshow', currentRightSlide);
}
