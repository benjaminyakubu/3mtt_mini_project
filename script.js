// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length > 7) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    const indicator = document.getElementById('strengthIndicator');
    if (indicator) {
        indicator.className = `strength strength-${strength}`;
        indicator.textContent = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength] || 'Very Weak';
    }
}

// Generate strong password
function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 16; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('newPassword').value = password;
    checkPasswordStrength(password);
}

// Show decrypted password
function showPassword(passwordId) {
    const masterPass = prompt('Enter your master password to decrypt:');
    if (!masterPass) return;
    
    fetch('passwords.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=decrypt&password_id=${passwordId}&master_pass=${encodeURIComponent(masterPass)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const span = document.getElementById(`password_${passwordId}`);
            span.innerHTML = `<strong>${data.password}</strong>`;
            span.className = 'password-shown';
            setTimeout(() => {
                span.innerHTML = '';
                span.className = 'password-hidden';
            }, 5000);
        } else {
            alert('Invalid master password!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error decrypting password');
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('newPassword');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
});
