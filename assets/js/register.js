/**
 * register.js - التحقق المباشر وإدارة واجهة صفحة التسجيل
 */
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const fullnameInput = document.getElementById('fullname');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
        const phoneInput = document.getElementById('phone');
    
    const fullnameFeedback = document.getElementById('fullnameFeedback');
    const usernameFeedback = document.getElementById('usernameFeedback');
    const emailFeedback = document.getElementById('emailFeedback');
    const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');
        const phoneFeedback = document.getElementById('phoneFeedback');
    
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');
    const roleSelect = document.getElementById('role');

    // إظهار وإخفاء كلمة المرور
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            togglePasswordBtn.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
        });
    }

    // قياس قوة كلمة المرور
    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let score = 0;

            if (val.length >= 6) score += 1;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score += 1;
            if (/\d/.test(val)) score += 1;
            if (/[^a-zA-Z\d]/.test(val)) score += 1;

            let width = '0%';
            let color = 'transparent';
            let label = 'قوة كلمة المرور';

            if (val.length > 0) {
                if (score === 0 || score === 1) {
                    width = '25%';
                    color = '#ef4444'; // أحمر
                    label = 'ضعيفة جداً';
                } else if (score === 2) {
                    width = '50%';
                    color = '#f97316'; // برتقالي
                    label = 'ضعيفة';
                } else if (score === 3) {
                    width = '75%';
                    color = '#3b82f6'; // أزرق
                    label = 'متوسطة';
                } else if (score === 4) {
                    width = '100%';
                    color = '#10b981'; // أخضر
                    label = 'قوية';
                }
            }

            strengthBar.style.width = width;
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = `قوة كلمة المرور: ${label}`;
            strengthText.style.color = color || 'var(--muted)';
        });
    }

    // التحقق المباشر من تطابق كلمات المرور
    if (confirmPasswordInput && passwordInput) {
        const checkPasswords = () => {
            if (confirmPasswordInput.value === '') {
                confirmPasswordFeedback.textContent = '';
            } else if (confirmPasswordInput.value !== passwordInput.value) {
                confirmPasswordFeedback.textContent = 'كلمتا المرور غير متطابقتين.';
                confirmPasswordFeedback.style.color = '#ef4444';
            } else {
                confirmPasswordFeedback.textContent = 'كلمتا المرور متطابقتان.';
                confirmPasswordFeedback.style.color = '#10b981';
            }
        };
        confirmPasswordInput.addEventListener('input', checkPasswords);
        passwordInput.addEventListener('input', checkPasswords);
    }

    // فحص البيانات المكررة عبر AJAX
    if (usernameInput) {
        usernameInput.addEventListener('blur', () => {
            const username = usernameInput.value.trim();
            if (username === '') {
                usernameFeedback.textContent = '';
                return;
            }
            if (username.length < 3) {
                usernameFeedback.textContent = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل.';
                usernameFeedback.style.color = '#ef4444';
                return;
            }
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                usernameFeedback.textContent = 'اسم المستخدم يجب أن يحتوي على أحرف وأرقام وشرطة سفلية فقط.';
                usernameFeedback.style.color = '#ef4444';
                return;
            }
            
            fetch(`app/check_duplicate.php?username=${encodeURIComponent(username)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.username_taken) {
                        usernameFeedback.textContent = 'اسم المستخدم هذا غير متاح.';
                        usernameFeedback.style.color = '#ef4444';
                    } else {
                        usernameFeedback.textContent = 'اسم المستخدم متاح.';
                        usernameFeedback.style.color = '#10b981';
                    }
                })
                .catch(() => {
                    usernameFeedback.textContent = '';
                });
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', () => {
            const email = emailInput.value.trim();
            if (email === '') {
                emailFeedback.textContent = '';
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailFeedback.textContent = 'البريد الإلكتروني المدخل غير صالح.';
                emailFeedback.style.color = '#ef4444';
                return;
            }

            fetch(`app/check_duplicate.php?email=${encodeURIComponent(email)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.email_taken) {
                        emailFeedback.textContent = 'البريد الإلكتروني هذا مستخدم بالفعل.';
                        emailFeedback.style.color = '#ef4444';
                    } else {
                        emailFeedback.textContent = 'البريد الإلكتروني متاح.';
                        emailFeedback.style.color = '#10b981';
                    }
                })
                .catch(() => {
                    emailFeedback.textContent = '';
                });
        });
    }

    // منع إرسال النموذج مكرراً والتحقق النهائي
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const fullname = fullnameInput.value.trim();
            const username = usernameInput.value.trim();
            const email = emailInput.value.trim();
            const phone = phoneInput ? phoneInput.value.trim() : '';
            const role = roleSelect ? roleSelect.value : 'patient';
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            let hasError = false;

            if (fullname === '' || username === '' || email === '' || password === '' || confirmPassword === '') {
                alert('يرجى ملء جميع الحقول المطلوبة.');
                hasError = true;
            } else if (!['patient', 'doc'].includes(role)) {
                alert('يرجى اختيار نوع حساب صحيح.');
                hasError = true;
            } else if (password !== confirmPassword) {
                alert('كلمتا المرور غير متطابقتين.');
                hasError = true;
            }

            // فحص الهاتف باختصار (اختياري، إنْ وُجد)
            if (phone !== '' && !/^[0-9+\-\s()]{6,20}$/.test(phone)) {
                if (phoneFeedback) {
                    phoneFeedback.textContent = 'رقم الهاتف غير صالح.';
                    phoneFeedback.style.color = '#ef4444';
                }
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            } else {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التسجيل...';
            }
        });
    }
});
