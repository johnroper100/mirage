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
                        <a v-bind:href="getEditingPageViewPath()" class="btn btn-primary me-md-2 mb-1 mb-md-0"
                            v-if="viewPage == 'editPage' && editingMode == 1 && editingPathless == false && getEditingPageViewPath()" target="_blank"><i
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
                                                    <span class="badge text-bg-dark" v-if="page.isPasswordProtected">Protected</span>
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
                                <h4><small class="text-warning me-1" v-if="page.isPublished == false">ðŸ”’</small>{{page.title}}</h4>
                                <div class="d-flex flex-wrap gap-2 mb-1">
                                    <span class="badge text-bg-warning" v-if="page.isPublished == false">Draft</span>
                                    <span class="badge text-bg-dark" v-if="page.isPasswordProtected">Protected</span>
                                </div>
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
                                <a :href="getPageViewPath(page)" class="btn btn-primary btn-sm me-1" target="_blank" v-if="page.isPathless == false && getPageViewPath(page)"><i
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
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="pagePasswordProtectionSwitch"
                                    v-model="editingPasswordProtected">
                                <label class="form-check-label" for="pagePasswordProtectionSwitch">Password Protect Page</label>
                            </div>
                            <div class="mb-3" v-if="editingPasswordProtected">
                                <label class="form-label">Page Password:</label>
                                <input v-model="editingPassword" type="password" class="form-control"
                                    :placeholder="editingHasSavedPassword ? 'Leave blank to keep current password' : 'Enter a password'">
                                <div class="form-text" v-if="editingHasSavedPassword">Leave this blank to keep the current password. Turn off password protection to remove it.</div>
                                <div class="form-text" v-else>Visitors must enter this password before Mirage renders the page.</div>
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

