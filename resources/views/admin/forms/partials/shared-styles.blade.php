<style>
.form-composer { display: flex; flex-direction: column; gap: 18px; }
.form-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.form-panel__head { display: flex; gap: 14px; align-items: flex-start; padding: 16px 20px; border-bottom: 1px solid #f3f4f6; background: #fafbfc; }
.form-panel__code { background: #1d4ed8; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; letter-spacing: 1px; align-self: flex-start; }
.form-panel__lockicon { width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #10b981; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.form-panel__title { font-size: 16px; font-weight: 600; color: #111827; margin: 0; }
.form-panel__desc { font-size: 13px; color: #6b7280; margin: 4px 0 0; }
.form-panel__body { padding: 20px; }
.form-panel--locked { background: #fafafa; }
.form-panel--locked .form-panel__body { background: #fff; }

.form-grid { gap: 0; }
.form-grid__col { padding: 0 8px 16px; }
.form-grid__label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
.form-grid__required { color: #dc2626; margin-left: 2px; }
.form-grid__help { font-size: 11.5px; color: #6b7280; margin: 4px 0 0; }
.form-grid__heading { font-size: 14px; font-weight: 600; color: #4b5563; padding: 0 8px 6px; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.form-control, .form-select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: 14px; width: 100%; }
.form-control:focus, .form-select:focus { outline: none; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }
.input-group { display: flex; align-items: stretch; }
.input-group-text { background: #f3f4f6; border: 1px solid #d1d5db; border-right: none; border-radius: 7px 0 0 7px; padding: 9px 12px; font-size: 13px; color: #4b5563; }
.input-group .form-control { border-radius: 0 7px 7px 0; }

.radio-group { display: flex; gap: 8px; flex-wrap: wrap; }
.radio-pill, .checkbox-pill { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 99px; cursor: pointer; background: #fff; transition: all 0.15s; margin: 0; font-size: 13.5px; }
.radio-pill:hover, .checkbox-pill:hover { border-color: #1d4ed8; }
.radio-pill input[type="radio"], .checkbox-pill input[type="checkbox"] { margin: 0; accent-color: #1d4ed8; }
.radio-pill input[type="radio"]:checked + span,
.checkbox-pill input[type="checkbox"]:checked + span { font-weight: 600; }

.locked-fields { margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 4px 24px; }
.locked-fields__row { padding: 8px 0; border-bottom: 1px dashed #e5e7eb; }
.locked-fields__row dt { font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 2px; }
.locked-fields__row dd { margin: 0; color: #111827; font-size: 14px; }
.locked-signature { margin-top: 18px; padding-top: 14px; border-top: 1px solid #e5e7eb; }
.locked-signature__label { font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 6px; }
.locked-signature__img { max-height: 90px; max-width: 280px; background: #fff; border: 1px solid #e5e7eb; padding: 4px; border-radius: 6px; }
.locked-signature__meta { font-size: 11px; color: #9ca3af; margin-top: 6px; }
.locked-signature__meta code { background: #f3f4f6; padding: 1px 4px; border-radius: 3px; }

.form-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 6px; }
.btn-action { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 8px; font-weight: 600; font-size: 14px; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; }
.btn-action--primary { background: #1d4ed8; color: #fff; }
.btn-action--primary:hover { background: #1e40af; color: #fff; }
.btn-action--draft { background: #fff; color: #4b5563; border-color: #d1d5db; }
.btn-action--draft:hover { border-color: #1d4ed8; color: #1d4ed8; }
.btn-action--danger { background: #fff; color: #dc2626; border-color: #fecaca; }
.btn-action--danger:hover { background: #fee2e2; border-color: #dc2626; }
.btn-action--ghost { background: transparent; color: #6b7280; border-color: #d1d5db; }
.btn-action--ghost:hover { background: #f3f4f6; }

.form-meta-strip { display: flex; flex-wrap: wrap; gap: 14px; padding: 14px 18px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 16px; }
.form-meta-strip__item { display: flex; flex-direction: column; gap: 2px; }
.form-meta-strip__label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }
.form-meta-strip__value { font-size: 14px; font-weight: 600; color: #111827; }

.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; }
.alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.alert-info    { background: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; }

.status-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.status-pill--draft       { background: #f3f4f6; color: #4b5563; }
.status-pill--in_progress { background: #eff6ff; color: #1d4ed8; }
.status-pill--completed   { background: #ecfdf5; color: #065f46; }
.status-pill--rejected    { background: #fee2e2; color: #991b1b; }
.status-pill--cancelled   { background: #fef3c7; color: #92400e; }
.status-pill--archived    { background: #e5e7eb; color: #4b5563; }
</style>
