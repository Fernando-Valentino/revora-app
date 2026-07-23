<style>
    /* Stepper Style */
    .stepper-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
    }
    .stepper-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }
    .stepper-item .step-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--background);
        border: 2px solid var(--border);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    .stepper-item .step-title {
        font-size: 11.5px;
        font-weight: 500;
        color: var(--text-secondary);
        text-align: center;
    }
    .stepper-line {
        flex: 1;
        height: 2px;
        background-color: var(--border);
        margin-top: 17px;
        transition: background-color 0.3s ease;
    }
    .stepper-line.completed {
        background-color: var(--success) !important;
    }
    .stepper-item.active .step-number {
        background-color: var(--primary-blue-light);
        border-color: var(--primary-blue);
        color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.15);
    }
    .stepper-item.active .step-title {
        color: var(--primary-blue);
        font-weight: 600;
    }
    .stepper-item.completed .step-number {
        background-color: var(--success);
        border-color: var(--success);
        color: #ffffff;
    }
    .stepper-item.completed .step-title {
        color: var(--success);
        font-weight: 600;
    }

    /* Card and Table Tweaks */
    .text-primary-custom {
        color: var(--primary-blue);
    }
    .bg-success-subtle {
        background-color: rgba(22, 163, 74, 0.1) !important;
    }
    .bg-danger-subtle {
        background-color: rgba(220, 38, 38, 0.1) !important;
    }
    .bg-primary-subtle {
        background-color: rgba(0, 91, 170, 0.1) !important;
    }
    .text-sm {
        font-size: 13px !important;
    }

    /* Metric Cards Styling to avoid wrapping */
    .metric-card-custom {
        background-color: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px 14px !important;
        box-shadow: 0 1px 3px rgba(0, 91, 170, 0.03);
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 100%;
        transition: all 0.2s ease-in-out;
    }
    .metric-card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 91, 170, 0.08);
        border-color: rgba(0, 91, 170, 0.3);
    }
    .metric-label-custom {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }
    .metric-value-custom {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
        white-space: nowrap !important;
        display: block;
    }
    .metric-value-custom.text-success {
        color: var(--success) !important;
    }

    /* Step checklist styling */
    .progress-step {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 12.5px;
        color: var(--text-secondary);
        padding: 4px 8px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .progress-step.active {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
        font-weight: 600;
    }
    .progress-step.active .step-label {
        color: var(--primary-blue) !important;
    }
    .progress-step.success-step {
        background-color: rgba(22, 163, 74, 0.05);
        color: var(--success);
    }
    .progress-step.success-step .step-label {
        color: var(--success) !important;
    }
    .progress-step.failed-step {
        background-color: rgba(220, 38, 38, 0.05);
        color: var(--danger);
    }
    .progress-step.failed-step .step-label {
        color: var(--danger) !important;
    }

    /* Table Cell Text Wrapping Fix & Full Width */
    table.dataTable,
    .table-custom-nowrap {
        width: 100% !important;
    }
    .table-custom-nowrap th,
    .table-custom-nowrap td {
        white-space: nowrap !important;
        vertical-align: middle;
    }

    /* Custom Nav Pills for Pipeline Steps */
    #v-pills-tab .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 14px !important;
        border-radius: 10px !important;
        margin-bottom: 8px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12.5px !important;
        font-weight: 600;
        text-align: left;
        transition: all 0.2s ease-in-out;
        line-height: 1.3;
    }
    #v-pills-tab .nav-link .tab-step-number {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
        margin-right: 10px;
        flex-shrink: 0;
        border: 2px solid #cbd5e1;
        background-color: #ffffff;
        color: #64748b;
        transition: all 0.2s ease;
    }
    #v-pills-tab .nav-link:hover {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
        border-color: rgba(0, 91, 170, 0.2);
    }
    #v-pills-tab .nav-link:hover .tab-step-number {
        border-color: var(--primary-blue);
        color: var(--primary-blue);
    }
    #v-pills-tab .nav-link.active {
        background-color: var(--primary-blue) !important;
        color: #ffffff !important;
        border-color: var(--primary-blue) !important;
        box-shadow: 0 4px 10px rgba(0, 91, 170, 0.15);
    }
    #v-pills-tab .nav-link.active .tab-step-number {
        background-color: #ffffff;
        border-color: #ffffff;
        color: var(--primary-blue);
    }

    /* Compact table preview styling */
    .table-preview-custom {
        font-size: 11.5px !important;
    }
    .table-preview-custom th,
    .table-preview-custom td {
        padding: 6px 8px !important;
    }

    .bg-light-subtle {
        background-color: #f8fafc !important;
    }
    
    /* Badge variants */
    .badge-active {
        background-color: rgba(22, 163, 74, 0.1);
        color: #16a34a;
    }
    .badge-inactive {
        background-color: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }
    .badge-holiday {
        background-color: rgba(0, 91, 170, 0.1);
        color: #005BAA;
    }
    .badge-weekend {
        background-color: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }
</style>
