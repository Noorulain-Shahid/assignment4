document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    clearErrors();
    
    let isValid = true;
    
    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const terms = document.getElementById('terms').checked;
    
    // Validation
    if (fullName === '' || fullName.length < 3) {
        showError('fullName', 'Full name must be at least 3 characters');
        isValid = false;
    } else {
        showSuccess('fullName');
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess('email');
    }
    
    const cleanedPhone = phone.replace(/\D/g, '');
    if (cleanedPhone && !/^[0-9]{10,15}$/.test(cleanedPhone)) {
        showError('phone', 'Please enter a valid phone number');
        isValid = false;
    } else {
        showSuccess('phone');
    }
    
    if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        isValid = false;
    } else {
        showSuccess('password');
    }
    
    if (password !== confirmPassword) {
        showError('confirmPassword', 'Passwords do not match');
        isValid = false;
    } else {
        showSuccess('confirmPassword');
    }
    
    if (!terms) {
        showError('terms', 'You must accept the terms and conditions');
        isValid = false;
    }
    
    if (isValid) {
        const submitBtn = this.querySelector('.auth-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
        submitBtn.disabled = true;
        
        try {
            // Generate username from email
            const username = email.split('@')[0];
            
            // Call backend API
            const response = await fetch('api/signup.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: username,
                    email: email,
                    password: password,
                    full_name: fullName,
                    phone: phone
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store user session
                const sessionData = {
                    id: data.user.id,
                    username: data.user.username,
                    email: data.user.email,
                    full_name: data.user.full_name,
                    loggedIn: true,
                    loginTime: new Date().toISOString()
                };
                
                localStorage.setItem('userSession', JSON.stringify(sessionData));
                localStorage.setItem('sessionToken', data.session_token);
                
                showNotification('Account created successfully! Redirecting...', 'success');
                
                this.reset();
                clearErrors();
                
                setTimeout(() => {
                    window.location.href = 'home.html';
                }, 1500);
            } else {
                showError('email', data.message || 'Registration failed');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showNotification(data.message || 'Registration failed!', 'error');
            }
        } catch (error) {
            console.error('Signup error:', error);
            showError('email', 'Registration failed. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            showNotification('Registration failed. Please check your connection.', 'error');
        }
    }
});

function showError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    if (input && errorElement) {
        input.classList.add('error');
        input.classList.remove('success');
        errorElement.textContent = message;
    }
}

function showSuccess(fieldId) {
    const input = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    if (input && errorElement) {
        input.classList.add('success');
        input.classList.remove('error');
        errorElement.textContent = '';
    }
}

function clearErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(el => el.textContent = '');
    
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.classList.remove('error', 'success');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = event.currentTarget;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Phone number formatting
document.getElementById('phone')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
        if (value.length <= 3) {
            e.target.value = value;
        } else if (value.length <= 6) {
            e.target.value = value.slice(0, 3) + '-' + value.slice(3);
        } else {
            e.target.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
        }
    }
});

// Set max date for DOB (must be at least 13 years old)
const dobInput = document.getElementById('dob');
if (dobInput) {
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
    const minDate = new Date(today.getFullYear() - 120, today.getMonth(), today.getDate());
    dobInput.max = maxDate.toISOString().split('T')[0];
    dobInput.min = minDate.toISOString().split('T')[0];
}
