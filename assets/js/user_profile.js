
    (function() {
        window.switchTab = function(tab) {
            var navBtnInfo = document.getElementById('navBtnInfo');
            var navBtnPassword = document.getElementById('navBtnPassword');
            var tabInfo = document.getElementById('tabInfo');
            var tabPassword = document.getElementById('tabPassword');

            if (tab === 'info') {
                navBtnInfo.classList.add('profile-nav__btn--active');
                navBtnPassword.classList.remove('profile-nav__btn--active');
                tabInfo.style.display = 'block';
                tabPassword.style.display = 'none';
            } else {
                navBtnPassword.classList.add('profile-nav__btn--active');
                navBtnInfo.classList.remove('profile-nav__btn--active');
                tabPassword.style.display = 'block';
                tabInfo.style.display = 'none';
            }
        };

        var toggleCurrent = document.getElementById('toggleCurrent');
        var currentPassword = document.getElementById('currentPassword');

        if (toggleCurrent && currentPassword) {
            var eyeOpen = toggleCurrent.querySelector('.eye-open');
            var eyeClosed = toggleCurrent.querySelector('.eye-closed');
            var visible = false;

            toggleCurrent.addEventListener('click', function() {
                visible = !visible;
                if (visible) {
                    currentPassword.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    currentPassword.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });
        }

        var newPasswordInput = document.getElementById('newPassword');
        var strengthFill = document.getElementById('strengthFill');
        var strengthText = document.getElementById('strengthText');

        if (newPasswordInput && strengthFill && strengthText) {
            var levels = [{
                    text: 'بسیار ضعیف',
                    color: '#E74C3C',
                    width: '15%'
                },
                {
                    text: 'ضعیف',
                    color: '#E67E22',
                    width: '30%'
                },
                {
                    text: 'متوسط',
                    color: '#F39C12',
                    width: '55%'
                },
                {
                    text: 'خوب',
                    color: '#2ECC71',
                    width: '75%'
                },
                {
                    text: 'بسیار قوی',
                    color: '#27AE60',
                    width: '100%'
                }
            ];

            newPasswordInput.addEventListener('input', function() {
                var pass = this.value;

                if (!pass) {
                    strengthFill.style.width = '0';
                    strengthFill.style.background = '#ddd';
                    strengthText.textContent = 'قدرت رمز عبور';
                    strengthText.style.color = '';
                    return;
                }

                var score = 0;
                if (pass.length >= 6) score++;
                if (pass.length >= 10) score++;
                if (/[A-Z]/.test(pass)) score++;
                if (/[a-z]/.test(pass)) score++;
                if (/[0-9]/.test(pass)) score++;
                if (/[^A-Za-z0-9]/.test(pass)) score++;

                var idx;
                if (score <= 1) idx = 0;
                else if (score === 2) idx = 1;
                else if (score === 3) idx = 2;
                else if (score === 4) idx = 3;
                else idx = 4;

                var level = levels[idx];
                strengthFill.style.width = level.width;
                strengthFill.style.background = level.color;
                strengthText.textContent = level.text;
                strengthText.style.color = level.color;
            });
        }

        var successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-6px)';
                successAlert.style.transition = 'all 0.3s ease-out';
                setTimeout(function() {
                    if (successAlert.parentNode) successAlert.remove();
                }, 300);
            }, 4000);
        }
    })();
