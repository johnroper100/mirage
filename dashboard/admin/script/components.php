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
