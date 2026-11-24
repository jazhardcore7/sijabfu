@push('styles')
<style>
    .vb-header { margin-bottom: 24px; }
    .vb-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    .vb-card-header {
        background: var(--c-surface-2);
        border-bottom: 1px solid var(--c-border);
        padding: 14px 22px;
        font-size: 14px; font-weight: 700; color: var(--c-text);
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .vb-card-body { padding: 20px 22px; }
    .vb-filter { display: flex; gap: 10px; flex-wrap: wrap; padding: 16px 22px; border-bottom: 1px solid var(--c-border); }
    .vb-input, .vb-select, .vb-textarea {
        border: 1.5px solid var(--c-border-strong);
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 13px;
        color: var(--c-text);
        background: var(--c-surface);
        font-family: inherit;
    }
    .vb-input { flex: 1; min-width: 220px; }
    .vb-textarea { width: 100%; min-height: 80px; resize: vertical; }
    .vb-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, var(--bps-dark), var(--bps-mid));
        color: #FFFFFF; border: none; border-radius: 10px;
        padding: 10px 18px; font-size: 13px; font-weight: 700;
        cursor: pointer; text-decoration: none;
    }
    .vb-btn.secondary { background: var(--c-surface-2); color: var(--c-text); border: 1.5px solid var(--c-border-strong); }
    .vb-btn.ms { background: linear-gradient(135deg, #15803D, #16A34A); }
    .vb-btn.tms { background: linear-gradient(135deg, #B45309, #EA580C); }
    .vb-table { width: 100%; border-collapse: collapse; }
    .vb-table th {
        text-align: left; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px;
        color: var(--c-text-2); background: var(--c-surface-2);
        padding: 12px 16px; border-bottom: 1px solid var(--c-border);
    }
    .vb-table td { padding: 12px 16px; border-bottom: 1px solid var(--c-border-soft); font-size: 13px; color: var(--c-text); vertical-align: top; }
    .vb-muted { color: var(--c-text-muted); font-size: 12px; }
    .vb-badge { display: inline-flex; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
    .badge-step-1 { background: #FFEDD5; color: #9A3412; }
    .badge-step-2 { background: #DBEAFE; color: #1E40AF; }
    .badge-step-3 { background: #DCFCE7; color: #166534; }
    .badge-step-4 { background: #E0E7FF; color: #3730A3; }
    .badge-step-5 { background: #FEF3C7; color: #92400E; }
    .vb-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
    .vb-grid > div { padding: 12px 22px; border-bottom: 1px solid var(--c-border-soft); }
    .vb-label { font-size: 11px; color: var(--c-text-faint); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .vb-value { font-size: 13.5px; font-weight: 600; color: var(--c-text); }
    .vb-berkas-row { display: grid; grid-template-columns: 1.4fr 1fr 1.4fr; gap: 12px; align-items: start; padding: 14px 0; border-bottom: 1px dashed var(--c-border); }
    .vb-berkas-row:last-child { border-bottom: none; }
    .vb-berkas-title { font-size: 13.5px; font-weight: 700; color: var(--c-text); }
    .vb-links { display: flex; gap: 10px; margin-top: 4px; font-size: 12px; }
    .vb-links a { color: var(--bps-mid); font-weight: 700; text-decoration: none; }
    .vb-radio { display: flex; flex-direction: column; gap: 4px; font-size: 12.5px; color: var(--c-text-2); }
    .vb-alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 16px; display: flex; gap: 8px; }
    .vb-alert.success { background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; }
    .vb-alert.error { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; }
    .vb-decision { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
    .vb-pagination { padding: 16px 22px; }
    @media (max-width: 768px) {
        .vb-grid { grid-template-columns: 1fr; }
        .vb-berkas-row { grid-template-columns: 1fr; }
        .vb-table thead { display: none; }
        .vb-table td { display: block; border-bottom: none; padding: 6px 16px; }
        .vb-table tr { display: block; border-bottom: 1px solid var(--c-border); padding: 8px 0; }
    }
</style>
@endpush
