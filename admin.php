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
    <script defer src="all.min.js"></script>
    <title>Mirage Admin</title>
    <style>
        * {
            border-radius: 0 !important;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }

        .bg-dark {
            background-color: #212529 !important;
        }

        .bg-secondary {
            background-color: #343a40 !important;
        }

        .bg-light {
            background-color: #f1f3f5 !important;
        }

        .btn-dark {
            color: #f8f9fa;
            background-color: #212529;
            border-color: #212529;
        }

        .btn-success {
            color: #f8f9fa;
            background-color: #2f9e44;
            border-color: #2f9e44;
        }

        .btn-primary {
            color: #f8f9fa;
            background-color: #1971c2;
            border-color: #1971c2;
        }

        #app {
            overflow-x: hidden;
        }

        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: -15rem;
            -webkit-transition: margin .25s ease-out;
            -moz-transition: margin .25s ease-out;
            -o-transition: margin .25s ease-out;
            transition: margin .25s ease-out;
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
        }

        #sidebar-wrapper .list-group {
            width: 15rem;
        }

        #page-content-wrapper {
            min-width: 100vw;
        }

        body.mirage-sidenav-toggled #app #sidebar-wrapper {
            margin-left: 0;
        }

        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                min-width: 0;
                width: 100%;
            }

            body.mirage-sidenav-toggled #app #sidebar-wrapper {
                margin-left: -15rem;
            }
        }

        .sidebarItem:hover {
            cursor: pointer;
        }

        .sidebarItem {
            border-left: solid 5px transparent;
        }

        .sidebarItem.active {
            border-left: solid 5px #37b24d;
            background-color: #343a40;
        }

        .navbar-toggler {
            line-height: 1.5 !important;
            color: #f8f9fa !important;
            padding: .375rem .75rem !important;
            font-size: 1rem !important;
        }

        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active,
        .tab-content>.active,
        .nav-tabs {
            border: 0 !important;
        }
    </style>
</head>

<body>
    <div class="d-flex" id="app">
        <!-- Sidebar-->
        <div class="bg-dark text-light" id="sidebar-wrapper">
            <div class="sidebar-heading bg-secondary text-light text-center text-uppercase shadow-sm">Mirage Admin</div>
            <div class="list-group list-group-flush mt-2">
                <span class="p-2 ps-3 sidebarItem mt-2" @click="viewPage = 0" :class="{'active': viewPage == 0}"><i class="fa-solid fa-gauge me-1"></i> General</span>
                <span class="p-2 ps-3 sidebarItem mt-2" @click="getPages(collection)" :class="{'active': (viewPage == 1 || viewPage == 2) && activeCollection.id == collection.id}" v-for="collection in theme.collections"><i class="fa-solid me-1" :class="collection.icon"></i> {{collection.name}}</span>
                <span class="p-2 ps-3 sidebarItem mt-2" @click="viewPage = 3" :class="{'active': viewPage == 3}"><i class="fa-solid fa-gears me-1"></i> Settings</span>
                <span class="p-2 ps-3 sidebarItem mt-2"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Log Out</span>
            </div>
        </div>
        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 0"><i class="fa-solid fa-gauge me-1"></i> General</h4>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 1"><i class="fa-solid me-1" :class="activeCollection.icon"></i> {{activeCollection.name}}</h4>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 2"><i class="fa-solid fa-file-plus me-1"></i> Add Page</h4>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 3"><i class="fa-solid fa-gears me-1"></i> Settings</h4>
                    <button class="btn btn-dark navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <button class="btn btn-primary" v-if="viewPage == 0">View Site</button>
                            <button class="btn btn-success" v-if="viewPage == 1" @click="addPage">Add Page</button>
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
                                    <h4>{{page.title}}</h4>
                                    <h6 class="text-secondary">{{page.path}} -> {{page.templateName}}</h6>
                                </div>
                                <div class="col-12 col-md-3 text-md-end">
                                    <a class="btn btn-primary btn-sm me-1">Edit</a>
                                    <a class="btn btn-danger btn-sm" @click="deletePage(page)">Delete</a>
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
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab" aria-controls="content" aria-selected="true">Content</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="options-tab" data-bs-toggle="tab" data-bs-target="#options" type="button" role="tab" aria-controls="options" aria-selected="false">Options</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active p-3" id="content" role="tabpanel" aria-labelledby="content-tab">Content</div>
                            <div class="tab-pane fade p-3" id="options" role="tabpanel" aria-labelledby="options-tab">Options</div>
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
                                <select class="form-select" aria-label="Available Tamples">
                                    <option selected disabled>Select a Template</option>
                                    <template v-for="template in theme.templates" :key="template.id">
                                        <option v-if="activeCollection.allowed_templates != null && activeCollection.allowed_templates.includes(template.id)">{{template.name}}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary">Add Page</button>
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

        });

        const App = {
            data() {
                return {
                    viewPage: 0,
                    activeCollection: {},
                    theme: {},
                    pages: {}
                }
            },
            methods: {
                getTheme() {
                    var comp = this;
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onload = function() {
                        comp.theme = JSON.parse(this.responseText);
                    }
                    xmlhttp.open("GET", "/mirage/api/theme", true);
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
                    xmlhttp.open("GET", "/mirage/api/page/collection/" + collection.id, true);
                    xmlhttp.send();
                },
                deletePage(page) {
                    if (confirm("Are you sure you want to delete this?") == true) {
                        var comp = this;
                        var xmlhttp = new XMLHttpRequest();
                        xmlhttp.onload = function() {
                            console.log(this.responseText);
                            comp.getPages(page.type);
                        }
                        xmlhttp.open("DELETE", "/mirage/api/page/" + page._id, true);
                        xmlhttp.send();
                    }
                },
                addPage() {
                    var myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {});
                    myModal.show();
                    //this.viewPage = 2;
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