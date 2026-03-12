<?php include 'header.php'; ?>
<div class="d-flex" id="app">
    <!-- Sidebar-->
    <div class="bg-dark text-light" id="sidebar-wrapper">
        <div class="sidebar-heading bg-secondary text-light text-center text-uppercase shadow-sm">Mirage Admin</div>
        <div class="list-group list-group-flush mt-2">
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('general')"
                :class="{'active text-light': viewPage == 'general'}"><i class="fa-solid fa-gauge-simple me-1"></i>
                General</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="getPages(collection)"
                :class="{'active text-light': (viewPage == 'pages' || viewPage == 'editPage') && activeCollection.id == collection.id}"
                v-for="collection in activeTheme.collections"><i class="fa-solid me-1" :class="collection.icon"></i>
                {{collection.name}}</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('menus'); getAllPages();"
                :class="{'active text-light': viewPage == 'menus'}" v-if="activeUser.accountType != 2"><i class="fa-solid fa-chart-bar me-1"></i>
                Menus</span>
            <!--<span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('comments')" :class="{'active text-light': viewPage == 'comments'}"><i class="fa-solid fa-comments me-1"></i> Comments</span>-->
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('forms')" :class="{'active text-light': viewPage == 'forms'}" v-if="activeUser.accountType != 2"><i class="fa-solid fa-envelope-open-text me-1"></i> Form Submissions</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('media')"
                :class="{'active text-light': viewPage == 'media'}"><i class="fa-solid fa-folder-tree me-1"></i>
                Media</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('users')"
                :class="{'active text-light': viewPage == 'users'}"><i class="fa-solid fa-users me-1"></i> Users</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('settings'); getSiteSettings();"
                :class="{'active text-light': viewPage == 'settings'}" v-if="activeUser.accountType == 0"><i class="fa-solid fa-gears me-1"></i> Settings</span>
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
                <h4 class="mb-0 ms-2" v-if="viewPage == 'menus'">Menus</h4>
                <!--<h4 class="mb-0 ms-2" v-if="viewPage == 'comments'">Comments</h4>-->
                <h4 class="mb-0 ms-2" v-if="viewPage == 'forms'">Form Submissions</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'media'">Media</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'users'">Users</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'settings'">Settings</h4>
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
                        <button class="btn btn-success" v-if="viewPage == 'menus'" @click="saveMenus"><i
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
                        <button class="btn btn-success" v-if="viewPage == 'settings'" @click="saveSiteSettings"><i
                                class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Page content-->
        <div class="container-fluid pt-3 pb-3 ps-4 pe-4">
            <div v-if="viewPage == 'general'">
                <div class="row">
                    <div class="col-12 col-lg-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h3>Welcome to Mirage!</h3>
                                <p class="mb-0">Here is some quick information about your site:</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 mb-3">
                        <div class="card bg-primary text-light shadow-sm">
                            <div class="card-body text-center">
                                <i class="fa-solid fa-file-lines fa-2xl mb-3"></i>
                                <h4><i>Pages</i></h4>
                                <h3 class="mb-1">{{counts.pages}}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 mb-3">
                        <div class="card bg-success text-light shadow-sm">
                            <div class="card-body text-center">
                                <i class="fa-solid fa-users fa-2xl mb-3"></i>
                                <h4><i>Users</i></h4>
                                <h3 class="mb-1">{{counts.users}}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 mb-3">
                        <div class="card bg-danger text-light shadow-sm">
                            <div class="card-body text-center">
                                <i class="fa-solid fa-photo-film-music fa-2xl mb-3"></i>
                                <h4><i>Media Files</i></h4>
                                <h3 class="mb-1">{{counts.media}}</h3>
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
                                            v-bind:data-bs-target="'#collapse'+index" aria-expanded="false"
                                            v-bind:aria-controls="'#collapse'+index">
                                            {{section.name}}
                                        </button>
                                    </h2>
                                    <div :id="'collapse'+index" class="accordion-collapse collapse"
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
                                <input v-model="editingPath" type="text" class="form-control" placeholder="/">
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
            <div v-if="viewPage == 'menus'">
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
                    <div class="col-12 col-md-6 mb-3" v-for="submission in formSubmissions">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center gap-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox"
                                        :id="'submissionSelect' + submission._id" v-model="selectedFormSubmissionIDs"
                                        :value="submission._id">
                                    <label class="form-check-label" :for="'submissionSelect' + submission._id">
                                        {{submission.formName}} Form Submission
                                    </label>
                                </div>
                                <button class="btn btn-sm btn-danger" @click="deleteFormSubmission(submission._id)">Delete</button>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item" v-for="field in submission.fields"><b>{{field.name}}</b>: <a :href="'mailto:' + field.value" v-if="field.type == 'email'">{{field.value}}</a> <span v-else>{{field.value}}</span></li>
                                <li class="list-group-item"><b>Submitted:</b> {{getDate(submission.created)}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'media'">
                <div class="row" style="overflow-y: auto; max-height: 45rem;">
                    <div class="col-12" v-if="mediaItems.length == 0">No media items uploaded. Use the <i>Upload
                            Media</i> button to add some to your site.</div>
                    <div v-for="item in mediaItems" class="col-6 col-md-4 col-lg-2 mb-3 p-2 ">
                        <div class="mediaItem shadow-sm">
                            <img v-bind:src="'<?php echo BASEPATH; ?>/uploads/'+item.fileSmall" alt=""
                                class="mb-1 d-block w-100" style="height: 10rem; object-fit: cover;"
                                v-if="item.type == 'image'">
                            <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" alt=""
                                class="mb-1 d-block w-100" style="height: 10rem; object-fit: cover;" v-else>
                            <small class="p-2 d-block" style="word-wrap: break-word;">{{item.file}}</small>
                            <button class="btn btn-sm btn-primary mb-2 ms-2"
                                @click="editMediaItem(item)" v-if="activeUser.accountType != 2 || activeUser._id == item.createdUser || activeUser._id == item.editedUser">Edit</button>
                            <button class="btn btn-sm btn-danger mb-2 ms-2"
                                @click="deleteMediaFile(item._id)" v-if="activeUser.accountType != 2 || activeUser._id == item.createdUser || activeUser._id == item.editedUser">Remove</button>
                        </div>
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
            <div v-if="viewPage == 'settings'">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Site Title:</label>
                            <input v-model="siteSettings.siteTitle" type="text" class="form-control" placeholder="My website">
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
                        <div class="border rounded bg-light p-3">
                            <h6 class="mb-2">Preview</h6>
                            <p class="mb-1" v-if="siteSettings.footerText != ''">{{applySiteSettingTokens(siteSettings.footerText)}}</p>
                            <p class="mb-0" v-if="siteSettings.copyrightText != ''">{{applySiteSettingTokens(siteSettings.copyrightText)}}</p>
                            <p class="mb-0 text-secondary" v-if="siteSettings.footerText == '' && siteSettings.copyrightText == ''">Footer preview is empty.</p>
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
                            <div class="row" style="overflow-y: auto; overflow-x: hidden; max-height: 35rem;">
                                <div class="col-4 col-md-2 overflow-auto" v-for="item in listMediaItems" @click="selectFileItem(item._id)">
                                    <img v-bind:src="'<?php echo BASEPATH; ?>/uploads/'+item.fileSmall" alt=""
                                        class="img-fluid me-3 mb-3 mediaItem shadow"
                                        style="width: 100%; height: 6rem; object-fit: cover;" v-if="item.type == 'image'">
                                    <div v-else>
                                        <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" alt=""
                                            class="img-fluid me-3 mb-3 mediaItem shadow"
                                            style="width: 100%; height: 6rem; object-fit: cover;">
                                        <p>{{item.file}}</p>
                                    </div>
                                </div>
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
                            <div class="mb-3">
                                <label class="form-label">Caption:</label>
                                <input v-model="editingMediaItem.caption" type="text" class="form-control"
                                    placeholder="Caption for this item">
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
                                    multiple>
                                <small class="text-muted d-block mt-2">
                                    Max file size: <?php echo htmlspecialchars(formatBytes(getUploadFileLimitBytes()), ENT_QUOTES, 'UTF-8'); ?>.
                                    <?php if (getPostMaxSizeBytes() > 0 && getPostMaxSizeBytes() !== getUploadFileLimitBytes()) { ?>
                                        Total upload limit: <?php echo htmlspecialchars(formatBytes(getPostMaxSizeBytes()), ENT_QUOTES, 'UTF-8'); ?>.
                                    <?php } ?>
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="uploadMediaFiles">Upload
                                File(s)</button>
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
    var richtextMediaTarget = null;
    const MIRAGE_CSRF_TOKEN = document.querySelector('meta[name="mirage-csrf-token"]')?.getAttribute('content') || '';
    const MAX_UPLOAD_FILE_BYTES = <?php echo (int) getUploadFileLimitBytes(); ?>;
    const MAX_UPLOAD_TOTAL_BYTES = <?php echo (int) getPostMaxSizeBytes(); ?>;
    const MAX_UPLOAD_FILE_LABEL = <?php echo json_encode(formatBytes(getUploadFileLimitBytes())); ?>;
    const MAX_UPLOAD_TOTAL_LABEL = <?php echo json_encode(formatBytes(getPostMaxSizeBytes())); ?>;

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
                richtextMediaTarget = null;
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
                counts: {},
                siteSettings: {
                    siteTitle: "",
                    footerText: "",
                    copyrightText: ""
                },
                activeUser: {
                    accountType: 2
                },
                users: {},
                menuItems: {},
                mediaItems: {},
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
                editingMediaItem: {},
                selectMediaItemType: "image"
            }
        },
        computed: {
            listMediaItems() {
                return Object.values(this.mediaItems).filter((obj) => obj.type == this.selectMediaItemType);
            },
            isAddPageDisabled() {
                var allowedTemplates = Array.isArray(this.activeCollection.allowed_templates) ? this.activeCollection.allowed_templates : [];
                var title = typeof this.editingTitle === 'string' ? this.editingTitle.trim() : '';

                return title === '' || (this.editingTemplateName === '' && allowedTemplates.length > 1);
            }
        },
        methods: {
            getDate(dateItem) {
                return new Date(dateItem * 1000).toLocaleString();
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

                this.viewPage = page;
                if (page !== 'pages') {
                    this.pageOrderDirty = false;
                    this.pageOrderSaving = false;
                }

                return true;
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
                xmlhttp.onload = function () {
                    comp.mediaItems = JSON.parse(this.responseText);
                }
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
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    if (this.status < 200 || this.status >= 300) {
                        return;
                    }

                    var settings = JSON.parse(this.responseText);
                    comp.siteSettings = {
                        siteTitle: settings.siteTitle || "",
                        footerText: settings.footerText || "",
                        copyrightText: settings.copyrightText || ""
                    };
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/settings", true);
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
                        comp.siteSettings = {
                            siteTitle: "",
                            footerText: "",
                            copyrightText: ""
                        };
                    }

                    if (comp.activeUser.accountType != 2) {
                        comp.getFormSubmissions();
                        comp.getMenus();
                    } else {
                        comp.formSubmissions = [];
                        comp.selectedFormSubmissionIDs = [];
                        comp.menuItems = {};
                    }
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

                    comp.siteSettings = {
                        siteTitle: response.siteTitle || "",
                        footerText: response.footerText || "",
                        copyrightText: response.copyrightText || ""
                    };
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

                return Object.values(this.mediaItems).find(function (item) {
                    return item._id == itemID;
                }) || null;
            },
            resolveRichtextMediaTarget(target) {
                if (target != null && target.$ta != null && target.$ta.length > 0) {
                    return target.$ta;
                }

                var $target = null;
                if (target != null) {
                    if (target.jquery != null) {
                        $target = target;
                    } else if (target.currentTarget != null) {
                        $target = $(target.currentTarget);
                    } else {
                        $target = $(target);
                    }
                }

                if ($target != null && $target.length > 0) {
                    var $targetBox = $target.closest('.trumbowyg-box');
                    if ($targetBox.length > 0) {
                        var $targetTextarea = $targetBox.find('textarea').first();
                        if ($targetTextarea.length > 0) {
                            return $targetTextarea;
                        }
                    }
                }

                var $activeBox = $(document.activeElement).closest('.trumbowyg-box');
                if ($activeBox.length > 0) {
                    var $activeTextarea = $activeBox.find('textarea').first();
                    if ($activeTextarea.length > 0) {
                        return $activeTextarea;
                    }
                }

                return null;
            },
            buildMediaFileUrl(filename) {
                return '<?php echo BASEPATH; ?>/uploads/' + encodeURIComponent(filename);
            },
            getMediaPreviewUrl(itemID) {
                var mediaPath = this.getMediaFilePath(itemID);
                return mediaPath != null ? this.buildMediaFileUrl(mediaPath) : null;
            },
            insertRichtextImageFromMedia(itemID) {
                var mediaItem = this.getMediaItemById(itemID);
                if (mediaItem == null || mediaItem.type !== 'image' || richtextMediaTarget == null || richtextMediaTarget.length === 0) {
                    return false;
                }

                try {
                    richtextMediaTarget.trumbowyg('restoreRange');
                    var range = richtextMediaTarget.trumbowyg('getRange');
                    var $editor = richtextMediaTarget.closest('.trumbowyg-box').find('.trumbowyg-editor').first();
                    if (range == null || $editor.length === 0) {
                        return false;
                    }

                    var imageNode = document.createElement('img');
                    imageNode.src = this.buildMediaFileUrl(mediaItem.file);
                    if (mediaItem.caption !== '') {
                        imageNode.alt = mediaItem.caption;
                    }

                    range.deleteContents();
                    range.insertNode(imageNode);
                    range.setStartAfter(imageNode);
                    range.setEndAfter(imageNode);

                    var selection = window.getSelection();
                    if (selection != null) {
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }

                    richtextMediaTarget.trumbowyg('html', $editor.html());
                    richtextMediaTarget.trumbowyg('saveRange');
                    richtextMediaTarget.trigger('tbwchange');
                    return true;
                } catch (error) {
                    console.error('Unable to insert media library image into rich text editor.', error);
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
                this.selectMediaItemType = "image";
                richtextMediaTarget = null;
            },
            selectFileItem(id) {
                var comp = this;
                if (comp.selectFileTarget != null && comp.selectFileTarget.type == "featuredImage") {
                    comp.editingFeaturedImage = id;
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
                if (comp.selectFileTarget != null && comp.selectFileTarget.type == "richtextImage") {
                    if (!comp.insertRichtextImageFromMedia(id)) {
                        alert("The selected image could not be inserted.");
                    }
                    selectFileModal.hide();
                    comp.clearSelectFileTarget();
                    return;
                }
                comp.clearSelectFileTarget();
            },
            openRichtextMediaPicker(editor) {
                var $target = this.resolveRichtextMediaTarget(editor);
                if ($target == null) {
                    alert("The editor selection could not be located.");
                    return;
                }

                $target.trumbowyg('saveRange');
                richtextMediaTarget = $target;
                this.selectFileTarget = {
                    type: "richtextImage"
                };
                this.selectMediaItemType = "image";
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
            getUploadErrorMessage(xmlhttp) {
                try {
                    var response = JSON.parse(xmlhttp.responseText);
                    if (response.message) {
                        return response.message;
                    }
                } catch (error) {
                }

                return "Upload failed. Please try again.";
            },
            uploadMediaFiles() {
                var comp = this;
                var fileInput = document.getElementById('uploadMediaFiles');
                var files = Array.from(fileInput.files);

                if (!comp.validateUploadFiles(files)) {
                    return;
                }

                var formData = new FormData();
                for (var x = 0; x < files.length; x++) {
                    formData.append("uploadMediaFiles[]", files[x]);
                }

                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    if (xmlhttp.status >= 200 && xmlhttp.status < 300) {
                        fileInput.value = "";
                        uploadMediaModal.hide();
                        comp.getMedia();
                        comp.getCounts();
                    } else {
                        alert(comp.getUploadErrorMessage(xmlhttp));
                    }
                }
                xmlhttp.onerror = function () {
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
                        comp.getMedia();
                        comp.getCounts();
                    }
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/media/" + itemID, true);
                    xmlhttp.send();
                }
            },
            editMediaItem(item) {
                this.editingMediaItem = {
                    "file": item.file,
                    "fileSmall": item.fileSmall,
                    "caption": item.caption,
                    "editingID": item._id
                };
                editMediaModal.show();
            },
            saveMediaItem() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    editMediaModal.hide();
                    comp.getMedia();
                    comp.getCounts();
                }
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
                return mediaItem != null ? mediaItem.fileSmall : null;
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
                this.selectMediaItemType = "image";
                selectFileModal.show();
            }
        },
        mounted() {
            this.getTheme();
            this.getMedia();
            this.getCounts();
            this.getUsers();
            this.getActiveUser();
        }
    }

    const app = Vue.createApp(App);

    app.component('Trumbowyg', VueTrumbowyg.default);

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
            return {
                richtextOptions: null
            }
        },
        created() {
            this.richtextOptions = this.buildRichtextOptions();
        },
        methods: {
            buildRichtextOptions() {
                var comp = this;
                return {
                    svgPath: '<?php echo BASEPATH ?>/assets/img/icons.svg',
                    btnsDef: {
                        mediaLibrary: {
                            ico: 'insertImage',
                            title: 'Insert image from media library',
                            fn: function() {
                                comp.$root.openRichtextMediaPicker(this);
                            }
                        }
                    },
                    btns: [
                        ['historyUndo', 'historyRedo'],
                        ['formatting'],
                        ['strong', 'em', 'del'],
                        ['superscript', 'subscript'],
                        ['link'],
                        ['mediaLibrary'],
                        ['outdent', 'indent'],
                        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                        ['unorderedList', 'orderedList'],
                        ['horizontalRule'],
                        ['removeformat'],
                        ['viewHTML', 'fullscreen']
                    ],
                    imageWidthModalEdit: true,
                    removeformatPasted: true,
                    autogrow: true
                };
            },
            buildFieldPath(fieldID) {
                return this.path.concat(fieldID);
            },
            buildListPath(fieldID, index) {
                return this.path.concat([fieldID, index]);
            },
            selectMediaItem(fieldPath, type) {
                this.$root.selectFileTarget = {
                    type: "templateField",
                    path: fieldPath
                };
                this.$root.selectMediaItemType = type;
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
            <div class="mb-3" >
                <label class="form-label d-block">{{field.name}}:</label>
                <input v-if="field.type == 'text'" v-model="field.value" type="text" class="form-control" :placeholder="field.placeholder">
                <input v-if="field.type == 'link'" v-model="field.value" type="link" class="form-control" :placeholder="field.placeholder">
                <select v-if="field.type == 'select'" v-model="field.value" class="form-select" :aria-label="field.name">
                    <option value="">None</option>
                    <option :value="option.value" v-for="option in field.options">{{option.name}}</option>
                </select>
                <select v-if="field.type == 'page'" v-model="field.value" class="form-select" :aria-label="field.name">
                    <option value="">None</option>
                    <option :value="option._id" v-for="option in filter_collection(this.$root.pages, field.collection)">{{option.title}}</option>
                </select>
                <textarea v-if="field.type == 'textarea'" v-model="field.value" type="link" class="form-control" :placeholder="field.placeholder"></textarea>
                <trumbowyg v-if="field.type == 'richtext' && richtextOptions != null" v-model="field.value" :config="richtextOptions"></trumbowyg>
                <img v-bind:src="getMediaPreviewUrl(field.value)" v-if="field.type == 'media' && field.subtype == 'image' && getMediaPreviewUrl(field.value) != null" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                <div v-if="field.type == 'media' && field.subtype == 'file' && getMediaFilePath(field.value) != null">
                    <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                    <p>{{getMediaFilePath(field.value)}}</p>
                </div>
                <button class="btn btn-sm btn-primary me-2" v-if="field.type == 'media'" @click="selectMediaItem(buildFieldPath(field.id), field.subtype)"><span v-if="!hasMediaSelection(field.value)">Select</span><span v-else>Replace</span> Item</button>
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
