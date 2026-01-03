document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    clearErrors();
    
    let isValid = true;
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess('email');
    }
    
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        isValid = false;
    } else {
        showSuccess('password');
    }
    
    if (isValid) {
        const submitBtn = this.querySelector('.auth-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        submitBtn.disabled = true;
        
        try {
            // Use backend API for login
            const response = await fetch('api/login.php?v=' + Date.now(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            });
            
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
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
                
                if (rememberMe) {
                    localStorage.setItem('userSession', JSON.stringify(sessionData));
                    localStorage.setItem('sessionToken', data.session_token);
                } else {
                    sessionStorage.setItem('userSession', JSON.stringify(sessionData));
                }
                
                showNotification('Login successful! Redirecting...', 'success');
                
                this.reset();
                clearErrors();
                
                setTimeout(() => {
                    window.location.href = 'home.html';
                }, 1500);
            } else {
                showError('email', data.message || 'Invalid email or password');
                showError('password', data.message || 'Invalid email or password');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showNotification(data.message || 'Invalid email or password!', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            showError('email', 'Login failed. Please try again.');
            showError('password', 'Login failed. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            showNotification('Login failed. Please check your connection.', 'error');
        }
    }
});

function showError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    input.classList.add('error');
    input.classList.remove('success');
    errorElement.textContent = message;
}

function showSuccess(fieldId) {
    const input = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    input.classList.add('success');
    input.classList.remove('error');
    errorElement.textContent = '';
}

function clearErrors() {
    const inputs = document.querySelectorAll('.auth-form input');
    inputs.forEach(input => {
        input.classList.remove('error', 'success');
    });
    
    const errors = document.querySelectorAll('.error-message');
    errors.forEach(error => error.textContent = '');
}

function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = input.parentElement.querySelector('.toggle-password');
    
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

document.addEventListener('DOMContentLoaded', function() {
    const session = localStorage.getItem('userSession') || sessionStorage.getItem('userSession');
    if (session) {
        const userData = JSON.parse(session);
        showNotification(`Welcome back, ${userData.fullName}! Redirecting...`, 'success');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1500);
    }
});
