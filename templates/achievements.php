<?php
$pageTitle = 'Мои достижения';
$db = $GLOBALS['db'];
$achievements = new Achievements($db);
$dashboard = $achievements->getAchievementsDashboard(Auth::id());
$noindex = true;
ob_start();
?>

<div class="achievements-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-family:'JetBrains Mono',monospace; font-size:24px; color:var(--accent); margin:0;">
            🏆 Мои достижения
        </h1>
        <p style="color:var(--text-dim); font-size:13px; margin-top:4px;">
            <?= $dashboard['unlocked'] ?> из <?= $dashboard['total'] ?> · Прогресс <?= $dashboard['progress'] ?>%
        </p>
    </div>
    <a href="/" class="btn-primary" style="text-decoration:none; padding:10px 20px; font-size:12px;">
        ← К анализу
    </a>
</div>

<div class="card" style="margin-bottom:24px; padding:16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div style="flex:1; min-width:150px;">
            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-dim); margin-bottom:4px;">
                <span>Прогресс коллекции</span>
                <span><?= $dashboard['unlocked'] ?>/<?= $dashboard['total'] ?></span>
            </div>
            <div style="width:100%; height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                <div style="width:<?= $dashboard['progress'] ?>%; height:100%; background:var(--accent); border-radius:3px; transition:width 0.5s;"></div>
            </div>
        </div>
        <div style="font-family:'JetBrains Mono',monospace; font-size:20px; color:var(--accent);">
            <?= $dashboard['progress'] ?>%
        </div>
    </div>
</div>

<?php foreach ($dashboard['by_category'] as $catKey => $cat): ?>
    <?php if (empty($cat['achievements'])) continue; ?>
    <?php if ($catKey === 'secret' && empty($cat['achievements'])) continue; ?>
    <div style="margin-bottom:24px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
            <span style="font-size:20px;"><?= $cat['icon'] ?? '📌' ?></span>
            <h2 style="font-family:'JetBrains Mono',monospace; font-size:14px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin:0;">
                <?= htmlspecialchars($cat['name']) ?>
                <span style="font-size:11px; opacity:0.6;">(<?= count($cat['achievements']) ?>)</span>
            </h2>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px;">
            <?php foreach ($cat['achievements'] as $ach): ?>
                <div class="achievement-card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:16px 18px; display:flex; align-items:center; gap:14px;">
                    <div style="font-size:32px; flex-shrink:0;"><?= $ach['icon'] ?? '🎯' ?></div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:600; color:var(--text); font-size:14px;">
                            <?= htmlspecialchars($ach['name']) ?>
                        </div>
                        <div style="font-size:12px; color:var(--text-dim);">
                            <?= htmlspecialchars($ach['description']) ?>
                        </div>
                        <div style="font-size:10px; color:var(--text-dim); opacity:0.6; margin-top:4px;">
                            Получено: <?= date('d.m.Y H:i', strtotime($ach['unlocked_at'])) ?>
                        </div>
                    </div>
                    <div style="font-size:18px; flex-shrink:0; color:var(--accent);">✅</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if ($dashboard['unlocked'] === 0): ?>
    <div class="card" style="text-align:center; padding:40px 20px;">
        <div style="font-size:48px; margin-bottom:12px;">🎯</div>
        <p style="color:var(--text); font-size:16px; margin-bottom:8px;">У вас пока нет достижений</p>
        <p style="color:var(--text-dim); font-size:13px;">Проведите анализ числа и начните коллекционировать ачивки!</p>
        <a href="/" class="btn-primary" style="display:inline-block; margin-top:16px; text-decoration:none; padding:10px 24px; font-size:13px;">Анализировать</a>
    </div>
<?php endif; ?>

<style>
    .achievement-card {
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .achievement-card:hover {
        border-color: var(--accent);
        box-shadow: 0 0 20px rgba(0, 255, 65, 0.05);
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>