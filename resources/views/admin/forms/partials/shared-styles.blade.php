<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

/* ────────────────────────────────────────────────────────────
   Forms — design language (matches Positions / Departments)
   ──────────────────────────────────────────────────────────── */
.form-shell, .form-shell * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.form-shell { max-width: 1020px; padding: 4px 0 60px; }

/* Page header ── matches ps-page-header from positions */
.form-page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; padding-bottom: 22px; border-bottom: 1.5px solid #ebebeb; flex-wrap: wrap; }
.form-page-title { font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em; line-height: 1.1; margin: 0; display: inline-flex; flex-direction: column; }
.form-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.form-page-sub { margin: 12px 0 0; font-size: 0.88rem; color: #8a8fa0; font-weight: 400; max-width: 620px; line-height: 1.5; }

.form-back-link { display: inline-flex; align-items: center; gap: 6px; color: #6b7280; text-decoration: none; font-size: 0.82rem; font-weight: 500; transition: color .15s; margin-bottom: 8px; }
.form-back-link:hover { color: #0c0c0c; text-decoration: none; }

.form-code-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #0c0c0c; color: #fff; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; border-radius: 6px; align-self: flex-start; margin-bottom: 10px; }

/* ── Stage stepper ── */
.stage-stepper { display: flex; gap: 0; background: #fff; border: 1.5px solid #ebebeb; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; overflow-x: auto; position: relative; }
.stage-stepper::-webkit-scrollbar { height: 6px; }
.stage-stepper::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }
.stage-step { display: flex; align-items: center; flex-shrink: 0; gap: 10px; padding: 0 12px; position: relative; }
.stage-step:not(:last-child)::after { content: ''; position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 16px; height: 1.5px; background: #ebebeb; }
.stage-step__dot { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; background: #f3f4f6; color: #9ca3af; border: 1.5px solid #ebebeb; transition: all .2s; flex-shrink: 0; }
.stage-step__label { font-size: 0.78rem; font-weight: 500; color: #9ca3af; white-space: nowrap; }
.stage-step--done .stage-step__dot { background: #0c0c0c; color: #fff; border-color: #0c0c0c; }
.stage-step--done .stage-step__label { color: #374151; }
.stage-step--active .stage-step__dot { background: #fff; color: #0c0c0c; border-color: #0c0c0c; box-shadow: 0 0 0 4px rgba(12,12,12,.08); }
.stage-step--active .stage-step__label { color: #0c0c0c; font-weight: 600; }

/* ── Form panels (composer body) ── */
.form-composer { display: flex; flex-direction: column; gap: 18px; }
.form-panel { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.form-panel__head { display: flex; gap: 14px; align-items: flex-start; justify-content: space-between; padding: 18px 22px 14px; border-bottom: 1.5px solid #f5f5f5; }
.form-panel__title { font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0; display: inline-flex; flex-direction: column; }
.form-panel__title-bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.form-panel__desc { font-size: 0.78rem; color: #9ca3af; margin: 8px 0 0; line-height: 1.5; max-width: 520px; }
.form-panel__step-num { width: 26px; height: 26px; border-radius: 50%; background: #0c0c0c; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0; }
.form-panel__code { display: inline-flex; align-items: center; justify-content: center; padding: 5px 10px; border-radius: 6px; background: #0c0c0c; color: #fff; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.08em; flex-shrink: 0; align-self: flex-start; }
.form-panel__lockicon { width: 28px; height: 28px; border-radius: 8px; background: #f0fdf4; color: #15803d; display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid #bbf7d0; flex-shrink: 0; align-self: flex-start; }
.form-panel__body { padding: 22px; }
.form-panel--locked { background: #fafafa; }
.form-panel--locked .form-panel__body { background: #fff; }

/* ── Grid + inputs ── */
.form-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 18px 16px; margin: 0; }
.form-field { display: flex; flex-direction: column; gap: 7px; }
.form-field--col-12 { grid-column: span 12; }
.form-field--col-8 { grid-column: span 8; }
.form-field--col-6 { grid-column: span 6; }
.form-field--col-4 { grid-column: span 4; }
.form-field--col-3 { grid-column: span 3; }
.form-field--col-2 { grid-column: span 2; }

@media (max-width: 900px) {
    .form-field--col-8, .form-field--col-6 { grid-column: span 12; }
    .form-field--col-4, .form-field--col-3 { grid-column: span 6; }
}
@media (max-width: 560px) {
    .form-field--col-4, .form-field--col-3, .form-field--col-2 { grid-column: span 12; }
}

.form-grid__heading { grid-column: span 12; font-size: 0.72rem; font-weight: 700; color: #b0b5c0; text-transform: uppercase; letter-spacing: 0.08em; margin: 6px 0 -4px; }

.form-field__label { font-size: 0.78rem; font-weight: 600; color: #374151; line-height: 1.2; letter-spacing: 0.01em; }
.form-field__required { color: #ef4444; margin-left: 2px; }
.form-field__help { font-size: 0.74rem; color: #9ca3af; margin: 4px 0 0; line-height: 1.4; }

.form-control,
.form-select,
.form-shell input[type="text"],
.form-shell input[type="email"],
.form-shell input[type="number"],
.form-shell input[type="date"],
.form-shell input[type="tel"],
.form-shell textarea,
.form-shell select {
    display: block;
    width: 100%;
    padding: 11px 14px;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #111827;
    line-height: 1.4;
    outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
    font-family: 'Outfit', sans-serif !important;
}
.form-control:hover,
.form-select:hover,
.form-shell textarea:hover { border-color: #d1d5db; }
.form-control:focus,
.form-select:focus,
.form-shell input:focus,
.form-shell textarea:focus,
.form-shell select:focus {
    border-color: #0c0c0c;
    box-shadow: 0 0 0 3px rgba(12,12,12,.06);
}
.form-shell textarea { resize: vertical; min-height: 76px; line-height: 1.5; }
.form-control::placeholder,
.form-shell input::placeholder,
.form-shell textarea::placeholder { color: #c7cbd6; }
.form-control:disabled,
.form-shell input:disabled,
.form-shell textarea:disabled,
.form-shell select:disabled { background: #f9fafb; color: #6b7280; cursor: not-allowed; }

/* Currency input group */
.input-group { display: flex; align-items: stretch; border: 1.5px solid #e5e7eb; border-radius: 10px; overflow: hidden; transition: border-color .15s, box-shadow .15s; }
.input-group:focus-within { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.input-group-text { background: #fafafa; border: none; border-right: 1.5px solid #ebebeb; padding: 11px 14px; font-size: 0.85rem; font-weight: 600; color: #6b7280; display: flex; align-items: center; }
.input-group .form-control { border: none; border-radius: 0; }
.input-group .form-control:focus { box-shadow: none; }

/* Radio / checkbox pills */
.radio-group { display: flex; gap: 8px; flex-wrap: wrap; }
.radio-pill, .checkbox-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 16px; border: 1.5px solid #ebebeb; border-radius: 99px;
    cursor: pointer; background: #fff; transition: all .15s; margin: 0;
    font-size: 0.85rem; font-weight: 500; color: #374151;
    user-select: none;
}
.radio-pill:hover, .checkbox-pill:hover { border-color: #0c0c0c; color: #0c0c0c; }
.radio-pill input[type="radio"], .checkbox-pill input[type="checkbox"] { margin: 0; accent-color: #0c0c0c; }
.radio-pill:has(input:checked), .checkbox-pill:has(input:checked) { background: #0c0c0c; color: #fff; border-color: #0c0c0c; }

/* Locked / signed fields display */
.locked-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px 28px; margin: 0; }
.locked-fields__row { padding: 6px 0; border-bottom: 1.5px dashed #ebebeb; }
.locked-fields__row dt { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: #b0b5c0; margin-bottom: 4px; font-weight: 600; }
.locked-fields__row dd { margin: 0; color: #111827; font-size: 0.88rem; font-weight: 500; line-height: 1.4; }
.locked-signature { margin-top: 18px; padding-top: 16px; border-top: 1.5px solid #f5f5f5; }
.locked-signature__label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: #b0b5c0; margin-bottom: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
.locked-signature__img { max-height: 90px; max-width: 280px; background: #fff; border: 1.5px solid #ebebeb; padding: 6px; border-radius: 8px; display: block; }
.locked-signature__broken { display: inline-block; padding: 12px 14px; background: #fef3c7; color: #92400e; border: 1.5px dashed #fde68a; border-radius: 8px; font-size: 0.78rem; font-weight: 500; max-width: 420px; line-height: 1.5; }
.locked-signature__broken code { background: rgba(146, 64, 14, .1); padding: 1px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace !important; font-size: 0.72rem; }
.locked-signature__meta { font-size: 0.72rem; color: #b0b5c0; margin-top: 6px; }
.locked-signature__meta code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace !important; font-size: 0.7rem; }
.locked-signature__badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 99px; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
.locked-signature__badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.locked-signature__badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

.stale-pill { display: inline-block; margin-left: 8px; padding: 2px 7px; border-radius: 99px; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; vertical-align: middle; }
.stale-pill--warn   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.stale-pill--danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.is_dark .stale-pill--warn   { background: #422006; color: #fde68a; border-color: #78350f; }
.is_dark .stale-pill--danger { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }
.is_dark .locked-signature__broken { background: #422006; color: #fde68a; border-color: #78350f; }
.is_dark .locked-signature__badge--ok  { background: #052e16; color: #86efac; border-color: #14532d; }
.is_dark .locked-signature__badge--bad { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }

/* Action buttons */
.form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 4px; flex-wrap: wrap; }
.btn-action { display: inline-flex; align-items: center; gap: 7px; padding: 11px 22px; border-radius: 10px; font-weight: 600; font-size: 0.86rem; border: 1.5px solid transparent; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.btn-action--primary { background: #0c0c0c; color: #fff; }
.btn-action--primary:hover { background: #1f2937; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }
.btn-action--draft { background: #fff; color: #4b5563; border-color: #e5e7eb; }
.btn-action--draft:hover { border-color: #0c0c0c; color: #0c0c0c; }
.btn-action--danger { background: #fff; color: #ef4444; border-color: #fee2e2; }
.btn-action--danger:hover { background: #fef2f2; border-color: #fca5a5; }
.btn-action--ghost { background: transparent; color: #6b7280; border-color: #e5e7eb; }
.btn-action--ghost:hover { background: #f9fafb; color: #374151; }
.btn-action:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

/* Metadata strip (form view) */
.form-meta-strip { display: flex; flex-wrap: wrap; gap: 24px; padding: 16px 22px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 14px; margin-bottom: 18px; }
.form-meta-strip__item { display: flex; flex-direction: column; gap: 3px; }
.form-meta-strip__label { font-size: 0.66rem; text-transform: uppercase; letter-spacing: 0.07em; color: #b0b5c0; font-weight: 600; }
.form-meta-strip__value { font-size: 0.88rem; font-weight: 600; color: #111827; }

/* Alerts */
.alert { display: flex; gap: 10px; padding: 12px 14px; border-radius: 10px; margin-bottom: 14px; font-size: 0.85rem; font-weight: 500; border: 1.5px solid transparent; align-items: flex-start; }
.alert ul { margin: 0; padding-left: 16px; }
.alert-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.alert-danger  { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
.alert-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.alert-info    { background: #eff6ff; color: #1e3a8a; border-color: #bfdbfe; }

/* Status pills */
.status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 0.66rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; border: 1px solid transparent; }
.status-pill::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.status-pill--draft       { background: #f3f4f6; color: #6b7280; border-color: #e5e7eb; }
.status-pill--in_progress { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.status-pill--completed   { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.status-pill--rejected    { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
.status-pill--cancelled   { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.status-pill--archived    { background: #e5e7eb; color: #4b5563; border-color: #d1d5db; }

/* Dark mode */
.is_dark .form-page-title { color: #f3f4f6; }
.is_dark .form-title-bar  { background: #f3f4f6; }
.is_dark .form-page-header { border-color: #1e2330; }
.is_dark .form-page-sub  { color: #6b7280; }
.is_dark .form-back-link { color: #9ca3af; }
.is_dark .form-back-link:hover { color: #f3f4f6; }
.is_dark .form-code-chip { background: #f3f4f6; color: #0c0c0c; }
.is_dark .stage-stepper { background: #111827; border-color: #1e2330; }
.is_dark .stage-step__dot { background: #0f172a; border-color: #1e2330; }
.is_dark .stage-step--done .stage-step__dot { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
.is_dark .stage-step--active .stage-step__dot { background: #0f172a; color: #f3f4f6; border-color: #f3f4f6; }
.is_dark .stage-step__label { color: #6b7280; }
.is_dark .stage-step--active .stage-step__label { color: #f3f4f6; }
.is_dark .form-panel { background: #111827; border-color: #1e2330; }
.is_dark .form-panel__head { border-color: #1e2330; }
.is_dark .form-panel__title { color: #f3f4f6; }
.is_dark .form-panel__title-bar { background: #f3f4f6; }
.is_dark .form-panel__step-num { background: #f3f4f6; color: #0c0c0c; }
.is_dark .form-control,
.is_dark .form-select,
.is_dark .form-shell input,
.is_dark .form-shell textarea,
.is_dark .form-shell select { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .form-control:focus { border-color: #f3f4f6; box-shadow: 0 0 0 3px rgba(243,244,246,.08); }
.is_dark .input-group { border-color: #2d3748; }
.is_dark .input-group-text { background: #0f172a; border-color: #2d3748; color: #9ca3af; }
.is_dark .radio-pill, .is_dark .checkbox-pill { background: #0f172a; border-color: #2d3748; color: #d1d5db; }
.is_dark .radio-pill:has(input:checked), .is_dark .checkbox-pill:has(input:checked) { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
.is_dark .btn-action--primary { background: #f3f4f6; color: #0c0c0c; }
.is_dark .btn-action--primary:hover { background: #e5e7eb; color: #0c0c0c; }
.is_dark .btn-action--draft { background: #0f172a; color: #d1d5db; border-color: #2d3748; }
.is_dark .form-meta-strip { background: #111827; border-color: #1e2330; }
.is_dark .form-meta-strip__value { color: #f3f4f6; }
.is_dark .locked-fields__row dd { color: #f3f4f6; }
</style>
