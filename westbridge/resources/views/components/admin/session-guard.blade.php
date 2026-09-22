{{--
    Admin session guard (browser side). The server enforces every limit; this
    only makes it pleasant:
      - tells the server the person is active while they type or click, so a
        long edit is never mistaken for idleness;
      - warns a minute before the screen locks;
      - locks in place (password box over the page) so unsaved work survives;
      - leaves for the sign-in page the moment the session has ended;
      - reloads any admin page restored by the Back button, so a signed-out
        browser can never show one from memory.
--}}
<div id="wb-lock" class="fixed inset-0 z-[100] hidden items-center justify-center bg-navy-950/85 px-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="wb-lock-title">
    <form id="wb-lock-form" class="w-full max-w-sm rounded-sm bg-white p-8 text-center shadow-[0_30px_60px_-20px_rgba(0,0,0,0.7)]">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-navy-700 text-lime-400">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V7.5a4 4 0 018 0v3" stroke-linecap="round"/></svg>
        </span>
        <h2 id="wb-lock-title" class="mt-4 font-display text-xl font-bold text-navy-700">Screen locked</h2>
        <p class="mt-1 text-small text-paper-600">Enter your password to carry on. Nothing on this page has been lost.</p>
        <label for="wb-lock-password" class="sr-only">Password</label>
        <input id="wb-lock-password" type="password" autocomplete="current-password" required
            class="mt-5 block w-full rounded-xs border border-paper-300 px-3 py-2.5 text-[15px] focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20" placeholder="Password">
        <p id="wb-lock-error" class="mt-2 hidden text-left text-xs text-status-crit" role="alert"></p>
        <button class="mt-4 w-full rounded-xs bg-lime-500 py-3 font-display text-sm font-semibold text-navy-900 hover:bg-lime-400">Unlock</button>
        <button type="button" id="wb-lock-signout" class="mt-3 text-small font-semibold text-paper-600 underline hover:text-navy-700">Sign out instead</button>
    </form>
</div>

<div id="wb-idle-warning" class="fixed inset-x-4 bottom-4 z-[90] mx-auto hidden max-w-md items-center gap-4 rounded-sm border border-status-warn/40 bg-white px-5 py-4 shadow-[var(--shadow-overlay)] sm:inset-x-auto sm:right-6" role="status">
    <span class="min-w-0 flex-1 text-small text-paper-800">For security, the screen locks in <strong id="wb-idle-seconds" class="font-mono text-navy-700">60</strong> seconds.</span>
    <button type="button" id="wb-idle-stay" class="flex-shrink-0 rounded-xs bg-navy-700 px-3 py-2 font-display text-xs font-semibold text-white hover:bg-navy-800">Stay signed in</button>
</div>

{{-- Shown when Back would take a signed-in person out of the admin. --}}
<div id="wb-leave" class="fixed inset-0 z-[100] hidden items-center justify-center bg-navy-950/70 px-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="wb-leave-title">
    <div class="w-full max-w-sm rounded-sm bg-white p-8 text-center shadow-[0_30px_60px_-20px_rgba(0,0,0,0.7)]">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-status-warn/10 text-status-warn">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V7.5a4 4 0 018 0v3" stroke-linecap="round"/></svg>
        </span>
        <h2 id="wb-leave-title" class="mt-4 font-display text-xl font-bold text-navy-700">You are still signed in</h2>
        <p class="mt-2 text-small text-paper-600">To leave the admin panel, sign out first. Anyone using this computer after you could otherwise carry on as you.</p>
        <button type="button" id="wb-leave-signout" class="mt-6 w-full rounded-xs bg-navy-700 py-3 font-display text-sm font-semibold text-white hover:bg-navy-800">Sign out</button>
        <button type="button" id="wb-leave-stay" class="mt-3 w-full rounded-xs border border-paper-300 py-3 font-display text-sm font-semibold text-navy-700 hover:border-navy-400">Stay in the admin panel</button>
    </div>
</div>

<form id="wb-signout-form" method="POST" action="{{ route('admin.logout') }}" class="hidden">@csrf</form>

<script>
(function () {
    var cfg = {
        lockAfter: {{ \App\Support\AdminSession::lockSeconds() }},
        ping: @js(route('admin.session.ping')),
        status: @js(route('admin.session.status')),
        unlock: @js(route('admin.unlock')),
        login: @js(route('login')),
    };

    var lastActivity = Date.now();
    var lastPing = Date.now();
    var locked = false;
    var el = function (id) { return document.getElementById(id); };
    var csrf = function () { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; };

    function leave() { window.location.replace(cfg.login); }

    function request(method, url, body) {
        return fetch(url, {
            method: method,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: body ? JSON.stringify(body) : undefined,
        });
    }

    function lock() {
        if (locked) return;
        locked = true;
        el('wb-idle-warning').classList.add('hidden');
        el('wb-idle-warning').classList.remove('flex');
        el('wb-lock').classList.remove('hidden');
        el('wb-lock').classList.add('flex');
        setTimeout(function () { el('wb-lock-password').focus(); }, 50);
    }

    function unlocked(token) {
        locked = false;
        lastActivity = lastPing = Date.now();
        if (token) {
            var m = document.querySelector('meta[name="csrf-token"]');
            if (m) m.content = token;
            document.querySelectorAll('input[name="_token"]').forEach(function (i) { i.value = token; });
        }
        el('wb-lock').classList.add('hidden');
        el('wb-lock').classList.remove('flex');
        el('wb-lock-password').value = '';
        el('wb-lock-error').classList.add('hidden');
    }

    function check() {
        request('GET', cfg.status).then(function (r) {
            if (r.status === 401 || r.status === 419) return leave();
            if (!r.ok) return;
            return r.json().then(function (s) {
                if (!s.authenticated) return leave();
                if (s.locked) return lock();
            });
        }).catch(function () { /* offline for a moment - try again next time */ });
    }

    // Activity: counted locally, reported to the server at most once a minute.
    ['keydown', 'mousedown', 'touchstart', 'input', 'wheel'].forEach(function (evt) {
        document.addEventListener(evt, function () {
            if (locked) return;
            lastActivity = Date.now();
            if (Date.now() - lastPing > 60000) {
                lastPing = Date.now();
                request('POST', cfg.ping).then(function (r) {
                    if (r.status === 423) lock();
                    if (r.status === 401 || r.status === 419) leave();
                });
            }
        }, { passive: true });
    });

    el('wb-idle-stay').addEventListener('click', function () {
        lastActivity = lastPing = Date.now();
        request('POST', cfg.ping);
        el('wb-idle-warning').classList.add('hidden');
        el('wb-idle-warning').classList.remove('flex');
    });

    el('wb-lock-form').addEventListener('submit', function (e) {
        e.preventDefault();
        request('POST', cfg.unlock, { password: el('wb-lock-password').value }).then(function (r) {
            if (r.status === 401 || r.status === 419) return leave();
            return r.json().then(function (d) {
                if (r.ok) return unlocked(d.csrf);
                el('wb-lock-error').textContent = d.message || 'That password is not correct.';
                el('wb-lock-error').classList.remove('hidden');
                el('wb-lock-password').select();
            });
        });
    });

    el('wb-lock-signout').addEventListener('click', function () { el('wb-signout-form').submit(); });

    // Every second: warn in the last minute, then ask the server to decide.
    setInterval(function () {
        if (locked) return;
        var idle = (Date.now() - lastActivity) / 1000;
        var left = Math.ceil(cfg.lockAfter - idle);
        if (left <= 60 && left > 0) {
            el('wb-idle-seconds').textContent = left;
            el('wb-idle-warning').classList.remove('hidden');
            el('wb-idle-warning').classList.add('flex');
        } else {
            el('wb-idle-warning').classList.add('hidden');
            el('wb-idle-warning').classList.remove('flex');
        }
        if (left <= 0) check();
    }, 1000);

    setInterval(check, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) check(); });

    // Back button at the edge of the admin: when the page before this one is
    // the sign-in page or outside the admin, Back would leave the panel while
    // still signed in. Keep the person here and ask them to sign out instead.
    // (Browsers only honour this after the first click or key press on the
    // page. Without it the server still sends a signed-in person who reaches
    // the sign-in page straight back to the dashboard.)
    var adminBase = @js(url('/admin'));
    var ref = document.referrer || '';
    var cameFromOutside = ref.indexOf(adminBase) !== 0
        || ref.indexOf(@js(route('login'))) === 0
        || ref.indexOf(@js(route('admin.lock'))) === 0;

    if (cameFromOutside) {
        var guarded = false;
        var guard = function () {
            if (guarded) return;
            guarded = true;
            history.pushState({ wbGuard: true }, '', location.href);
        };
        guard();
        ['mousedown', 'keydown', 'touchstart'].forEach(function (evt) {
            document.addEventListener(evt, function () {
                if (!history.state || !history.state.wbGuard) { guarded = false; guard(); }
            }, { once: true, passive: true });
        });

        window.addEventListener('popstate', function () {
            if (locked) return;
            history.pushState({ wbGuard: true }, '', location.href);
            el('wb-leave').classList.remove('hidden');
            el('wb-leave').classList.add('flex');
        });

        el('wb-leave-stay').addEventListener('click', function () {
            el('wb-leave').classList.add('hidden');
            el('wb-leave').classList.remove('flex');
        });
        el('wb-leave-signout').addEventListener('click', function () { el('wb-signout-form').submit(); });
    }

    // Back/forward: a page restored from the browser's memory is re-fetched,
    // so the server decides - a signed-out browser lands on the sign-in page.
    window.addEventListener('pageshow', function (e) { if (e.persisted) window.location.reload(); else check(); });
})();
</script>
