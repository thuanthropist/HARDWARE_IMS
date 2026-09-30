{{--
    Drop-in activation wizard. @include('license-client::activation-wizard') from your
    own Settings -> License page (inside whatever card/layout wrapper you already use).

    Runs four steps in sequence against this package's own routes (see routes/web.php
    and LicenseActivationWizard): config present -> crypto capability -> connectivity ->
    real activation. Each step reports pass/fail/warn independently instead of one
    opaque "activation failed" message, and a progress bar tracks how far it got.

    Self-contained plain CSS (not Tailwind) — this file lives in vendor/, outside any
    host app's Tailwind content scan, so relying on Tailwind classes here would render
    unstyled. Prefixed with lc- to avoid clashing with the host app's own styles.
--}}
<div
    x-data="licenseActivationWizard({
        checkConfigUrl: @js(route('license-client.wizard.check-config')),
        checkCryptoUrl: @js(route('license-client.wizard.check-crypto')),
        checkConnectivityUrl: @js(route('license-client.wizard.check-connectivity')),
        activateUrl: @js(route('license-client.wizard.activate')),
        csrfToken: @js(csrf_token()),
    })"
    class="lc-wizard"
>
    <div class="lc-wizard__field">
        <label class="lc-wizard__label" for="lc-license-key">License key</label>
        <input
            id="lc-license-key"
            type="text"
            class="lc-wizard__input"
            placeholder="EOFF-XXXX-XXXX-XXXX-XXXX"
            x-model="licenseKey"
            :disabled="running"
        >
    </div>

    <div class="lc-wizard__progress-track">
        <div class="lc-wizard__progress-fill" :style="`width: ${progress}%`"></div>
    </div>

    <ul class="lc-wizard__steps">
        <template x-for="step in steps" :key="step.key">
            <li class="lc-wizard__step" :class="'lc-wizard__step--' + step.state">
                <span class="lc-wizard__icon">
                    <template x-if="step.state === 'pending'">
                        <span class="lc-wizard__dot"></span>
                    </template>
                    <template x-if="step.state === 'running'">
                        <span class="lc-wizard__spinner"></span>
                    </template>
                    <template x-if="step.state === 'ok'">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.07 7.07a1 1 0 01-1.415 0L4.296 9.851a1 1 0 111.415-1.415l3.212 3.213 6.364-6.364a1 1 0 011.415 0z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="step.state === 'warn'">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="step.state === 'error'">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                    </template>
                </span>
                <span class="lc-wizard__body">
                    <span class="lc-wizard__title" x-text="step.label"></span>
                    <span class="lc-wizard__message" x-show="step.message" x-text="step.message"></span>
                </span>
            </li>
        </template>
    </ul>

    <div class="lc-wizard__actions">
        <button type="button" class="lc-wizard__button lc-wizard__button--primary" @click="run()" :disabled="running || licenseKey.trim() === ''">
            <span x-show="!running" x-text="succeeded ? 'Activate again' : 'Run activation'"></span>
            <span x-show="running">Running…</span>
        </button>
        <template x-if="succeeded">
            <span class="lc-wizard__success">
                License activated<template x-if="expiresAt"><span> — expires <span x-text="expiresAt"></span></span></template><template x-if="!expiresAt"><span> — does not expire</span></template>.
            </span>
        </template>
    </div>
</div>

<style>
    .lc-wizard { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 480px; }
    .lc-wizard__field { margin-bottom: 16px; }
    .lc-wizard__label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    .lc-wizard__input { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; font-family: ui-monospace, SFMono-Regular, monospace; }
    .lc-wizard__input:disabled { background: #f1f5f9; color: #94a3b8; }
    .lc-wizard__progress-track { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-bottom: 18px; }
    .lc-wizard__progress-fill { height: 100%; background: #3366ff; border-radius: 999px; transition: width 300ms ease; }
    .lc-wizard__steps { list-style: none; margin: 0 0 18px; padding: 0; display: flex; flex-direction: column; gap: 4px; }
    .lc-wizard__step { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 10px; }
    .lc-wizard__step--running { background: #eff4ff; }
    .lc-wizard__step--ok { background: #ecfdf5; }
    .lc-wizard__step--warn { background: #fffbeb; }
    .lc-wizard__step--error { background: #fef2f2; }
    .lc-wizard__icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; }
    .lc-wizard__icon svg { width: 20px; height: 20px; }
    .lc-wizard__step--ok .lc-wizard__icon { color: #059669; }
    .lc-wizard__step--warn .lc-wizard__icon { color: #d97706; }
    .lc-wizard__step--error .lc-wizard__icon { color: #dc2626; }
    .lc-wizard__dot { display: block; width: 8px; height: 8px; border-radius: 999px; background: #cbd5e1; margin: 6px; }
    .lc-wizard__spinner { display: block; width: 16px; height: 16px; border: 2px solid #c7d2fe; border-top-color: #3366ff; border-radius: 999px; animation: lc-spin 700ms linear infinite; }
    @keyframes lc-spin { to { transform: rotate(360deg); } }
    .lc-wizard__body { display: flex; flex-direction: column; gap: 2px; }
    .lc-wizard__title { font-size: 14px; font-weight: 600; color: #1e293b; }
    .lc-wizard__message { font-size: 12.5px; color: #64748b; }
    .lc-wizard__step--error .lc-wizard__message { color: #b91c1c; }
    .lc-wizard__step--warn .lc-wizard__message { color: #92400e; }
    .lc-wizard__actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .lc-wizard__button { border: none; border-radius: 10px; padding: 10px 18px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; }
    .lc-wizard__button--primary { background: #3366ff; color: #fff; }
    .lc-wizard__button--primary:disabled { background: #93a9f8; cursor: not-allowed; }
    .lc-wizard__success { font-size: 13px; color: #059669; font-weight: 500; }
</style>

<script>
    function licenseActivationWizard(config) {
        return {
            licenseKey: '',
            running: false,
            succeeded: false,
            expiresAt: null,
            steps: [
                { key: 'config', label: 'Configuration present', state: 'pending', message: '' },
                { key: 'crypto', label: 'Signature verification capability', state: 'pending', message: '' },
                { key: 'connectivity', label: 'License Server reachable', state: 'pending', message: '' },
                { key: 'activate', label: 'Activate & verify token', state: 'pending', message: '' },
            ],

            get progress() {
                const done = this.steps.filter(s => ['ok', 'warn', 'error'].includes(s.state)).length;
                return Math.round((done / this.steps.length) * 100);
            },

            reset() {
                this.succeeded = false;
                this.expiresAt = null;
                this.steps.forEach(s => { s.state = 'pending'; s.message = ''; });
            },

            async runStep(key, fn) {
                const step = this.steps.find(s => s.key === key);
                step.state = 'running';
                try {
                    const result = await fn();
                    step.state = result.ok ? (result.warning ? 'warn' : 'ok') : 'error';
                    step.message = result.message || '';
                    return result.ok;
                } catch (e) {
                    step.state = 'error';
                    step.message = 'Unexpected error: ' + e.message;
                    return false;
                }
            },

            async getJson(url) {
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                return res.json();
            },

            async postJson(url, body) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                    },
                    body: JSON.stringify(body || {}),
                });
                return res.json();
            },

            async run() {
                if (this.licenseKey.trim() === '' || this.running) return;

                this.running = true;
                this.reset();

                if (! await this.runStep('config', () => this.getJson(config.checkConfigUrl))) { this.running = false; return; }
                if (! await this.runStep('crypto', () => this.getJson(config.checkCryptoUrl))) { this.running = false; return; }
                if (! await this.runStep('connectivity', () => this.postJson(config.checkConnectivityUrl))) { this.running = false; return; }

                const activateResult = await (async () => {
                    const step = this.steps.find(s => s.key === 'activate');
                    step.state = 'running';
                    const result = await this.postJson(config.activateUrl, { license_key: this.licenseKey.trim() });
                    step.state = result.ok ? 'ok' : 'error';
                    step.message = result.message || '';
                    return result;
                })();

                if (activateResult.ok) {
                    this.succeeded = true;
                    this.expiresAt = activateResult.expires_at || null;
                }

                this.running = false;
            },
        };
    }
</script>
