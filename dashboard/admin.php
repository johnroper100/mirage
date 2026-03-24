<?php include 'header.php'; ?>
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
<div class="d-flex" id="app">
    <!-- Sidebar-->
    <div class="bg-dark text-light" id="sidebar-wrapper">
        <div class="sidebar-heading bg-secondary text-light text-center text-uppercase shadow-sm">Mirage Admin</div>
        <div class="list-group list-group-flush mt-2">
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="openGeneralDashboard()"
                :class="{'active text-light': viewPage == 'general'}"><i class="fa-solid fa-gauge-simple me-1"></i>
                General</span>
            <hr>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="getPages(collection)"
                :class="{'active text-light': (viewPage == 'pages' || viewPage == 'editPage') && activeCollection.id == collection.id}"
                v-for="collection in activeTheme.collections"><i class="fa-solid me-1" :class="collection.icon"></i>
                {{collection.name}}</span>
            <hr>
            <!--<span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('comments')" :class="{'active text-light': viewPage == 'comments'}"><i class="fa-solid fa-comments me-1"></i> Comments</span>-->
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('forms')" :class="{'active text-light': viewPage == 'forms'}" v-if="activeUser.accountType != 2"><i class="fa-solid fa-envelope-open-text me-1"></i> Form Submissions</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('media')"
                :class="{'active text-light': viewPage == 'media'}"><i class="fa-solid fa-folder-tree me-1"></i>
                Media</span>
            <hr>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('menus'); getAllPages();"
                :class="{'active text-light': viewPage == 'menus'}" v-if="canAccessMenus()"><i class="fa-solid fa-chart-bar me-1"></i>
                Menus</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('users')"
                :class="{'active text-light': viewPage == 'users'}"><i class="fa-solid fa-users me-1"></i> Users</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('settings'); getSiteSettings();"
                :class="{'active text-light': viewPage == 'settings'}" v-if="canAccessSettings()"><i class="fa-solid fa-gears me-1"></i> Settings</span>
            <hr>
            <form action="<?php echo BASEPATH ?>/logout" method="POST" class="m-0">
                <?php echo getCsrfTokenFieldHtml(); ?>
                <button type="submit" class="btn btn-link w-100 text-start p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary"><i class="fa-solid fa-right-from-bracket me-1"></i> Log Out</button>
            </form>
            <small class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary">System Version: v<?php echo MIRAGE_VERSION; ?></small>
            <small class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary">PHP Version: v<?php echo phpversion(); ?></small>
            <small class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary"><i class="fa-brands fa-github me-1"></i> <a href="https://github.com/johnroper100/mirage" class="text-secondary" target="_blank">GitHub</a></small>
        </div>
    </div>
    <!-- Page content wrapper-->
    <div id="page-content-wrapper">
        <!-- Top navigation-->
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-dark" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'general'">General</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'pages'">{{activeCollection.name}}</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'editPage' && editingMode == 0">Add Page</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'editPage' && editingMode == 1">Edit Page</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'menus' && canAccessMenus()">Menus</h4>
                <!--<h4 class="mb-0 ms-2" v-if="viewPage == 'comments'">Comments</h4>-->
                <h4 class="mb-0 ms-2" v-if="viewPage == 'forms'">Form Submissions</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'media'">Media</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'users'">Users</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'settings' && canAccessSettings()">Settings</h4>
                <button class="btn btn-dark navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <div class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <a href="<?php echo ORIGBASEPATH; ?>" class="btn btn-primary" v-if="viewPage == 'general'"
                            target="_blank"><i class="fa-solid fa-up-right-from-square me-1"></i> View Site</a>
                        <button class="btn btn-success me-md-2 mb-1 mb-md-0" v-if="viewPage == 'pages' && canReorderCollectionPages()" @click="savePageOrder" :disabled="!pageOrderDirty || pageOrderSaving"><i
                                class="fa-solid fa-floppy-disk me-1"></i><span v-if="!pageOrderSaving">Save Order</span><span v-else>Saving...</span></button>
                        <button class="btn btn-success" v-if="viewPage == 'pages'" @click="addPage"><i
                                class="fa-solid fa-plus me-1"></i> Add Page</button>
                        <a v-bind:href="viewPath(editingPath)" class="btn btn-primary me-md-2 mb-1 mb-md-0"
                            v-if="viewPage == 'editPage' && editingMode == 1 && editingPathless == false" target="_blank"><i
                                class="fa-solid fa-up-right-from-square me-1"></i> View</a>
                        <button class="btn btn-danger me-md-2 mb-1 mb-md-0" @click="deletePage(editingID)"
                            v-if="viewPage == 'editPage' && editingMode == 1"><i class="fa-solid fa-trash-can me-1"></i>
                            Remove</button>
                        <button class="btn btn-success" v-if="viewPage == 'editPage'" @click="savePage"><i
                                class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                        <button class="btn btn-success" v-if="viewPage == 'menus' && canAccessMenus()" @click="saveMenus"><i
                                class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                        <div class="btn-group me-md-2 mb-1 mb-md-0" v-if="viewPage == 'forms'">
                            <button class="btn btn-outline-secondary" @click="selectAllFormSubmissions" :disabled="formSubmissions.length == 0"><i
                                    class="fa-solid fa-check-double me-1"></i> Select All</button>
                            <button class="btn btn-outline-secondary" @click="clearFormSubmissionSelection" :disabled="selectedFormSubmissionIDs.length == 0"><i
                                    class="fa-solid fa-xmark me-1"></i> Select None</button>
                        </div>
                        <button class="btn btn-danger me-md-2 mb-1 mb-md-0" v-if="viewPage == 'forms'"
                            @click="deleteSelectedFormSubmissions" :disabled="selectedFormSubmissionIDs.length == 0"><i
                                class="fa-solid fa-trash-can me-1"></i> Delete Selected<span
                                v-if="selectedFormSubmissionIDs.length > 0"> ({{selectedFormSubmissionIDs.length}})</span></button>
                        <button class="btn btn-success" v-if="viewPage == 'media'" @click="openUploadMediaModal"><i
                                class="fa-solid fa-arrow-up-from-bracket me-1"></i> Upload Media</button>
                        <button class="btn btn-success" v-if="viewPage == 'users'" @click="addUser"
                            v-if="activeUser.accountType != 2"><i class="fa-solid fa-user-plus me-1"></i> Add
                            User</button>
                        <button class="btn btn-success" v-if="viewPage == 'settings' && canAccessSettings()" @click="saveSiteSettings"><i
                                class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Page content-->
        <div class="container-fluid pt-3 pb-3 ps-4 pe-4">
            <div v-if="viewPage == 'general'">
                <div class="row g-3">
                    <div class="col-12 col-xl-8">
                        <div class="card shadow-sm h-100 mirage-dashboard-hero">
                            <div class="card-body">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 h-100">
                                    <div>
                                        <span class="mirage-dashboard-kicker">Overview</span>
                                        <h2 class="mb-2">{{generalSiteTitle}}</h2>
                                        <p class="text-secondary mb-3">Signed in as <strong>{{activeUser.name || 'User'}}</strong> ({{getAccountTypeLabel(activeUser.accountType)}}). Use this dashboard to jump back into content, review what needs attention, and keep the site moving.</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="<?php echo ORIGBASEPATH; ?>" class="btn btn-primary" target="_blank"><i class="fa-solid fa-up-right-from-square me-1"></i> View Site</a>
                                            <button type="button" class="btn btn-outline-secondary" @click="refreshGeneralDashboard" :disabled="dashboardPagesLoading"><i class="fa-solid fa-rotate-right me-1"></i> <span v-if="!dashboardPagesLoading">Refresh</span><span v-else>Refreshing...</span></button>
                                            <button type="button" class="btn btn-outline-secondary" @click="setPage('media')"><i class="fa-solid fa-folder-tree me-1"></i> Media</button>
                                            <button type="button" class="btn btn-outline-secondary" @click="setPage('forms')" v-if="canAccessMenus()"><i class="fa-solid fa-envelope-open-text me-1"></i> Forms</button>
                                            <button type="button" class="btn btn-outline-secondary" @click="setPage('settings'); getSiteSettings();" v-if="canAccessSettings()"><i class="fa-solid fa-gears me-1"></i> Settings</button>
                                        </div>
                                    </div>
                                    <div class="mirage-dashboard-glance">
                                        <div class="mirage-dashboard-glance-item">
                                            <small class="mirage-dashboard-label">Collections</small>
                                            <strong>{{generalCollectionCount}}</strong>
                                            <div class="small text-secondary">{{generalTemplateCount}} templates, {{generalMenuCount}} menus</div>
                                        </div>
                                        <div class="mirage-dashboard-glance-item">
                                            <small class="mirage-dashboard-label">Content Status</small>
                                            <strong>{{generalPublishedCount}}</strong>
                                            <div class="small text-secondary">{{generalDraftCount}} draft<span v-if="generalDraftCount !== 1">s</span> waiting</div>
                                        </div>
                                        <div class="mirage-dashboard-glance-item">
                                            <small class="mirage-dashboard-label">Media Storage</small>
                                            <strong>{{formatBytes(mediaLibraryStats.totalStorageBytes)}}</strong>
                                            <div class="small text-secondary">{{mediaLibraryStats.attention}} item<span v-if="mediaLibraryStats.attention !== 1">s</span> need attention</div>
                                        </div>
                                        <div class="mirage-dashboard-glance-item">
                                            <small class="mirage-dashboard-label">Live Traffic</small>
                                            <strong>{{analyticsSummary.activeVisitors}}</strong>
                                            <div class="small text-secondary">{{analyticsSummary.pageViewsLast30Minutes}} view<span v-if="analyticsSummary.pageViewsLast30Minutes !== 1">s</span> in the last 30 minutes</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">Quick Start</h5>
                                        <p class="small text-secondary mb-0">Open a collection or start a new page.</p>
                                    </div>
                                    <span class="badge text-bg-light border" v-if="dashboardPagesLoading">Loading</span>
                                </div>
                                <div class="mirage-dashboard-quick-list" v-if="generalCollectionSummaries.length > 0">
                                    <div class="mirage-dashboard-quick-row" v-for="summary in generalCollectionSummaries" :key="'collection-summary-' + summary.collection.id">
                                        <div>
                                            <div class="fw-semibold">{{summary.collection.name}}</div>
                                            <div class="mirage-dashboard-collection-meta">
                                                <span>{{summary.total}} page<span v-if="summary.total !== 1">s</span></span>
                                                <span>{{summary.drafts}} draft<span v-if="summary.drafts !== 1">s</span></span>
                                            </div>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" @click="getPages(summary.collection)">Open</button>
                                            <button type="button" class="btn btn-success" @click="quickAddPage(summary.collection)">Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mirage-dashboard-empty text-secondary small" v-else>
                                    No collections are configured in the active theme yet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card shadow-sm h-100 mirage-dashboard-stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mirage-dashboard-stat-icon">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </div>
                                    <div>
                                        <small class="mirage-dashboard-label">Pages</small>
                                        <span class="mirage-dashboard-stat-value">{{counts.pages || dashboardPages.length}}</span>
                                        <div class="small text-secondary">{{generalPublishedCount}} live, {{generalDraftCount}} draft<span v-if="generalDraftCount !== 1">s</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card shadow-sm h-100 mirage-dashboard-stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mirage-dashboard-stat-icon">
                                        <i class="fa-solid fa-pen-ruler"></i>
                                    </div>
                                    <div>
                                        <small class="mirage-dashboard-label">Collections</small>
                                        <span class="mirage-dashboard-stat-value">{{generalCollectionCount}}</span>
                                        <div class="small text-secondary">{{generalTemplateCount}} templates available</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card shadow-sm h-100 mirage-dashboard-stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mirage-dashboard-stat-icon">
                                        <i class="fa-solid fa-photo-film-music"></i>
                                    </div>
                                    <div>
                                        <small class="mirage-dashboard-label">Media</small>
                                        <span class="mirage-dashboard-stat-value">{{counts.media || mediaLibraryStats.total}}</span>
                                        <div class="small text-secondary">{{formatBytes(mediaLibraryStats.totalStorageBytes)}} stored</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="card shadow-sm h-100 mirage-dashboard-stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mirage-dashboard-stat-icon">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <div>
                                        <small class="mirage-dashboard-label">Team</small>
                                        <span class="mirage-dashboard-stat-value">{{generalUserCounts.total || counts.users || 0}}</span>
                                        <div class="small text-secondary">{{generalUserCounts.admins}} admin<span v-if="generalUserCounts.admins !== 1">s</span>, {{generalUserCounts.editors}} editor<span v-if="generalUserCounts.editors !== 1">s</span>, {{generalUserCounts.authors}} author<span v-if="generalUserCounts.authors !== 1">s</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12 col-xl-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">Live Traffic</h5>
                                        <p class="small text-secondary mb-0">First-party recent activity in Mirage, with Google Analytics status layered on top.</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge text-bg-light border" v-if="analyticsLoading">Refreshing</span>
                                        <span class="badge" :class="analyticsSummary.trackingConfigured ? 'text-bg-success' : 'text-bg-warning'">{{analyticsSummary.trackingConfigured ? 'GA Ready' : 'GA Not Set'}}</span>
                                    </div>
                                </div>
                                <div class="mirage-analytics-metric-grid mb-3">
                                    <div class="mirage-analytics-metric">
                                        <small class="mirage-dashboard-label">Active Now</small>
                                        <strong>{{analyticsSummary.activeVisitors}}</strong>
                                        <div class="small text-secondary">Visitors seen in the last five minutes.</div>
                                    </div>
                                    <div class="mirage-analytics-metric">
                                        <small class="mirage-dashboard-label">Last 30 Minutes</small>
                                        <strong>{{analyticsSummary.pageViewsLast30Minutes}}</strong>
                                        <div class="small text-secondary">Recent page views across public pages.</div>
                                    </div>
                                    <div class="mirage-analytics-metric">
                                        <small class="mirage-dashboard-label">Today</small>
                                        <strong>{{analyticsSummary.pageViewsToday}}</strong>
                                        <div class="small text-secondary">Page views since midnight on this server.</div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <div class="fw-semibold mb-2">Top Pages</div>
                                        <div class="mirage-dashboard-empty text-secondary small" v-if="analyticsSummary.topPages.length === 0">
                                            No public traffic has been recorded yet.
                                        </div>
                                        <div class="mirage-dashboard-list" v-else>
                                            <div class="mirage-dashboard-list-item" v-for="page in analyticsSummary.topPages" :key="'analytics-top-page-' + page.path">
                                                <div class="d-flex justify-content-between gap-3">
                                                    <div>
                                                        <div class="fw-semibold">{{page.title || 'Untitled Page'}}</div>
                                                        <div class="mirage-dashboard-path">{{page.path || '/'}}</div>
                                                    </div>
                                                    <span class="badge text-bg-light border align-self-start">{{page.views}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="fw-semibold mb-2">Recent Views</div>
                                        <div class="mirage-dashboard-empty text-secondary small" v-if="analyticsSummary.recentViews.length === 0">
                                            Waiting for public traffic.
                                        </div>
                                        <div class="mirage-dashboard-list" v-else>
                                            <div class="mirage-dashboard-list-item" v-for="view in analyticsSummary.recentViews" :key="'analytics-recent-view-' + view.path + '-' + view.created">
                                                <div class="d-flex justify-content-between gap-3">
                                                    <div>
                                                        <div class="fw-semibold">{{view.title || 'Untitled Page'}}</div>
                                                        <div class="mirage-dashboard-path">{{view.path || '/'}}</div>
                                                    </div>
                                                    <div class="small text-secondary text-end">{{formatRelativeTime(view.created)}}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-secondary mt-3" v-if="canAccessSettings()">
                                    Paste a Google Analytics tracking code in Settings. Theme developers only need <code v-pre>&lt;?php echo $mirageMetaTag; ?&gt;</code> inside the template <code>&lt;head&gt;</code>.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="mb-3">
                                    <h5 class="mb-1">Site Health</h5>
                                    <p class="small text-secondary mb-0">High-signal checks without leaving the dashboard.</p>
                                </div>
                                <div class="mirage-dashboard-health-list">
                                    <div class="mirage-dashboard-health-item">
                                        <div>
                                            <div class="fw-semibold">Draft pages</div>
                                            <div class="small text-secondary">Unpublished content waiting for review or release.</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="generalDraftCount > 0 ? 'text-bg-warning' : 'text-bg-success'">{{generalDraftCount}}</span>
                                        </div>
                                    </div>
                                    <div class="mirage-dashboard-health-item">
                                        <div>
                                            <div class="fw-semibold">Media attention</div>
                                            <div class="small text-secondary">Files missing previews or originals in storage.</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="mediaLibraryStats.attention > 0 ? 'text-bg-warning' : 'text-bg-success'">{{mediaLibraryStats.attention}}</span>
                                        </div>
                                    </div>
                                    <div class="mirage-dashboard-health-item" v-if="canAccessMenus()">
                                        <div>
                                            <div class="fw-semibold">Form inbox</div>
                                            <div class="small text-secondary">Form submissions currently in the dashboard.</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="formSubmissions.length > 0 ? 'text-bg-primary' : 'text-bg-secondary'">{{formSubmissions.length}}</span>
                                        </div>
                                    </div>
                                    <div class="mirage-dashboard-health-item" v-if="canAccessMenus()">
                                        <div>
                                            <div class="fw-semibold">Navigation coverage</div>
                                            <div class="small text-secondary">{{generalMenuItemCount}} item<span v-if="generalMenuItemCount !== 1">s</span> across {{generalMenuCount}} menu<span v-if="generalMenuCount !== 1">s</span>.</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="generalMenuItemCount > 0 ? 'text-bg-success' : 'text-bg-secondary'">{{generalMenuItemCount}}</span>
                                        </div>
                                    </div>
                                    <div class="mirage-dashboard-health-item" v-if="canAccessSettings()">
                                        <div>
                                            <div class="fw-semibold">Site settings</div>
                                            <div class="small text-secondary">Title {{(siteSettings.siteTitle || '').trim() !== '' ? 'configured' : 'needs setup'}}, social {{((siteSettings.siteDescription || '').trim() !== '' || hasSelectedMedia(siteSettings.socialImage) || (siteSettings.twitterSite || '').trim() !== '' || (siteSettings.facebookAppId || '').trim() !== '') ? 'configured' : 'empty'}}.</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="(siteSettings.siteTitle || '').trim() !== '' ? 'text-bg-success' : 'text-bg-warning'">{{(siteSettings.siteTitle || '').trim() !== '' ? 'Ready' : 'Review'}}</span>
                                        </div>
                                    </div>
                                    <div class="mirage-dashboard-health-item" v-if="canAccessSettings()">
                                        <div>
                                            <div class="fw-semibold">Google Analytics</div>
                                            <div class="small text-secondary" v-if="analyticsSummary.trackingConfigured">Tracking code {{analyticsSummary.trackingCode}} is ready for Mirage head injection.</div>
                                            <div class="small text-secondary" v-else>Add a tracking code in Settings to enable native Google Analytics loading.</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="analyticsSummary.trackingConfigured ? 'text-bg-success' : 'text-bg-warning'">{{analyticsSummary.trackingConfigured ? 'Enabled' : 'Needs setup'}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">Recently Updated</h5>
                                        <p class="small text-secondary mb-0">Latest changes across all collections.</p>
                                    </div>
                                    <span class="badge text-bg-light border" v-if="dashboardPagesLoading">Refreshing</span>
                                </div>
                                <div class="mirage-dashboard-empty text-secondary small" v-if="dashboardPagesLoading && generalRecentPages.length === 0">
                                    Loading recent content...
                                </div>
                                <div class="mirage-dashboard-empty text-secondary small" v-else-if="generalRecentPages.length === 0">
                                    No pages have been created yet. Start with a collection on the right.
                                </div>
                                <div class="mirage-dashboard-list" v-else>
                                    <div class="mirage-dashboard-list-item" v-for="page in generalRecentPages" :key="'recent-page-' + page._id">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                            <div>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                    <strong>{{page.title || 'Untitled Page'}}</strong>
                                                    <span class="badge" :class="page.isPublished === false ? 'text-bg-warning' : 'text-bg-success'">{{page.isPublished === false ? 'Draft' : 'Live'}}</span>
                                                    <span class="badge text-bg-light border">{{getCollectionName(page.collection)}}</span>
                                                </div>
                                                <div class="mirage-dashboard-path">
                                                    {{page.templateName || 'Template'}}
                                                    <span v-if="getPageDisplayPath(page)"> - {{getPageDisplayPath(page)}}</span>
                                                </div>
                                                <div class="small text-secondary mt-1">Updated {{getDate(page.edited)}}</div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a class="btn btn-sm btn-outline-secondary" :href="getPageViewPath(page)" target="_blank" v-if="getPageViewPath(page)"><i class="fa-solid fa-up-right-from-square me-1"></i> View</a>
                                                <button type="button" class="btn btn-sm btn-primary" @click="editPage(page._id, false)" v-if="canEditPageRecord(page)"><i class="fa-solid fa-pen-to-square me-1"></i> Edit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'pages'">
                <div class="alert alert-light border shadow-sm mt-2 mb-0" v-if="getCollectionSortMode(activeCollection) == 'custom'">
                    This collection uses custom ordering. Arrange items with the arrow buttons, then save the order.
                </div>
                <ul class="list-group mt-2 shadow-sm">
                    <li v-for="(page, index) in pages" class="list-group-item">
                        <div class="row mt-1">
                            <div class="col-12 col-md-9">
                                <h4><small class="text-warning me-1" v-if="page.isPublished == false">🔒</small>{{page.title}}</h4>
                                <h6 class="text-secondary" v-if="page.isPathless == false">T: {{page.templateName}} <i
                                        class="fa-solid fa-right-long"></i> /<span
                                        v-if="activeCollection.subpath">{{activeCollection.subpath}}/</span>{{page.path}}
                                </h6>
                                <h6 class="text-secondary" v-else>T: {{page.templateName}}</h6>
                            </div>
                            <div class="col-12 col-md-3 text-md-end">
                                <button class="btn btn-outline-secondary btn-sm me-1" @click="movePageUp(page._id)" :disabled="pageOrderSaving || index === 0" v-if="canReorderCollectionPages()"><i
                                        class="fa-solid fa-angle-up"></i></button>
                                <button class="btn btn-outline-secondary btn-sm me-1" @click="movePageDown(page._id)" :disabled="pageOrderSaving || index === pages.length - 1" v-if="canReorderCollectionPages()"><i
                                        class="fa-solid fa-angle-down"></i></button>
                                <a :href="viewPath(page.path)" class="btn btn-primary btn-sm me-1" target="_blank" v-if="page.isPathless == false"><i
                                        class="fa-solid fa-up-right-from-square me-1"></i> View</a>
                                <button class="btn btn-danger btn-sm me-1" @click="deletePage(page._id)" v-if="activeUser.accountType != 2 || activeUser._id == page.createdUser || activeUser._id == page.editedUser"><i
                                        class="fa-solid fa-trash-can me-1"></i> Remove</button>
                                <button class="btn btn-success btn-sm" @click="editPage(page._id, false)" v-if="activeUser.accountType != 2 || activeUser._id == page.createdUser || activeUser._id == page.editedUser"><i
                                        class="fa-solid fa-pen-to-square me-1"></i> Edit</button>
                            </div>
                        </div>
                    </li>
                    <li v-if="pages.length == 0" class="list-group-item">
                        No <span class="text-lowercase">{{activeCollection.name}}</span> have been created! Use the
                        <i>Add Page</i> button above to create content.
                    </li>
                </ul>
            </div>
            <div v-if="viewPage == 'editPage'">
                <div class="bg-light shadow-sm">
                    <ul class="nav nav-tabs bg-secondary" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="content-tab" data-bs-toggle="tab"
                                data-bs-target="#content" type="button" role="tab" aria-controls="content"
                                aria-selected="true">Content</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="options-tab" data-bs-toggle="tab" data-bs-target="#options"
                                type="button" role="tab" aria-controls="options" aria-selected="false">Options</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active p-3" id="content" role="tabpanel"
                            aria-labelledby="content-tab">
                            <div class="mb-3">
                                <label class="form-label">Page Title:</label>
                                <input v-model="editingTitle" type="text" class="form-control"
                                    placeholder="My awesome page">
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Page Description:</label>
                                        <textarea rows="3" class="form-control" v-model="editingDescription" maxlength="160"></textarea>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label d-block">Featured Image:</label>
                                        <img v-bind:src="getMediaPreviewUrl(editingFeaturedImage)" v-if="getMediaPreviewUrl(editingFeaturedImage) != null" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                                        <button class="btn btn-sm btn-primary me-2" @click="selectFeaturedImage"><span v-if="!hasSelectedMedia(editingFeaturedImage)">Select</span><span v-else>Replace</span> Image</button>
                                        <button class="btn btn-sm btn-danger" v-if="hasSelectedMedia(editingFeaturedImage)" @click="editingFeaturedImage = null">Remove Image</button>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion">
                                <div class="accordion-item mb-2" v-for="(section, index) in editingTemplate.sections">
                                    <h2 class="accordion-header" :id="'heading'+index">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            v-bind:data-bs-target="'#collapse'+index" aria-expanded="true"
                                            v-bind:aria-controls="'#collapse'+index">
                                            {{section.name}}
                                        </button>
                                    </h2>
                                    <div :id="'collapse'+index" class="accordion-collapse collapse show"
                                        aria-labelledby="'heading'+index">
                                        <div class="accordion-body">
                                            <templateinput :field="field" :key="field.id" v-for="field in section.fields">
                                            </templateinput>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade p-3" id="options" role="tabpanel" aria-labelledby="options-tab">
                            <div class="mb-3" v-if="editingPathless == false">
                                <label class="form-label">Page Path:</label>
                                <div class="input-group">
                                    <span class="input-group-text" v-if="editingPathPrefix">{{editingPathPrefix}}</span>
                                    <input v-model="editingPath" type="text" class="form-control"
                                        :placeholder="editingPathPrefix ? 'page-url' : '/'">
                                </div>
                            </div>
                            <div class="form-check form-switch mb-3" v-if="activeUser.accountType != 2">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                    v-model="editingPublished" v-bind:value="editingPublished">
                                <label class="form-check-label" for="flexSwitchCheckDefault">Page Published</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Saved:</label>
                                <input disabled v-model="editingEditedDate" type="text" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">First Saved:</label>
                                <input disabled v-model="editingDate" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'menus' && canAccessMenus()">
                <div class="card mb-3" v-for="menu in activeTheme.menus">
                    <div class="card-header">
                        {{menu.name}}
                        <button class="btn btn-sm btn-success float-end" @click="addMenuItem(menu.id)">Add Menu
                            Item</button>
                    </div>
                    <div class="card-body">
                        <span v-if="getMenuItems(menu.id).length == 0">There are no menu items yet. Add one to
                            begin.</span>
                        <div class="border rounded p-3 mb-3" v-for="entry in getMenuItems(menu.id)"
                            :style="{ marginLeft: (entry.depth * 1.25) + 'rem' }">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <strong>{{ entry.item.name }}</strong>
                                    <div class="text-secondary small">
                                        {{ entry.depth == 0 ? 'Top level item' : 'Dropdown under ' + getMenuItemName(menu.id, entry.item.parentItemID) }}
                                    </div>
                                </div>
                                <span class="badge text-bg-secondary" v-if="entry.childCount > 0">{{ entry.childCount }} child{{ entry.childCount == 1 ? '' : 'ren' }}</span>
                            </div>
                            <div class="row">
                            <div class="col-12 col-md-3 mb-3">
                                <label class="form-label">Item Type:</label>
                                <select class="form-select" v-model.number="entry.item.type" @change="onMenuItemTypeChange(entry.item)">
                                    <option :value="0">Page</option>
                                    <option :value="1">External Link</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <label class="form-label">Parent Item:</label>
                                <select class="form-select" v-model="entry.item.parentItemID" @change="onMenuParentChange(menu.id, entry.item)">
                                    <option :value="null">Top level</option>
                                    <option v-for="parentItem in getMenuParentOptions(menu.id, entry.item.itemID)" :value="parentItem.itemID">
                                        {{ getMenuItemName(menu.id, parentItem.itemID) }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-3" v-if="entry.item.type == 0">
                                <label class="form-label">Page:</label>
                                <select class="form-select" v-model="entry.item.page">
                                    <option v-for="page in pages" v-bind:value="page._id">{{page.collection}} -> {{page.title}}</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-3" v-if="entry.item.type == 1">
                                <label class="form-label">External Link:</label>
                                <input type="url" v-model="entry.item.link" class="form-control"
                                    placeholder="https://www.mywebsite.com/">
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <label class="form-label">Item Name:</label>
                                <input type="text" v-model="entry.item.name" class="form-control" placeholder="New Menu Item">
                            </div>
                            <div class="col-12 col-md-2 mb-3">
                                <button class="btn btn-success me-1" @click="moveMenuItemUp(menu.id, entry.item.itemID)"><i
                                        class="fa-solid fa-angle-up"></i></button>
                                <button class="btn btn-success me-1" @click="moveMenuItemDown(menu.id, entry.item.itemID)"><i
                                        class="fa-solid fa-angle-down"></i></button>
                                <button class="btn btn-danger" @click="deleteMenuItem(menu.id, entry.item.itemID)"><i
                                        class="fa-solid fa-trash"></i></button>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--<div v-if="viewPage == 'comments'">
                Comments
            </div>-->
            <div v-if="viewPage == 'forms'">
                <div class="row">
                    <div class="col-12 mb-3" v-if="formSubmissions.length == 0">
                        <div class="alert alert-light border shadow-sm mb-0">No form submissions yet.</div>
                    </div>
                    <div class="col-12 mb-4" v-for="group in formSubmissionGroups" :key="'submissionGroup' + group.key">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3"
                            :class="{'border-bottom pb-2': hasMultipleSubmissionForms}">
                            <div>
                                <h5 class="mb-1">{{group.label}}</h5>
                                <p class="text-muted mb-0">{{group.submissions.length}} submission{{group.submissions.length == 1 ? '' : 's'}}</p>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-secondary" @click="selectFormSubmissionGroup(group)"
                                    :disabled="isFormSubmissionGroupFullySelected(group)">
                                    Select Form
                                </button>
                                <button class="btn btn-outline-secondary" @click="clearFormSubmissionGroupSelection(group)"
                                    :disabled="getFormSubmissionGroupSelectionCount(group) == 0">
                                    Clear
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3" v-for="submission in group.submissions" :key="submission._id">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox"
                                                :id="'submissionSelect' + submission._id" v-model="selectedFormSubmissionIDs"
                                                :value="submission._id">
                                            <label class="form-check-label" :for="'submissionSelect' + submission._id">
                                                {{group.label}} Form Submission
                                            </label>
                                        </div>
                                        <button class="btn btn-sm btn-danger" @click="deleteFormSubmission(submission._id)">Delete</button>
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item" v-for="field in submission.fields" :key="submission._id + '-' + field.id"><b>{{field.name}}</b>: <a :href="'mailto:' + field.value" v-if="field.type == 'email'">{{field.value}}</a> <span v-else>{{field.value}}</span></li>
                                        <li class="list-group-item"><b>Submitted:</b> {{getDate(submission.created)}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'media'">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-xl-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label mb-1">Search Media</label>
                                        <input v-model="mediaSearch" type="search" class="form-control"
                                            placeholder="Filename, caption, alt text">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label mb-1">Filter</label>
                                        <select v-model="mediaFilter" class="form-select">
                                            <option value="all">All Items</option>
                                            <option value="image">Images</option>
                                            <option value="file">Files</option>
                                            <option value="attention">Needs Attention</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label mb-1">Sort</label>
                                        <select v-model="mediaSort" class="form-select">
                                            <option value="newest">Newest</option>
                                            <option value="oldest">Oldest</option>
                                            <option value="name">Name</option>
                                            <option value="largest">Largest</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 d-grid">
                                        <button type="button" class="btn btn-outline-secondary"
                                            @click="resetMediaFilters" :disabled="!hasActiveMediaFilters">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Library Summary</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="mirage-media-stat">
                                            <small>Total</small>
                                            <strong>{{mediaLibraryStats.total}}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mirage-media-stat">
                                            <small>Images</small>
                                            <strong>{{mediaLibraryStats.images}}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mirage-media-stat">
                                            <small>Files</small>
                                            <strong>{{mediaLibraryStats.files}}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mirage-media-stat" :class="{'mirage-media-stat--warning': mediaLibraryStats.attention > 0}">
                                            <small>Needs Attention</small>
                                            <strong>{{mediaLibraryStats.attention}}</strong>
                                        </div>
                                    </div>
                                </div>
                                <p class="small text-secondary mb-0 mt-3">Estimated storage used: {{formatBytes(mediaLibraryStats.totalStorageBytes)}}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning shadow-sm" v-if="mediaLibraryStats.attention > 0">
                    {{mediaLibraryStats.attention}} media item<span v-if="mediaLibraryStats.attention !== 1">s</span> need storage attention. Filter by <strong>Needs Attention</strong> to review missing originals or previews.
                </div>
                <div class="alert alert-danger shadow-sm" v-if="mediaError">{{mediaError}}</div>
                <div class="alert alert-light border shadow-sm" v-if="mediaLoading">Loading media library...</div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" v-if="!mediaLoading && filteredMediaItems.length > 0">
                    <p class="small text-secondary mb-0">Showing {{mediaPageStart}}-{{mediaPageEnd}} of {{filteredMediaItems.length}} matching items</p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="goToMediaPage(effectiveMediaPage - 1)" :disabled="effectiveMediaPage <= 1">Previous</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary disabled">Page {{effectiveMediaPage}} of {{totalMediaPages}}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="goToMediaPage(effectiveMediaPage + 1)" :disabled="effectiveMediaPage >= totalMediaPages">Next</button>
                    </div>
                </div>
                <div class="row" v-if="!mediaLoading">
                    <div class="col-12" v-if="mediaLibraryStats.total === 0">No media items uploaded. Use the <i>Upload
                            Media</i> button to add some to your site.</div>
                    <div class="col-12" v-else-if="filteredMediaItems.length === 0">No media items match the current search or filter.</div>
                    <div v-for="item in paginatedMediaItems" class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                        <div class="mediaItem shadow-sm h-100">
                            <img :src="item.previewUrl" :alt="item.altText || item.displayName"
                                class="mb-0 d-block w-100 mirage-media-card-image"
                                v-if="item.type == 'image' && item.previewUrl">
                            <div class="mirage-media-file-placeholder d-flex align-items-center justify-content-center"
                                v-else>
                                <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" alt=""
                                    class="img-fluid">
                            </div>
                            <div class="p-3">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge text-bg-primary text-uppercase">{{item.type}}</span>
                                    <span class="badge"
                                        :class="getMediaStatusBadgeClass(item)">{{getMediaStatusLabel(item)}}</span>
                                </div>
                                <p class="mb-1 fw-semibold" style="word-break: break-word;">{{item.displayName}}</p>
                                <p class="small text-secondary mb-1" v-if="item.caption">Caption: {{item.caption}}</p>
                                <p class="small text-secondary mb-1" v-if="item.altText">Alt: {{item.altText}}</p>
                                <p class="small text-secondary mb-2">{{getMediaMetaSummary(item)}}</p>
                                <p class="small text-warning mb-2" v-if="item.storageIssues && item.storageIssues.length > 0">{{item.storageIssues.join(', ')}}</p>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        @click="copyMediaUrl(item)" :disabled="!item.fileUrl">Copy URL</button>
                                    <a class="btn btn-sm btn-outline-secondary" :href="item.fileUrl" target="_blank"
                                        rel="noopener" v-if="item.fileUrl">Open</a>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-primary"
                                        @click="editMediaItem(item)" v-if="activeUser.accountType != 2 || activeUser._id == item.createdUser || activeUser._id == item.editedUser">Edit</button>
                                    <button class="btn btn-sm btn-danger"
                                        @click="deleteMediaFile(item._id)" v-if="activeUser.accountType != 2 || activeUser._id == item.createdUser || activeUser._id == item.editedUser">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2" v-if="!mediaLoading && filteredMediaItems.length > mediaPageSize">
                    <p class="small text-secondary mb-0">Page {{effectiveMediaPage}} of {{totalMediaPages}}</p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="goToMediaPage(effectiveMediaPage - 1)" :disabled="effectiveMediaPage <= 1">Previous</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="goToMediaPage(effectiveMediaPage + 1)" :disabled="effectiveMediaPage >= totalMediaPages">Next</button>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'users'">
                <div class="table-responsive">
                    <table class="table table-secondary table-striped shadow-sm">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Account Type</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users">
                                <td>{{user.name}}</td>
                                <td>{{user.email}}</td>
                                <td><span v-if="user.accountType == 0">Administrator</span><span
                                        v-if="user.accountType == 1">Editor</span><span
                                        v-if="user.accountType == 2">Author</span></td>
                                <td><button class="btn btn-sm btn-primary me-1 mb-1 mb-md-0" @click="editUser(user)"
                                        v-if="activeUser.accountType == 0 || (activeUser.accountType == 1 && user.accountType != 0) || activeUser._id == user._id">Edit</button>
                                    <button class="btn btn-sm btn-danger" @click="deleteUser(user._id)"
                                        v-if="(activeUser.accountType == 0 || (activeUser.accountType == 1 && user.accountType != 0)) && activeUser._id != user._id">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-if="viewPage == 'settings' && canAccessSettings()">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Site Title:</label>
                            <input v-model="siteSettings.siteTitle" type="text" class="form-control" placeholder="My website">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default Meta Description:</label>
                            <textarea v-model="siteSettings.siteDescription" rows="3" class="form-control" maxlength="220" placeholder="Fallback description used when a page does not have its own summary."></textarea>
                            <div class="form-text">Mirage uses each page's own description first, then falls back to this value for standard, Open Graph, and Twitter description tags.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">Default Social Image:</label>
                            <img v-bind:src="getMediaPreviewUrl(siteSettings.socialImage)" v-if="getMediaPreviewUrl(siteSettings.socialImage) != null" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                            <button class="btn btn-sm btn-primary me-2" @click="selectSiteSocialImage"><span v-if="!hasSelectedMedia(siteSettings.socialImage)">Select</span><span v-else>Replace</span> Image</button>
                            <button class="btn btn-sm btn-danger" v-if="hasSelectedMedia(siteSettings.socialImage)" @click="siteSettings.socialImage = null">Remove Image</button>
                            <div class="form-text mt-2">Used only when a page does not have its own featured image.</div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Open Graph Locale:</label>
                                    <input v-model="siteSettings.socialLocale" type="text" class="form-control" placeholder="en_US">
                                    <div class="form-text">Examples: <code>en_US</code>, <code>fr_CA</code>.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Twitter Site Handle:</label>
                                    <input v-model="siteSettings.twitterSite" type="text" class="form-control" placeholder="@example">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Twitter Creator Handle:</label>
                                    <input v-model="siteSettings.twitterCreator" type="text" class="form-control" placeholder="@example">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Facebook App ID:</label>
                            <input v-model="siteSettings.facebookAppId" type="text" class="form-control" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Footer Text:</label>
                            <textarea v-model="siteSettings.footerText" rows="3" class="form-control" placeholder="Optional footer text"></textarea>
                            <div class="form-text">Optional text shown above the copyright line. Tokens are supported here too.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Copyright Text:</label>
                            <textarea v-model="siteSettings.copyrightText" rows="3" class="form-control" placeholder="{{year}} {{siteTitle}} - All Rights Reserved."></textarea>
                            <div class="form-text">Available variables: <code v-pre>{{year}}</code> for the current year and <code v-pre>{{siteTitle}}</code> for the current site title.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Google Analytics Tracking Code:</label>
                            <input v-model="siteSettings.googleAnalyticsTrackingCode" type="text" class="form-control" placeholder="G-XXXXXXXXXX">
                            <div class="form-text">Paste a Google Analytics measurement ID or the full Google tag snippet. Mirage will extract the tracking code automatically.</div>
                        </div>
                        <div class="border rounded bg-light p-3">
                            <h6 class="mb-2">Preview</h6>
                            <p class="mb-1" v-if="siteSettings.footerText != ''">{{applySiteSettingTokens(siteSettings.footerText)}}</p>
                            <p class="mb-0" v-if="siteSettings.copyrightText != ''">{{applySiteSettingTokens(siteSettings.copyrightText)}}</p>
                            <p class="mb-0 text-secondary" v-if="siteSettings.footerText == '' && siteSettings.copyrightText == ''">Footer preview is empty.</p>
                        </div>
                        <div class="border rounded bg-light p-3 mt-3">
                            <h6 class="mb-2">Head Integration</h6>
                            <p class="mb-2 text-secondary">Mirage injects canonical URLs, standard description tags, Open Graph tags, Twitter card tags, and optional analytics from one native head slot.</p>
                            <p class="mb-2" v-if="(siteSettings.googleAnalyticsTrackingCode || '').trim() !== ''">Tracking code detected: <code>{{siteSettings.googleAnalyticsTrackingCode}}</code></p>
                            <p class="mb-2 text-secondary" v-else>Google Analytics is currently off.</p>
                            <p class="small text-secondary mb-1">Theme developers only need <code v-pre>&lt;?php echo $mirageMetaTag; ?&gt;</code> inside the template <code>&lt;head&gt;</code>.</p>
                            <p class="small text-secondary mb-0">Page-level descriptions and featured images override these site defaults automatically.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="addPageModal" tabindex="-1"
                aria-labelledby="addPageModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPageModalLabel">Add a Page to
                                <b>{{activeCollection.name}}</b>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <label class="form-label">Page Title:</label>
                                <input v-model="editingTitle" type="text" class="form-control"
                                    placeholder="My awesome page">
                            </div>
                            <div class="mt-3" v-if="activeCollection.allowed_templates && activeCollection.allowed_templates.length > 1">
                                <label class="form-label">Page Template:</label>
                                <select v-model="editingTemplateName" class="form-select"
                                    aria-label="Available Templates">
                                    <option selected disabled value="">Select A Template</option>
                                    <template v-for="template in activeTheme.templates" :key="template.id">
                                        <option :value="template.id"
                                            v-if="activeCollection.allowed_templates != null && activeCollection.allowed_templates.includes(template.id)">
                                            {{template.name}}</option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="editNewPage"
                                :disabled="isAddPageDisabled">Add Page</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="addUserModal" tabindex="-1"
                aria-labelledby="addUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel"><span
                                    v-if="editingUser.editingMode == 0">Add</span><span
                                    v-if="editingUser.editingMode == 1">Edit</span> a User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name:</label>
                                <input v-model="editingUser.name" type="text" class="form-control"
                                    placeholder="John Smith">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email:</label>
                                <input v-model="editingUser.email" type="text" class="form-control"
                                    placeholder="johnsmith@gmail.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password:</label>
                                <input v-model="editingUser.password" type="password" class="form-control"
                                    placeholder="mysecretpassword">
                            </div>
                            <div class="mb-3" v-if="activeUser.accountType != 2">
                                <label class="form-label">Account Type:</label>
                                <select v-model="editingUser.accountType" class="form-select">
                                    <option selected disabled value="">Select An Account Type</option>
                                    <option value="0" v-if="activeUser.accountType == 0">Administrator</option>
                                    <option value="1">Editor</option>
                                    <option value="2">Author</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Biographical Info:</label>
                                <textarea v-model="editingUser.bio" class="form-control" rows="3"
                                    placeholder="Say a little about yourself"></textarea>
                            </div>
                            <div class="mb-3" v-if="activeUser.accountType != 2">
                                <label class="form-label">Notify About Form Submissions:</label>
                                <select v-model="editingUser.notifySubmissions" class="form-select">
                                    <option selected disabled value="">Select an Option</option>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="saveUser">Save User</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="selectFileModal" tabindex="-1" aria-labelledby="selectFileModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectFileModalLabel">Select A File</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <button class="btn btn-success w-100 mb-3" @click="openUploadMediaModal"><i
                                    class="fa-solid fa-arrow-up-from-bracket me-1"></i> Upload Media</button>
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md-8">
                                    <input v-model="selectFileSearch" type="search" class="form-control"
                                        placeholder="Search available files">
                                </div>
                                <div class="col-12 col-md-4">
                                    <select v-model="selectMediaItemType" class="form-select" :disabled="selectMediaAccepts !== 'both'">
                                        <option value="all" v-if="selectMediaAccepts === 'both'">All Media</option>
                                        <option value="image" v-if="selectMediaAccepts === 'both' || selectMediaAccepts === 'image'">Images</option>
                                        <option value="file" v-if="selectMediaAccepts === 'both' || selectMediaAccepts === 'file'">Files</option>
                                    </select>
                                    <div class="form-text" v-if="selectMediaAccepts !== 'both'">This picker accepts {{selectMediaAccepts === 'image' ? 'images' : 'files'}} only.</div>
                                </div>
                            </div>
                            <div class="row" style="overflow-y: auto; overflow-x: hidden; max-height: 35rem;">
                                <div class="col-6 col-md-3 col-xl-2 overflow-auto mb-3" v-for="item in listMediaItems" :key="'media-' + item._id" @click="selectFileItem(item._id)">
                                    <div :class="{'mirage-media-option--disabled': !canSelectMediaItem(item)}">
                                    <img :src="item.previewUrl" :alt="item.altText || item.displayName"
                                        class="img-fluid me-3 mb-2 mediaItem shadow mirage-media-grid-item"
                                        style="width: 100%; height: 6rem; object-fit: cover;" v-if="item.type == 'image' && item.previewUrl">
                                    <div v-if="item.type == 'image'">
                                        <p class="mb-1"><strong>{{item.displayName}}</strong></p>
                                        <p class="mirage-media-caption mb-1" v-if="item.altText">Alt: {{item.altText}}</p>
                                        <p class="mirage-media-caption mb-0" v-if="item.caption">Caption: {{item.caption}}</p>
                                    </div>
                                    <div v-else>
                                        <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" alt=""
                                            class="img-fluid me-3 mb-2 mediaItem shadow mirage-media-grid-item"
                                            style="width: 100%; height: 6rem; object-fit: cover;">
                                        <p class="mb-1"><strong>{{item.displayName}}</strong></p>
                                        <p class="mirage-media-caption mb-0" v-if="item.caption">{{item.caption}}</p>
                                    </div>
                                    <p class="mirage-media-caption text-warning mb-0" v-if="item.storageStatus !== 'ready'">{{getMediaStatusLabel(item)}}</p>
                                    </div>
                                </div>
                                <div class="col-12" v-if="listMediaItems.length === 0">No media items match this picker view.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="editMediaModal" tabindex="-1"
                aria-labelledby="editMediaModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editMediaModalLabel">Edit Media Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3" v-if="editingMediaItem.file">
                                <img :src="editingMediaItem.previewUrl" :alt="editingMediaItem.altText || editingMediaItem.displayName"
                                    class="img-fluid img-thumbnail d-block mb-3"
                                    style="max-height: 14rem; object-fit: contain;" v-if="editingMediaItem.type === 'image' && editingMediaItem.previewUrl">
                                <div class="small text-secondary">
                                    <div><strong>Name:</strong> {{editingMediaItem.displayName || editingMediaItem.file}}</div>
                                    <div v-if="editingMediaItem.originalName && editingMediaItem.originalName !== editingMediaItem.file"><strong>Stored as:</strong> {{editingMediaItem.file}}</div>
                                    <div><strong>Type:</strong> {{editingMediaItem.type}}</div>
                                    <div v-if="editingMediaItem.mimeType"><strong>MIME:</strong> {{editingMediaItem.mimeType}}</div>
                                    <div><strong>Storage:</strong> {{formatBytes(editingMediaItem.totalStorageBytes || editingMediaItem.fileSize || 0)}}</div>
                                    <div v-if="editingMediaItem.width && editingMediaItem.height"><strong>Dimensions:</strong> {{editingMediaItem.width}} x {{editingMediaItem.height}}</div>
                                    <div class="text-warning" v-if="editingMediaItem.storageIssues && editingMediaItem.storageIssues.length > 0"><strong>Status:</strong> {{editingMediaItem.storageIssues.join(', ')}}</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Caption:</label>
                                <input v-model="editingMediaItem.caption" type="text" class="form-control"
                                    placeholder="Caption for this item">
                            </div>
                            <div class="mb-0" v-if="editingMediaItem.type === 'image'">
                                <label class="form-label">Alt Text:</label>
                                <input v-model="editingMediaItem.altText" type="text" class="form-control"
                                    placeholder="Describe the image for accessibility">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="saveMediaItem">Save Media Item</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-labelledby="uploadMediaModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadMediaModalLabel">Upload Media File(s)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="formFile" class="form-label">Select File(s):</label>
                                <input class="form-control" type="file" id="uploadMediaFiles" name="uploadMediaFiles[]"
                                    accept="<?php echo htmlspecialchars('.' . implode(',.', getAcceptedUploadExtensions()), ENT_QUOTES, 'UTF-8'); ?>"
                                    multiple @change="syncUploadSelection">
                                <small class="text-muted d-block mt-2">
                                    Max file size: <?php echo htmlspecialchars(formatBytes(getUploadFileLimitBytes()), ENT_QUOTES, 'UTF-8'); ?>.
                                    <?php if (getPostMaxSizeBytes() > 0 && getPostMaxSizeBytes() !== getUploadFileLimitBytes()) { ?>
                                        Total upload limit: <?php echo htmlspecialchars(formatBytes(getPostMaxSizeBytes()), ENT_QUOTES, 'UTF-8'); ?>.
                                    <?php } ?>
                                    Accepted types: <?php echo htmlspecialchars(strtoupper(implode(', ', getAcceptedUploadExtensions())), ENT_QUOTES, 'UTF-8'); ?>.
                                </small>
                            </div>
                            <div class="border rounded p-3 bg-light" v-if="pendingUploadFiles.length > 0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Ready To Upload</strong>
                                    <small class="text-secondary">{{pendingUploadFiles.length}} file<span v-if="pendingUploadFiles.length !== 1">s</span> - {{formatBytes(uploadSelectionTotalBytes)}}</small>
                                </div>
                                <div class="small" style="max-height: 12rem; overflow-y: auto;">
                                    <div class="d-flex justify-content-between align-items-center py-1" v-for="file in pendingUploadFiles" :key="file.name + '-' + file.size">
                                        <span style="word-break: break-word;">{{file.name}}</span>
                                        <span class="text-secondary ms-3">{{formatBytes(file.size)}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="uploadMediaFiles" :disabled="mediaUploading"><span v-if="!mediaUploading">Upload File(s)</span><span v-else>Uploading...</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
                selectMediaItemType: "image"
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
                if (page === 'menus' || page === 'forms') {
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
            setPage(page, update = false) {
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

                return true;
            },
            openGeneralDashboard() {
                if (!this.setPage('general')) {
                    return;
                }

                this.refreshGeneralDashboard();
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
            getPages(collection, update = false) {
                if (!this.canLeaveCurrentView(update)) {
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.pages = JSON.parse(this.responseText);
                    comp.activeCollection = collection;
                    comp.syncPageOrders();
                    comp.pageOrderDirty = false;
                    comp.pageOrderSaving = false;
                    comp.setPage('pages', true);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/collections/" + collection.id + "/pages", true);
                xmlhttp.send();
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
            editPage(pageID, update) {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    var pageDetails = JSON.parse(this.responseText);
                    comp.editPageTemplate(pageDetails, update);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/pages/" + pageID, true);
                xmlhttp.send();
            },
            deletePage(pageID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function () {
                        comp.getPages(comp.activeCollection, true);
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
            },
            editNewPage() {
                if (this.isAddPageDisabled) {
                    return;
                }

                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.editingTemplate = JSON.parse(this.responseText);
                    comp.setPage('editPage');
                    comp.editingPath = comp.editingTitle.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
                    comp.editingMode = 0;
                    comp.editingPathless = comp.editingTemplate.isPathless;
                    comp.editingPublished = false;
                    comp.editingDate = "Never";
                    comp.editingEditedDate = "Never";
                    addPageModal.hide();
                }
                if (comp.editingTemplateName == "" && Array.isArray(comp.activeCollection.allowed_templates) && comp.activeCollection.allowed_templates.length > 0) {
                    comp.editingTemplateName = comp.activeCollection.allowed_templates[0];
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/templates/" + comp.editingTemplateName, true);
                xmlhttp.send();
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
            editPageTemplate(page, update) {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.editingTemplate = JSON.parse(this.responseText);
                    comp.editingTemplateName = page.templateName;
                    comp.setPage('editPage', update);
                    comp.editingMode = 1;
                    comp.editingTitle = page.title;
                    comp.editingFeaturedImage = comp.normalizeOptionalMediaId(page.featuredImage);
                    comp.editingDescription = page.description;
                    comp.editingPath = page.path;
                    comp.editingPathless = comp.editingTemplate.isPathless;
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
                    isPublished: this.editingPublished
                }
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.editPage(JSON.parse(this.responseText)._id, true);
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
                if (path === '') {
                    return '';
                }

                var collection = this.getCollectionById(page.collection);
                var subpath = collection != null && typeof collection.subpath === 'string'
                    ? collection.subpath.trim().replace(/^\/+|\/+$/g, '')
                    : '';

                return '/' + (subpath !== '' ? subpath + '/' : '') + path;
            },
            getPageViewPath(page) {
                var displayPath = this.getPageDisplayPath(page);
                return displayPath !== '' ? ('<?php echo BASEPATH; ?>' + displayPath) : null;
            },
            viewPath(path) {
                if (this.activeCollection.subpath && this.activeCollection.subpath != "") {
                    return '<?php echo BASEPATH; ?>/' + this.activeCollection.subpath + "/" + path;
                } else {
                    return '<?php echo BASEPATH; ?>/' + path;
                }
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
        mounted() {
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

    app.component('mirage-editor', {
        props: {
            modelValue: {
                type: String,
                default: ''
            },
            placeholder: {
                type: String,
                default: ''
            }
        },
        emits: ['update:modelValue'],
        data() {
            return {
                editorId: 'mirage-editor-' + Math.random().toString(36).slice(2, 10),
                editor: null,
                isLoading: true,
                useTextareaFallback: false,
                syncingFromEditor: false,
                syncingFromProps: false
            };
        },
        mounted() {
            if (typeof tinymce === 'undefined') {
                this.useTextareaFallback = true;
                this.isLoading = false;
                return;
            }

            this.initEditor();
        },
        beforeUnmount() {
            if (this.editor != null) {
                this.editor.remove();
                this.editor = null;
            }
        },
        watch: {
            modelValue(newValue) {
                if (this.useTextareaFallback || this.editor == null || this.syncingFromEditor || this.syncingFromProps) {
                    return;
                }

                var incoming = normalizeEditorHtml(newValue);
                var current = normalizeEditorHtml(this.editor.getContent({ format: 'html' }));
                if (incoming === current) {
                    return;
                }

                this.syncingFromProps = true;
                this.editor.setContent(incoming);
                this.syncingFromProps = false;
            }
        },
        methods: {
            initEditor() {
                var comp = this;

                tinymce.init({
                    selector: '#' + this.editorId,
                    promotion: false,
                    branding: false,
                    menubar: false,
                    browser_spellcheck: true,
                    contextmenu: 'link image table',
                    plugins: 'advlist autolink autoresize charmap code fullscreen image link lists media searchreplace table visualblocks wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | mirageMedia mirageButton mirageColumns mirageHtml | removeformat code fullscreen',
                    toolbar_mode: 'sliding',
                    min_height: 420,
                    autoresize_bottom_margin: 24,
                    resize: true,
                    object_resizing: true,
                    image_uploadtab: false,
                    image_title: true,
                    image_caption: true,
                    link_default_protocol: 'https',
                    link_assume_external_targets: 'https',
                    convert_urls: false,
                    relative_urls: false,
                    remove_script_host: false,
                    extended_valid_elements: 'iframe[src|title|width|height|allow|allowfullscreen|frameborder|style|class|referrerpolicy],div[class|style|id|data-*],section[class|style|id|data-*],span[class|style|id|data-*],a[href|target|rel|class|style|id|data-*],img[src|alt|width|height|class|style|data-media-id],button[type|class|style],svg[*],path[*]',
                    content_style: MIRAGE_EDITOR_CONTENT_STYLE,
                    body_class: 'mirage-body-content',
                    setup(editor) {
                        comp.editor = editor;

                        editor.ui.registry.addButton('mirageMedia', {
                            icon: 'image',
                            tooltip: 'Insert image from media library',
                            onAction() {
                                comp.$root.openEditorMediaPicker(editor);
                            }
                        });

                        editor.ui.registry.addButton('mirageButton', {
                            text: 'Button',
                            tooltip: 'Insert styled button link',
                            onAction() {
                                comp.openButtonDialog(editor);
                            }
                        });

                        editor.ui.registry.addMenuButton('mirageColumns', {
                            text: 'Columns',
                            fetch(callback) {
                                callback([
                                    {
                                        type: 'menuitem',
                                        text: '2 columns',
                                        onAction() {
                                            editor.insertContent(buildColumnsHtml(2));
                                        }
                                    },
                                    {
                                        type: 'menuitem',
                                        text: '3 columns',
                                        onAction() {
                                            editor.insertContent(buildColumnsHtml(3));
                                        }
                                    }
                                ]);
                            }
                        });

                        editor.ui.registry.addButton('mirageHtml', {
                            text: 'HTML',
                            tooltip: 'Insert custom HTML',
                            onAction() {
                                comp.openHtmlDialog(editor);
                            }
                        });

                        editor.on('init', function () {
                            editor.setContent(normalizeEditorHtml(comp.modelValue));
                            comp.isLoading = false;
                        });

                        editor.on('change input undo redo', function () {
                            comp.emitEditorValue();
                        });
                    }
                }).catch(function (error) {
                    console.error('Unable to initialize the HTML editor.', error);
                    comp.useTextareaFallback = true;
                    comp.isLoading = false;
                });
            },
            emitEditorValue() {
                if (this.editor == null || this.syncingFromProps) {
                    return;
                }

                this.syncingFromEditor = true;
                this.$emit('update:modelValue', normalizeEditorHtml(this.editor.getContent({ format: 'html' })));

                var comp = this;
                window.setTimeout(function () {
                    comp.syncingFromEditor = false;
                }, 0);
            },
            onTextareaInput(event) {
                this.$emit('update:modelValue', event.target.value);
            },
            openButtonDialog(editor) {
                editor.windowManager.open({
                    title: 'Insert button',
                    body: {
                        type: 'panel',
                        items: [
                            {
                                type: 'input',
                                name: 'text',
                                label: 'Button text'
                            },
                            {
                                type: 'input',
                                name: 'href',
                                label: 'Link URL'
                            },
                            {
                                type: 'selectbox',
                                name: 'variant',
                                label: 'Style',
                                items: [
                                    { text: 'Primary', value: 'primary' },
                                    { text: 'Secondary', value: 'secondary' }
                                ]
                            },
                            {
                                type: 'checkbox',
                                name: 'newTab',
                                label: 'Open in new tab'
                            }
                        ]
                    },
                    initialData: {
                        text: '',
                        href: '',
                        variant: 'primary',
                        newTab: false
                    },
                    buttons: [
                        {
                            type: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            type: 'submit',
                            text: 'Insert',
                            primary: true
                        }
                    ],
                    onSubmit(api) {
                        var data = api.getData();
                        if (String(data.href || '').trim() === '') {
                            window.alert('A link URL is required.');
                            return;
                        }

                        editor.insertContent(buildButtonHtml(data));
                        api.close();
                    }
                });
            },
            openHtmlDialog(editor) {
                editor.windowManager.open({
                    title: 'Insert custom HTML',
                    body: {
                        type: 'panel',
                        items: [
                            {
                                type: 'textarea',
                                name: 'html',
                                label: 'HTML snippet'
                            }
                        ]
                    },
                    initialData: {
                        html: ''
                    },
                    buttons: [
                        {
                            type: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            type: 'submit',
                            text: 'Insert',
                            primary: true
                        }
                    ],
                    onSubmit(api) {
                        var data = api.getData();
                        var html = buildEmbedHtml(data.html);
                        if (html === '') {
                            window.alert('Add some HTML to insert.');
                            return;
                        }

                        editor.insertContent(html);
                        api.close();
                    }
                });
            }
        },
        template: `
            <div>
                <div class="mirage-editor-shell" :class="{ 'is-loading': isLoading && !useTextareaFallback }">
                    <textarea :id="editorId" ref="textarea" class="form-control mirage-editor-textarea" :placeholder="placeholder" :value="modelValue" @input="onTextareaInput"></textarea>
                    <div v-if="isLoading && !useTextareaFallback" class="mirage-editor-status">Loading editor...</div>
                </div>
                <div class="mirage-editor-help mt-2">Supports text, links, lists, buttons, media-library images, raw HTML/YouTube embeds, and 2 or 3 responsive columns.</div>
            </div>
        `
    });

    app.component('templateinput', {
        props: {
            field: Object,
            path: {
                type: Array,
                default() {
                    return [];
                }
            }
        },
        data() {
            if (this.field.type == 'page') {
                this.$root.getAllPages();
            }
            return {};
        },
        methods: {
            buildFieldPath(fieldID) {
                return this.path.concat(fieldID);
            },
            buildListPath(fieldID, index) {
                return this.path.concat([fieldID, index]);
            },
            getMediaFieldAccepts(field) {
                return this.$root.normalizeMediaAccepts(field != null ? (field.accepts || field.subtype || 'both') : 'both');
            },
            getMediaFieldAcceptsLabel(field) {
                var accepts = this.getMediaFieldAccepts(field);
                if (accepts === 'image') {
                    return 'Images only';
                }
                if (accepts === 'file') {
                    return 'Files only';
                }

                return 'Images or files';
            },
            getSelectedMediaItem(id) {
                return this.$root.getMediaItemById(id);
            },
            isSelectedMediaImage(id) {
                var mediaItem = this.getSelectedMediaItem(id);
                return mediaItem != null && mediaItem.type === 'image';
            },
            isSelectedMediaFile(id) {
                var mediaItem = this.getSelectedMediaItem(id);
                return mediaItem != null && mediaItem.type === 'file';
            },
            selectMediaItem(fieldPath, accepts) {
                this.$root.selectFileTarget = {
                    type: "templateField",
                    path: fieldPath
                };
                this.$root.setSelectMediaConstraint(accepts);
                selectFileModal.show();
            },
            addListItem(field) {
                if (field.value == null) {
                    field.value = [];
                }
                field.value.push(JSON.parse(JSON.stringify(field.fields)));
            },
            removeListItem(field, id) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    field.value.splice(id, 1);
                }
            },
            getMediaFilePath(id) {
                return this.$root.getMediaFilePath(id);
            },
            getMediaPreviewUrl(id) {
                return this.$root.getMediaPreviewUrl(id);
            },
            hasMediaSelection(id) {
                return this.$root.hasSelectedMedia(id);
            },
            filter_collection(list, name) {
                if (!Array.isArray(list)) {
                    return [];
                }
                return list.filter(function (item) {
                    return item.collection == name;
                });
            }
        },
        template: `
            <div class="mb-3">
                <label class="form-label d-block">{{field.name}}:</label>
                <input v-if="field.type == 'text'" v-model="field.value" type="text" class="form-control" :placeholder="field.placeholder">
                <input v-if="field.type == 'link'" v-model="field.value" type="url" class="form-control" :placeholder="field.placeholder">
                <select v-if="field.type == 'select'" v-model="field.value" class="form-select" :aria-label="field.name">
                    <option value="">None</option>
                    <option :value="option.value" v-for="option in field.options">{{option.name}}</option>
                </select>
                <select v-if="field.type == 'page'" v-model="field.value" class="form-select" :aria-label="field.name">
                    <option value="">None</option>
                    <option :value="option._id" v-for="option in filter_collection(this.$root.pages, field.collection)">{{option.title}}</option>
                </select>
                <textarea v-if="field.type == 'textarea'" v-model="field.value" class="form-control" :placeholder="field.placeholder"></textarea>
                <mirage-editor v-if="field.type == 'richtext'" v-model="field.value" :placeholder="field.placeholder || ''"></mirage-editor>
                <small class="text-muted d-block mb-2" v-if="field.type == 'media'">{{getMediaFieldAcceptsLabel(field)}}</small>
                <img v-bind:src="getMediaPreviewUrl(field.value)" v-if="field.type == 'media' && isSelectedMediaImage(field.value) && getMediaPreviewUrl(field.value) != null" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                <div v-if="field.type == 'media' && isSelectedMediaFile(field.value) && getMediaFilePath(field.value) != null">
                    <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                    <p>{{getMediaFilePath(field.value)}}</p>
                </div>
                <button class="btn btn-sm btn-primary me-2" v-if="field.type == 'media'" @click="selectMediaItem(buildFieldPath(field.id), getMediaFieldAccepts(field))"><span v-if="!hasMediaSelection(field.value)">Select</span><span v-else>Replace</span> Item</button>
                <button class="btn btn-sm btn-danger" v-if="field.type == 'media' && hasMediaSelection(field.value)" @click="field.value = null">Remove Item</button>
                <div v-if="field.type == 'list'" class="ps-3">
                    <div v-for="(listItem, i) in field.value" class="mb-3 bg-secondary text-light p-2 pb-1" :key="field.id + '-' + i">
                        <button class="btn btn-danger btn-sm mb-2" @click="removeListItem(field, i)">Remove</button>
                        <templateinput :field="subField" :path="buildListPath(field.id, i)" :key="subField.id + '-' + i + '-' + subIndex" v-for="(subField, subIndex) in listItem"></templateinput>
                    </div>
                    <button class="btn btn-sm btn-success w-100" @click="addListItem(field)">Add Item</button>
                </div>
            </div>
        `
    });

    window.mirageAdminApp = app.mount('#app');
</script>
<?php include 'footer.php'; ?>
