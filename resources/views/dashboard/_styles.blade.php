@push('styles')
<style>
    .dash-wrap { max-width: 1400px; margin: 0 auto; padding: 24px 32px 8px; width: 100%; }
    .dash-section-title { font-size: 17px; font-weight: 800; color: var(--c-text); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; }
    .dash-chart, .dash-card {
        background: var(--c-surface); border: 1px solid var(--c-border); border-radius: 16px;
        padding: 16px 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }
    .dash-chart-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .dash-chart-title { font-size: 14px; font-weight: 700; color: var(--c-text); }
    .dash-chart-total { font-size: 12px; color: var(--c-text-muted); font-weight: 600; }
    .dash-bar-row { display: grid; grid-template-columns: 120px 1fr 36px; gap: 10px; align-items: center; margin-bottom: 8px; }
    .dash-bar-label { font-size: 12px; color: var(--c-text-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dash-bar-track { height: 12px; background: var(--c-surface-3); border-radius: 6px; overflow: hidden; }
    .dash-bar-fill { height: 100%; border-radius: 6px; min-width: 2px; }
    .dash-bar-value { font-size: 12.5px; font-weight: 700; color: var(--c-text); text-align: right; }
    .dash-card-title { font-size: 14px; font-weight: 700; color: var(--c-text); margin-bottom: 10px; }
    .dash-kv { display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; font-size: 13px; color: var(--c-text-2); }
    .dash-kv strong { color: var(--c-text); }
    .dash-muted { font-size: 12.5px; color: var(--c-text-muted); }
    .dash-btn-disabled {
        margin-top: 12px; width: 100%; padding: 10px; border-radius: 10px; border: none;
        background: var(--c-surface-3); color: var(--c-text-faint); font-weight: 700; cursor: not-allowed;
    }
    .dash-btn-sync { margin-top: 12px; width: 100%; padding: 10px; border-radius: 10px; border: none; cursor: pointer; background: linear-gradient(135deg, #10B981, #059669); color: #FFFFFF; font-weight: 700; }
    .dash-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 12px 0; }
    .dash-step { text-align: center; padding: 12px 8px; border-radius: 12px; background: var(--c-surface-3); color: var(--c-text-muted); font-size: 12px; font-weight: 700; }
    .dash-step.done { background: #D1FAE5; color: #065F46; }
    .dash-step.active { background: #DBEAFE; color: #1E40AF; }
    .dash-step.perbaikan { background: #FEF3C7; color: #92400E; }
    .dash-progress { height: 10px; border-radius: 6px; background: var(--c-surface-3); overflow: hidden; }
    .dash-progress > div { height: 100%; background: linear-gradient(90deg, #34D399, #10B981); }
    .dash-notif-item { padding: 8px 0; border-bottom: 1px dashed var(--c-border); font-size: 13px; }
    .dash-notif-item:last-child { border-bottom: none; }
    .dash-notif-item a { color: var(--c-text); text-decoration: none; font-weight: 700; }
    @media (max-width: 640px) {
        .dash-wrap { padding: 16px; }
        .dash-steps { grid-template-columns: repeat(2, 1fr); }
        .dash-bar-row { grid-template-columns: 90px 1fr 30px; }
    }
</style>
@endpush
