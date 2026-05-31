
    (function() {
        const passwordInput = document.getElementById('signupPassword');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        if (!passwordInput || !strengthFill || !strengthText) return;

        const levels = [
            { text: 'بسیار ضعیف', color: '#E74C3C', width: '15%' },
            { text: 'ضعیف', color: '#E67E22', width: '30%' },
            { text: 'متوسط', color: '#F39C12', width: '55%' },
            { text: 'خوب', color: '#2ECC71', width: '75%' },
            { text: 'بسیار قوی', color: '#27AE60', width: '100%' }
        ];

        passwordInput.addEventListener('input', function() {
            const pass = this.value;

            if (!pass) {
                strengthFill.style.width = '0';
                strengthFill.style.background = '#ddd';
                strengthText.textContent = 'قدرت رمز عبور';
                strengthText.style.color = '';
                return;
            }

            let score = 0;
            if (pass.length >= 6) score++;
            if (pass.length >= 10) score++;
            if (/[A-Z]/.test(pass)) score++;
            if (/[a-z]/.test(pass)) score++;
            if (/[0-9]/.test(pass)) score++;
            if (/[^A-Za-z0-9]/.test(pass)) score++;

            let levelIndex;
            if (score <= 1) levelIndex = 0;
            else if (score === 2) levelIndex = 1;
            else if (score === 3) levelIndex = 2;
            else if (score === 4) levelIndex = 3;
            else levelIndex = 4;

            const level = levels[levelIndex];

            strengthFill.style.width = level.width;
            strengthFill.style.background = level.color;
            strengthText.textContent = level.text;
            strengthText.style.color = level.color;
        });
    })();
