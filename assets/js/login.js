/**
 * login.js - تفاعلات صفحة تسجيل الدخول والتحقق
 */
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', (e) => {
            // التحقق من المدخلات قبل الإرسال
            const username = usernameInput.value.trim();
            const password = passwordInput.value;

            if (username === '' || password === '') {
                e.preventDefault();
                alert('يرجى كتابة اسم المستخدم/البريد الإلكتروني وكلمة المرور.');
                return;
            }

            // تعطيل الزر لمنع الضغط المكرر وتغيير حالته البصرية
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
        });
    }
});
