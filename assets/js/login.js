
    (function() {
        const passwordInput = document.getElementById('loginPassword');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const eyeOpen = toggleBtn.querySelector('.eye-open');
        const eyeClosed = toggleBtn.querySelector('.eye-closed');

        let isPasswordVisible = false;

        toggleBtn.addEventListener('click', function() {
            isPasswordVisible = !isPasswordVisible;

            if (isPasswordVisible) {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
                toggleBtn.setAttribute('aria-label', 'مخفی کردن رمز عبور');
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
                toggleBtn.setAttribute('aria-label', 'نمایش رمز عبور');
            }
        });

        const loginInput = document.getElementById('loginInput');
        if (loginInput && !loginInput.value) {
            loginInput.focus();
        }
    })();
