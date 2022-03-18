<?php include 'header.php'; ?>
<div class="d-flex" id="app">
    <!-- Sidebar-->
    <div class="bg-dark text-light" id="sidebar-wrapper">
        <div class="sidebar-heading bg-secondary text-light text-center text-uppercase shadow-sm">Mirage Admin</div>
        <div class="list-group list-group-flush mt-2">
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('general')" :class="{'active text-light': viewPage == 'general'}"><i class="fa-solid fa-gauge-simple me-1"></i> General</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="getPages(collection)" :class="{'active text-light': (viewPage == 'pages' || viewPage == 'editPage') && activeCollection.id == collection.id}" v-for="collection in theme.collections"><i class="fa-solid me-1" :class="collection.icon"></i> {{collection.name}}</span>
            <!--<span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('menus')" :class="{'active text-light': viewPage == 'menus'}"><i class="fa-solid fa-chart-bar me-1"></i> Menus</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('comments')" :class="{'active text-light': viewPage == 'comments'}"><i class="fa-solid fa-comments me-1"></i> Comments</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('forms')" :class="{'active text-light': viewPage == 'forms'}"><i class="fa-solid fa-envelope-open-text me-1"></i> Forms</span>-->
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('media')" :class="{'active text-light': viewPage == 'media'}"><i class="fa-solid fa-folder-tree me-1"></i> Media</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('themes')" :class="{'active text-light': viewPage == 'themes'}"><i class="fa-solid fa-swatchbook me-1"></i> Themes</span>
            <span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('users')" :class="{'active text-light': viewPage == 'users'}"><i class="fa-solid fa-users me-1"></i> Users</span>
            <!--<span class="p-2 ps-3 sidebarItem mt-2 text-secondary" @click="setPage('settings')" :class="{'active text-light': viewPage == 'settings'}"><i class="fa-solid fa-gears me-1"></i> Settings</span>-->
            <a class="p-2 ps-3 sidebarItem mt-2 text-decoration-none text-secondary" href="<?php echo BASEPATH ?>/logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Log Out</a>
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
                <h4 class="mb-0 ms-2" v-if="viewPage == 'comments'">Comments</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'forms'">Forms</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'media'">Media</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'themes'">Themes</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'users'">Users</h4>
                <h4 class="mb-0 ms-2" v-if="viewPage == 'settings'">Settings</h4>
                <button class="btn btn-dark navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <div class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <a href="<?php echo BASEPATH; ?>" class="btn btn-primary" v-if="viewPage == 'general'" target="_blank"><i class="fa-solid fa-up-right-from-square me-1"></i> View Site</a>
                        <button class="btn btn-success" v-if="viewPage == 'pages'" @click="addPage"><i class="fa-solid fa-plus me-1"></i> Add Page</button>
                        <a v-bind:href="'<?php echo BASEPATH; ?>'+editingPath" class="btn btn-primary me-md-2 mb-1 mb-md-0" v-if="viewPage == 'editPage' && editingMode == 1" target="_blank"><i class="fa-solid fa-up-right-from-square me-1"></i> View</a>
                        <button class="btn btn-danger me-md-2 mb-1 mb-md-0" @click="deletePage(editingID)" v-if="viewPage == 'editPage' && editingMode == 1"><i class="fa-solid fa-trash-can me-1"></i> Remove</button>
                        <button class="btn btn-success" v-if="viewPage == 'editPage'" @click="savePage"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                        <button class="btn btn-success" v-if="viewPage == 'media'" @click="openUploadMediaModal"><i class="fa-solid fa-arrow-up-from-bracket me-1"></i> Upload Media</button>
                        <button class="btn btn-success" v-if="viewPage == 'users'"><i class="fa-solid fa-user-plus me-1"></i> Add User</button>
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
                                <h4><i class="fa-solid fa-xs fa-lock me-1 text-warning" v-if="page.published == false"></i>{{page.title}}</h4>
                                <h6 class="text-secondary">T: {{page.templateName}} <i class="fa-solid fa-right-long"></i> {{page.path}}</h6>
                            </div>
                            <div class="col-12 col-md-3 text-md-end">
                                <a v-bind:href="'<?php echo BASEPATH; ?>'+page.path" class="btn btn-primary btn-sm me-1" target="_blank"><i class="fa-solid fa-up-right-from-square me-1"></i> View</a>
                                <button class="btn btn-danger btn-sm me-1" @click="deletePage(page._id)"><i class="fa-solid fa-trash-can me-1"></i> Remove</button>
                                <button class="btn btn-success btn-sm" @click="editPage(page._id, false)"><i class="fa-solid fa-pen-to-square me-1"></i> Edit</button>
                            </div>
                        </div>
                    </li>
                    <li v-if="pages.length == 0" class="list-group-item">
                        No <span class="text-lowercase">{{activeCollection.name}}</span> have been created! Use the <i>Add Page</i> button above to create content.
                    </li>
                </ul>
            </div>
            <div v-if="viewPage == 'editPage'">
                <div class="bg-light shadow-sm">
                    <ul class="nav nav-tabs bg-secondary" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab" aria-controls="content" aria-selected="true">Content</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="options-tab" data-bs-toggle="tab" data-bs-target="#options" type="button" role="tab" aria-controls="options" aria-selected="false">Options</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active p-3" id="content" role="tabpanel" aria-labelledby="content-tab">
                            <div class="mb-3">
                                <label class="form-label">Page Title:</label>
                                <input v-model="editingTitle" type="text" class="form-control" placeholder="My awesome page">
                            </div>
                            <div class="accordion">
                                <div class="accordion-item mb-2" v-for="(section, index) in editingTemplate.sections">
                                    <h2 class="accordion-header" :id="'heading'+index">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" v-bind:data-bs-target="'#collapse'+index" aria-expanded="false" v-bind:aria-controls="'#collapse'+index">
                                            {{section.name}}
                                        </button>
                                    </h2>
                                    <div :id="'collapse'+index" class="accordion-collapse collapse" aria-labelledby="'heading'+index">
                                        <div class="accordion-body">
                                            <templateinput :field="field" v-for="field in section.fields"></templateinput>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade p-3" id="options" role="tabpanel" aria-labelledby="options-tab">
                            <div class="mb-3">
                                <label class="form-label">Page Path:</label>
                                <input v-model="editingPath" type="text" class="form-control" placeholder="/">
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" v-model="editingPublished" v-bind:value="editingPublished">
                                <label class="form-check-label" for="flexSwitchCheckDefault">Page Published</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Saved:</label>
                                <input disabled v-model="editingDate" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'menus'">
                Menus
            </div>
            <div v-if="viewPage == 'comments'">
                Comments
            </div>
            <div v-if="viewPage == 'forms'">
                Forms
            </div>
            <div v-if="viewPage == 'media'">
                <div class="row" style="overflow-y: auto; max-height: 45rem;">
                    <div class="col-12" v-if="mediaItems.length == 0">No media items uploaded. Use the <i>Upload Media</i> button to add some to your site.</div>
                    <div v-for="item in mediaItems" class="col-6 col-md-4 col-lg-2 mb-3 p-2 ">
                        <div class="mediaItem shadow-sm">
                            <img v-bind:src="'<?php echo BASEPATH; ?>/uploads/'+item.file" alt="" class="mb-1 d-block w-100" style="height: 10rem; object-fit: cover;" v-if="['png', 'jpg', 'gif', 'jpeg', 'svg'].includes(item.extension.toLowerCase())">
                            <img src="<?php echo BASEPATH; ?>/assets/img/fileUnknown.png" alt="" class="mb-1 d-block w-100" style="height: 10rem; object-fit: cover;" v-else>
                            <small class="p-2 d-block" style="word-wrap: break-word;">{{item.file}}</small>
                            <button class="btn btn-sm btn-danger mb-2 ms-2" @click="deleteMediaFile(item._id)">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="viewPage == 'themes'">
                <div class="row">
                    <div class="col-12 col-md-3 mb-3" v-for="theme in themes">
                        <div class="card shadow-sm" v-bind:class="{'border border-warning border-2': theme.active}">
                            <div class="card-body">
                                <h5 class="card-title">{{theme.name}} <small class="text-secondary">v{{theme.version}}</small></h5>
                                <p class="card-text">
                                    Author: {{theme.author}}
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-success me-2" v-if="!theme.active">Activate</button>
                                <button class="btn btn-sm btn-primary me-2" v-if="theme.active" :disabled="themes.length < 2">Deactivate</button>
                                <button class="btn btn-sm btn-danger" :disabled="themes.length < 2">Remove</button>
                            </div>
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
                                <td>{{user.accountType}}</td>
                                <td><button class="btn btn-sm btn-primary me-1">Edit</button> <button class="btn btn-sm btn-danger">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-if="viewPage == 'settings'">
                Settings
            </div>
            <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="addPageModal" tabindex="-1" aria-labelledby="addPageModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPageModalLabel">Add a Page to <b>{{activeCollection.name}}</b></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Page Title:</label>
                                <input v-model="editingTitle" type="text" class="form-control" placeholder="My awesome page">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Page Template:</label>
                                <select v-model="editingTemplateName" class="form-select" aria-label="Available Templates">
                                    <option selected disabled value="">Select a Template</option>
                                    <template v-for="template in theme.templates" :key="template.id">
                                        <option :value="template.id" v-if="activeCollection.allowed_templates != null && activeCollection.allowed_templates.includes(template.id)">{{template.name}}</option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="editNewPage" :class="{'disabled': editingTitle == '' || editingTemplateName == ''}">Add Page</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="selectFileModal" tabindex="-1" aria-labelledby="selectFileModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectFileModalLabel">Select A File</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <button class="btn btn-success w-100 mb-3" @click="openUploadMediaModal"><i class="fa-solid fa-arrow-up-from-bracket me-1"></i> Upload Media</button>
                            <div class="row" style="overflow-y: auto; overflow-x: hidden; max-height: 35rem;">
                                <div class="col-4 col-md-2" v-for="item in mediaItems">
                                    <img @click="selectFileItem(item.file)" v-bind:src="'<?php echo BASEPATH; ?>/uploads/'+item.file" alt="" class="img-fluid me-3 mb-3 mediaItem shadow" style="width: 100%; height: 6rem; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-labelledby="uploadMediaModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadMediaModalLabel">Upload Media File(s)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="formFile" class="form-label">Select File(s):</label>
                                <input class="form-control" type="file" id="uploadMediaFiles" name="uploadMediaFiles[]" multiple>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="uploadMediaFiles">Upload File(s)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var addPageModal;
    var selectFileModal;
    var uploadMediaModal;

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
        selectFileModal = new bootstrap.Modal(document.getElementById('selectFileModal'), {});
        uploadMediaModal = new bootstrap.Modal(document.getElementById('uploadMediaModal'), {});
    });

    const App = {
        data() {
            return {
                viewPage: 'general',
                activeCollection: {},
                theme: {},
                themes: {},
                pages: {},
                counts: {},
                users: {},
                mediaItems: {},
                editingTemplate: {},
                editingTitle: "",
                editingTemplateName: "",
                editingPath: "",
                editingMode: 0,
                editingID: null,
                editingPublished: true,
                editingDate: null,
                selectFileFieldID: "",
            }
        },
        methods: {
            setPage(page, update = false) {
                if (update == false && this.viewPage == 'editPage') {
                    if (confirm('Are you sure you want to leave? Any unsaved work will be lost.')) {
                        this.viewPage = page;
                    }
                } else {
                    this.viewPage = page;
                }
            },
            getThemes() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.themes = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/themes", true);
                xmlhttp.send();
            },
            getTheme() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.theme = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/themes/current", true);
                xmlhttp.send();
            },
            getMedia() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.mediaItems = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/media", true);
                xmlhttp.send();
            },
            getCounts() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.counts = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/counts", true);
                xmlhttp.send();
            },
            getUsers() {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.users = JSON.parse(this.responseText);
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/users", true);
                xmlhttp.send();
            },
            getPages(collection) {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.pages = JSON.parse(this.responseText);
                    comp.setPage('pages');
                    comp.activeCollection = collection;
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/collections/" + collection.id + "/pages", true);
                xmlhttp.send();
            },
            editPage(pageID, update) {
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
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
                    xmlhttp.onload = function() {
                        comp.getPages(comp.activeCollection);
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
                xmlhttp.onload = function() {
                    comp.editingTemplate = JSON.parse(this.responseText);
                    comp.setPage('editPage');
                    comp.editingPath = "/" + comp.editingTitle.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
                    comp.editingMode = 0;
                    comp.editingPublished = false;
                    comp.editingDate = "Never";
                    addPageModal.hide();
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
                        content[field.id].forEach(function(subField) {
                            var subItem = JSON.parse(JSON.stringify(field.fields));
                            subItem.forEach(function(subFields) {
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
                xmlhttp.onload = function() {
                    comp.editingTemplate = JSON.parse(this.responseText);
                    comp.editingTemplateName = page.templateName;
                    comp.setPage('editPage', update);
                    comp.editingMode = 1;
                    comp.editingTitle = page.title;
                    comp.editingPath = page.path;
                    comp.editingID = page._id;
                    comp.editingPublished = page.published;
                    var dateObject = new Date(page.edited * 1000);
                    comp.editingDate = dateObject.toLocaleString();
                    comp.editingTemplate.sections.forEach(function(section) {
                        section.fields.forEach(function(field) {
                            comp.getTemplateValue(page.content, field);
                        });
                    });
                }
                xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/templates/" + page.templateName, true);
                xmlhttp.send();
            },
            savePage() {
                var data = {
                    template: this.editingTemplate,
                    templateName: this.editingTemplateName,
                    title: this.editingTitle,
                    path: this.editingPath,
                    collection: this.activeCollection.id,
                    published: this.editingPublished
                }
                var comp = this;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onload = function() {
                    comp.editPage(JSON.parse(this.responseText)._id, true);
                    alert("Page saved!");
                }
                if (this.editingMode == 0) {
                    xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/pages/generate", true);
                } else {
                    xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/pages/" + comp.editingID, true);
                }
                xmlhttp.setRequestHeader('Content-Type', 'application/json');
                xmlhttp.send(JSON.stringify(data));
            },
            selectFileItem(filename) {
                var comp = this;
                this.editingTemplate.sections.forEach(function(section) {
                    section.fields.forEach(function(field) {
                        if (field.id == comp.selectFileFieldID) {
                            field.value = filename;
                            selectFileModal.hide();
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
                xmlhttp.onload = function() {
                    document.getElementById('uploadMediaFiles').value = "";
                    uploadMediaModal.hide();
                    comp.getMedia();
                }
                xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/media/upload");
                xmlhttp.send(formData);
            },
            deleteMediaFile(itemID) {
                if (confirm("Are you sure you want to delete this?") == true) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.getMedia();
                    }
                    xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/media/" + itemID, true);
                    xmlhttp.send();
                }
            }
        },
        mounted() {
            this.getTheme();
            this.getThemes();
            this.getMedia();
            this.getCounts();
            this.getUsers();
        }
    }

    const app = Vue.createApp(App);

    app.component('Trumbowyg', VueTrumbowyg.default);

    app.component('templateinput', {
        props: ['field'],
        data() {
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
                            serverPath: "<?php echo BASEPATH ?>/api/media/upload/richtext"
                        }
                    }
                }
            }
        },
        methods: {
            selectImage(fieldID) {
                this.$parent.selectFileFieldID = fieldID;
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
                <textarea v-if="field.type == 'textarea'" v-model="field.value" type="link" class="form-control" :placeholder="field.placeholder"></textarea>
                <trumbowyg v-if="field.type == 'richtext'" v-model="field.value" :config="richtextOptions"></trumbowyg>
                <img v-bind:src="'<?php echo BASEPATH; ?>/uploads/'+field.value" v-if="field.type == 'image' && field.value != null" class="d-block img-thumbnail mb-1" style="width: auto; height: 10rem; object-fit: cover;">
                <button class="btn btn-sm btn-primary me-2" v-if="field.type == 'image'" @click="selectImage(field.id)"><span v-if="field.value == null">Select</span><span v-if="field.value != null">Replace</span> Image</button>
                <button class="btn btn-sm btn-danger" v-if="field.type == 'image' && field.value != null" @click="field.value = null">Remove Image</button>
                <div v-if="field.type == 'list'" class="ps-3">
                    <div v-for="(listItem, i) in field.value" class="mb-3 bg-secondary text-light p-2 pb-1">
                        <button class="btn btn-danger btn-sm mb-2" @click="removeListItem(field, i)">Remove</button>
                        <templateinput :field="subField" v-for="subField in listItem"></templateinput>
                    </div>
                    <button class="btn btn-sm btn-success w-100" @click="addListItem(field)">Add Item</button>
                </div>
            </div>
            `
    });

    app.mount('#app');
</script>
<?php include 'footer.php'; ?>