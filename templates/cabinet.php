<?php
$pageTitle = 'Личный кабинет';
$paymentSuccess = $GLOBALS['payment_success'] ?? null;
$db = $GLOBALS['db'];
$dashboard = new Dashboard($db);
$analyses = $dashboard->getHistory(Auth::id());
$viewingDashboard = isset($data) ? $data : null;
$noindex = true;
ob_start();
?>

<div id="terminal-header" style="font-family:'JetBrains Mono',monospace; font-size:13px; color:var(--text-dim); margin-bottom:24px; padding:12px 16px; background:var(--bg-card); border:1px solid var(--border); border-radius:4px; letter-spacing:0.5px; height:100px; overflow-y:auto; display:flex; flex-direction:column;">
    <div style="flex-shrink:0;">
        <span style="color:var(--accent);">kbu@analytics</span>:<span style="color:var(--text);">~</span>$ <span id="terminal-status">ожидание ввода...</span>
        <span id="terminal-cursor" style="color:var(--accent); display:none;">█</span>
    </div>
    <div id="terminal-lines" style="flex:1;"></div>
    <div id="terminal-spinner" style="display:none; flex-shrink:0;">
        <span id="spinner-char" style="color:var(--accent);">>⠋</span> <span id="spinner-status">Инициализация...</span>
    </div>
</div>

<div id="content-area">
    <?php if ($viewingDashboard): ?>
        <div id="dashboard-container">
            <?= $dashboard->renderHTML($viewingDashboard) ?>
        </div>
        <div style="text-align:center; margin-top:20px;">
            <a href="/" class="btn-primary" style="display:inline-block; text-decoration:none;">← Новый анализ</a>
        </div>
    <?php else: ?>
        <div class="card" style="text-align:center; margin:0 auto 24px;">
            <div class="card-label">Новый анализ</div>
            <form id="analyze-form" style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
                <input type="number" name="value" id="analyze-input" class="form-input"
                    placeholder="Введите значимую цифру..."
                    style="flex:1; min-width:180px; font-size:18px; letter-spacing:2px;"
                    required autofocus>
                <button type="submit" class="btn-primary" id="analyze-btn">▶ Анализ</button>
            </form>
        </div>

        <div class="card">
            <div class="card-label">История анализов</div>
            <?php if (empty($analyses)): ?>
                <p style="color:var(--text-dim); text-align:center; padding:20px 0;">
                    У вас пока нет ни одного анализа. Введите число выше и нажмите «Анализ».
                </p>
            <?php else: ?>
                <div class="history">
                    <?php foreach ($analyses as $a): ?>
                        <div class="block-history" style="background: var(--bg);"
                            onmouseenter="this.style.borderColor='var(--accent)';"
                            onmouseleave="this.style.borderColor='var(--border)';">

                            <!-- Дата + число + КБУ (основная инфа) -->
                            <div class="kbu">
                                <div style="font-size:11px; color:var(--text-dim); margin-bottom:4px;">
                                    <?= date('d.m.Y H:i', strtotime($a['created_at'])) ?>
                                </div>
                                <div style="font-family:'JetBrains Mono',monospace; font-size:16px; color:var(--accent);">
                                    <?= htmlspecialchars($a['input_value']) ?>
                                    <span style="font-size:12px; color:<?= match ($a['dynamics']) {
                                                                            'растёт ▲' => 'var(--accent)',
                                                                            'падает ▼' => 'var(--error)',
                                                                            default => 'var(--text-dim)',
                                                                        } ?>; margin-left:6px;">
                                        КБУ <?= $a['kbu'] ?> <?= $a['dynamics'] ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Кнопки действий -->
                            <div style="display:flex; gap:8px; flex-shrink:0;">
                                <a href="/dashboard/<?= $a['public_hash'] ?>"
                                    style="font-size:11px; color:var(--accent); text-decoration:none; padding:6px 12px; border:1px solid var(--accent); border-radius:3px; display: flex; white-space:nowrap; align-items:center; justify-content:center;"
                                    onmouseenter="this.style.background='var(--accent)';this.style.color='var(--bg)';"
                                    onmouseleave="this.style.background='transparent';this.style.color='var(--accent)';">
                                    Смотреть
                                </a>
                                <button onclick="copyToClipboard('https://brednia.ru/p/<?= $a['public_hash'] ?>', 'copy-history-<?= $a['id'] ?>')"
                                    id="copy-history-<?= $a['id'] ?>"
                                    style="background:transparent; border:1px solid var(--border); color:var(--text-dim); padding:8px 12px; border-radius:3px; cursor:pointer; transition:all 0.3s; white-space:nowrap; display:inline-flex; align-items:center; justify-content:center;"
                                    onmouseenter="this.style.borderColor='var(--accent)';this.style.color='var(--accent)';"
                                    onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)';"
                                    title="Скопировать публичную ссылку">Поделится
                                    <svg style="width:16px;height:16px;display:inline; margin-left: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div id="app-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:var(--bg-card); border:1px solid var(--accent); border-radius:4px; padding:24px; max-width:420px; width:90%; text-align:center; box-shadow:0 0 40px rgba(0,255,65,0.15);">
            <div id="modal-title" style="font-family:'JetBrains Mono',monospace; font-size:10px; text-transform:uppercase; letter-spacing:2px; color:var(--text-dim); margin-bottom:16px;">
                Сообщение
            </div>
            <p id="modal-message" style="color:var(--text); font-size:14px; line-height:1.6; margin-bottom:20px;"></p>
            <div id="modal-buttons" style="display:flex; gap:12px; justify-content:center;"></div>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['new_achievements'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const achievements = <?= json_encode($_SESSION['new_achievements']) ?>;
            showAchievementNotification(achievements);
        });
    </script>
    <?php unset($_SESSION['new_achievements']); ?>
<?php endif; ?>

<script>
    function showAchievementNotification(achievements) {
        if (!achievements || achievements.length === 0) return;
        const container = document.createElement('div');
        container.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:3000; display:flex; flex-direction:column; gap:10px; max-width:350px;';
        achievements.forEach(ach => {
            const card = document.createElement('div');
            card.style.cssText = 'background:var(--bg-card); border:1px solid var(--accent); border-radius:8px; padding:16px 20px; box-shadow:0 0 40px rgba(0,255,65,0.2); animation:slideIn 0.5s ease; display:flex; align-items:center; gap:12px;';
            card.innerHTML = `
            <div style="font-size:28px;">${ach.icon || '🎯'}</div>
            <div>
                <div style="font-size:11px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px;">Новое достижение!</div>
                <div style="font-weight:600; color:var(--text); font-size:15px;">${ach.name}</div>
                <div style="font-size:11px; color:var(--text-dim);">${ach.description}</div>
            </div>
        `;
            container.appendChild(card);
        });
        document.body.appendChild(container);
        setTimeout(() => container.remove(), 8000);
    }
    const style = document.createElement('style');
    style.textContent = '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }';
    document.head.appendChild(style);
</script>

<?php if ($paymentSuccess): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('✅ Оплата прошла', <?= json_encode($paymentSuccess) ?>, [{
                text: 'Отлично',
                value: 'ok',
                primary: true
            }, ]);
        });
    </script>
<?php endif; ?>

<style>
    @keyframes blink {
        50% {
            opacity: 0;
        }
    }

    .card-corner-badge {
        position: absolute;
        top: 8px;
        right: 12px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        color: var(--text-dim);
        opacity: 0.6;
    }
</style>

<script>
    let modalResolve = null;

    function showModal(title, message, buttons) {
        return new Promise((resolve) => {
            const modal = document.getElementById('app-modal');
            const titleEl = document.getElementById('modal-title');
            const msgEl = document.getElementById('modal-message');
            const btnsEl = document.getElementById('modal-buttons');

            titleEl.textContent = title;
            msgEl.textContent = message;
            btnsEl.innerHTML = '';

            modal.style.display = 'flex';

            buttons.forEach(btn => {
                const el = document.createElement('button');
                el.textContent = btn.text;
                el.style.cssText = btn.primary ?
                    'padding:10px 20px; font-size:13px; background:transparent; border:1px solid var(--accent); color:var(--accent); font-family:\'JetBrains Mono\',monospace; cursor:pointer; border-radius:3px; transition:all 0.3s;' :
                    'padding:10px 20px; font-size:13px; background:transparent; border:1px solid var(--text-dim); color:var(--text-dim); font-family:\'JetBrains Mono\',monospace; cursor:pointer; border-radius:3px; transition:all 0.3s;';
                el.addEventListener('mouseenter', () => {
                    el.style.borderColor = 'var(--accent)';
                    el.style.color = 'var(--accent)';
                    el.style.boxShadow = '0 0 12px rgba(0,255,65,0.2)';
                });
                el.addEventListener('mouseleave', () => {
                    el.style.borderColor = btn.primary ? 'var(--accent)' : 'var(--text-dim)';
                    el.style.color = btn.primary ? 'var(--accent)' : 'var(--text-dim)';
                    el.style.boxShadow = 'none';
                });
                el.addEventListener('click', () => {
                    modal.style.display = 'none';
                    resolve(btn.value);
                });
                btnsEl.appendChild(el);
            });
        });
    }

    const spinnerFrames = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

    function startThinking(phrases, callback) {
        const terminalStatus = document.getElementById('terminal-status');
        const terminalCursor = document.getElementById('terminal-cursor');
        const terminalLines = document.getElementById('terminal-lines');
        const terminalSpinner = document.getElementById('terminal-spinner');
        const spinnerChar = document.getElementById('spinner-char');
        const spinnerStatus = document.getElementById('spinner-status');
        const terminalHeader = document.getElementById('terminal-header');

        terminalStatus.textContent = 'инициализация анализа...';
        terminalCursor.style.display = 'inline';
        terminalCursor.style.animation = 'blink 1s step-end infinite';
        terminalLines.innerHTML = '';
        terminalSpinner.style.display = 'block';

        const totalDuration = 4000 + Math.random() * 2000;
        const delayPerLine = totalDuration / phrases.length;
        let lineIndex = 0;
        let spinnerFrame = 0;

        const spinnerInterval = setInterval(() => {
            spinnerFrame = (spinnerFrame + 1) % spinnerFrames.length;
            spinnerChar.textContent = spinnerFrames[spinnerFrame];
        }, 100);

        function addNextLine() {
            if (lineIndex >= phrases.length) {
                clearInterval(spinnerInterval);
                spinnerChar.textContent = '✓';
                spinnerStatus.textContent = 'Вывод результата';
                spinnerStatus.style.color = 'var(--accent)';
                terminalCursor.style.display = 'none';
                terminalStatus.textContent = 'анализ завершён успешно';
                terminalHeader.scrollTop = terminalHeader.scrollHeight;
                setTimeout(callback, 400);
                return;
            }

            const line = phrases[lineIndex];
            const isLast = lineIndex === phrases.length - 1;
            const div = document.createElement('div');
            div.style.opacity = '0';
            div.style.transition = 'opacity 0.8s';
            div.textContent = '  ✓ ' + line;
            if (isLast) div.style.color = 'var(--accent)';
            terminalLines.appendChild(div);
            spinnerStatus.textContent = line;
            terminalHeader.scrollTop = terminalHeader.scrollHeight;
            requestAnimationFrame(() => {
                div.style.opacity = '1';
            });
            lineIndex++;
            setTimeout(addNextLine, delayPerLine);
        }

        addNextLine();
    }

    document.getElementById('analyze-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('analyze-input').value.trim();
        const btn = document.getElementById('analyze-btn');

        if (input === '') return;
        if (!/^-?\d+([.,]\d+)?$/.test(input.replace(',', '.'))) {
            showModal('⚠ Некорректный ввод', 'Введите число. Анализ работает только с числами.', [{
                text: 'Понятно',
                value: 'ok',
                primary: true
            }, ]);
            document.getElementById('analyze-input').value = '';
            return;
        }

        const normalizedInput = input.replace(',', '.');
        btn.textContent = '⌛ АНАЛИЗ...';
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.6';

        fetch('/thinking-phrases')
            .then(r => r.json())
            .then(phrases => {
                startThinking(phrases, function() {
                    fetch('/analyze', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'value=' + encodeURIComponent(normalizedInput),
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.error === 'insufficient_funds') {
                                window.location.href = '/topup';
                                return;
                            }
                            if (data.error === 'duplicate') {
                                showModal('⚠ Обнаружен повтор', data.message, [{
                                        text: 'Отмена',
                                        value: false
                                    },
                                    {
                                        text: 'Да',
                                        value: true,
                                        primary: true
                                    },
                                ]).then(confirmed => {
                                    if (confirmed) {
                                        fetch('/analyze', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: 'value=' + encodeURIComponent(input) + '&force=true',
                                            })
                                            .then(r => r.json())
                                            .then(data2 => {
                                                document.getElementById('content-area').innerHTML = data2.html;
                                                updateBalance();
                                            });
                                    } else {
                                        const btn = document.getElementById('analyze-btn');
                                        if (btn) {
                                            btn.textContent = '▶ Анализ';
                                            btn.style.pointerEvents = 'auto';
                                            btn.style.opacity = '1';
                                        }
                                    }
                                });
                                return;
                            }
                            document.getElementById('content-area').innerHTML = data.html;
                            updateBalance();
                            if (data.achievements && data.achievements.length > 0) {
                                showAchievementNotification(data.achievements);
                            }
                        })
                        .catch(() => {
                            btn.textContent = '▶ Анализ';
                            btn.style.pointerEvents = 'auto';
                            btn.style.opacity = '1';
                        });
                });
            });
    });

    function updateBalance() {
        fetch('/api/balance')
            .then(r => r.json())
            .then(data => {
                const el = document.querySelector('.wallet-balance');
                if (el) {
                    el.textContent = data.balance;
                }
            });
    }

    function toggleTooltip(event) {
        event.stopPropagation();
        const trigger = event.currentTarget;
        document.querySelectorAll('.tooltip-trigger.active').forEach(el => {
            if (el !== trigger) el.classList.remove('active');
        });
        trigger.classList.toggle('active');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tooltip-trigger')) {
            document.querySelectorAll('.tooltip-trigger.active').forEach(el => el.classList.remove('active'));
        }
    });

    function copyToClipboard(text, elementId) {
        navigator.clipboard.writeText(text).then(function() {
            const btn = document.getElementById(elementId);
            if (btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<svg style="width:14px;height:14px;display:inline;vertical-align:middle;" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                btn.style.borderColor = 'var(--accent)';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.borderColor = 'var(--border)';
                }, 1500);
            }
        }).catch(function() {});
    }
</script>

<style>
    @keyframes glitchOverlay {
        0% {
            opacity: 0;
            transform: scale(0.8);
        }

        20% {
            transform: scale(1.1);
        }

        40% {
            transform: scale(0.9);
        }

        60% {
            transform: scale(1.05);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes glitchCard {
        0% {
            transform: translate(10px, -10px) rotate(2deg);
        }

        10% {
            transform: translate(-10px, 10px) rotate(-2deg);
        }

        20% {
            transform: translate(5px, -5px) rotate(1deg);
        }

        30% {
            transform: translate(-5px, 5px) rotate(-1deg);
        }

        40% {
            transform: translate(3px, -3px);
        }

        50% {
            transform: translate(-3px, 3px);
        }

        60% {
            transform: translate(2px, -2px);
        }

        70% {
            transform: translate(-2px, 2px);
        }

        100% {
            transform: translate(0, 0);
        }
    }

    @keyframes glitchBorder {
        0% {
            transform: translate(-2px, 2px);
        }

        50% {
            transform: translate(2px, -2px);
        }

        100% {
            transform: translate(-2px, 2px);
        }
    }

    @keyframes glitchText {
        0% {
            transform: translate(-1px, 1px) skewX(-2deg);
        }

        25% {
            transform: translate(1px, -1px) skewX(2deg);
        }

        50% {
            transform: translate(-1px, -1px) skewX(-1deg);
        }

        75% {
            transform: translate(1px, 1px) skewX(1deg);
        }

        100% {
            transform: translate(0, 0);
        }
    }

    @keyframes spinGlitch {
        0% {
            transform: rotate(0deg) scale(0);
            opacity: 0;
        }

        50% {
            transform: rotate(720deg) scale(1.5);
            opacity: 1;
        }

        70% {
            transform: rotate(540deg) scale(0.9);
        }

        100% {
            transform: rotate(720deg) scale(1);
        }
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 0.2;
        }

        50% {
            opacity: 1;
        }
    }
</style>

<script>
    function showAchievementNotification(achievements) {
        if (!achievements || achievements.length === 0) return;

        // Обновить счётчик в top-bar
        const badge = document.querySelector('.achievement-badge');
        if (badge) {
            const current = parseInt(badge.textContent) || 0;
            badge.textContent = current + achievements.length;
        }

        let index = 0;

        function showNext() {
            if (index >= achievements.length) return;

            const ach = achievements[index];

            const overlay = document.createElement('div');
            overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: glitchOverlay 0.3s;
        `;

            const card = document.createElement('div');
            card.style.cssText = `
            background: var(--bg-card);
            border: 2px solid var(--accent);
            border-radius: 16px;
            padding: 50px 60px;
            text-align: center;
            position: relative;
            animation: glitchCard 0.5s;
            box-shadow: 0 0 100px rgba(0,255,65,0.2);
            max-width: 90vw;
            min-width: 320px;
        `;

            card.innerHTML = `
            <div style="position: absolute; top: -1px; left: -1px; right: -1px; bottom: -1px; border: 2px solid var(--error); border-radius: 16px; animation: glitchBorder 0.2s infinite; pointer-events: none;"></div>
            <div style="font-size: 80px; animation: spinGlitch 0.8s;">${ach.icon || '🎯'}</div>
            <div style="font-size: 11px; color: var(--error); letter-spacing: 4px; margin-top: 8px; text-transform: uppercase;">⚠ СИСТЕМА ВЗЛОМАНА ⚠</div>
            <div style="font-size: 36px; font-family: 'JetBrains Mono', monospace; color: var(--accent); margin-top: 4px; text-shadow: 0 0 40px rgba(0,255,65,0.5); animation: glitchText 0.3s infinite;">
                ${ach.name}
            </div>
            <div style="font-size: 14px; color: var(--text-dim); margin-top: 8px;">${ach.description}</div>
            <div style="margin-top: 16px; font-size: 12px; color: var(--text-dim); opacity: 0.5;">
                ⚡ НЕВЕРОЯТНОЕ ДОСТИЖЕНИЕ ПОЛУЧЕНО ⚡
            </div>
            <div style="margin-top: 12px; display: flex; gap: 4px; justify-content: center;">
                <span style="display: inline-block; width: 8px; height: 8px; background: var(--error); border-radius: 50%; animation: blink 0.3s infinite;"></span>
                <span style="display: inline-block; width: 8px; height: 8px; background: var(--accent); border-radius: 50%; animation: blink 0.3s infinite 0.1s;"></span>
                <span style="display: inline-block; width: 8px; height: 8px; background: var(--error); border-radius: 50%; animation: blink 0.3s infinite 0.2s;"></span>
            </div>
        `;

            overlay.appendChild(card);
            document.body.appendChild(overlay);

            // Закрываем через 5 секунд, затем показываем следующую
            setTimeout(() => {
                overlay.style.transition = 'all 0.7s';
                overlay.style.opacity = '0';
                overlay.style.transform = 'scale(1.5)';
                setTimeout(() => {
                    overlay.remove();
                    index++;
                    showNext(); // ← следующая ачивка только после закрытия текущей
                }, 700);
            }, 5000);
        }

        showNext();
    }

    <?php if (!empty($_SESSION['new_achievements'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const achievements = <?= json_encode(array_map(function ($ach) {
                                        return [
                                            'icon' => $ach['icon'] ?? '🎯',
                                            'name' => $ach['name'] ?? 'Достижение',
                                            'description' => $ach['description'] ?? 'Вы достигли чего-то невероятного!'
                                        ];
                                    }, $_SESSION['new_achievements'])) ?>;

            showAchievementNotification(achievements);
        });
        <?php unset($_SESSION['new_achievements']); ?>
    <?php endif; ?>
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>