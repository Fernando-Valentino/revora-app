{{-- Inline styles for the Laporan (Report) pages --}}
<style>
    .report-container {
        font-family: 'Inter', sans-serif;
        color: #101828;
    }

    /* ---- Toolbar: plain line, no heavy box ---- */
    .toolbar {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(16,24,40,0.04);
        font-size: 13px;
    }
    .toolbar .field {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4B5768;
    }
    .toolbar .field span {
        color: #64748B;
        font-weight: 500;
        font-size: 12.5px;
    }
    .toolbar .field input, 
    .toolbar .field select {
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background-color: #F8FAFC;
        font-size: 13px;
        color: #1E293B;
        font-family: inherit;
        font-weight: 600;
        outline: none;
        padding: 6px 12px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .toolbar .field input:focus, 
    .toolbar .field select:focus {
        border-color: #005BAA;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.1);
    }
    .toolbar .spacer {
        flex: 1;
    }
    .link-btn {
        font-size: 13px;
        font-weight: 600;
        color: #005BAA;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        text-decoration: none;
        transition: color 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .link-btn:hover {
        color: #003B73;
        text-decoration: underline;
    }

    .btn-export-pdf {
        background-color: #FEF2F2;
        border: 1px solid #FCA5A5;
        color: #991B1B !important;
        font-size: 12.5px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(153, 27, 27, 0.05);
    }
    .btn-export-pdf:hover {
        background-color: #FEE2E2;
        border-color: #F87171;
        color: #7F1D1D !important;
        text-decoration: none;
    }

    .btn-export-excel {
        background-color: #F0FDF4;
        border: 1px solid #86EFAC;
        color: #166534 !important;
        font-size: 12.5px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(22, 101, 52, 0.05);
    }
    .btn-export-excel:hover {
        background-color: #DCFCE7;
        border-color: #4ADE80;
        color: #14532D !important;
        text-decoration: none;
    }

    .btn-reset {
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #475569 !important;
        font-size: 12.5px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .btn-reset:hover {
        background-color: #F1F5F9;
        border-color: #CBD5E1;
        color: #1E293B !important;
        text-decoration: none;
    }

    /* ---- Hero: one number, one sentence ---- */
    .hero {
        margin-bottom: 36px;
    }
    .hero .eyebrow {
        font-size: 12px;
        font-weight: 600;
        color: #1A7F4E;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 10px;
    }
    .hero .row {
        display: flex;
        align-items: baseline;
        gap: 16px;
        margin-bottom: 12px;
    }
    .hero .figure {
        font-size: 40px;
        font-weight: 700;
        color: #101828;
        letter-spacing: -0.02em;
    }
    .hero .figure small {
        font-size: 16px;
        font-weight: 500;
        color: #98A2B3;
        margin-left: 4px;
        letter-spacing: 0;
    }
    .hero-bar {
        height: 6px;
        border-radius: 4px;
        background: #E7E9EE;
        overflow: hidden;
        margin-bottom: 14px;
        width: 100%;
        max-width: 600px;
    }
    .hero-bar i {
        display: block;
        height: 100%;
        background: #1A7F4E;
        border-radius: 4px;
        transition: width 0.5s ease-out;
    }
    .hero p {
        font-size: 14.5px;
        color: #4B5768;
        line-height: 1.6;
        margin: 0;
        max-width: 600px;
    }

    /* ---- Quick facts: plain row, not boxed ---- */
    .facts {
        display: flex;
        gap: 0;
        margin-bottom: 40px;
        border-top: 1px solid #E7E9EE;
        border-bottom: 1px solid #E7E9EE;
        padding: 16px 0;
    }
    .fact {
        flex: 1;
        padding-right: 20px;
        border-right: 1px solid #E7E9EE;
    }
    .fact:last-child {
        border-right: none;
        padding-right: 0;
    }
    .fact:not(:first-child) {
        padding-left: 20px;
    }
    .fact .label {
        font-size: 12px;
        color: #98A2B3;
        margin-bottom: 6px;
        text-transform: uppercase;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .fact .value {
        font-size: 15px;
        font-weight: 600;
        color: #101828;
    }
    .fact .value.bad {
        color: #C22B2B;
    }
    .fact .value.good {
        color: #1A7F4E;
    }

    /* ---- Sections ---- */
    .section-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #101828;
    }
    .section-desc {
        font-size: 13px;
        color: #4B5768;
        margin-bottom: 18px;
    }

    /* ---- Chart container ---- */
    .chart-container-card {
        background: #ffffff;
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        padding: 24px;
    }

    /* ---- Future projection card ---- */
    .future-proj-card {
        background: #ffffff;
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        padding: 24px;
    }
    .list-proj-item {
        background: #FAFAFB;
        border: 1px solid #E7E9EE;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    .list-proj-item:hover {
        background: #ffffff;
        border-color: #005BAA;
        transform: translateX(2px);
    }
    .recommendation-box {
        background: rgba(0, 91, 170, 0.03);
        border: 1px solid rgba(0, 91, 170, 0.1);
        border-radius: 10px;
        padding: 14px;
    }

    /* ---- Report card ---- */
    .report-card {
        background: #ffffff;
        border: 1px solid #E7E9EE;
        border-radius: 12px;
        padding: 26px 28px;
        box-shadow: 0 1px 3px rgba(16,24,40,0.04), 0 0 1px rgba(16,24,40,0.05);
        margin-bottom: 20px;
    }

    /* ---- Period (dynamic) filter inputs ---- */
    .toolbar-label {
        color: #98A2B3;
        font-weight: 500;
        font-size: 12.5px;
        white-space: nowrap;
    }
    .toolbar-sep {
        color: #D0D5DD;
        font-weight: 400;
        font-size: 13px;
        margin: 0 2px;
    }
    .period-inputs {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #1E293B;
    }
    .period-inputs input[type="date"],
    .period-inputs input[type="number"],
    .period-inputs select {
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background-color: #F8FAFC;
        font-size: 13px;
        color: #1E293B;
        font-family: inherit;
        font-weight: 600;
        outline: none;
        padding: 6px 12px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .period-inputs input[type="date"]:focus,
    .period-inputs input[type="number"]:focus,
    .period-inputs select:focus {
        border-color: #005BAA;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.1);
    }
    .period-inputs input[type="number"] {
        width: 82px;
        text-align: center;
        -moz-appearance: textfield;
    }
    .period-inputs input[type="number"]::-webkit-inner-spin-button,
    .period-inputs input[type="number"]::-webkit-outer-spin-button {
        opacity: 0.4;
    }

    /* ---- Rayon list: plain rows, single accent scale ---- */
    .rayon-list {
        border-top: 1px solid #E7E9EE;
        margin-bottom: 24px;
    }
    .rayon-row {
        display: grid;
        grid-template-columns: 120px 1fr 120px;
        align-items: center;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #E7E9EE;
    }
    .rayon-row .name {
        font-size: 13.5px;
        font-weight: 600;
        color: #101828;
    }
    .rayon-row .bar-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .rayon-row .bar-track {
        flex: 1;
        height: 6px;
        border-radius: 4px;
        background: #E7E9EE;
        overflow: hidden;
    }
    .rayon-row .bar-fill {
        display: block;
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease-out;
    }
    .rayon-row .pct {
        font-size: 12.5px;
        color: #98A2B3;
        width: 45px;
        text-align: right;
        flex-shrink: 0;
        font-weight: 500;
    }
    .rayon-row .status {
        font-size: 12px;
        font-weight: 600;
        text-align: right;
    }
    .rayon-row .status.good {
        color: #1A7F4E;
    }
    .rayon-row .status.mid {
        color: #3FA772;
    }
    .rayon-row .status.bad {
        color: #C22B2B;
    }

    /* ---- Technical detail, collapsible ---- */
    details.tech-details {
        border-top: 1px solid #E7E9EE;
        padding-top: 20px;
        margin-top: 30px;
    }
    summary.tech-summary {
        font-size: 13.5px;
        font-weight: 600;
        color: #005BAA;
        cursor: pointer;
        list-style: none;
        display: flex;
        align-items: center;
        gap: 8px;
        outline: none;
        user-select: none;
    }
    summary.tech-summary::-webkit-details-marker {
        display: none;
    }
    summary.tech-summary .chev {
        transition: transform .15s;
        color: #98A2B3;
        font-size: 11px;
    }
    details.tech-details[open] summary.tech-summary .chev {
        transform: rotate(180deg);
    }
    .tech-body {
        padding-top: 20px;
    }
    .tech-grid {
        display: flex;
        gap: 0;
        margin-bottom: 24px;
        border-top: 1px solid #E7E9EE;
        border-bottom: 1px solid #E7E9EE;
        padding: 16px 0;
    }
    .tech-metric {
        flex: 1;
        padding-right: 18px;
        border-right: 1px solid #E7E9EE;
    }
    .tech-metric:first-child {
        padding-left: 0;
    }
    .tech-metric:not(:first-child) {
        padding-left: 18px;
    }
    .tech-metric:last-child {
        border-right: none;
        padding-right: 0;
    }
    .tech-metric .l {
        font-size: 11px;
        color: #98A2B3;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 500;
    }
    .tech-metric .v {
        font-size: 16px;
        font-weight: 700;
        color: #101828;
    }

    /* ---- Minimal Table style ---- */
    .table-minimal {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        margin-top: 10px;
    }
    .table-minimal thead th {
        text-align: left;
        font-size: 11px;
        color: #98A2B3;
        font-weight: 600;
        padding: 0 12px 10px 0;
        border-bottom: 1px solid #E7E9EE;
        text-transform: uppercase;
    }
    .table-minimal tbody td {
        padding: 12px 12px 12px 0;
        border-bottom: 1px solid #E7E9EE;
        color: #4B5768;
    }
    .table-minimal tbody td:first-child, 
    .table-minimal thead th:first-child {
        padding-left: 0;
    }
    .table-minimal tbody tr:hover td {
        color: #101828;
        background: #FAFAFB;
    }

    /* Dynamic pulse ring */
    .animate-pulse {
        animation: pulse-ring 2s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.95); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(0.95); opacity: 1; }
    }
</style>

