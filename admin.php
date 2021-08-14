<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="<?php echo BASEPATH; ?>/assets/css/admin.css" rel="stylesheet">
    <script defer src="<?php echo BASEPATH; ?>/assets/js/all.min.js"></script>
    <title>Mirage Admin</title>
</head>

<body>
    <div class="d-flex" id="app">
        <!-- Sidebar-->
        <div class="bg-dark text-light" id="sidebar-wrapper">
            <div class="sidebar-heading bg-secondary text-light text-center text-uppercase shadow-sm">Mirage Admin</div>
            <div class="list-group list-group-flush mt-2">
                <span class="p-2 ps-3 sidebarItem mt-2" @click="viewPage = 0" :class="{'active': viewPage == 0}"><i class="fa-solid fa-gauge-simple me-1"></i> General</span>
                <span class="p-2 ps-3 sidebarItem mt-2" @click="getPages(collection)" :class="{'active': (viewPage == 1 || viewPage == 2) && activeCollection.id == collection.id}" v-for="collection in theme.collections"><i class="fa-solid me-1" :class="collection.icon"></i> {{collection.name}}</span>
                <span class="p-2 ps-3 sidebarItem mt-2" @click="viewPage = 3" :class="{'active': viewPage == 3}"><i class="fa-solid fa-gears me-1"></i> Settings</span>
                <a class="p-2 ps-3 sidebarItem mt-2 text-light text-decoration-none" href="<?php echo BASEPATH ?>/logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Log Out</a>
            </div>
        </div>
        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                    <h4 class="mb-0 ms-2" v-if="viewPage == 0">General</h4>
                    <h4 class="mb-0 ms-2" v-if="viewPage == 1">{{activeCollection.name}}</h4>
                    <h4 class="mb-0 ms-2" v-if="viewPage == 2 && editingMode == 0">Add Page</h4>
                    <h4 class="mb-0 ms-2" v-if="viewPage == 2 && editingMode == 1">Edit Page</h4>
                    <h4 class="mb-0 ms-2" v-if="viewPage == 3">Settings</h4>
                    <button class="btn btn-dark navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <button class="btn btn-primary" v-if="viewPage == 0"><i class="fa-solid fa-up-right-from-square me-1"></i> View Site</button>
                            <button class="btn btn-success" v-if="viewPage == 1" @click="addPage"><i class="fa-solid fa-plus me-1"></i> Add Page</button>
                            <button class="btn btn-danger me-md-2 mb-1 mb-md-0" @click="deletePage(editingID)" v-if="viewPage == 2 && editingMode == 1"><i class="fa-solid fa-trash-can me-1"></i> Delete</button>
                            <button class="btn btn-success" v-if="viewPage == 2" @click="savePage"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Page content-->
            <div class="container-fluid pt-3 pb-3 ps-5 pe-4">
                <div v-if="viewPage == 1">
                    <ul class="list-group mt-2 shadow-sm">
                        <li v-for="page in pages" class="list-group-item">
                            <div class="row mt-1">
                                <div class="col-12 col-md-9">
                                    <h4><i class="fa-solid fa-xs fa-lock me-1 text-warning" v-if="page.private"></i> {{page.title}}</h4>
                                    <h6 class="text-secondary">T: {{page.templateName}} <i class="fa-solid fa-right-long"></i> {{page.path}}</h6>
                                </div>
                                <div class="col-12 col-md-3 text-md-end">
                                    <a class="btn btn-primary btn-sm me-1" @click="editPage(page._id)"><i class="fa-solid fa-pen-to-square me-1"></i> Edit</a>
                                    <a class="btn btn-danger btn-sm" @click="deletePage(page._id)"><i class="fa-solid fa-trash-can me-1"></i> Delete</a>
                                </div>
                            </div>
                        </li>
                        <li v-if="pages.length == 0" class="list-group-item">
                            No <span class="text-lowercase">{{activeCollection.name}}</span> have been created! Use the <i>Add Page</i> button above to create content.
                        </li>
                    </ul>
                </div>
                <div v-if="viewPage == 2">
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
                                    <div class="accordion-item" v-for="(section, index) in editingTemplate.sections">
                                        <h2 class="accordion-header" :id="'heading'+index">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" v-bind:data-bs-target="'#collapse'+index" aria-expanded="false" v-bind:aria-controls="'#collapse'+index">
                                                {{section.name}}
                                            </button>
                                        </h2>
                                        <div :id="'collapse'+index" class="accordion-collapse collapse" aria-labelledby="'heading'+index">
                                            <div class="accordion-body">
                                                <div class="mb-3" v-for="field in section.fields">
                                                    <label class="form-label">{{field.name}}:</label>
                                                    <input v-if="field.type == 'text'" v-model="field.value" type="text" class="form-control" :placeholder="field.placeholder">
                                                    <input v-if="field.type == 'link'" v-model="field.value" type="link" class="form-control" :placeholder="field.placeholder">
                                                </div>
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
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" v-model="editingPrivate" v-bind:value="editingPrivate">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">Page Is Private</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="viewPage == 3">
                    Settings
                </div>
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Add a Page to <b>{{activeCollection.name}}</b></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Page Title:</label>
                                    <input v-model="editingTitle" type="text" class="form-control" placeholder="My awesome page">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Page Path:</label>
                                    <input v-model="editingPath" type="text" class="form-control" placeholder="/">
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
                                <button type="button" class="btn btn-primary" @click="editNewPage" :class="{'disabled': editingTitle == '' || editingTemplateName == '' || editingPath == ''}">Add Page</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue@next"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        var myModal;

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

            myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {});

        });

        const App = {
            data() {
                return {
                    viewPage: 0,
                    activeCollection: {},
                    theme: {},
                    pages: {},
                    editingTemplate: {},
                    editingTitle: "",
                    editingTemplateName: "",
                    editingPath: "",
                    editingMode: 0,
                    editingID: null,
                    editingPrivate: true
                }
            },
            methods: {
                getTheme() {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.theme = JSON.parse(this.responseText);
                    }
                    xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/theme", true);
                    xmlhttp.send();
                },
                getPages(collection) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.pages = JSON.parse(this.responseText);
                        comp.viewPage = 1;
                        comp.activeCollection = collection;
                    }
                    xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/page/collection/" + collection.id, true);
                    xmlhttp.send();
                },
                editPage(pageID) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        var pageDetails = JSON.parse(this.responseText);
                        comp.editPageTemplate(pageDetails);
                    }
                    xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/page/" + pageID, true);
                    xmlhttp.send();
                },
                deletePage(pageID) {
                    if (confirm("Are you sure you want to delete this?") == true) {
                        var comp = this;
                        var xmlhttp = new XMLHttpRequest();
                        xmlhttp.onload = function() {
                            comp.getPages(comp.activeCollection);
                        }
                        xmlhttp.open("DELETE", "<?php echo BASEPATH ?>/api/page/" + pageID, true);
                        xmlhttp.send();
                    }
                },
                addPage() {
                    myModal.show();
                    this.editingTitle = "";
                    this.editingPath = "";
                    this.editingTemplateName = "";
                },
                editNewPage() {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.editingTemplate = JSON.parse(this.responseText);
                        comp.viewPage = 2;
                        comp.editingMode = 0;
                        comp.editingPrivate = true;
                        myModal.hide();
                    }
                    xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/template/" + comp.editingTemplateName, true);
                    xmlhttp.send();
                },
                editPageTemplate(page) {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.editingTemplate = JSON.parse(this.responseText);
                        comp.editingTemplateName = page.templateName;
                        comp.viewPage = 2;
                        comp.editingMode = 1;
                        comp.editingTitle = page.title;
                        comp.editingPath = page.path;
                        comp.editingID = page._id;
                        comp.editingPrivate = page.private;
                        comp.editingTemplate.sections.forEach(function(section) {
                            section.fields.forEach(function(field) {
                                if (page["content"][field.id] != null) {
                                    field.value = page["content"][field.id];
                                }
                            });
                        });
                    }
                    xmlhttp.open("GET", "<?php echo BASEPATH ?>/api/template/" + page.templateName, true);
                    xmlhttp.send();
                },
                savePage() {
                    this.editingTemplate.sections.forEach(function(section) {
                        section.fields.forEach(function(field) {
                            if (field.type == 'list') {
                                field.value = [];
                                if (field.items != null && field.items.length > 0) {
                                    field.items.forEach(function(item) {
                                        let itemValue = {};
                                        item.forEach(function(subItem) {
                                            itemValue[subItem.id] = subItem.value;
                                        });
                                        field.value.push(itemValue);
                                    });
                                }
                            }
                        });
                    });
                    var data = {
                        template: this.editingTemplate,
                        templateName: this.editingTemplateName,
                        title: this.editingTitle,
                        path: this.editingPath,
                        collection: this.activeCollection.id,
                        private: this.editingPrivate
                    }
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.editPage(JSON.parse(this.responseText)._id);
                        alert("Page saved!");
                    }
                    if (this.editingMode == 0) {
                        xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/page/generate", true);
                    } else {
                        xmlhttp.open("POST", "<?php echo BASEPATH ?>/api/page/" + comp.editingID, true);
                    }
                    xmlhttp.setRequestHeader('Content-Type', 'application/json');
                    xmlhttp.send(JSON.stringify(data));
                }
            },
            mounted() {
                this.getTheme();
            }
        }

        const app = Vue.createApp(App);

        app.mount('#app');
    </script>
</body>

</html>