    var addPageModal;
    var addUserModal;
    var selectFileModal;
    var uploadMediaModal;
    var editMediaModal;
    var editorMediaTarget = null;
    const MIRAGE_BASEPATH = <?php echo json_encode(BASEPATH); ?>;
    const MIRAGE_CSRF_TOKEN = document.querySelector('meta[name="mirage-csrf-token"]')?.getAttribute('content') || '';
    const MAX_UPLOAD_FILE_BYTES = <?php echo (int) getUploadFileLimitBytes(); ?>;
    const MAX_UPLOAD_TOTAL_BYTES = <?php echo (int) getPostMaxSizeBytes(); ?>;
    const MAX_UPLOAD_FILE_LABEL = <?php echo json_encode(formatBytes(getUploadFileLimitBytes())); ?>;
    const MAX_UPLOAD_TOTAL_LABEL = <?php echo json_encode(formatBytes(getPostMaxSizeBytes())); ?>;
    const MEDIA_UPLOAD_ACCEPTED_EXTENSIONS = <?php echo json_encode(array_values(getAcceptedUploadExtensions())); ?>;
    const MIRAGE_DEFAULT_SITE_TITLE = <?php echo json_encode($siteTitle ?? ''); ?>;
    const MIRAGE_EDITOR_CONTENT_STYLE = `
        body {
            padding: 1rem;
            color: #212529;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            line-height: 1.7;
        }

        img {
            max-width: 100%;
            height: auto;
            cursor: move;
        }

        .mirage-content-button {
            display: inline-block;
            padding: 0.8rem 1.4rem;
            border: 1px solid #7ed321;
            background: #7ed321;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-decoration: none;
            text-transform: uppercase;
        }

        .mirage-content-button--secondary {
            background: transparent;
            color: #7ed321;
        }

        .mirage-columns {
            display: grid;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .mirage-columns--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mirage-columns--3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .mirage-column {
            min-height: 2rem;
        }

        .mirage-embed {
            margin: 1.5rem 0;
        }

        .mirage-embed iframe {
            width: 100%;
            min-height: 22rem;
            border: 0;
        }

        @media (max-width: 767px) {
            .mirage-columns--2,
            .mirage-columns--3 {
                grid-template-columns: 1fr;
            }

            .mirage-embed iframe {
                min-height: 14rem;
            }
        }
    `;

    const originalXhrOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method) {
        this._mirageMethod = String(method || 'GET').toUpperCase();
        return originalXhrOpen.apply(this, arguments);
    };

    const originalXhrSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function(body) {
        if (MIRAGE_CSRF_TOKEN && !['GET', 'HEAD', 'OPTIONS'].includes(this._mirageMethod || 'GET')) {
            this.setRequestHeader('X-Mirage-Csrf', MIRAGE_CSRF_TOKEN);
        }

        return originalXhrSend.call(this, body);
    };

    function canRestoreFocus(element) {
        return element instanceof HTMLElement
            && element.isConnected
            && typeof element.focus === 'function'
            && element.closest('[aria-hidden="true"], [inert]') == null;
    }

    function setupModalFocusManagement(modalElement) {
        if (!(modalElement instanceof HTMLElement)) {
            return;
        }

        if (!modalElement.classList.contains('show')) {
            modalElement.setAttribute('inert', '');
        }

        modalElement.addEventListener('show.bs.modal', function () {
            var activeElement = document.activeElement;
            modalElement._mirageReturnFocus = activeElement instanceof HTMLElement ? activeElement : null;
            modalElement.removeAttribute('inert');
        });

        modalElement.addEventListener('shown.bs.modal', function () {
            modalElement.removeAttribute('inert');
        });

        modalElement.addEventListener('hide.bs.modal', function () {
            var activeElement = document.activeElement;
            if (activeElement instanceof HTMLElement && modalElement.contains(activeElement)) {
                activeElement.blur();
            }

            modalElement.setAttribute('inert', '');
        });

        modalElement.addEventListener('hidden.bs.modal', function () {
            var returnFocusTarget = modalElement._mirageReturnFocus;
            modalElement._mirageReturnFocus = null;

            if (canRestoreFocus(returnFocusTarget)) {
                returnFocusTarget.focus({ preventScroll: true });
            }
        });
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeEditorHtml(value) {
        var html = String(value == null ? '' : value).trim();
        var collapsed = html
            .replace(/&nbsp;/gi, '')
            .replace(/<br[^>]*>/gi, '')
            .replace(/\s+/g, '')
            .toLowerCase();

        return collapsed === '' || collapsed === '<p></p>' ? '' : html;
    }

    function buildMediaFileUrl(filename) {
        return MIRAGE_BASEPATH + '/uploads/' + encodeURIComponent(String(filename || ''));
    }

    function buildEditorImageHtml(mediaItem) {
        var src = escapeHtml(String(mediaItem.fileUrl || buildMediaFileUrl(mediaItem.file || '')));
        var altText = escapeHtml((mediaItem.altText || mediaItem.caption || '').trim());
        var mediaId = escapeHtml(String(mediaItem._id || ''));

        return '<img src="' + src + '" alt="' + altText + '" data-media-id="' + mediaId + '" class="img-responsive">';
    }

    function buildButtonHtml(data) {
        var classes = ['mirage-content-button'];
        if (data.variant === 'secondary') {
            classes.push('mirage-content-button--secondary');
        }

        var href = escapeHtml((data.href || '').trim() || '#');
        var label = escapeHtml((data.text || '').trim() || 'Button');
        var attributes = [
            'href="' + href + '"',
            'class="' + classes.join(' ') + '"'
        ];

        if (data.newTab) {
            attributes.push('target="_blank"');
            attributes.push('rel="noopener noreferrer"');
        }

        return '<a ' + attributes.join(' ') + '>' + label + '</a>';
    }

    function buildColumnsHtml(columnCount) {
        var count = Number(columnCount) === 3 ? 3 : 2;
        var columns = [];

        for (var index = 0; index < count; index++) {
            columns.push('<div class="mirage-column"><p>Column ' + (index + 1) + ' content</p></div>');
        }

        return '<div class="mirage-columns mirage-columns--' + count + '">' + columns.join('') + '</div><p></p>';
    }

    function buildEmbedHtml(rawHtml) {
        var html = String(rawHtml || '').trim();
        if (html === '') {
            return '';
        }

        if (/<iframe[\s>]/i.test(html) && !/<div[^>]+class="[^"]*mirage-embed/i.test(html)) {
            return '<div class="mirage-embed">' + html + '</div>';
        }

        return html;
    }

    function createEmptySiteSettings() {
        return {
            siteTitle: "",
            siteDescription: "",
            socialImage: null,
            socialLocale: "en_US",
            twitterSite: "",
            twitterCreator: "",
            facebookAppId: "",
            footerText: "",
            copyrightText: "",
            googleAnalyticsTrackingCode: ""
        };
    }

    function normalizeSiteSettingsResponse(settings) {
        settings = settings && typeof settings === 'object' ? settings : {};

        return {
            siteTitle: settings.siteTitle || "",
            siteDescription: settings.siteDescription || "",
            socialImage: settings.socialImage == null ? null : settings.socialImage,
            socialLocale: settings.socialLocale || "en_US",
            twitterSite: settings.twitterSite || "",
            twitterCreator: settings.twitterCreator || "",
            facebookAppId: settings.facebookAppId || "",
            footerText: settings.footerText || "",
            copyrightText: settings.copyrightText || "",
            googleAnalyticsTrackingCode: settings.googleAnalyticsTrackingCode || ""
        };
    }

    window.addEventListener('DOMContentLoaded', event => {
        const addPageModalElement = document.getElementById('addPageModal');
        const addUserModalElement = document.getElementById('addUserModal');
        const selectFileModalElement = document.getElementById('selectFileModal');
        const uploadMediaModalElement = document.getElementById('uploadMediaModal');
        const editMediaModalElement = document.getElementById('editMediaModal');

        // Toggle the side navigation
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            // Uncomment Below to persist sidebar toggle between refreshes
            if (localStorage.getItem('mirage|sidebar-toggle') === 'true') {
                document.body.classList.toggle('mirage-sidenav-toggled');
            }
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('mirage-sidenav-toggled');
                localStorage.setItem('mirage|sidebar-toggle', document.body.classList.contains('mirage-sidenav-toggled'));
            });
        }

        [
            addPageModalElement,
            addUserModalElement,
            selectFileModalElement,
            uploadMediaModalElement,
            editMediaModalElement
        ].forEach(setupModalFocusManagement);

        addPageModal = new bootstrap.Modal(addPageModalElement, {});
        addUserModal = new bootstrap.Modal(addUserModalElement, {});
        selectFileModal = new bootstrap.Modal(selectFileModalElement, {});
        uploadMediaModal = new bootstrap.Modal(uploadMediaModalElement, {});
        editMediaModal = new bootstrap.Modal(editMediaModalElement, {});

        selectFileModalElement.addEventListener('hidden.bs.modal', function () {
            if (window.mirageAdminApp != null && typeof window.mirageAdminApp.clearSelectFileTarget === 'function') {
                window.mirageAdminApp.clearSelectFileTarget();
            } else {
                editorMediaTarget = null;
            }
        });

        uploadMediaModalElement.addEventListener('hidden.bs.modal', function () {
            if (window.mirageAdminApp != null && typeof window.mirageAdminApp.clearUploadSelection === 'function') {
                window.mirageAdminApp.clearUploadSelection();
            }
        });
    });

