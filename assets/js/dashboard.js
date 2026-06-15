/**
 * dashboard.js - تفاعلات لوحة التحكم
 */
document.addEventListener('DOMContentLoaded', () => {
    // إضافة تفاعل بسيط للضغط على الكروت لنقل المستخدم إذا كان هناك رابط مخصص
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('click', (e) => {
            // إذا لم يكن الهدف هو رابط مباشر بداخل الكارت، نقوم بتوجيه المستخدم لرابط الكارت نفسه
            if (e.target.tagName !== 'A' && e.target.closest('a') === null) {
                const href = card.getAttribute('href');
                if (href && href !== '#') {
                    window.location.href = href;
                }
            }
        });
    });

    // إضافة تأثير بسيط عند مرور مؤشر الفأرة على الأرقام الإحصائية
    const statNums = document.querySelectorAll('.stat-num');
    statNums.forEach(num => {
        num.addEventListener('mouseenter', () => {
            num.style.transform = 'scale(1.1)';
            num.style.transition = 'transform 0.2s ease';
        });
        num.addEventListener('mouseleave', () => {
            num.style.transform = 'scale(1)';
        });
    });
});
