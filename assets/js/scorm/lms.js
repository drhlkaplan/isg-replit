/**
 * ISG LMS — SCORM 1.2 / 2004 API Adapter
 * Adapted from eFront LMSFunctions.php SCORM engine.
 * Uses vanilla fetch() instead of Prototype.js AJAX.
 */
(function (global) {
    'use strict';

    var cfg = global.ISG_SCORM || {};
    var courseId   = cfg.courseId   || 0;
    var scormVer   = cfg.scormVersion || '1.2';
    var commitUrl  = cfg.commitUrl  || '/scorm/commit?kurs=' + courseId;
    var debug      = cfg.debug      || false;

    /* ---------- Tracking data store ---------- */
    var _data = {};
    var _initialized = false;
    var _terminated  = false;
    var _lastError   = '0';
    var _dirty       = false;

    /* ---------- Fast-forward / compliance tracking ---------- */
    var _sessionStartTime  = null;   // set on Initialize
    var _completionChecked = false;  // only flag once per session
    var _minDurationSec    = cfg.minDurationSeconds || 30;
    var _compliance        = cfg.compliance || {};

    /**
     * Called whenever SCORM content sets a terminal status (passed/completed/failed).
     * Compares real elapsed time against the course minimum duration.
     * If the content attempts completion in less than _minDurationSec, it is flagged
     * as fast-forward (i.e. the learner skipped through the material).
     */
    function _checkFastForward(statusValue) {
        if (_completionChecked) return;
        var terminal = /passed|completed|failed/.test(String(statusValue).toLowerCase());
        if (!terminal) return;

        if (_sessionStartTime !== null) {
            var elapsedSec = (Date.now() - _sessionStartTime) / 1000;
            dbg('Fast-forward check: elapsed=' + elapsedSec.toFixed(1) + 's min=' + _minDurationSec + 's status=' + statusValue);

            if (elapsedSec < _minDurationSec) {
                _completionChecked = true;
                _compliance.fastForwardCount = (_compliance.fastForwardCount || 0) + 1;
                _compliance.lowQualityFlag   = 1;
                dbg('FAST-FORWARD DETECTED: count=' + _compliance.fastForwardCount);

                /* Immediately report to server so it's persisted regardless of Finish/Terminate */
                fetch(commitUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'fast_forward_count=' + _compliance.fastForwardCount +
                          '&low_quality_flag=1',
                    keepalive: true
                }).catch(function(){});

                /* Show warning banner in parent frame */
                try {
                    var ffBanner = parent.document.getElementById('fast-forward-banner');
                    if (ffBanner) {
                        ffBanner.style.display = 'block';
                        setTimeout(function(){ ffBanner.style.display = 'none'; }, 6000);
                    }
                } catch(e) {}
            }
        }
    }


    /* Seed from server-side tracking */
    var serverData = cfg.trackingData || {};
    if (serverData) {
        if (scormVer === '1.2') {
            if (serverData.lesson_status) _data['cmi.core.lesson_status'] = serverData.lesson_status;
            if (serverData.score_raw)     _data['cmi.core.score.raw']     = serverData.score_raw;
            if (serverData.score_min)     _data['cmi.core.score.min']     = serverData.score_min;
            if (serverData.score_max)     _data['cmi.core.score.max']     = serverData.score_max;
            if (serverData.total_time)    _data['cmi.core.total_time']    = serverData.total_time;
            if (serverData.suspend_data)  _data['cmi.suspend_data']       = serverData.suspend_data;
            if (serverData.location)      _data['cmi.core.lesson_location'] = serverData.location;
        } else {
            if (serverData.completion_status) _data['cmi.completion_status'] = serverData.completion_status;
            if (serverData.success_status)    _data['cmi.success_status']    = serverData.success_status;
            if (serverData.score_raw)         _data['cmi.score.raw']         = serverData.score_raw;
            if (serverData.score_scaled)      _data['cmi.score.scaled']      = serverData.score_scaled;
            if (serverData.total_time)        _data['cmi.total_time']        = serverData.total_time;
            if (serverData.suspend_data)      _data['cmi.suspend_data']      = serverData.suspend_data;
            if (serverData.location)          _data['cmi.location']          = serverData.location;
        }
    }

    function dbg(msg) { if (debug) console.log('[ISG-SCORM] ' + msg); }

    /* ---------- commit to server ---------- */
    function _commit(async) {
        if (!_dirty) return 'true';
        var payload = {};
        if (scormVer === '1.2') {
            payload.lesson_status   = _data['cmi.core.lesson_status']       || '';
            payload.score_raw       = _data['cmi.core.score.raw']           || '';
            payload.score_min       = _data['cmi.core.score.min']           || '';
            payload.score_max       = _data['cmi.core.score.max']           || '';
            payload.total_time      = _data['cmi.core.total_time']          || '';
            payload.session_time    = _data['cmi.core.session_time']        || '';
            payload.suspend_data    = _data['cmi.suspend_data']             || '';
            payload.location        = _data['cmi.core.lesson_location']     || '';
        } else {
            payload.completion_status = _data['cmi.completion_status']      || '';
            payload.success_status    = _data['cmi.success_status']         || '';
            payload.score_raw         = _data['cmi.score.raw']              || '';
            payload.score_scaled      = _data['cmi.score.scaled']           || '';
            payload.total_time        = _data['cmi.total_time']             || '';
            payload.session_time      = _data['cmi.session_time']           || '';
            payload.suspend_data      = _data['cmi.suspend_data']           || '';
            payload.location          = _data['cmi.location']               || '';
        }

        /* Include compliance tracking data — _compliance is the live object updated by detectors */
        if (_compliance.tabSwitchCount  !== undefined) payload.tab_switch_count  = _compliance.tabSwitchCount;
        if (_compliance.fastForwardCount !== undefined) payload.fast_forward_count = _compliance.fastForwardCount;
        if (_compliance.lowQualityFlag  !== undefined)  payload.low_quality_flag  = _compliance.lowQualityFlag;

        var body = new URLSearchParams(payload);
        dbg('Committing: ' + body.toString());

        fetch(commitUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
            keepalive: true
        }).then(function (r) { return r.json(); }).then(function (res) {
            dbg('Commit response: ' + JSON.stringify(res));
            _dirty = false;
            if (res.progress !== undefined) _updateProgressBar(res.progress);
            if (res.enrollStatus === 'completed') _onCompletion();
        }).catch(function (e) { dbg('Commit error: ' + e); });

        return 'true';
    }

    function _updateProgressBar(pct) {
        var player = parent.window.ISG_PLAYER;
        if (player && player.updateStatus) {
            player.updateStatus(null, pct);
        } else {
            // Fallback: direct element access
            var bar = parent.document.getElementById('progress-fill');
            if (bar) bar.style.width = pct + '%';
            var disp = parent.document.getElementById('pct-label');
            if (disp) disp.textContent = pct + '%';
        }
    }

    function _updateStatusBadge(status) {
        var player = parent.window.ISG_PLAYER;
        if (player && player.updateStatus) {
            player.updateStatus(status, null);
        } else {
            // Fallback
            var badge = parent.document.getElementById('status-display');
            if (badge) badge.textContent = status;
        }
    }

    function _onCompletion() {
        var player = parent.window.ISG_PLAYER;
        if (player && player.updateStatus) {
            player.updateStatus('completed', 100);
        }
    }

    /* ====================================================
       SCORM 1.2 API  (window.API)
    ==================================================== */
    var API_12 = {
        LMSInitialize: function (param) {
            dbg('LMSInitialize(' + param + ')');
            _initialized    = true;
            _terminated     = false;
            _lastError      = '0';
            _sessionStartTime = Date.now();
            var player = parent.window.ISG_PLAYER;
            if (player && player.updateStatus) player.updateStatus('not attempted', null);
            return 'true';
        },
        LMSFinish: function (param) {
            dbg('LMSFinish(' + param + ')');
            /* Check fast-forward on session end using current lesson_status */
            _checkFastForward(_data['cmi.core.lesson_status'] || '');
            _commit(false);
            _terminated = true;
            _lastError  = '0';
            return 'true';
        },
        LMSGetValue: function (element) {
            dbg('LMSGetValue(' + element + ')');
            _lastError = '0';
            if (element === 'cmi.core.student_id')   return String(courseId);
            if (element === 'cmi.core.student_name')  return 'Öğrenci';
            if (element === 'cmi.core.entry') {
                var status = _data['cmi.core.lesson_status'] || '';
                return status === 'incomplete' ? 'resume' : 'ab-initio';
            }
            if (element === 'cmi.core.credit')       return 'credit';
            if (element === 'cmi.core.lesson_mode')  return 'normal';
            if (element === 'cmi.student_data.mastery_score') return '70';
            var val = _data[element];
            return (val !== undefined) ? String(val) : '';
        },
        LMSSetValue: function (element, value) {
            dbg('LMSSetValue(' + element + ', ' + value + ')');
            _lastError = '0';
            _data[element] = value;
            _dirty = true;
            if (element === 'cmi.core.lesson_status') {
                _updateStatusBadge(value);
                _checkFastForward(value);
            }
            return 'true';
        },
        LMSCommit: function (param) {
            dbg('LMSCommit(' + param + ')');
            return _commit(true);
        },
        LMSGetLastError: function () { return _lastError; },
        LMSGetErrorString: function (code) {
            var errs = {
                '0':'No Error','101':'General Exception','201':'Invalid argument error',
                '202':'Element cannot have children','203':'Element not an array',
                '301':'Not initialized','401':'Not implemented error',
                '402':'Invalid set value','403':'Element is read only',
                '404':'Element is write only','405':'Incorrect data type'
            };
            return errs[String(code)] || 'Unknown Error';
        },
        LMSGetDiagnostic: function (code) { return 'Diagnostic: ' + code; }
    };

    /* ====================================================
       SCORM 2004 API  (window.API_1484_11)
    ==================================================== */
    var API_2004 = {
        Initialize: function (param) {
            dbg('Initialize(' + param + ')');
            _initialized    = true;
            _terminated     = false;
            _lastError      = '0';
            _sessionStartTime = Date.now();
            if (!_data['cmi.completion_status'])  _data['cmi.completion_status'] = 'not attempted';
            if (!_data['cmi.success_status'])      _data['cmi.success_status']    = 'unknown';
            return 'true';
        },
        Terminate: function (param) {
            dbg('Terminate(' + param + ')');
            /* Check fast-forward on session end */
            _checkFastForward(_data['cmi.completion_status'] || _data['cmi.success_status'] || '');
            _commit(false);
            _terminated = true;
            _lastError  = '0';
            return 'true';
        },
        GetValue: function (element) {
            dbg('GetValue(' + element + ')');
            _lastError = '0';
            if (element === 'cmi.learner_id')           return String(courseId);
            if (element === 'cmi.learner_name')         return 'Öğrenci';
            if (element === 'cmi.entry')                return _data['cmi.completion_status'] === 'incomplete' ? 'resume' : 'ab-initio';
            if (element === 'cmi.credit')               return 'credit';
            if (element === 'cmi.mode')                 return 'normal';
            if (element === 'cmi.completion_threshold') return '1';
            if (element === 'cmi.scaled_passing_score') return '0.7';
            var val = _data[element];
            return (val !== undefined) ? String(val) : '';
        },
        SetValue: function (element, value) {
            dbg('SetValue(' + element + ', ' + value + ')');
            _lastError = '0';
            _data[element] = value;
            _dirty = true;
            if (element === 'cmi.completion_status' || element === 'cmi.success_status') {
                _updateStatusBadge(value);
                _checkFastForward(value);
            }
            return 'true';
        },
        Commit: function (param) {
            dbg('Commit(' + param + ')');
            return _commit(true);
        },
        GetLastError: function () { return _lastError; },
        GetErrorString: function (code) {
            var errs = {
                '0':'No Error','101':'General Exception','102':'General Initialization Failure',
                '103':'Already Initialized','104':'Content Instance Terminated',
                '111':'General Termination Failure','112':'Termination Before Initialization',
                '113':'Termination After Termination','122':'Retrieve Data Before Initialization',
                '123':'Retrieve Data After Termination','132':'Store Data Before Initialization',
                '133':'Store Data After Termination','142':'Commit Before Initialization',
                '143':'Commit After Termination','201':'General Argument Error',
                '301':'General Get Failure','351':'General Set Failure',
                '391':'General Commit Failure','401':'Undefined Data Model Element',
                '402':'Unimplemented Data Model Element','403':'Data Model Element Value Not Initialized',
                '404':'Data Model Element Is Read Only','405':'Data Model Element Is Write Only',
                '406':'Data Model Element Type Mismatch','407':'Data Model Element Value Out Of Range',
                '408':'Data Model Dependency Not Established'
            };
            return errs[String(code)] || 'Unknown Error';
        },
        GetDiagnostic: function (code) { return 'Diagnostic: ' + code; }
    };

    /* ---------- Expose APIs ---------- */
    if (scormVer === '2004') {
        global.API_1484_11 = API_2004;
        dbg('SCORM 2004 API mounted on window.API_1484_11');
    } else {
        global.API = API_12;
        dbg('SCORM 1.2 API mounted on window.API');
    }

    /* ---------- Auto-commit every 30s ---------- */
    setInterval(function () { if (_initialized && !_terminated) _commit(true); }, 30000);

    /* ---------- Commit on page leave ---------- */
    global.addEventListener('beforeunload', function () {
        if (_initialized && !_terminated) _commit(false);
    });

    dbg('ISG SCORM adapter loaded — version ' + scormVer);

})(window);
