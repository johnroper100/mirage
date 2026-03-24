    const App = {
        data() {
            return {
                viewPage: 'general',
                activeCollection: {},
                activeTheme: {},
                pages: [],
                dashboardPages: [],
                dashboardPagesLoading: false,
                counts: {},
                analyticsSummary: {
                    trackingConfigured: false,
                    trackingCode: "",
                    activeVisitors: 0,
                    pageViewsLast30Minutes: 0,
                    pageViewsToday: 0,
                    topPages: [],
                    recentViews: [],
                    lastUpdated: 0
                },
                analyticsLoading: false,
                siteSettings: createEmptySiteSettings(),
                activeUser: {
                    accountType: 2
                },
                users: {},
                menuItems: {},
                mediaItems: [],
                mediaLoading: false,
                mediaError: "",
                mediaSearch: "",
                mediaFilter: "all",
                mediaSort: "newest",
                mediaPage: 1,
                mediaPageSize: 24,
                selectFileSearch: "",
                selectMediaAccepts: "image",
                pendingUploadFiles: [],
                mediaUploading: false,
                formSubmissions: [],
                selectedFormSubmissionIDs: [],
                editingTemplate: {},
                editingTitle: "",
                editingFeaturedImage: null,
                editingDescription: "",
                editingTemplateName: "",
                editingPath: "",
                editingPathless: false,
                editingPasswordProtected: false,
                editingHasSavedPassword: false,
                editingPassword: "",
                editingMode: 0,
                editingID: null,
                editingPublished: true,
                editingDate: null,
                editingEditedDate: null,
                pageOrderDirty: false,
                pageOrderSaving: false,
                selectFileTarget: null,
                editingUser: {
                    "accountType": "",
                    "editingMode": 0,
                },
                editingMediaItem: {
                    type: "image",
                    caption: "",
                    altText: ""
                },
                selectMediaItemType: "image",
                historyInitialized: false,
                themeLoaded: false,
                activeUserLoaded: false
            }
        },
        computed: {
            mediaLibraryStats() {
                var stats = {
                    total: 0,
                    images: 0,
                    files: 0,
                    attention: 0,
                    totalStorageBytes: 0
                };

                this.mediaItems.forEach(function (item) {
                    stats.total += 1;
                    if (item.type === 'image') {
                        stats.images += 1;
                    } else {
                        stats.files += 1;
                    }

                    if (item.storageStatus !== 'ready') {
                        stats.attention += 1;
                    }

                    stats.totalStorageBytes += Number(item.totalStorageBytes || 0);
                });

                return stats;
            },
            userList() {
                return Array.isArray(this.users) ? this.users : Object.values(this.users || {});
            },
            generalSiteTitle() {
                var configuredTitle = typeof this.siteSettings.siteTitle === 'string' ? this.siteSettings.siteTitle.trim() : '';
                if (configuredTitle !== '') {
                    return configuredTitle;
                }

                var fallbackTitle = typeof MIRAGE_DEFAULT_SITE_TITLE === 'string' ? MIRAGE_DEFAULT_SITE_TITLE.trim() : '';
                return fallbackTitle !== '' ? fallbackTitle : 'Your site';
            },
            generalCollectionCount() {
                return Array.isArray(this.activeTheme.collections) ? this.activeTheme.collections.length : 0;
            },
            generalTemplateCount() {
                return Array.isArray(this.activeTheme.templates) ? this.activeTheme.templates.length : 0;
            },
            generalMenuCount() {
                return Array.isArray(this.activeTheme.menus) ? this.activeTheme.menus.length : 0;
            },
            generalMenuItemCount() {
                var total = 0;

                Object.keys(this.menuItems || {}).forEach((menuID) => {
                    if (Array.isArray(this.menuItems[menuID])) {
                        total += this.menuItems[menuID].length;
                    }
                });

                return total;
            },
            generalPublishedCount() {
                return (Array.isArray(this.dashboardPages) ? this.dashboardPages : []).reduce(function (count, page) {
                    return count + (page.isPublished === false ? 0 : 1);
                }, 0);
            },
            generalDraftCount() {
                return (Array.isArray(this.dashboardPages) ? this.dashboardPages : []).reduce(function (count, page) {
                    return count + (page.isPublished === false ? 1 : 0);
                }, 0);
            },
            generalRecentPages() {
                return (Array.isArray(this.dashboardPages) ? this.dashboardPages.slice() : [])
                    .sort(function (left, right) {
                        return Number(right.edited || 0) - Number(left.edited || 0);
                    })
                    .slice(0, 6);
            },
            generalCollectionSummaries() {
                var collections = Array.isArray(this.activeTheme.collections) ? this.activeTheme.collections : [];
                var pages = Array.isArray(this.dashboardPages) ? this.dashboardPages : [];

                return collections
                    .map(function (collection) {
                        var matchingPages = pages.filter(function (page) {
                            return String(page.collection || '') === String(collection.id || '');
                        });

                        return {
                            collection: collection,
                            total: matchingPages.length,
                            drafts: matchingPages.reduce(function (count, page) {
                                return count + (page.isPublished === false ? 1 : 0);
                            }, 0),
                            published: matchingPages.reduce(function (count, page) {
                                return count + (page.isPublished === false ? 0 : 1);
                            }, 0)
                        };
                    })
                    .sort(function (left, right) {
                        if (right.total !== left.total) {
                            return right.total - left.total;
                        }

                        return String(left.collection.name || '').localeCompare(String(right.collection.name || ''));
                    });
            },
            generalUserCounts() {
                return this.userList.reduce(function (counts, user) {
                    counts.total += 1;

                    if (Number(user.accountType) === 0) {
                        counts.admins += 1;
                    } else if (Number(user.accountType) === 1) {
                        counts.editors += 1;
                    } else {
                        counts.authors += 1;
                    }

                    return counts;
                }, {
                    total: 0,
                    admins: 0,
                    editors: 0,
                    authors: 0
                });
            },
            filteredMediaItems() {
                return this.filterAndSortMediaItems(this.mediaItems, this.mediaSearch, this.mediaFilter, this.mediaSort);
            },
            totalMediaPages() {
                return Math.max(1, Math.ceil(this.filteredMediaItems.length / this.mediaPageSize));
            },
            formSubmissionGroups() {
                var groups = [];
                var groupedSubmissions = {};
                var comp = this;

                (Array.isArray(this.formSubmissions) ? this.formSubmissions : []).forEach(function (submission) {
                    var groupKey = comp.getFormSubmissionGroupKey(submission);
                    if (groupedSubmissions[groupKey] == null) {
                        groupedSubmissions[groupKey] = {
                            key: groupKey,
                            label: comp.getFormSubmissionGroupLabel(submission),
                            submissions: []
                        };
                        groups.push(groupedSubmissions[groupKey]);
                    }

                    groupedSubmissions[groupKey].submissions.push(submission);
                });

                groups.sort(function (left, right) {
                    return String(left.label || '').localeCompare(String(right.label || ''));
                });

                return groups;
            },
            hasMultipleSubmissionForms() {
                return this.formSubmissionGroups.length > 1;
            },
            effectiveMediaPage() {
                return Math.min(Math.max(Number(this.mediaPage || 1), 1), this.totalMediaPages);
            },
            paginatedMediaItems() {
                var start = (this.effectiveMediaPage - 1) * this.mediaPageSize;
                return this.filteredMediaItems.slice(start, start + this.mediaPageSize);
            },
            mediaPageStart() {
                if (this.filteredMediaItems.length === 0) {
                    return 0;
                }

                return ((this.effectiveMediaPage - 1) * this.mediaPageSize) + 1;
            },
            mediaPageEnd() {
                if (this.filteredMediaItems.length === 0) {
                    return 0;
                }

                return Math.min(this.filteredMediaItems.length, this.effectiveMediaPage * this.mediaPageSize);
            },
            listMediaItems() {
                var pickerFilter = this.selectMediaAccepts === 'both'
                    ? this.selectMediaItemType
                    : this.selectMediaAccepts;
                return this.filterAndSortMediaItems(this.mediaItems, this.selectFileSearch, pickerFilter, 'newest');
            },
            editingPathPrefix() {
                var subpath = this.activeCollection && typeof this.activeCollection.subpath === 'string'
                    ? this.activeCollection.subpath.trim().replace(/^\/+|\/+$/g, '')
                    : '';

                return subpath !== '' ? subpath + '/' : '';
            },
            hasActiveMediaFilters() {
                return this.mediaSearch.trim() !== ''
                    || this.mediaFilter !== 'all'
                    || this.mediaSort !== 'newest';
            },
            uploadSelectionTotalBytes() {
                return this.pendingUploadFiles.reduce(function (totalBytes, file) {
                    return totalBytes + Number(file.size || 0);
                }, 0);
            },
            isAddPageDisabled() {
                var allowedTemplates = Array.isArray(this.activeCollection.allowed_templates) ? this.activeCollection.allowed_templates : [];
                var title = typeof this.editingTitle === 'string' ? this.editingTitle.trim() : '';

                return title === '' || (this.editingTemplateName === '' && allowedTemplates.length > 1);
            }
        },
        watch: {
            mediaSearch() {
                this.mediaPage = 1;
            },
            mediaFilter() {
                this.mediaPage = 1;
            },
            mediaSort() {
                this.mediaPage = 1;
            }
        },
        methods: {
            getActiveAccountType() {
                return Number(this.activeUser && this.activeUser.accountType);
            },
            getAccountTypeLabel(accountType) {
                if (Number(accountType) === 0) {
                    return 'Administrator';
                }

                if (Number(accountType) === 1) {
                    return 'Editor';
                }

                return 'Author';
            },
            canAccessMenus() {
                return this.getActiveAccountType() !== 2;
            },
            canAccessSettings() {
                return this.getActiveAccountType() === 0;
            },
            canAccessView(page) {
                if (page === 'menus') {
                    return this.getActiveAccountType() !== 2 && this.generalMenuCount > 0;
                }

                if (page === 'forms') {
                    return this.getActiveAccountType() !== 2;
                }

                if (page === 'settings') {
                    return this.canAccessSettings();
                }

                return true;
            },
            ensureAuthorizedView() {
                if (this.canAccessView(this.viewPage)) {
                    return;
                }

                this.viewPage = 'general';
                this.pageOrderDirty = false;
                this.pageOrderSaving = false;

                if (this.historyInitialized) {
                    this.syncAdminHistory(true);
                }
            },
            getCollectionById(collectionID) {
                if (collectionID == null) {
                    return null;
                }

                var normalizedCollectionID = String(collectionID);
                var collections = Array.isArray(this.activeTheme.collections) ? this.activeTheme.collections : [];
                return collections.find(function (collection) {
                    return String(collection.id || '') === normalizedCollectionID;
                }) || null;
            },
            normalizeAdminRoute(route) {
                route = route && typeof route === 'object' ? route : {};

                var normalizedView = String(route.view || 'general').trim();
                var allowedViews = ['general', 'pages', 'editPage', 'menus', 'forms', 'media', 'users', 'settings'];
                if (!allowedViews.includes(normalizedView)) {
                    normalizedView = 'general';
                }

                var normalizedCollectionID = route.collection == null ? '' : String(route.collection).trim();
                if ((normalizedView === 'pages' || normalizedView === 'editPage') && normalizedCollectionID === '') {
                    return {
                        view: 'general'
                    };
                }

                if (normalizedView === 'pages') {
                    return {
                        view: 'pages',
                        collection: normalizedCollectionID
                    };
                }

                if (normalizedView === 'editPage') {
                    var normalizedMode = String(route.mode || 'edit').trim().toLowerCase();
                    if (normalizedMode === 'new') {
                        var normalizedNewRoute = {
                            view: 'editPage',
                            collection: normalizedCollectionID,
                            mode: 'new'
                        };
                        var normalizedTemplateName = route.template == null ? '' : String(route.template).trim();
                        if (normalizedTemplateName !== '') {
                            normalizedNewRoute.template = normalizedTemplateName;
                        }

                        return normalizedNewRoute;
                    }

                    var normalizedPageID = route.page == null ? '' : String(route.page).trim();
                    if (normalizedPageID === '') {
                        return {
                            view: 'pages',
                            collection: normalizedCollectionID
                        };
                    }

                    return {
                        view: 'editPage',
                        collection: normalizedCollectionID,
                        mode: 'edit',
                        page: normalizedPageID
                    };
                }

                return {
                    view: normalizedView
                };
            },
            parseAdminRouteFromLocation(url = window.location.href) {
                var locationUrl = new URL(url, window.location.origin);
                return this.normalizeAdminRoute({
                    view: locationUrl.searchParams.get('adminView'),
                    collection: locationUrl.searchParams.get('adminCollection'),
                    page: locationUrl.searchParams.get('adminPage'),
                    mode: locationUrl.searchParams.get('adminMode'),
                    template: locationUrl.searchParams.get('adminTemplate')
                });
            },
            buildAdminHistoryUrl(route) {
                var normalizedRoute = this.normalizeAdminRoute(route);
                var locationUrl = new URL(window.location.href, window.location.origin);

                [
                    'adminView',
                    'adminCollection',
                    'adminPage',
                    'adminMode',
                    'adminTemplate'
                ].forEach(function (paramName) {
                    locationUrl.searchParams.delete(paramName);
                });

                if (normalizedRoute.view !== 'general') {
                    locationUrl.searchParams.set('adminView', normalizedRoute.view);
                }

                if (normalizedRoute.collection != null) {
                    locationUrl.searchParams.set('adminCollection', normalizedRoute.collection);
                }

                if (normalizedRoute.mode != null) {
                    locationUrl.searchParams.set('adminMode', normalizedRoute.mode);
                }

                if (normalizedRoute.page != null) {
                    locationUrl.searchParams.set('adminPage', normalizedRoute.page);
                }

                if (normalizedRoute.template != null) {
                    locationUrl.searchParams.set('adminTemplate', normalizedRoute.template);
                }

                return locationUrl.pathname + locationUrl.search + locationUrl.hash;
            },
            areAdminRoutesEqual(leftRoute, rightRoute) {
                return JSON.stringify(this.normalizeAdminRoute(leftRoute)) === JSON.stringify(this.normalizeAdminRoute(rightRoute));
            },
            getCurrentAdminRoute() {
                var route = {
                    view: this.viewPage
                };

                if ((this.viewPage === 'pages' || this.viewPage === 'editPage') && this.activeCollection && this.activeCollection.id != null) {
                    route.collection = String(this.activeCollection.id);
                }

                if (this.viewPage === 'editPage') {
                    if (Number(this.editingMode) === 0) {
                        route.mode = 'new';
                        if (this.editingTemplateName != null && String(this.editingTemplateName).trim() !== '') {
                            route.template = String(this.editingTemplateName).trim();
                        }
                    } else {
                        route.mode = 'edit';
                        if (this.editingID != null && String(this.editingID).trim() !== '') {
                            route.page = String(this.editingID).trim();
                        }
                    }
                }

                return this.normalizeAdminRoute(route);
            },
            commitAdminHistory(historyMode = 'push') {
                if (historyMode === 'none') {
                    return;
                }

                this.syncAdminHistory(historyMode === 'replace');
            },
            syncAdminHistory(replace = false) {
                if (!this.historyInitialized || typeof window.history === 'undefined' || typeof window.history.replaceState !== 'function') {
                    return;
                }

                var route = this.getCurrentAdminRoute();
                var currentRoute = this.parseAdminRouteFromLocation(window.location.href);
                var targetUrl = this.buildAdminHistoryUrl(route);
                var state = {
                    mirageAdminRoute: route
                };

                if (!replace && this.areAdminRoutesEqual(route, currentRoute)) {
                    window.history.replaceState(state, document.title, targetUrl);
                    return;
                }

                if (replace) {
                    window.history.replaceState(state, document.title, targetUrl);
                    return;
                }

                window.history.pushState(state, document.title, targetUrl);
            },
            restoreAdminRoute(route, historyMode = 'replace') {
                var normalizedRoute = this.normalizeAdminRoute(route);

                if (normalizedRoute.view === 'general') {
                    return this.openGeneralDashboard(historyMode);
                }

                if (normalizedRoute.view === 'menus') {
                    if (!this.canAccessView('menus')) {
                        return this.openGeneralDashboard(historyMode);
                    }

                    return this.openMenusPage(historyMode);
                }

                if (normalizedRoute.view === 'settings') {
                    if (!this.canAccessView('settings')) {
                        return this.openGeneralDashboard(historyMode);
                    }

                    return this.openSettingsPage(historyMode);
                }

                if (normalizedRoute.view === 'forms' || normalizedRoute.view === 'media' || normalizedRoute.view === 'users') {
                    return this.setPage(normalizedRoute.view, false, historyMode);
                }

                var collection = this.getCollectionById(normalizedRoute.collection);
                if (collection == null) {
                    return this.openGeneralDashboard(historyMode);
                }

                if (normalizedRoute.view === 'pages') {
                    return this.getPages(collection, false, historyMode);
                }

                if (normalizedRoute.mode === 'new') {
                    if (this.viewPage === 'editPage'
                        && Number(this.editingMode) === 0
                        && String(this.activeCollection.id || '') === String(collection.id || '')) {
                        this.activeCollection = collection;
                        if (normalizedRoute.template != null && normalizedRoute.template !== '') {
                            this.editingTemplateName = normalizedRoute.template;
                        }

                        this.commitAdminHistory(historyMode);
                        return true;
                    }

                    this.activeCollection = collection;
                    if (normalizedRoute.template != null && normalizedRoute.template !== '') {
                        this.editingTemplateName = normalizedRoute.template;
                    }

                    return this.editNewPage(false, historyMode, normalizedRoute);
                }

                if (this.viewPage === 'editPage'
                    && Number(this.editingMode) === 1
                    && String(this.editingID || '') === normalizedRoute.page) {
                    this.activeCollection = collection;
                    this.commitAdminHistory(historyMode);
                    return true;
                }

                this.activeCollection = collection;
                return this.editPage(normalizedRoute.page, false, historyMode);
            },
            attemptHistoryInitialization() {
                if (this.historyInitialized || !this.themeLoaded || !this.activeUserLoaded) {
                    return;
                }

                this.historyInitialized = true;
                var initialRoute = this.parseAdminRouteFromLocation(window.location.href);
                if (initialRoute.view === 'general') {
                    this.syncAdminHistory(true);
                    return;
                }

                if (!this.restoreAdminRoute(initialRoute, 'replace')) {
                    this.syncAdminHistory(true);
                }
            },
            handleBrowserPopState(event) {
                if (!this.historyInitialized) {
                    return;
                }

                var route = event && event.state && event.state.mirageAdminRoute
                    ? event.state.mirageAdminRoute
                    : this.parseAdminRouteFromLocation(window.location.href);

                if (!this.restoreAdminRoute(route, 'replace')) {
                    this.syncAdminHistory(true);
                }
            },
            getDate(dateItem) {
                return new Date(dateItem * 1000).toLocaleString();
            },
            formatRelativeTime(dateItem) {
                var timestamp = Number(dateItem || 0);
                if (timestamp <= 0) {
                    return 'Just now';
                }

                var secondsAgo = Math.max(0, Math.floor((Date.now() / 1000) - timestamp));
                if (secondsAgo < 60) {
                    return 'Just now';
                }

                if (secondsAgo < 3600) {
                    var minutesAgo = Math.floor(secondsAgo / 60);
                    return minutesAgo + ' min ago';
                }

                if (secondsAgo < 86400) {
                    var hoursAgo = Math.floor(secondsAgo / 3600);
                    return hoursAgo + ' hr ago';
                }

                return this.getDate(timestamp);
            },
            getFormSubmissionGroupKey(submission) {
                if (submission == null || typeof submission !== 'object') {
                    return 'unknown-form';
                }

                var formID = typeof submission.form === 'string' ? submission.form.trim() : '';
                if (formID !== '') {
                    return 'form:' + formID;
                }

                var formName = typeof submission.formName === 'string' ? submission.formName.trim() : '';
                if (formName !== '') {
                    return 'name:' + formName.toLowerCase();
                }

                return 'unknown-form';
            },
            getFormSubmissionGroupLabel(submission) {
                if (submission != null && typeof submission.formName === 'string' && submission.formName.trim() !== '') {
                    return submission.formName.trim();
                }

                if (submission != null && typeof submission.form === 'string' && submission.form.trim() !== '') {
                    return submission.form.trim();
                }

                return 'Unknown Form';
            },
            getFormSubmissionSelectionLookup() {
                var lookup = {};

                this.selectedFormSubmissionIDs.forEach(function (selectedID) {
                    lookup[String(selectedID)] = true;
                });

                return lookup;
            },
            getFormSubmissionGroupSelectionCount(group) {
                if (group == null || !Array.isArray(group.submissions) || group.submissions.length === 0) {
                    return 0;
                }

                var selectedLookup = this.getFormSubmissionSelectionLookup();
                return group.submissions.reduce(function (selectedCount, submission) {
                    if (selectedLookup[String(submission._id)] === true) {
                        return selectedCount + 1;
                    }

                    return selectedCount;
                }, 0);
            },
            isFormSubmissionGroupFullySelected(group) {
                if (group == null || !Array.isArray(group.submissions) || group.submissions.length === 0) {
                    return false;
                }

                return this.getFormSubmissionGroupSelectionCount(group) === group.submissions.length;
            },
            selectFormSubmissionGroup(group) {
                if (group == null || !Array.isArray(group.submissions) || group.submissions.length === 0) {
                    return;
                }

                var selectedLookup = this.getFormSubmissionSelectionLookup();
                var nextSelection = this.selectedFormSubmissionIDs.slice();

                group.submissions.forEach(function (submission) {
                    var submissionID = String(submission._id);
                    if (selectedLookup[submissionID] !== true) {
                        nextSelection.push(submission._id);
                        selectedLookup[submissionID] = true;
                    }
                });

                this.selectedFormSubmissionIDs = nextSelection;
            },
            clearFormSubmissionGroupSelection(group) {
                if (group == null || !Array.isArray(group.submissions) || group.submissions.length === 0) {
                    return;
                }

                var removeLookup = {};
                group.submissions.forEach(function (submission) {
                    removeLookup[String(submission._id)] = true;
                });

                this.selectedFormSubmissionIDs = this.selectedFormSubmissionIDs.filter(function (selectedID) {
                    return removeLookup[String(selectedID)] !== true;
                });
            },
            formatBytes(bytes) {
                var numericBytes = Number(bytes || 0);
                if (!isFinite(numericBytes) || numericBytes <= 0) {
                    return '0 B';
                }

                var units = ['B', 'KB', 'MB', 'GB', 'TB'];
                var power = Math.min(Math.floor(Math.log(numericBytes) / Math.log(1024)), units.length - 1);
                var value = numericBytes / Math.pow(1024, power);
                var precision = value >= 10 || power === 0 ? 0 : 1;

                return String(Number(value.toFixed(precision))) + ' ' + units[power];
            },
            normalizeMediaSearchText(value) {
                return String(value || '').trim().toLowerCase();
            },
            filterAndSortMediaItems(items, searchQuery, filterMode, sortMode) {
                var query = this.normalizeMediaSearchText(searchQuery);
                var filteredItems = (Array.isArray(items) ? items : []).filter(function (item) {
                    if (!item) {
                        return false;
                    }

                    if (filterMode === 'attention' && item.storageStatus === 'ready') {
                        return false;
                    }

                    if ((filterMode === 'image' || filterMode === 'file') && item.type !== filterMode) {
                        return false;
                    }

                    if (query === '') {
                        return true;
                    }

                    var searchText = [
                        item.displayName,
                        item.originalName,
                        item.file,
                        item.caption,
                        item.altText,
                        item.mimeType
                    ].join(' ').toLowerCase();

                    return searchText.indexOf(query) !== -1;
                });

                filteredItems.sort(function (left, right) {
                    if (sortMode === 'oldest') {
                        return Number(left.edited || left.created || 0) - Number(right.edited || right.created || 0);
                    }

                    if (sortMode === 'name') {
                        return String(left.displayName || left.file || '').localeCompare(String(right.displayName || right.file || ''));
                    }

                    if (sortMode === 'largest') {
                        return Number(right.totalStorageBytes || right.fileSize || 0) - Number(left.totalStorageBytes || left.fileSize || 0);
                    }

                    return Number(right.edited || right.created || 0) - Number(left.edited || left.created || 0);
                });

                return filteredItems;
            },
            resetMediaFilters() {
                this.mediaSearch = "";
                this.mediaFilter = "all";
                this.mediaSort = "newest";
                this.mediaPage = 1;
            },
            goToMediaPage(page) {
                this.mediaPage = Math.min(Math.max(Number(page || 1), 1), this.totalMediaPages);
            },
            getMediaStatusLabel(item) {
                if (item == null || item.storageStatus === 'ready') {
                    return 'Ready';
                }

                if (item.storageStatus === 'missing') {
                    return 'Original Missing';
                }

                if (item.storageStatus === 'degraded') {
                    return 'Preview Missing';
                }

                return 'Needs Attention';
            },
            getMediaStatusBadgeClass(item) {
                if (item == null || item.storageStatus === 'ready') {
                    return 'text-bg-success';
                }

                if (item.storageStatus === 'missing') {
                    return 'text-bg-danger';
                }

                return 'text-bg-warning';
            },
            getMediaMetaSummary(item) {
                if (item == null) {
                    return '';
                }

                var details = [];
                if (item.width && item.height) {
                    details.push(String(item.width) + ' x ' + String(item.height));
                }

                details.push(this.formatBytes(item.fileSize || item.totalStorageBytes || 0));
                details.push('Updated ' + this.getDate(item.edited || item.created));

                return details.join(' - ');
            },
            normalizeMediaAccepts(value) {
                var normalized = String(value || '').trim().toLowerCase();
                if (!normalized) {
                    return 'both';
                }

                if (normalized !== 'image' && normalized !== 'file' && normalized !== 'both') {
                    return 'both';
                }

                return normalized;
            },
            setSelectMediaConstraint(accepts) {
                this.selectMediaAccepts = this.normalizeMediaAccepts(accepts);
                this.selectMediaItemType = this.selectMediaAccepts === 'both' ? 'all' : this.selectMediaAccepts;
            },
            isMediaItemAllowedForAccepts(item, accepts) {
                if (item == null || item.type == null) {
                    return false;
                }

                var normalizedAccepts = this.normalizeMediaAccepts(accepts);
                return normalizedAccepts === 'both' || item.type === normalizedAccepts;
            },
            resolveMediaUrl(url) {
                var normalizedUrl = String(url || '').trim();
                if (normalizedUrl === '') {
                    return '';
                }

                try {
                    return new URL(normalizedUrl, window.location.origin).href;
                } catch (error) {
                    return normalizedUrl;
                }
            },
            fallbackCopyText(text) {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.setAttribute('readonly', 'readonly');
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            },
            copyMediaUrl(item) {
                var mediaUrl = this.resolveMediaUrl(item != null ? item.fileUrl : '');
                if (mediaUrl === '') {
                    return;
                }

                var comp = this;
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(mediaUrl)
                        .then(function () {
                            alert('Media URL copied.');
                        })
                        .catch(function () {
                            comp.fallbackCopyText(mediaUrl);
                            alert('Media URL copied.');
                        });
                    return;
                }

                this.fallbackCopyText(mediaUrl);
                alert('Media URL copied.');
            },
            canSelectMediaItem(item) {
                return item != null
                    && item.fileExists !== false
                    && this.isMediaItemAllowedForAccepts(item, this.selectMediaAccepts);
            },
            syncUploadSelection() {
                var fileInput = document.getElementById('uploadMediaFiles');
                var files = fileInput != null && fileInput.files ? Array.from(fileInput.files) : [];
                this.pendingUploadFiles = files.map(function (file) {
                    return {
                        name: file.name,
                        size: file.size
                    };
                });
            },
            clearUploadSelection() {
                var fileInput = document.getElementById('uploadMediaFiles');
                if (fileInput != null) {
                    fileInput.value = "";
                }

                this.pendingUploadFiles = [];
                this.mediaUploading = false;
            },
            applySiteSettingTokens(text) {
                var siteTitle = typeof this.siteSettings.siteTitle === 'string' ? this.siteSettings.siteTitle.trim() : '';

                return String(text || '')
                    .replace(/\{\{\s*year\s*\}\}/gi, String(new Date().getFullYear()))
                    .replace(/\{\{\s*site(?:_|)title\s*\}\}/gi, siteTitle);
            },
            normalizeOptionalMediaId(itemID) {
                if (itemID == null) {
                    return null;
                }

                if (typeof itemID === 'string') {
                    var normalized = itemID.trim();
                    if (normalized === '' || normalized.toLowerCase() === 'null' || normalized.toLowerCase() === 'undefined') {
                        return null;
                    }

                    return /^\d+$/.test(normalized) ? Number(normalized) : normalized;
                }

                return itemID;
            },
            hasSelectedMedia(itemID) {
                return this.normalizeOptionalMediaId(itemID) != null;
            },
            canEditPageRecord(page) {
                return this.activeUser.accountType != 2 || this.activeUser._id == page.createdUser || this.activeUser._id == page.editedUser;
            },
            getCollectionSortMode(collection = null) {
                var selectedCollection = collection || {};
                var sortMode = typeof selectedCollection.sort === 'string' ? selectedCollection.sort.trim().toLowerCase() : 'newest';
                return ['newest', 'oldest', 'custom'].includes(sortMode) ? sortMode : 'newest';
            },
            canReorderCollectionPages(collection = null) {
                var selectedCollection = collection || this.activeCollection;
                if (this.getCollectionSortMode(selectedCollection) !== 'custom' || !Array.isArray(this.pages) || this.pages.length < 2) {
                    return false;
                }

                return this.pages.every((page) => this.canEditPageRecord(page));
            },
            canLeaveCurrentView(update = false) {
                if (update === true) {
                    return true;
                }

                if (this.viewPage == 'editPage') {
                    return confirm('Are you sure you want to leave? Any unsaved work will be lost.');
                }

                if (this.viewPage == 'pages' && this.pageOrderDirty) {
                    return confirm('Are you sure you want to leave? Any unsaved collection order changes will be lost.');
                }

                return true;
            },
            setPage(page, update = false, historyMode = 'push') {
                if (!this.canLeaveCurrentView(update)) {
                    return false;
                }

                if (!this.canAccessView(page)) {
                    this.ensureAuthorizedView();
                    return false;
                }

                this.viewPage = page;
                if (page !== 'pages') {
                    this.pageOrderDirty = false;
                    this.pageOrderSaving = false;
                }

                if (page !== 'pages' && page !== 'editPage') {
                    this.commitAdminHistory(historyMode);
                }

                return true;
            },
            openGeneralDashboard(historyMode = 'push') {
                if (!this.setPage('general', false, historyMode)) {
                    return;
                }

                this.refreshGeneralDashboard();
                return true;
            },
            openMenusPage(historyMode = 'push') {
                if (!this.setPage('menus', false, historyMode)) {
                    return false;
                }

                this.getAllPages();
                return true;
            },
            openSettingsPage(historyMode = 'push') {
                if (!this.setPage('settings', false, historyMode)) {
                    return false;
                }

                this.getSiteSettings();
                return true;
            },
            refreshGeneralDashboard() {
                this.getCounts();
                this.getDashboardPages();
                this.getUsers();
                this.getMedia();
                this.getAnalyticsSummary();

                if (this.canAccessMenus()) {
                    this.getFormSubmissions();
                    this.getMenus();
                }

                if (this.canAccessSettings()) {
                    this.getSiteSettings();
                }
            },
            getTheme() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.activeTheme = JSON.parse(this.responseText);
                    comp.themeLoaded = true;
                    comp.ensureAuthorizedView();
                    comp.attemptHistoryInitialization();
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/theme", true);
                xmlhttp.send();
            },
            getMedia() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                comp.mediaLoading = true;
                comp.mediaError = "";
                xmlhttp.onload = function () {
                    comp.mediaLoading = false;
                    if (this.status < 200 || this.status >= 300) {
                        comp.mediaItems = [];
                        comp.mediaError = comp.getRequestErrorMessage(this, "The media library could not be loaded.");
                        return;
                    }

                    try {
                        var items = JSON.parse(this.responseText);
                        comp.mediaItems = Array.isArray(items) ? items : [];
                    } catch (error) {
                        comp.mediaItems = [];
                        comp.mediaError = "The media library response could not be read.";
                    }
                }
                xmlhttp.onerror = function () {
                    comp.mediaLoading = false;
                    comp.mediaItems = [];
                    comp.mediaError = "The media library could not be loaded.";
                };
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/media", true);
                xmlhttp.send();
            },
            getFormSubmissions() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    if (this.status < 200 || this.status >= 300) {
                        comp.formSubmissions = [];
                        comp.selectedFormSubmissionIDs = [];
                        return;
                    }

                    var submissions = JSON.parse(this.responseText);
                    comp.formSubmissions = Array.isArray(submissions) ? submissions : Object.values(submissions);
                    comp.selectedFormSubmissionIDs = comp.selectedFormSubmissionIDs.filter(function (selectedID) {
                        return comp.formSubmissions.some(function (submission) {
                            return submission._id == selectedID;
                        });
                    });
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/form", true);
                xmlhttp.send();
            },
            selectAllFormSubmissions() {
                this.selectedFormSubmissionIDs = this.formSubmissions.map(function (submission) {
                    return submission._id;
                });
            },
            clearFormSubmissionSelection() {
                this.selectedFormSubmissionIDs = [];
            },
            deleteFormSubmissionRequest(submissionID) {
                return new Promise(function (resolve) {
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onloadend = function () {
                        resolve(xmlhttp);
                    };
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/form/" + submissionID, true);
                    xmlhttp.send();
                });
            },
            getMenus() {
                if (!this.canAccessMenus()) {
                    this.menuItems = {};
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    if (this.status < 200 || this.status >= 300) {
                        comp.menuItems = {};
                        return;
                    }

                    var initialMenuItems = JSON.parse(this.responseText);
                    comp.menuItems = {};
                    initialMenuItems.forEach(function (item) {
                        item = comp.normalizeMenuItem(item);
                        if (comp.menuItems[item.menuID] == undefined) {
                            comp.menuItems[item.menuID] = [];
                        }
                        comp.menuItems[item.menuID].push(item);
                    });
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/menus", true);
                xmlhttp.send();
            },
            generateMenuItemID() {
                return "menu_" + Date.now().toString(36) + "_" + Math.random().toString(36).slice(2, 10);
            },
            normalizeMenuItem(item = {}, menuID = "", order = 0) {
                return {
                    "menuID": item.menuID || menuID,
                    "itemID": item.itemID || (item._id != undefined ? "legacy_" + item._id : this.generateMenuItemID()),
                    "parentItemID": item.parentItemID || null,
                    "name": item.name || "New Menu Item",
                    "type": Number(item.type || 0),
                    "page": item.page != undefined && item.page !== null ? item.page : "",
                    "link": item.link || "",
                    "order": item.order != undefined ? Number(item.order) : order
                };
            },
            ensureRawMenuItems(menuID) {
                if (!Array.isArray(this.menuItems[menuID])) {
                    this.menuItems[menuID] = [];
                }

                return this.menuItems[menuID];
            },
            getRawMenuItems(menuID) {
                return Array.isArray(this.menuItems[menuID]) ? this.menuItems[menuID] : [];
            },
            isValidMenuParent(menuID, itemID, parentItemID) {
                if (parentItemID == null || parentItemID === "" || itemID === parentItemID) {
                    return false;
                }

                var itemsByID = {};
                this.getRawMenuItems(menuID).forEach(function (item) {
                    itemsByID[item.itemID] = item;
                });

                if (itemsByID[parentItemID] == undefined) {
                    return false;
                }

                var visited = new Set([itemID]);
                var currentParentID = parentItemID;
                while (currentParentID != null) {
                    if (visited.has(currentParentID)) {
                        return false;
                    }

                    visited.add(currentParentID);
                    if (itemsByID[currentParentID] == undefined) {
                        return false;
                    }

                    currentParentID = itemsByID[currentParentID].parentItemID || null;
                }

                return true;
            },
            getMenuDescendantIDs(menuID, itemID) {
                var descendants = [];
                var visited = new Set();
                var walk = (parentItemID) => {
                    this.getRawMenuItems(menuID).forEach((item) => {
                        if (item.parentItemID !== parentItemID || visited.has(item.itemID)) {
                            return;
                        }

                        visited.add(item.itemID);
                        descendants.push(item.itemID);
                        walk(item.itemID);
                    });
                };

                walk(itemID);
                return descendants;
            },
            getMenuItems(menuID) {
                var items = this.getRawMenuItems(menuID);
                if (items.length == 0) {
                    return [];
                }

                var itemsByID = {};
                var sortedItems = items
                    .map((item) => ({
                        item: item,
                        itemID: item.itemID,
                        parentItemID: item.parentItemID || null,
                        order: item.order,
                        children: []
                    }))
                    .sort((a, b) => a.order - b.order);

                sortedItems.forEach((entry) => {
                    itemsByID[entry.itemID] = entry;
                });

                var roots = [];
                sortedItems.forEach((entry) => {
                    var parent = entry.parentItemID ? itemsByID[entry.parentItemID] : null;
                    if (parent != null && this.isValidMenuParent(menuID, entry.itemID, entry.parentItemID)) {
                        parent.children.push(entry);
                    } else {
                        roots.push(entry);
                    }
                });

                var flattenedItems = [];
                var visit = (entry, depth) => {
                    flattenedItems.push({
                        item: entry.item,
                        depth: depth,
                        childCount: entry.children.length
                    });
                    entry.children
                        .sort((a, b) => a.order - b.order)
                        .forEach((childEntry) => visit(childEntry, depth + 1));
                };

                roots
                    .sort((a, b) => a.order - b.order)
                    .forEach((entry) => visit(entry, 0));

                return flattenedItems;
            },
            getMenuParentOptions(menuID, itemID) {
                var blockedItemIDs = new Set([itemID, ...this.getMenuDescendantIDs(menuID, itemID)]);
                return this.getMenuItems(menuID)
                    .map((entry) => entry.item)
                    .filter((item) => !blockedItemIDs.has(item.itemID));
            },
            getMenuItemName(menuID, itemID) {
                if (itemID == null || itemID === "") {
                    return "Top level";
                }

                var item = this.getRawMenuItems(menuID).find((menuItem) => menuItem.itemID === itemID);
                if (item == undefined || item.name === "") {
                    return "Unnamed Item";
                }

                return item.name;
            },
            syncMenuOrders(menuID) {
                var orderLookup = {};
                this.getMenuItems(menuID).forEach(function (entry, index) {
                    orderLookup[entry.item.itemID] = index;
                });

                this.getRawMenuItems(menuID).forEach(function (item) {
                    if (orderLookup[item.itemID] != undefined) {
                        item.order = orderLookup[item.itemID];
                    }
                });
            },
            syncAllMenuOrders() {
                for (let menuID in this.menuItems) {
                    this.syncMenuOrders(menuID);
                }
            },
            onMenuItemTypeChange(item) {
                item.type = Number(item.type);
                if (item.type === 0) {
                    item.link = "";
                    if ((item.page === "" || item.page == null) && Array.isArray(this.pages) && this.pages.length > 0) {
                        item.page = this.pages[0]._id;
                    }
                } else {
                    item.page = "";
                }
            },
            onMenuParentChange(menuID, item) {
                if (!this.isValidMenuParent(menuID, item.itemID, item.parentItemID)) {
                    item.parentItemID = null;
                }

                this.syncMenuOrders(menuID);
            },
            getCounts() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.counts = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/counts", true);
                xmlhttp.send();
            },
            getSiteSettings() {
                if (!this.canAccessSettings()) {
                    this.siteSettings = createEmptySiteSettings();
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    if (this.status < 200 || this.status >= 300) {
                        return;
                    }

                    var settings = JSON.parse(this.responseText);
                    comp.siteSettings = normalizeSiteSettingsResponse(settings);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/settings", true);
                xmlhttp.send();
            },
            getAnalyticsSummary() {
                var emptySummary = {
                    trackingConfigured: false,
                    trackingCode: "",
                    activeVisitors: 0,
                    pageViewsLast30Minutes: 0,
                    pageViewsToday: 0,
                    topPages: [],
                    recentViews: [],
                    lastUpdated: 0
                };
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                comp.analyticsLoading = true;
                xmlhttp.onload = function () {
                    comp.analyticsLoading = false;

                    if (this.status < 200 || this.status >= 300) {
                        comp.analyticsSummary = emptySummary;
                        return;
                    }

                    try {
                        var summary = JSON.parse(this.responseText);
                        comp.analyticsSummary = {
                            trackingConfigured: summary.trackingConfigured === true,
                            trackingCode: summary.trackingCode || "",
                            activeVisitors: Number(summary.activeVisitors || 0),
                            pageViewsLast30Minutes: Number(summary.pageViewsLast30Minutes || 0),
                            pageViewsToday: Number(summary.pageViewsToday || 0),
                            topPages: Array.isArray(summary.topPages) ? summary.topPages : [],
                            recentViews: Array.isArray(summary.recentViews) ? summary.recentViews : [],
                            lastUpdated: Number(summary.lastUpdated || 0)
                        };
                    } catch (error) {
                        comp.analyticsSummary = emptySummary;
                    }
                };
                xmlhttp.onerror = function () {
                    comp.analyticsLoading = false;
                    comp.analyticsSummary = emptySummary;
                };
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/analytics/summary", true);
                xmlhttp.send();
            },
            getActiveUser() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.activeUser = JSON.parse(this.responseText);
                    comp.activeUserLoaded = true;
                    if (comp.activeUser.accountType == 0) {
                        comp.getSiteSettings();
                    } else {
                        comp.siteSettings = createEmptySiteSettings();
                    }

                    if (comp.activeUser.accountType != 2) {
                        comp.getFormSubmissions();
                        comp.getMenus();
                    } else {
                        comp.formSubmissions = [];
                        comp.selectedFormSubmissionIDs = [];
                        comp.menuItems = {};
                    }

                    comp.ensureAuthorizedView();
                    comp.attemptHistoryInitialization();
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/users/active", true);
                xmlhttp.send();
            },
            getUsers() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.users = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/users", true);
                xmlhttp.send();
            },
            addMenuItem(menuID) {
                var pageID = Array.isArray(this.pages) && this.pages.length > 0 ? this.pages[0]._id : "";
                this.ensureRawMenuItems(menuID).push(this.normalizeMenuItem({
                    "menuID": menuID,
                    "name": "New Menu Item",
                    "type": 0,
                    "page": pageID,
                    "link": "",
                    "parentItemID": null,
                    "order": this.getRawMenuItems(menuID).length
                }, menuID, this.getRawMenuItems(menuID).length));
            },
            moveMenuItemUp(menuID, itemID) {
                var orderedItems = this.getMenuItems(menuID);
                var from = orderedItems.findIndex((entry) => entry.item.itemID === itemID);
                if (from < 0 || orderedItems.length < 2) {
                    return;
                }

                var to = from - 1;
                if (to < 0) {
                    to = orderedItems.length - 1;
                }

                var orderedIDs = orderedItems.map((entry) => entry.item.itemID);
                var movedID = orderedIDs.splice(from, 1)[0];
                orderedIDs.splice(to, 0, movedID);

                var orderLookup = {};
                orderedIDs.forEach(function (orderedID, index) {
                    orderLookup[orderedID] = index;
                });

                this.getRawMenuItems(menuID).forEach(function (item) {
                    item.order = orderLookup[item.itemID];
                });
            },
            moveMenuItemDown(menuID, itemID) {
                var orderedItems = this.getMenuItems(menuID);
                var from = orderedItems.findIndex((entry) => entry.item.itemID === itemID);
                if (from < 0 || orderedItems.length < 2) {
                    return;
                }

                var to = from + 1;
                if (to > orderedItems.length - 1) {
                    to = 0;
                }

                var orderedIDs = orderedItems.map((entry) => entry.item.itemID);
                var movedID = orderedIDs.splice(from, 1)[0];
                orderedIDs.splice(to, 0, movedID);

                var orderLookup = {};
                orderedIDs.forEach(function (orderedID, index) {
                    orderLookup[orderedID] = index;
                });

                this.getRawMenuItems(menuID).forEach(function (item) {
                    item.order = orderLookup[item.itemID];
                });
            },
            deleteMenuItem(menuID, itemID) {
                if (confirm('Are you sure you want to do this?')) {
                    var deletedItem = this.getRawMenuItems(menuID).find((item) => item.itemID === itemID);
                    if (deletedItem == undefined) {
                        return;
                    }

                    this.getRawMenuItems(menuID).forEach(function (item) {
                        if (item.parentItemID === itemID) {
                            item.parentItemID = deletedItem.parentItemID;
                        }
                    });
                    this.menuItems[menuID] = this.getRawMenuItems(menuID).filter((item) => item.itemID !== itemID);
                    this.syncMenuOrders(menuID);
                }
            },
            addUser() {
                this.editingUser = {
                    "accountType": "",
                    "notifySubmissions": 1,
                    "editingMode": 0,
                };
                addUserModal.show();
            },
            editUser(user) {
                this.editingUser = {
                    "name": user.name,
                    "email": user.email,
                    "bio": user.bio,
                    "accountType": user.accountType,
                    "notifySubmissions": user.notifySubmissions,
                    "editingMode": 1,
                    "editingID": user._id
                };
                addUserModal.show();
            },
            saveUser() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    addUserModal.hide();
                    comp.getUsers();
                    comp.getCounts();
                }
                if (comp.editingUser.editingMode == 0) {
                    xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/users", true);
                } else {
                    xmlhttp.open("PUT", "<?php echo BASEPATH ?>/api/users/" + comp.editingUser.editingID, true);
                }
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                xmlhttp.send(JSON.stringify(comp.editingUser));
            },
            deleteUser(userID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function () {
                        comp.getUsers();
                        comp.getCounts();
                    }
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/users/" + userID, true);
                    xmlhttp.send();
                }
            },
            syncPageOrders() {
                this.pages.forEach(function (page, index) {
                    page.order = index;
                });
            },
            movePageUp(pageID) {
                if (!this.canReorderCollectionPages() || this.pageOrderSaving) {
                    return;
                }

                var from = this.pages.findIndex((page) => page._id === pageID);
                if (from <= 0) {
                    return;
                }

                var movedPage = this.pages.splice(from, 1)[0];
                this.pages.splice(from - 1, 0, movedPage);
                this.syncPageOrders();
                this.pageOrderDirty = true;
            },
            movePageDown(pageID) {
                if (!this.canReorderCollectionPages() || this.pageOrderSaving) {
                    return;
                }

                var from = this.pages.findIndex((page) => page._id === pageID);
                if (from < 0 || from >= this.pages.length - 1) {
                    return;
                }

                var movedPage = this.pages.splice(from, 1)[0];
                this.pages.splice(from + 1, 0, movedPage);
                this.syncPageOrders();
                this.pageOrderDirty = true;
            },
            savePageOrder() {
                if (!this.canReorderCollectionPages() || !this.pageOrderDirty) {
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                comp.pageOrderSaving = true;
                xmlhttp.onload = function () {
                    comp.pageOrderSaving = false;

                    var response = [];
                    try {
                        response = JSON.parse(this.responseText);
                    } catch (error) {
                        response = [];
                    }

                    if (this.status < 200 || this.status >= 300) {
                        alert((response && response.message) || "Collection order could not be saved.");
                        comp.getPages(comp.activeCollection, true);
                        return;
                    }

                    comp.pages = Array.isArray(response) ? response : Object.values(response);
                    comp.syncPageOrders();
                    comp.pageOrderDirty = false;
                    alert("Collection order saved!");
                }
                xmlhttp.onerror = function () {
                    comp.pageOrderSaving = false;
                    alert("Collection order could not be saved.");
                    comp.getPages(comp.activeCollection, true);
                }
                xmlhttp.open("PUT", "<?php echo BASEPATH ?>/api/collections/" + comp.activeCollection.id + "/order", true);
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                xmlhttp.send(JSON.stringify({
                    pageIDs: comp.pages.map(function (page) {
                        return page._id;
                    })
                }));
            },
            getPages(collection, update = false, historyMode = 'push') {
                if (collection == null) {
                    return false;
                }

                if (!this.canLeaveCurrentView(update)) {
                    return false;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.pages = JSON.parse(this.responseText);
                    comp.activeCollection = collection;
                    comp.syncPageOrders();
                    comp.pageOrderDirty = false;
                    comp.pageOrderSaving = false;
                    comp.setPage('pages', true, 'none');
                    comp.commitAdminHistory(historyMode);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/collections/" + collection.id + "/pages", true);
                xmlhttp.send();
                return true;
            },
            getAllPages() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.pages = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/pages", true);
                xmlhttp.send();
            },
            getDashboardPages() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                comp.dashboardPagesLoading = true;
                xmlhttp.onload = function () {
                    comp.dashboardPagesLoading = false;

                    if (this.status < 200 || this.status >= 300) {
                        comp.dashboardPages = [];
                        return;
                    }

                    try {
                        var pages = JSON.parse(this.responseText);
                        comp.dashboardPages = Array.isArray(pages) ? pages : Object.values(pages || {});
                    } catch (error) {
                        comp.dashboardPages = [];
                    }
                }
                xmlhttp.onerror = function () {
                    comp.dashboardPagesLoading = false;
                    comp.dashboardPages = [];
                };
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/pages", true);
                xmlhttp.send();
            },
            editPage(pageID, update = false, historyMode = 'push') {
                if (pageID == null || String(pageID).trim() === '') {
                    return false;
                }

                if (!this.canLeaveCurrentView(update)) {
                    return false;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    var pageDetails = JSON.parse(this.responseText);
                    comp.editPageTemplate(pageDetails, true, historyMode);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/pages/" + pageID, true);
                xmlhttp.send();
                return true;
            },
            deletePage(pageID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function () {
                        comp.getPages(comp.activeCollection, true, 'replace');
                        comp.getCounts();
                        comp.getMenus();
                    }
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/pages/" + pageID, true);
                    xmlhttp.send();
                }
            },
            addPage() {
                addPageModal.show();
                this.editingTitle = "";
                this.editingFeaturedImage = null;
                this.editingDescription = "";
                this.editingPath = "";
                this.editingTemplateName = "";
                this.editingPasswordProtected = false;
                this.editingHasSavedPassword = false;
                this.editingPassword = "";
            },
            editNewPage(update = false, historyMode = 'push', routeState = null) {
                if (routeState == null && this.isAddPageDisabled) {
                    return false;
                }

                if (!this.canLeaveCurrentView(update)) {
                    return false;
                }

                var comp = this;
                var selectedTemplateName = routeState != null && typeof routeState.template === 'string'
                    ? routeState.template.trim()
                    : String(comp.editingTemplateName || '').trim();
                var allowedTemplates = Array.isArray(comp.activeCollection.allowed_templates) ? comp.activeCollection.allowed_templates : [];

                if (selectedTemplateName !== '' && allowedTemplates.length > 0 && !allowedTemplates.includes(selectedTemplateName)) {
                    selectedTemplateName = '';
                }

                if (selectedTemplateName === '' && allowedTemplates.length > 0) {
                    selectedTemplateName = allowedTemplates[0];
                }

                if (selectedTemplateName === '') {
                    return false;
                }

                comp.editingTemplateName = selectedTemplateName;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.editingTemplate = JSON.parse(this.responseText);
                    if (!comp.setPage('editPage', true, 'none')) {
                        return;
                    }

                    if (String(comp.editingPath || '').trim() === '') {
                        comp.editingPath = comp.editingTitle.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
                    }

                    comp.editingMode = 0;
                    comp.editingPathless = comp.editingTemplate.isPathless;
                    comp.editingPasswordProtected = false;
                    comp.editingHasSavedPassword = false;
                    comp.editingPassword = "";
                    comp.editingID = null;
                    comp.editingPublished = false;
                    comp.editingDate = "Never";
                    comp.editingEditedDate = "Never";
                    addPageModal.hide();
                    comp.commitAdminHistory(historyMode);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/templates/" + selectedTemplateName, true);
                xmlhttp.send();
                return true;
            },
            getTemplateValue(content, field) {
                var comp = this;
                if (content[field.id] != null) {
                    if (field.type != 'list') {
                        field.value = field.type == 'media'
                            ? comp.normalizeOptionalMediaId(content[field.id])
                            : content[field.id];
                    } else {
                        field.value = [];
                        content[field.id].forEach(function (subField) {
                            var subItem = JSON.parse(JSON.stringify(field.fields));
                            subItem.forEach(function (subFields) {
                                comp.getTemplateValue(subField, subFields);
                            });
                            field.value.push(subItem);
                        });
                    }
                }
            },
            editPageTemplate(page, update, historyMode = 'push') {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.editingTemplate = JSON.parse(this.responseText);
                    comp.editingTemplateName = page.templateName;
                    var activeCollection = comp.getCollectionById(page.collection);
                    if (activeCollection != null) {
                        comp.activeCollection = activeCollection;
                    }

                    if (!comp.setPage('editPage', update, 'none')) {
                        return;
                    }

                    comp.editingMode = 1;
                    comp.editingTitle = page.title;
                    comp.editingFeaturedImage = comp.normalizeOptionalMediaId(page.featuredImage);
                    comp.editingDescription = page.description;
                    comp.editingPath = page.path;
                    comp.editingPathless = comp.editingTemplate.isPathless;
                    comp.editingPasswordProtected = page.isPasswordProtected === true;
                    comp.editingHasSavedPassword = page.isPasswordProtected === true;
                    comp.editingPassword = "";
                    comp.editingID = page._id;
                    comp.editingPublished = page.isPublished;
                    var dateObject = new Date(page.edited * 1000);
                    comp.editingEditedDate = dateObject.toLocaleString();
                    var dateObject2 = new Date(page.created * 1000);
                    comp.editingDate = dateObject2.toLocaleString();
                    comp.editingTemplate.sections.forEach(function (section) {
                        section.fields.forEach(function (field) {
                            comp.getTemplateValue(page.content, field);
                        });
                    });
                    comp.commitAdminHistory(historyMode);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/templates/" + page.templateName, true);
                xmlhttp.send();
            },
            saveMenus() {
                if (!this.canAccessMenus()) {
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.getMenus();
                    alert("Menus saved!");
                }
                xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/menus", true);
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                comp.syncAllMenuOrders();
                var allMenuItems = [];
                for (let menuID in comp.menuItems) {
                    comp.menuItems[menuID]
                        .sort((a, b) => a.order - b.order)
                        .forEach(function (item) {
                            allMenuItems.push({
                                "menuID": item.menuID,
                                "itemID": item.itemID,
                                "parentItemID": item.parentItemID || null,
                                "name": item.name,
                                "type": Number(item.type),
                                "page": item.type === 0 ? item.page : "",
                                "link": item.type === 1 ? item.link : "",
                                "order": item.order
                            });
                        });
                }
                xmlhttp.send(JSON.stringify(allMenuItems));
            },
            saveSiteSettings() {
                if (!this.canAccessSettings()) {
                    return;
                }

                if ((this.siteSettings.siteTitle || '').trim() === '') {
                    alert("Site title is required.");
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    var response = {};

                    try {
                        response = JSON.parse(this.responseText);
                    } catch (error) {
                        response = {};
                    }

                    if (this.status < 200 || this.status >= 300) {
                        alert(response.message || "Settings could not be saved.");
                        return;
                    }

                    comp.siteSettings = normalizeSiteSettingsResponse(response);
                    comp.getAnalyticsSummary();
                    alert("Settings saved!");
                }
                xmlhttp.open("PUT", "<?php echo BASEPATH ?>/api/settings", true);
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                xmlhttp.send(JSON.stringify(comp.siteSettings));
            },
            savePage() {
                if (this.editingPasswordProtected && this.editingPassword === "" && !this.editingHasSavedPassword) {
                    alert("Enter a password to protect this page.");
                    return;
                }

                var data = {
                    template: this.editingTemplate,
                    templateName: this.editingTemplateName,
                    title: this.editingTitle,
                    featuredImage: this.normalizeOptionalMediaId(this.editingFeaturedImage),
                    description: this.editingDescription,
                    path: this.editingPath,
                    isPathless: this.editingTemplate.isPathless,
                    collection: this.activeCollection.id,
                    collectionSubpath: this.activeCollection.subpath,
                    isPublished: this.editingPublished,
                    isPasswordProtected: this.editingPasswordProtected,
                    pagePassword: this.editingPassword
                }
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.editPage(JSON.parse(this.responseText)._id, true, 'replace');
                    alert("Page saved!");
                    comp.getCounts();
                }
                if (this.editingMode == 0) {
                    xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/pages", true);
                } else {
                    xmlhttp.open("PUT", "<?php echo BASEPATH ?>/api/pages/" + comp.editingID, true);
                }
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                xmlhttp.send(JSON.stringify(data));
            },
            getMediaItemById(itemID) {
                itemID = this.normalizeOptionalMediaId(itemID);
                if (itemID == null) {
                    return null;
                }

                return this.mediaItems.find(function (item) {
                    return item._id == itemID;
                }) || null;
            },
            buildMediaFileUrl(filename) {
                return buildMediaFileUrl(filename);
            },
            getMediaPreviewUrl(itemID) {
                var mediaItem = this.getMediaItemById(itemID);
                return mediaItem != null ? (mediaItem.previewUrl || mediaItem.fileUrl || null) : null;
            },
            insertEditorImageFromMedia(itemID) {
                var mediaItem = this.getMediaItemById(itemID);
                if (mediaItem == null || mediaItem.type !== 'image' || !mediaItem.fileUrl || editorMediaTarget == null) {
                    return false;
                }

                try {
                    var editor = typeof tinymce !== 'undefined' ? tinymce.get(editorMediaTarget.editorId) : null;
                    if (editor == null) {
                        return false;
                    }

                    editor.focus();
                    if (editorMediaTarget.bookmark != null) {
                        editor.selection.moveToBookmark(editorMediaTarget.bookmark);
                    }

                    var selectedNode = editor.selection.getNode();
                    if (editorMediaTarget.replaceImage && selectedNode != null && selectedNode.nodeName === 'IMG') {
                        editor.dom.setAttrib(selectedNode, 'src', mediaItem.fileUrl);
                        editor.dom.setAttrib(selectedNode, 'alt', mediaItem.altText || mediaItem.caption || '');
                        editor.dom.setAttrib(selectedNode, 'data-media-id', String(mediaItem._id));
                        editor.nodeChanged();
                    } else {
                        editor.insertContent(buildEditorImageHtml(mediaItem));
                    }

                    editor.undoManager.add();
                    return true;
                } catch (error) {
                    console.error('Unable to insert media library image into the editor.', error);
                    return false;
                }
            },
            findTemplateFieldByPath(fields, path, offset) {
                for (var i = 0; i < fields.length; i++) {
                    var field = fields[i];
                    if (field.id != path[offset]) {
                        continue;
                    }
                    if (offset == path.length - 1) {
                        return field;
                    }
                    if (field.type != 'list' || !Array.isArray(field.value)) {
                        return null;
                    }
                    var listItem = field.value[path[offset + 1]];
                    if (!Array.isArray(listItem)) {
                        return null;
                    }
                    return this.findTemplateFieldByPath(listItem, path, offset + 2);
                }
                return null;
            },
            setTemplateFieldValue(path, value) {
                for (var i = 0; i < this.editingTemplate.sections.length; i++) {
                    var field = this.findTemplateFieldByPath(this.editingTemplate.sections[i].fields, path, 0);
                    if (field != null) {
                        field.value = value;
                        return true;
                    }
                }
                return false;
            },
            clearSelectFileTarget() {
                this.selectFileTarget = null;
                this.setSelectMediaConstraint('image');
                this.selectFileSearch = "";
                editorMediaTarget = null;
            },
            selectFileItem(id) {
                var comp = this;
                var mediaItem = comp.getMediaItemById(id);
                if (!comp.canSelectMediaItem(mediaItem)) {
                    alert("The selected file is not available in storage.");
                    return;
                }

                if (comp.selectFileTarget != null && comp.selectFileTarget.type == "featuredImage") {
                    comp.editingFeaturedImage = id;
                    selectFileModal.hide();
                    comp.clearSelectFileTarget();
                    return;
                }
                if (comp.selectFileTarget != null && comp.selectFileTarget.type == "siteSocialImage") {
                    comp.siteSettings.socialImage = id;
                    selectFileModal.hide();
                    comp.clearSelectFileTarget();
                    return;
                }
                if (comp.selectFileTarget != null && comp.selectFileTarget.type == "templateField") {
                    comp.setTemplateFieldValue(comp.selectFileTarget.path, id);
                    selectFileModal.hide();
                    comp.clearSelectFileTarget();
                    return;
                }
                if (comp.selectFileTarget != null && comp.selectFileTarget.type == "editorMedia") {
                    if (!comp.insertEditorImageFromMedia(id)) {
                        alert("The selected image could not be inserted.");
                    }
                    selectFileModal.hide();
                    comp.clearSelectFileTarget();
                    return;
                }
                comp.clearSelectFileTarget();
            },
            openEditorMediaPicker(editor) {
                if (editor == null) {
                    alert("The editor selection could not be located.");
                    return;
                }

                editorMediaTarget = {
                    editorId: editor.id,
                    bookmark: editor.selection.getBookmark(2, true),
                    replaceImage: editor.selection.getNode() != null && editor.selection.getNode().nodeName === 'IMG'
                };
                this.selectFileTarget = {
                    type: "editorMedia"
                };
                this.setSelectMediaConstraint('image');
                selectFileModal.show();
            },
            openUploadMediaModal() {
                uploadMediaModal.show();
            },
            validateUploadFiles(files) {
                if (files.length === 0) {
                    alert("Select at least one file to upload.");
                    return false;
                }

                var totalBytes = 0;
                for (var i = 0; i < files.length; i++) {
                    totalBytes += files[i].size;

                    var extension = String(files[i].name || '').split('.').pop().toLowerCase();
                    if (MEDIA_UPLOAD_ACCEPTED_EXTENSIONS.length > 0 && MEDIA_UPLOAD_ACCEPTED_EXTENSIONS.indexOf(extension) === -1) {
                        alert('"' + files[i].name + '" is not a supported file type.');
                        return false;
                    }

                    if (MAX_UPLOAD_FILE_BYTES > 0 && files[i].size > MAX_UPLOAD_FILE_BYTES) {
                        alert('"' + files[i].name + '" is too large to upload. The maximum allowed size is ' + MAX_UPLOAD_FILE_LABEL + '.');
                        return false;
                    }
                }

                if (MAX_UPLOAD_TOTAL_BYTES > 0 && totalBytes > MAX_UPLOAD_TOTAL_BYTES) {
                    alert('These files are too large to upload together. The total upload limit is ' + MAX_UPLOAD_TOTAL_LABEL + '.');
                    return false;
                }

                return true;
            },
            getRequestErrorMessage(xmlhttp, fallbackMessage = "Request failed. Please try again.") {
                try {
                    var response = JSON.parse(xmlhttp.responseText);
                    if (response.message) {
                        return response.message;
                    }
                } catch (error) {
                }

                return fallbackMessage;
            },
            uploadMediaFiles() {
                var comp = this;
                var fileInput = document.getElementById('uploadMediaFiles');
                var files = Array.from(fileInput.files);

                if (!comp.validateUploadFiles(files)) {
                    return;
                }

                comp.mediaUploading = true;
                var formData = new FormData();
                for (var x = 0; x < files.length; x++) {
                    formData.append("uploadMediaFiles[]", files[x]);
                }

                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.mediaUploading = false;
                    if (xmlhttp.status >= 200 && xmlhttp.status < 300) {
                        comp.clearUploadSelection();
                        uploadMediaModal.hide();
                        comp.getMedia();
                        comp.getCounts();
                    } else {
                        alert(comp.getRequestErrorMessage(xmlhttp, "Upload failed. Please try again."));
                    }
                }
                xmlhttp.onerror = function () {
                    comp.mediaUploading = false;
                    alert("Upload failed. Please try again.");
                };
                xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/media");
                xmlhttp.send(formData);
            },
            deleteMediaFile(itemID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function () {
                        if (xmlhttp.status >= 200 && xmlhttp.status < 300) {
                            comp.getMedia();
                            comp.getCounts();
                        } else {
                            alert(comp.getRequestErrorMessage(xmlhttp, "The media item could not be deleted."));
                        }
                    }
                    xmlhttp.onerror = function () {
                        alert("The media item could not be deleted.");
                    };
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/media/" + itemID, true);
                    xmlhttp.send();
                }
            },
            editMediaItem(item) {
                this.editingMediaItem = Object.assign({}, item, {
                    caption: item.caption || "",
                    altText: item.altText || "",
                    editingID: item._id
                });
                editMediaModal.show();
            },
            saveMediaItem() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    if (xmlhttp.status >= 200 && xmlhttp.status < 300) {
                        editMediaModal.hide();
                        comp.getMedia();
                        comp.getCounts();
                    } else {
                        alert(comp.getRequestErrorMessage(xmlhttp, "The media item could not be saved."));
                    }
                }
                xmlhttp.onerror = function () {
                    alert("The media item could not be saved.");
                };
                xmlhttp.open("PUT", "<?php echo BASEPATH ?>/api/media/" + comp.editingMediaItem.editingID, true);
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                xmlhttp.send(JSON.stringify(comp.editingMediaItem));
            },
            getMediaFilePath(itemID) {
                itemID = this.normalizeOptionalMediaId(itemID);
                if (itemID == null) {
                    return null;
                }

                var mediaItem = this.getMediaItemById(itemID);
                return mediaItem != null ? (mediaItem.displayName || mediaItem.file || null) : null;
            },
            getCollectionById(collectionID) {
                if (!Array.isArray(this.activeTheme.collections)) {
                    return null;
                }

                return this.activeTheme.collections.find(function (collection) {
                    return String(collection.id || '') === String(collectionID || '');
                }) || null;
            },
            getCollectionName(collectionID) {
                var collection = this.getCollectionById(collectionID);
                if (collection != null && typeof collection.name === 'string' && collection.name.trim() !== '') {
                    return collection.name;
                }

                return 'Unknown Collection';
            },
            getPageDisplayPath(page) {
                if (page == null || page.isPathless === true) {
                    return '';
                }

                var path = typeof page.path === 'string' ? page.path.trim().replace(/^\/+/, '') : '';
                var collection = this.getCollectionById(page.collection);
                var subpath = collection != null && typeof collection.subpath === 'string'
                    ? collection.subpath.trim().replace(/^\/+|\/+$/g, '')
                    : '';

                if (path === '' && subpath === '') {
                    return '/';
                }

                return '/' + (subpath !== '' ? subpath + (path !== '' ? '/' : '') : '') + path;
            },
            getPageViewPath(page) {
                var displayPath = this.getPageDisplayPath(page);
                if (displayPath === '') {
                    return null;
                }

                return '<?php echo BASEPATH; ?>' + displayPath;
            },
            getEditingPageViewPath() {
                return this.getPageViewPath({
                    collection: this.activeCollection.id,
                    path: this.editingPath,
                    isPathless: this.editingPathless,
                    isPublished: this.editingPublished,
                    isPasswordProtected: this.editingPasswordProtected
                });
            },
            async deleteFormSubmission(submissionID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var response = await this.deleteFormSubmissionRequest(submissionID);
                    if (response.status >= 200 && response.status < 300) {
                        this.selectedFormSubmissionIDs = this.selectedFormSubmissionIDs.filter(function (selectedID) {
                            return selectedID != submissionID;
                        });
                        this.getFormSubmissions();
                    } else {
                        alert("Delete failed. Please try again.");
                    }
                }
            },
            async deleteSelectedFormSubmissions() {
                if (this.selectedFormSubmissionIDs.length == 0) {
                    return;
                }

                var selectedIDs = this.selectedFormSubmissionIDs.slice();
                var submissionLabel = selectedIDs.length == 1 ? "submission" : "submissions";
                if (confirm("Are you sure you want to delete " + selectedIDs.length + " " + submissionLabel + "?") == true) {
                    var responses = await Promise.all(selectedIDs.map((submissionID) => this.deleteFormSubmissionRequest(submissionID)));
                    var failedDeletes = responses.filter(function (response) {
                        return response.status < 200 || response.status >= 300;
                    });

                    this.clearFormSubmissionSelection();
                    this.getFormSubmissions();

                    if (failedDeletes.length > 0) {
                        alert("Some submissions could not be deleted. Please refresh and try again.");
                    }
                }
            },
            selectFeaturedImage() {
                this.selectFileTarget = {
                    type: "featuredImage"
                };
                this.setSelectMediaConstraint('image');
                selectFileModal.show();
            },
            selectSiteSocialImage() {
                this.selectFileTarget = {
                    type: "siteSocialImage"
                };
                this.setSelectMediaConstraint('image');
                selectFileModal.show();
            },
            quickAddPage(collection) {
                if (collection == null || !this.canLeaveCurrentView()) {
                    return;
                }

                this.activeCollection = collection;
                this.addPage();
            }
        },
        beforeUnmount() {
            if (this._miragePopStateHandler != null) {
                window.removeEventListener('popstate', this._miragePopStateHandler);
                this._miragePopStateHandler = null;
            }
        },
        mounted() {
            this._miragePopStateHandler = this.handleBrowserPopState.bind(this);
            window.addEventListener('popstate', this._miragePopStateHandler);
            this.getTheme();
            this.getMedia();
            this.getCounts();
            this.getDashboardPages();
            this.getUsers();
            this.getActiveUser();
            this.getAnalyticsSummary();
        }
    }

    const app = Vue.createApp(App);
