<?php

/**
 * news.php - صفحة الأخبار والمطورين
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/User.php';

if (!User::isLoggedIn()) {
    redirect('index.php');
}

$team = [
    [
        'name' => 'شهد مصطفى حسين محمد',
        'uni_id' => '2420614',
        'phone' => '01147918812',
        'role' => 'Team Leader',
        'badge' => 'Team Leader'
    ],
    [
        'name' => 'محمد حسين حسن',
        'uni_id' => '2421007',
        'phone' => '01141345223',
        'role' => 'Technical Lead / Core Developer',
        'badge' => 'Technical Lead'
    ],
    ['name' => 'عمر محمد رشاد موسى', 'uni_id' => '2420838', 'phone' => '01069116571', 'role' => 'عضو فريق'],
    ['name' => 'رحمه محمد حمدي عبداللطيف', 'uni_id' => '2420440', 'phone' => '01013551785', 'role' => 'عضو فريق'],
    ['name' => 'تسنيم أشرف محمود عبد الفتاح', 'uni_id' => '2420300', 'phone' => '01203042403', 'role' => 'عضو فريق'],
    ['name' => 'إسراء عاطف علي', 'uni_id' => '2420111', 'phone' => '01227597053', 'role' => 'عضو فريق'],
    ['name' => 'شيماء راضي حمد عبد الجليل', 'uni_id' => '2420620', 'phone' => '01014190526', 'role' => 'عضو فريق'],
    ['name' => 'آلاء محمد عبدالرازق', 'uni_id' => '2420264', 'phone' => '01022654367', 'role' => 'عضو فريق'],
    ['name' => 'حازم محمود السيد', 'uni_id' => '2420341', 'phone' => '01092861969', 'role' => 'عضو فريق'],
    ['name' => 'مصطفى محمد رشاد موسى', 'uni_id' => '2421205', 'phone' => '+20 10 99264626', 'role' => 'عضو فريق'],
];

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="فريق العمل والمشروع - <?= e(APP_NAME) ?>">
    <title>فريق المشروع - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
    .news-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .news-card {
        background: white;
        border-radius: 1rem;
        padding: 1.75rem;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(15, 23, 42, 0.05);
        margin-bottom: 1.5rem;
    }

    .news-card h3 {
        margin: 0;
        color: var(--primary);
        font-size: 1.2rem;
    }

    .news-card p {
        color: #4b5563;
        line-height: 1.7;
    }

    .developer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr));
        gap: 1.5rem;
    }

    .developer-card {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.05);
    }

    @media (max-width: 480px) {
        .developer-card {
            padding: 1rem;
        }
    }

    .developer-card .header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .developer-card .avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: grid;
        place-items: center;
        font-size: 1.25rem;
    }

    .developer-card .name {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #111827;
    }

    .developer-card .role {
        color: #4b5563;
        font-size: 0.9rem;
    }

    .developer-card .contact-list {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0;
        display: grid;
        gap: 0.75rem;
    }

    .developer-card .contact-list li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #334155;
    }

    .developer-card .contact-list a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }

    .sponsor-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #ffffff;
        background: #10b981;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        font-size: 0.8rem;
        margin-top: 0.75rem;
    }

    .sponsor-badge i {
        color: #e9f5ef;
    }

    /* Special team card styles */
    .developer-card.team-leader {
        background: linear-gradient(180deg, #fffbf0, #ffffff);
        border: 1px solid #ffd7a5;
    }

    .developer-card.tech-accent {
        border: 1px solid rgba(99, 102, 241, 0.14);
        background: linear-gradient(180deg, rgba(99, 102, 241, 0.03), rgba(99, 102, 241, 0.015));
    }

    .developer-card {
        transition: box-shadow 200ms ease, transform 200ms ease;
        transform-origin: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 180px;
    }

    .developer-grid {
        align-items: stretch;
    }

    /* Hover glow */
    .developer-card:hover {
        box-shadow: 0 18px 40px rgba(99, 102, 241, 0.08), 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    /* Verified badge */
    .verified-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: linear-gradient(180deg, #2563eb, #1e40af);
        color: #fff;
        padding: 0.18rem 0.45rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .verified-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        cursor: default;
        position: relative;
    }

    /* Tooltip */
    .verified-trigger .tooltip {
        position: absolute;
        background: #0f172a;
        color: #fff;
        padding: 0.45rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        transform-origin: top right;
        box-shadow: 0 8px 20px rgba(2, 6, 23, 0.3);
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease, transform 160ms ease;
        transform: translateY(6px) scale(0.98);
        z-index: 40;
    }

    .verified-trigger:hover .tooltip,
    .verified-trigger:focus .tooltip {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    .developer-card {
        transition: transform 160ms ease, box-shadow 160ms ease;
        transform-origin: center;
    }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main class="main-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <section class="content">
            <h3>فريق المشروع</h3>
            <div class="developer-grid">
                <?php foreach ($team as $i => $member): ?>
                <?php
                    $extraClass = '';
                    if (isset($member['badge']) && $member['badge'] === 'Team Leader') $extraClass = 'team-leader';
                    elseif (isset($member['badge']) && $member['badge'] === 'Technical Lead') $extraClass = 'tech-accent';
                    ?>
                <div class="developer-card <?= $extraClass ?>" data-index="<?= $i ?>">
                    <div class="header">
                        <div class="avatar"><i class="fas fa-user"></i></div>
                        <div>
                            <p class="name"><?= e($member['name']) ?>
                                <?php if (isset($member['badge']) && $member['badge'] === 'Technical Lead'): ?>
                                <span class="verified-trigger" tabindex="0" aria-describedby="verified-<?= $i ?>">
                                    <span class="verified-badge" aria-hidden="true"><i
                                            class="fas fa-check-circle"></i></span>
                                    <span id="verified-<?= $i ?>" class="tooltip" role="tooltip">Verified
                                        Developer</span>
                                </span>






                                \

                                <?php endif; ?>
                            </p>
                            <p class="role"><?= e($member['role']) ?></p>
                        </div>
                    </div>

                    <ul class="contact-list">
                        <li><strong>الرقم الجامعي:</strong> <?= e($member['uni_id'] ?? '') ?></li>
                        <?php
                            $phoneRaw = $member['phone'] ?? '';
                            $digits = preg_replace('/\D/', '', $phoneRaw);
                            if ($digits !== '') {
                                if (strpos($digits, '0') === 0) {
                                    $waNumber = '20' . ltrim($digits, '0');
                                } else {
                                    $waNumber = $digits;
                                }
                            } else {
                                $waNumber = '';
                            }
                            ?>
                        <li><i class="fab fa-whatsapp"></i>
                            <?php if ($waNumber): ?>
                            <a href="https://wa.me/<?= $waNumber ?>" target="_blank"><?= e($phoneRaw) ?></a>
                            <?php else: ?>
                            <?= e('غير محدد') ?>
                            <?php endif; ?>
                        </li>
                    </ul>
                    <?php if (isset($member['badge'])): ?>
                    <div class="badge-container">
                        <span class="team-badge"><?= e($member['badge']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php include __DIR__ . '/includes/footer.php'; ?>
        </section>
    </main>
</body>

<script src="https://cdn.jsdelivr.net/npm/@motionone/dom/dist/motion.dom.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.developer-grid .developer-card');
    if (window.motion) {
        motion.animate(cards, {
            opacity: [0, 1],
            transform: ['translateY(12px)', 'translateY(0px)']
        }, {
            duration: 0.6,
            easing: 'ease-out',
            delay: 0,
            stagger: 0.06
        });

        // hover glow via motion for a subtle pop (no scale change)
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                motion.animate(card, {
                    boxShadow: ['0 6px 18px rgba(2,6,23,0.06)',
                        '0 18px 40px rgba(99,102,241,0.08), 0 6px 18px rgba(15,23,42,0.06)'
                    ]
                }, {
                    duration: 240
                });
            });
            card.addEventListener('mouseleave', () => {
                motion.animate(card, {
                    boxShadow: [
                        '0 18px 40px rgba(99,102,241,0.08), 0 6px 18px rgba(15,23,42,0.06)',
                        '0 6px 18px rgba(2,6,23,0.06)'
                    ]
                }, {
                    duration: 240
                });
            });
        });
    }
});
</script>

</html>