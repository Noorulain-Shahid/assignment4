document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    if (!name || !email || !subject || !message) {
        showNotification('Please fill in all required fields!', 'error');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Please enter a valid email address!', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    
    setTimeout(() => {
        const contacts = JSON.parse(localStorage.getItem('contacts')) || [];
        contacts.push({
            name,
            email,
            phone,
            subject,
            message,
            date: new Date().toISOString()
        });
        localStorage.setItem('contacts', JSON.stringify(contacts));
        
        this.reset();
        
        showNotification('Thank you! Your message has been sent successfully. We\'ll get back to you soon!', 'success');
        
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 1500);
});

const inputs = document.querySelectorAll('.contact-form input, .contact-form select, .contact-form textarea');
inputs.forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() === '' && this.hasAttribute('required')) {
            this.style.borderColor = '#e74c3c';
        } else {
            this.style.borderColor = 'var(--dark-beige)';
        }
    });
    
    input.addEventListener('focus', function() {
        this.style.borderColor = 'var(--accent-beige)';
    });
});
