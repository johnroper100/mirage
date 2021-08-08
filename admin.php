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
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <title>Mirage Admin</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
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

        .sidebarItem {
            width: 85%;
            margin: 0 auto;
            border-radius: 0.5rem;
        }

        .sidebarItem:hover {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="d-flex" id="app">
        <!-- Sidebar-->
        <div class="bg-dark text-light" id="sidebar-wrapper">
            <div class="sidebar-heading bg-secondary text-light">Mirage Dashboard</div>
            <div class="list-group list-group-flush mt-3">
                <span class="p-2 ps-3 sidebarItem mt-2" @click="viewPage = 0" :class="{'bg-success': viewPage == 0}">Dashboard</span>
                <span class="p-2 ps-3 sidebarItem mt-2" @click="getPages(collection)" :class="{'bg-success': viewPage == 1 && activeCollection.id == collection.id}" v-for="collection in theme.collections">{{collection.name}}</span>
                <span class="p-2 ps-3 sidebarItem mt-2" @click="viewPage = 2" :class="{'bg-success': viewPage == 2}">Settings</span>
                <span class="p-2 ps-3 sidebarItem mt-2">Log Out</span>
            </div>
        </div>
        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="sidebarToggle">=</button>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 0">Dashboard</h4>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 1">{{activeCollection.name}}</h4>
                    <h4 class="mb-0 ms-3" v-if="viewPage == 2">Settings</h4>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <button class="btn btn-primary" v-if="viewPage == 0">View Site</button>
                            <button class="btn btn-success" v-if="viewPage == 1">Add Page</button>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Page content-->
            <div class="container-fluid pt-3 pb-3 ps-5 pe-4">
                <div v-if="viewPage == 1">
                    <ul class="list-group mt-2">
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
                    settings
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