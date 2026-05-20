<style>
    .portal-hero {
        background: linear-gradient(135deg, #1f4e78 0%, #3c8dbc 100%);
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 20px;
        color: #fff;
        box-shadow: 0 4px 14px rgba(60, 141, 188, .22);
    }

    .portal-hero h3 {
        margin: 0 0 4px;
        font-weight: 700;
    }

    .portal-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .78);
    }

    .portal-stat {
        background: #fff;
        border: 1px solid #e4eaf0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        min-height: 96px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
    }

    .portal-stat-label {
        color: #7b8794;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .portal-stat-value {
        color: #172b3a;
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        margin-top: 8px;
    }

    .portal-card {
        background: #fff;
        border: 1px solid #e4eaf0;
        border-radius: 8px;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
    }

    .portal-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #eef2f6;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .portal-card-title {
        color: #172b3a;
        font-weight: 700;
        margin: 0;
    }

    .portal-student {
        padding: 16px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        border-bottom: 1px solid #f0f3f7;
    }

    .portal-student:last-child {
        border-bottom: none;
    }

    .portal-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #e8f0fb;
        color: #3c8dbc;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 24px;
    }

    .portal-student-name {
        color: #172b3a;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }

    .portal-meta {
        color: #7b8794;
        font-size: 12px;
        margin-top: 4px;
    }

    .portal-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .portal-pill {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
    }

    .portal-pill-ok {
        background: #e8f8f0;
        color: #00875a;
        border: 1px solid #b3e8d0;
    }

    .portal-pill-warn {
        background: #fff8e1;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .portal-pill-danger {
        background: #fdecea;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    .portal-empty {
        border: 2px dashed #dfe6ee;
        border-radius: 8px;
        color: #91a0ad;
        padding: 46px 20px;
        text-align: center;
        background: #fff;
    }

    .portal-table {
        margin-bottom: 0;
    }

    .portal-table > thead > tr > th {
        background: #f8fafc;
        color: #6b7a8d;
        font-size: 11px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    @media (max-width: 767px) {
        .portal-student {
            display: block;
        }

        .portal-avatar {
            margin-bottom: 10px;
        }
    }
</style>
