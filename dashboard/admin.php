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
            <!--<span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('settings')" :class="{'active text-light': viewPage == 'settings'}"><i class="fa-solid fa-gears me-1"></i> Settings</span>-->
            <a class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary"
                href="<?php echo BASEPATH ?>/logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Log Out</a>
            <small class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary">System Version: v<?php echo MIRAGE_VERSION; ?></small>
            <small class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary">PHP Version: v<?php echo phpversion(); ?></small>
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
                <!--<h4 class="mb-0 ms-2" v-if="viewPage == 'settings'">Settings</h4>-->
                <button class="btn btn-dark navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <div class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <a href="<?php echo ORIGBASEPATH; ?>" class="btn btn-primary" v-if="viewPage == 'general'"
                            target="_blank"><i class="fa-solid fa-up-right-from-square me-1"></i> View Site</a>
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
                        <button class="btn btn-success" v-if="viewPage == 'media'" @click="openUploadMediaModal"><i
                                class="fa-solid fa-arrow-up-from-bracket me-1"></i> Upload Media</button>
                        <button class="btn btn-success" v-if="viewPage == 'users'" @click="addUser"
                            v-if="activeUser.accountType != 2"><i class="fa-solid fa-user-plus me-1"></i> Add
                            User</button>
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
                <ul class="list-group mt-2 shadow-sm">
                    <li v-for="page in pages" class="list-group-item">
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
                                            <templateinput :field="field" v-for="field in section.fields">
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
                        <div class="row" v-for="(item, i) in getMenuItems(menu.id)">
                            <div class="col-12 col-md-3 mb-3">
                                <label class="form-label">Item Type:</label>
                                <select class="form-select" v-model="item.type">
                                    <option value="0">Page</option>
                                    <option value="1">External Link</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-3" v-if="item.type == 0">
                                <label class="form-label">Page:</label>
                                <select class="form-select" v-model="item.page">
                                    <option v-for="page in pages" v-bind:value="page._id">{{page.collection}} -> {{page.title}}</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-3" v-if="item.type == 1">
                                <label class="form-label">External Link:</label>
                                <input type="url" v-model="item.link" class="form-control"
                                    placeholder="https://www.mywebsite.com/">
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <label class="form-label">Item Name:</label>
                                <input type="text" v-model="item.name" class="form-control" placeholder="New Menu Item">
                            </div>
                            <div class="col-12 col-md-2 mb-3">
                                <button class="btn btn-success me-1" @click="moveMenuItemUp(i)"><i
                                        class="fa-solid fa-angle-up"></i></button>
                                <button class="btn btn-success me-1" @click="moveMenuItemDown(i)"><i
                                        class="fa-solid fa-angle-down"></i></button>
                                <button class="btn btn-danger" @click="deleteMenuItem(i)"><i
                                        class="fa-solid fa-trash"></i></button>
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
                    <div class="col-12 col-md-6 mb-3" v-for="submission in formSubmissions">
                        <div class="card">
                            <div class="card-header">
                                {{submission.formName}} Form Submission
                                <button class="btn btn-sm btn-danger float-end" @click="deleteFormSubmission(submission._id)">Delete</button>
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
            <!--<div v-if="viewPage == 'settings'">
                Settings
            </div>-->
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
                                :class="{'disabled': editingTitle == '' || (editingTemplateName == '' && activeCollection.allowed_templates|length > 1) }">Add Page</button>
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

    window.addEventListener('DOMContentLoaded', event => {

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

        addPageModal = new bootstrap.Modal(document.getElementById('addPageModal'), {});
        addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'), {});
        selectFileModal = new bootstrap.Modal(document.getElementById('selectFileModal'), {});
        uploadMediaModal = new bootstrap.Modal(document.getElementById('uploadMediaModal'), {});
        editMediaModal = new bootstrap.Modal(document.getElementById('editMediaModal'), {});
    });

    const App = {
        data() {
            return {
                viewPage: 'general',
                activeCollection: {},
                activeTheme: {},
                pages: {},
                counts: {},
                activeUser: {},
                users: {},
                menuItems: {},
                mediaItems: {},
                formSubmissions: {},
                editingTemplate: {},
                editingTitle: "",
                editingTemplateName: "",
                editingPath: "",
                editingPathless: false,
                editingMode: 0,
                editingID: null,
                editingPublished: true,
                editingDate: null,
                editingEditedDate: null,
                selectFileFieldID: "",
                selectFileFieldParent: "",
                selectFileFiendIndex: null,
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
            }
        },
        methods: {
            getDate(dateItem) {
                return new Date(dateItem * 1000).toLocaleString();
            },
            setPage(page, update = false) {
                if (update == false && this.viewPage == 'editPage') {
                    if (confirm('Are you sure you want to leave? Any unsaved work will be lost.')) {
                        this.viewPage = page;
                    }
                } else {
                    this.viewPage = page;
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
                    comp.formSubmissions = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/form", true);
                xmlhttp.send();
            },
            getMenus() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.menuItems = JSON.parse(this.responseText);
                    comp.menuItems.forEach(function (item) {
                        delete item._id;
                    });
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/menus", true);
                xmlhttp.send();
            },
            getMenuItems(menuID) {
                return this.menuItems.filter(id => menuID).sort((a, b) => a.order - b.order);
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
            getActiveUser() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.activeUser = JSON.parse(this.responseText);
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
                this.menuItems.push({
                    "menuID": menuID,
                    "name": "New Menu Item",
                    "type": 0,
                    "page": "",
                    "link": "",
                    "order": this.menuItems.length
                });
            },
            moveMenuItemUp(from) {
                var to = from - 1;
                if (to < 0) {
                    to = this.menuItems.length - 1;
                }
                var f = this.menuItems.splice(from, 1)[0];
                this.menuItems.splice(to, 0, f);
                this.menuItems.forEach(function (item, index) {
                    item.order = index;
                });
            },
            moveMenuItemDown(from) {
                var to = from + 1;
                if (to > this.menuItems.length - 1) {
                    to = 0;
                }
                var f = this.menuItems.splice(from, 1)[0];
                this.menuItems.splice(to, 0, f);
                this.menuItems.forEach(function (item, index) {
                    item.order = index;
                });
            },
            deleteMenuItem(index) {
                if (confirm('Are you sure you want to do this?')) {
                    this.menuItems.splice(index, 1);
                    this.menuItems.forEach(function (item, index) {
                        item.order = index;
                    });
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
            getPages(collection) {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    comp.pages = JSON.parse(this.responseText);
                    comp.setPage('pages');
                    comp.activeCollection = collection;
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
                        comp.getPages(comp.activeCollection);
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
                this.editingPath = "";
                this.editingTemplateName = "";
            },
            editNewPage() {
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
                if (comp.editingTemplateName == "" && comp.activeCollection.allowed_templates.length > 0) {
                    comp.editingTemplateName = comp.activeCollection.allowed_templates[0];
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/templates/" + comp.editingTemplateName, true);
                xmlhttp.send();
            },
            getTemplateValue(content, field) {
                var comp = this;
                if (content[field.id] != null) {
                    if (field.type != 'list') {
                        field.value = content[field.id];
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
                xmlhttp.send(JSON.stringify(comp.menuItems));
            },
            savePage() {
                var data = {
                    template: this.editingTemplate,
                    templateName: this.editingTemplateName,
                    title: this.editingTitle,
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
            selectFileItem(id) {
                var comp = this;
                this.editingTemplate.sections.forEach(function (section) {
                    section.fields.forEach(function (field) {
                        if (comp.selectFileFieldParent != "") {
                            if (field.id == comp.selectFileFieldParent) {
                                field.value[comp.selectFileFieldIndex].forEach(function (field2) {
                                    if (field2.id == comp.selectFileFieldID) {
                                        field2.value = id;
                                        selectFileModal.hide();
                                    }
                                });
                            }
                        } else {
                            if (field.id == comp.selectFileFieldID) {
                                field.value = id;
                                selectFileModal.hide();
                            }
                        }
                    });
                });
            },
            openUploadMediaModal() {
                uploadMediaModal.show();
            },
            uploadMediaFiles() {
                var comp = this;
                var formData = new FormData();
                var ins = document.getElementById('uploadMediaFiles').files.length;
                for (var x = 0; x < ins; x++) {
                    formData.append("uploadMediaFiles[]", document.getElementById('uploadMediaFiles').files[x]);
                }

                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function () {
                    document.getElementById('uploadMediaFiles').value = "";
                    uploadMediaModal.hide();
                    comp.getMedia();
                    comp.getCounts();
                }
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
                var returnVar = null;
                this.mediaItems.forEach(function (item) {
                    if (item._id == itemID) {
                        returnVar = item.fileSmall;
                    }
                });
                return returnVar;
            },
            viewPath(path) {
                if (this.activeCollection.subpath && this.activeCollection.subpath != "") {
                    return '<?php echo BASEPATH; ?>/' + this.activeCollection.subpath + "/" + path;
                } else {
                    return '<?php echo BASEPATH; ?>/' + path;
                }
            },
            deleteFormSubmission(submissionID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function () {
                        comp.getFormSubmissions();
                    }
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/form/" + submissionID, true);
                    xmlhttp.send();
                }
            },
        },
        mounted() {
            this.getTheme();
            this.getMedia();
            this.getFormSubmissions();
            this.getMenus();
            this.getCounts();
            this.getUsers();
            this.getActiveUser();
        }
    }

    const app = Vue.createApp(App);

    app.component('Trumbowyg', VueTrumbowyg.default);

    app.component('templateinput', {
        props: ['field', 'parent', "index"],
        data() {
            if (this.field.type == 'page') {
                this.$parent.getAllPages();
            }
            return {
                richtextOptions: {
                    svgPath: '<?php echo BASEPATH ?>/assets/img/icons.svg',
                    btns: [
                        ['viewHTML'],
                        ['formatting'],
                        ['strong', 'em', 'del'],
                        ['superscript', 'subscript'],
                        ['link'],
                        ['insertImage', 'upload'],
                        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                        ['unorderedList', 'orderedList'],
                        ['horizontalRule'],
                        ['removeformat'],
                        ['fullscreen']
                    ],
                    plugins: {
                        upload: {
                            serverPath: "<?php echo BASEPATH ?>/api/media/richtext"
                        }
                    }
                }
            }
        },
        methods: {
            selectMediaItem(fieldID, parent, index, type) {
                if (parent) {
                    this.$parent.$parent.selectFileFieldID = fieldID;
                    this.$parent.$parent.selectFileFieldParent = parent.id;
                    this.$parent.$parent.selectFileFieldIndex = index;
                    this.$parent.$parent.selectMediaItemType = type;
                } else {
                    this.$parent.selectFileFieldID = fieldID;
                    this.$parent.selectFileFieldParent = "";
                    this.$parent.selectFileFieldIndex = "";
                    this.$parent.selectMediaItemType = type;
                }
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
            filter_collection(list, name) {
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
                    <option :value="option._id" v-for="option in filter_collection(this.$parent.pages, field.collection)">{{option.title}}</option>
                </select>
                <textarea v-if="field.type == 'textarea'" v-model="field.value" type="link" class="form-control" :placeholder="field.placeholder"></textarea>
                <trumbowyg v-if="field.type == 'richtext'" v-model="field.value" :config="richtextOptions"></trumbowyg>
                <img v-bind:src="'<?php echo BASEPATH; ?>/uploads/'+getMediaFilePath(field.value)" v-if="field.type == 'media' && field.subtype == 'image' && field.value != null" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                <div v-if="field.type == 'media' && field.subtype == 'file' && field.value != null">
                    <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                    <p>{{getMediaFilePath(field.value)}}</p>
                </div>
                <button class="btn btn-sm btn-primary me-2" v-if="field.type == 'media'" @click="selectMediaItem(field.id, parent, index, field.subtype)"><span v-if="field.value == null">Select</span><span v-if="field.value != null">Replace</span> Item</button>
                <button class="btn btn-sm btn-danger" v-if="field.type == 'media' && field.value != null" @click="field.value = null">Remove Item</button>
                <div v-if="field.type == 'list'" class="ps-3">
                    <div v-for="(listItem, i) in field.value" class="mb-3 bg-secondary text-light p-2 pb-1" :key="listItem.id">
                        <button class="btn btn-danger btn-sm mb-2" @click="removeListItem(field, i)">Remove</button>
                        <templateinput :field="subField" :parent="field" :index="i" v-for="subField in listItem"></templateinput>
                    </div>
                    <button class="btn btn-sm btn-success w-100" @click="addListItem(field)">Add Item</button>
                </div>
            </div>
            `
    });

    app.mount('#app');
</script>
<?php include 'footer.php'; ?>
