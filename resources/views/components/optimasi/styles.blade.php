<style>
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
        background-color: #ffffff;
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
    .btn-active-tab {
        background-color: #005BAA !important;
        color: #ffffff !important;
        border: 1.5px solid #005BAA !important;
        box-shadow: 0 2px 6px rgba(0, 91, 170, 0.25);
        outline: none !important;
    }
    #tab-btn-grid:not(.btn-active-tab),
    #tab-btn-gwo:not(.btn-active-tab) {
        background-color: transparent !important;
        border: 1.5px solid transparent !important;
        color: #6b7280 !important;
    }
    #tab-btn-grid:not(.btn-active-tab):hover,
    #tab-btn-gwo:not(.btn-active-tab):hover {
        background-color: rgba(0, 91, 170, 0.07) !important;
        color: #005BAA !important;
        border-color: rgba(0, 91, 170, 0.2) !important;
    }
    .progress-step {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 12.5px;
        color: var(--text-secondary);
        padding: 6px 10px;
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
    .param-input-editable {
        background-color: #ffffff !important;
        border-color: var(--primary-blue) !important;
        box-shadow: 0 0 0 2px rgba(0, 91, 170, 0.12) !important;
    }
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
    .bg-warning-subtle {
        background-color: rgba(245, 158, 11, 0.1) !important;
    }
    .bg-light-subtle {
        background-color: #f8fafc !important;
    }
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
    table.dataTable,
    .table-custom-nowrap {
        width: 100% !important;
    }
    .table-custom-nowrap th,
    .table-custom-nowrap td {
        white-space: nowrap !important;
        vertical-align: middle;
    }
</style>
