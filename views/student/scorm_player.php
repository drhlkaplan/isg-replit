<?php
$pageTitle = htmlspecialchars($course['title']) . ' — İSG SCORM';
$bodyClass = 'scorm-player-body';
include __DIR__ . '/../_layout.php';
?>
<style>
/* ── Scope: only on SCORM player page ───────────────────────────────── */
body.scorm-player-body main          { padding: 0 !important; }
body.scorm-player-body               { overflow: hidden; }
body.scorm-fs                        { overflow: hidden; }

/* ── Player wrapper — fills viewport below navbar ───────────────────── */
.scorm-wrapper {
    display: flex;
    flex-direction: column;
    /* dvh = dynamic viewport height (shrinks when mobile browser bar collapses) */
    height: calc(100dvh - var(--navbar-h, 56px));
    height: calc(100vh  - var(--navbar-h, 56px)); /* fallback */
    background: #0d0d0d;
    position: relative;
    overflow: hidden;
}

/* ── CSS fullscreen mode (class toggle on <body>) ───────────────────── */
body.scorm-fs .scorm-wrapper {
    position: fixed;
    inset: 0;
    height: 100dvh !important;
    height: 100vh  !important; /* fallback */
    z-index: 9999;
}

/* ── Player header ──────────────────────────────────────────────────── */
.scorm-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #111827;
    border-bottom: 1px solid rgba(255,255,255,.07);
    flex-shrink: 0;
    color: #fff;
    transition: opacity .3s;
    min-height: 0;
}
body.scorm-fs .scorm-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    z-index: 10;
    background: linear-gradient(to bottom, rgba(0,0,0,.92) 0%, rgba(0,0,0,.6) 60%, transparent 100%);
    border: none;
    padding: 14px 22px 36px;
    pointer-events: none;
    opacity: 0;
}
body.scorm-fs.scorm-overlay-on .scorm-header {
    opacity: 1;
    pointer-events: auto;
}

/* ── SCORM iframe ───────────────────────────────────────────────────── */
#scorm-frame {
    flex: 1 1 0;
    width: 100%;
    border: none;
    background: #0d0d0d;
    display: block;
    min-height: 0;
}
body.scorm-fs #scorm-frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

/* ── Player footer ──────────────────────────────────────────────────── */
.scorm-footer {
    flex-shrink: 0;
    padding: 6px 14px calc(6px + env(safe-area-inset-bottom, 0px));
    background: #111827;
    border-top: 1px solid rgba(255,255,255,.07);
    min-height: 0;
}
body.scorm-fs .scorm-footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    z-index: 10;
    background: linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,.5) 60%, transparent 100%);
    border: none;
    padding: 28px 22px 16px;
    pointer-events: none;
    opacity: 0;
    transition: opacity .3s;
}
body.scorm-fs.scorm-overlay-on .scorm-footer {
    opacity: 1;
    pointer-events: auto;
}

/* ── Shared UI atoms ────────────────────────────────────────────────── */
.scorm-back-btn {
    display: flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 50%;
    background: rgba(255,255,255,.1);
    color: #fff; text-decoration: none; font-size: 1rem;
    flex-shrink: 0; transition: background .15s;
}
.scorm-back-btn:hover { background: rgba(255,255,255,.22); color: #fff; }

.scorm-course-title {
    font-size: .85rem; font-weight: 700; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    flex: 1 1 0; min-width: 0;
}
.scorm-course-meta { font-size: .72rem; color: rgba(255,255,255,.5); margin-top: 1px; }

.ctrl-btn {
    display: inline-flex; align-items: center; justify-content: center;
    height: 32px; padding: 0 10px; border-radius: 7px;
    border: none; cursor: pointer; font-size: .78rem; font-weight: 600;
    gap: 5px; transition: background .15s, transform .1s;
    white-space: nowrap; flex-shrink: 0;
}
.ctrl-btn:active { transform: scale(.95); }
.ctrl-btn.light  { background: rgba(255,255,255,.13); color: #fff; }
.ctrl-btn.light:hover { background: rgba(255,255,255,.24); }
.ctrl-btn.accent { background: #005695; color: #fff; }
.ctrl-btn.accent:hover { background: #006db0; }

.status-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 20px;
    font-size: .68rem; font-weight: 600;
    background: rgba(255,255,255,.12); color: rgba(255,255,255,.8);
}
.status-pill.passed  { background: rgba(40,167,69,.25);  color: #6fcf97; }
.status-pill.failed  { background: rgba(220,53,69,.25);  color: #ff6b6b; }
.status-pill.running { background: rgba(255,193,7,.15);  color: #ffc107; }

.progress-track {
    height: 4px; background: rgba(255,255,255,.2); border-radius: 2px; overflow: hidden;
}
.progress-fill {
    height: 100%; background: #28a745; border-radius: 2px; transition: width .4s ease;
}
.progress-labels {
    display: flex; justify-content: space-between;
    margin-top: 4px; font-size: .68rem; color: rgba(255,255,255,.4);
}

/* ── Alert banners ──────────────────────────────────────────────────── */
.alert-banner {
    display: none;
    position: fixed; top: 0; left: 0; right: 0; z-index: 99999;
    padding: 10px 20px; text-align: center;
    font-size: .8rem; font-weight: 600; color: #fff;
}
#tab-banner { background: #c0392b; }
#ff-banner  { background: #e67e22; }

/* ── Mobile ─────────────────────────────────────────────────────────── */
@media (max-width: 767px) {
    /* Navbar hidden in mobile player mode — maximize SCORM area */
    body.scorm-player-body .isg-navbar { display: none !important; }
    .scorm-wrapper {
        height: 100dvh !important;
        height: 100vh  !important;
    }

    /* Compact header */
    .scorm-header { padding: 6px 10px; gap: 6px; }
    .scorm-course-meta { display: none; }
    .scorm-course-title { font-size: .78rem; }
    #pct-display-btn { display: none !important; }

    /* Footer: always visible, compact, safe-area aware */
    .scorm-footer {
        padding: 5px 12px calc(5px + env(safe-area-inset-bottom, 0px));
    }
    .progress-labels { font-size: .64rem; }

    /* Auto-hide header/footer on mobile; tap iframe area to toggle */
    body.scorm-player-body .scorm-header,
    body.scorm-player-body .scorm-footer {
        transition: opacity .25s, max-height .25s;
        max-height: 60px;
        overflow: hidden;
    }
    body.scorm-player-body.scorm-ui-hidden .scorm-header,
    body.scorm-player-body.scorm-ui-hidden .scorm-footer {
        opacity: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
}
</style>

<!-- Alert banners -->
<div class="alert-banner" id="tab-banner">
    <i class="bi bi-exclamation-triangle-fill"></i>
    Sekme değişikliği algılandı! Eğitim süreniz izlenmektedir.
</div>
<div class="alert-banner" id="ff-banner">
    <i class="bi bi-fast-forward-fill"></i>
    Hızlı ilerleme tespit edildi. Bu durum kayıt altına alınmaktadır.
</div>

<div class="scorm-wrapper" id="scorm-wrapper">

    <!-- ── Player header ──────────────────────────────────────────────── -->
    <div class="scorm-header" id="scorm-header">
        <a href="/ogrenci/kurs/<?= $courseId ?>" class="scorm-back-btn" title="Kursa Dön">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="flex-grow-1 overflow-hidden">
            <div class="scorm-course-title"><?= htmlspecialchars($course['title']) ?></div>
            <div class="scorm-course-meta">
                <i class="bi bi-shield-fill-check me-1" style="color:#f5c518"></i>İSG Eğitim
                &nbsp;·&nbsp; <span id="status-display" class="status-pill">Yükleniyor…</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <span id="pct-display-btn" class="ctrl-btn light" style="cursor:default; pointer-events:none">
                <i class="bi bi-bar-chart-fill"></i><span id="pct-label"><?= (int)($tracking['progress_percent'] ?? 0) ?>%</span>
            </span>
            <button class="ctrl-btn accent" id="fullscreen-btn" title="Tam ekran (F)">
                <i class="bi bi-fullscreen" id="fs-icon"></i>
                <span id="fs-label" class="d-none d-md-inline">Tam Ekran</span>
            </button>
        </div>
    </div>

    <!-- ── SCORM iframe ───────────────────────────────────────────────── -->
    <iframe
        id="scorm-frame"
        name="scorm_content_frame"
        src="<?= SCORM_URL . htmlspecialchars($package['package_path']) . htmlspecialchars($package['launch_url']) ?>?uid=<?= $userId ?>&course=<?= $courseId ?>"
        sandbox="allow-scripts allow-same-origin allow-forms allow-pointer-lock allow-popups allow-modals"
    ></iframe>

    <!-- ── Player footer ──────────────────────────────────────────────── -->
    <div class="scorm-footer" id="scorm-footer">
        <div class="progress-track">
            <div class="progress-fill" id="progress-fill" style="width:<?= (int)($tracking['progress_percent'] ?? 0) ?>%"></div>
        </div>
        <div class="progress-labels">
            <span><i class="bi bi-clock me-1"></i><?= (int)($course['duration_minutes'] ?? 0) ?> dk</span>
            <span id="pct-label-bottom"><?= (int)($tracking['progress_percent'] ?? 0) ?>% tamamlandı</span>
        </div>
    </div>

</div><!-- /scorm-wrapper -->

<script>
/* ── Responsive height calculation ──────────────────────────────────── */
(function() {
    var isMobile = window.innerWidth <= 767;
    var wrapper  = document.getElementById('scorm-wrapper');
    var nav      = document.querySelector('.isg-navbar');

    function setHeight() {
        var mobile = window.innerWidth <= 767;
        var navH   = (!mobile && nav) ? nav.getBoundingClientRect().height : 0;
        document.documentElement.style.setProperty('--navbar-h', navH + 'px');
        var vh = window.innerHeight;
        wrapper.style.height = (vh - navH) + 'px';
    }

    setHeight();
    window.addEventListener('resize', setHeight);

    /* Mobile: tap on the iframe area toggles UI visibility */
    if (isMobile) {
        var body      = document.body;
        var hideTimer = null;

        function showUI() {
            body.classList.remove('scorm-ui-hidden');
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function() {
                body.classList.add('scorm-ui-hidden');
            }, 4000);
        }

        /* Show UI on first tap, hide after 4 s idle */
        document.addEventListener('touchstart', function() { showUI(); }, { passive: true });

        /* Start with UI visible briefly */
        setTimeout(function() { body.classList.add('scorm-ui-hidden'); }, 3000);
    }
})();

var ISG_SCORM = {
    userId:             <?= $userId ?>,
    courseId:           <?= $courseId ?>,
    scormVersion:       '<?= htmlspecialchars($package['scorm_version']) ?>',
    commitUrl:          '/scorm/commit?kurs=<?= $courseId ?>',
    dataUrl:            '/scorm/data?kurs=<?= $courseId ?>',
    csrfToken:          '',
    trackingData:       <?= json_encode($tracking) ?>,
    debug:              false,
    minDurationSeconds: <?= max(30, (int)(($course['duration_minutes'] ?? 0) * 60 * 0.5)) ?>,
    compliance: {
        tabSwitchCount:   <?= (int)($tracking['tab_switch_count'] ?? 0) ?>,
        fastForwardCount: <?= (int)($tracking['fast_forward_count'] ?? 0) ?>,
        lowQualityFlag:   <?= (int)($tracking['low_quality_flag'] ?? 0) ?>,
    }
};

/* ── CSS fullscreen (NO browser Fullscreen API — no permission prompts) */
(function() {
    var fsBtn   = document.getElementById('fullscreen-btn');
    var fsIcon  = document.getElementById('fs-icon');
    var fsLabel = document.getElementById('fs-label');
    var body    = document.body;
    var isFS    = false;
    var hideTimer;

    function showOverlay() {
        if (!isFS) return;
        body.classList.add('scorm-overlay-on');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function() {
            body.classList.remove('scorm-overlay-on');
        }, 3500);
    }

    function enterFS() {
        isFS = true;
        body.classList.add('scorm-fs');
        fsIcon.className  = 'bi bi-fullscreen-exit';
        fsLabel.textContent = 'Çık';
        fsBtn.title = 'Tam ekrandan çık (Esc veya F)';
        showOverlay();
    }

    function exitFS() {
        isFS = false;
        clearTimeout(hideTimer);
        body.classList.remove('scorm-fs', 'scorm-overlay-on');
        fsIcon.className  = 'bi bi-fullscreen';
        fsLabel.textContent = 'Tam Ekran';
        fsBtn.title = 'Tam ekran (F)';
    }

    fsBtn.addEventListener('click', function() { isFS ? exitFS() : enterFS(); });

    document.addEventListener('keydown', function(e) {
        var tag = document.activeElement && document.activeElement.tagName;
        if ((e.key === 'f' || e.key === 'F') && !['INPUT','TEXTAREA','SELECT'].includes(tag)) {
            isFS ? exitFS() : enterFS();
        }
        if (e.key === 'Escape' && isFS) exitFS();
    });

    // Show overlay on any movement in fullscreen
    document.addEventListener('mousemove', showOverlay);
    document.addEventListener('touchstart', showOverlay, { passive: true });
    document.addEventListener('keydown', showOverlay);
})();

/* ── Status + progress display ─────────────────────────────────────── */
(function() {
    var statusEl  = document.getElementById('status-display');
    var fillEl    = document.getElementById('progress-fill');
    var pctLabel  = document.getElementById('pct-label');
    var pctBottom = document.getElementById('pct-label-bottom');

    var statusMap = {
        'passed':        ['passed',  '<i class="bi bi-check-circle-fill"></i> Geçti'],
        'completed':     ['passed',  '<i class="bi bi-check-circle-fill"></i> Tamamlandı'],
        'failed':        ['failed',  '<i class="bi bi-x-circle-fill"></i> Başarısız'],
        'incomplete':    ['running', '<i class="bi bi-play-fill"></i> Devam Ediyor'],
        'in progress':   ['running', '<i class="bi bi-play-fill"></i> Devam Ediyor'],
        'browsed':       ['running', '<i class="bi bi-eye-fill"></i> Göz Atılıyor'],
        'not attempted': ['',        '<i class="bi bi-hourglass-split"></i> Başlamadı'],
    };

    window.ISG_PLAYER = {
        updateStatus: function(status, pct) {
            status = (status || '').toLowerCase();
            var info = statusMap[status] || ['', status];
            statusEl.className = 'status-pill ' + info[0];
            statusEl.innerHTML = info[1];
            if (pct !== undefined && pct !== null) {
                var p = Math.min(100, Math.max(0, pct));
                fillEl.style.width    = p + '%';
                pctLabel.textContent  = p + '%';
                pctBottom.textContent = p + '% tamamlandı';
            }
        }
    };

    var td = ISG_SCORM.trackingData;
    window.ISG_PLAYER.updateStatus(
        td.lesson_status || td.completion_status || 'not attempted',
        td.progress_percent || 0
    );
})();

/* ── Tab-switch compliance ──────────────────────────────────────────── */
(function() {
    var banner    = document.getElementById('tab-banner');
    var compliance = ISG_SCORM.compliance;

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) return;
        compliance.tabSwitchCount++;
        banner.style.display = 'block';
        setTimeout(function() { banner.style.display = 'none'; }, 5000);
        fetch(ISG_SCORM.commitUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'tab_switch_count=' + compliance.tabSwitchCount,
            keepalive: true
        }).catch(function(){});
    });
})();
</script>
<script src="/assets/js/scorm/lms.js"></script>

<?php include __DIR__ . '/../_layout_end.php'; ?>
