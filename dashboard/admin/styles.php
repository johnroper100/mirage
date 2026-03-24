<style>
    .mirage-dashboard-hero {
        border: 1px solid #dbe4ec;
        background: linear-gradient(135deg, #ffffff 0%, #eef5ff 52%, #eefbf3 100%);
    }

    .mirage-dashboard-kicker {
        display: inline-block;
        margin-bottom: 0.8rem;
        color: #1971c2;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .mirage-dashboard-glance {
        display: grid;
        gap: 0.75rem;
        min-width: 15rem;
    }

    .mirage-dashboard-glance-item,
    .mirage-dashboard-quick-row,
    .mirage-dashboard-list-item,
    .mirage-dashboard-health-item {
        border: 1px solid #dbe4ec;
        border-radius: 0.85rem;
        background: rgba(255, 255, 255, 0.92);
        padding: 0.9rem 1rem;
    }

    .mirage-dashboard-glance-item strong {
        display: block;
        margin-top: 0.15rem;
        font-size: 1.25rem;
        line-height: 1.1;
    }

    .mirage-dashboard-stat-card {
        border: 1px solid #dbe4ec;
        background: #ffffff;
    }

    .mirage-dashboard-stat-icon {
        width: 2.9rem;
        height: 2.9rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2f6;
        color: #343a40;
        font-size: 1.15rem;
    }

    .mirage-dashboard-stat-value {
        display: block;
        font-size: 1.8rem;
        line-height: 1;
        font-weight: 700;
        color: #212529;
    }

    .mirage-dashboard-label {
        display: block;
        color: #6c757d;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .mirage-dashboard-quick-list,
    .mirage-dashboard-list,
    .mirage-dashboard-health-list {
        display: grid;
        gap: 0.75rem;
    }

    .mirage-dashboard-quick-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
    }

    .mirage-dashboard-collection-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 0.75rem;
        margin-top: 0.35rem;
        color: #6c757d;
        font-size: 0.86rem;
    }

    .mirage-dashboard-path {
        color: #6c757d;
        font-size: 0.88rem;
        word-break: break-word;
    }

    .mirage-dashboard-health-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .mirage-dashboard-empty {
        border: 1px dashed #ced4da;
        border-radius: 0.85rem;
        background: #f8fafc;
        padding: 1rem;
    }

    .mirage-analytics-metric-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .mirage-analytics-metric {
        border: 1px solid #dbe4ec;
        border-radius: 0.85rem;
        background: #f8fafc;
        padding: 0.9rem 1rem;
    }

    .mirage-analytics-metric strong {
        display: block;
        margin-top: 0.2rem;
        font-size: 1.6rem;
        line-height: 1;
    }

    .mirage-media-stat {
        border: 1px solid #d6dce2;
        border-radius: 0.75rem;
        background: #f8fafc;
        padding: 0.85rem 0.9rem;
        text-align: center;
    }

    .mirage-media-stat small {
        display: block;
        margin-bottom: 0.25rem;
        color: #5f6b76;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .mirage-media-stat strong {
        display: block;
        margin-top: 0.15rem;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .mirage-media-stat--warning {
        border-color: #f0ad4e;
        background: #fff7e6;
    }

    .mirage-media-card-image {
        height: 9.5rem;
        object-fit: cover;
        background: #eef2f6;
    }

    .mirage-media-file-placeholder {
        height: 9.5rem;
        background: linear-gradient(135deg, #eef2f6 0%, #dde5ed 100%);
    }

    .mirage-media-file-placeholder img {
        width: 4.5rem;
        opacity: 0.65;
        cursor: default;
    }

    .mirage-media-option--disabled {
        opacity: 0.65;
    }

    @media (max-width: 767px) {
        .mirage-dashboard-quick-row,
        .mirage-dashboard-health-item {
            flex-direction: column;
            align-items: stretch;
        }

        .mirage-dashboard-glance {
            min-width: 0;
        }

        .mirage-analytics-metric-grid {
            grid-template-columns: 1fr;
        }

        .mirage-media-card-image,
        .mirage-media-file-placeholder {
            height: 7.5rem;
        }
    }
</style>
