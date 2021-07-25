<?php

$strJsonFileContents = file_get_contents("templates/mirage/home.json");
$array = json_decode($strJsonFileContents, true);

?>

<!DOCTYPE html>
<html>

<head>
    <title>WIP</title>
    <script src="https://unpkg.com/vue@next"></script>
</head>

<body>
    <div id="app">
        <div v-for="section in template.sections">
            <u>
                <h2>{{section.name}}</h2>
            </u>
            <div v-for="field in section.fields">
                {{field.name}}:
                <div v-if="field.type == 'text'">
                    <input type="text" v-model="field.value" :placeholder="field.placeholder">
                </div>
                <div v-if="field.type == 'list'">
                    <div v-for="(listItem, i) in field.items" style="background-color: gray; margin-bottom: 0.5rem;">
                        <button @click="removeListItem(field, i)">remove</button>
                        <div v-for="subField in listItem">
                            {{subField.name}}:
                            <div v-if="subField.type == 'text'">
                                <input type="text" v-model="subField.value" :placeholder="subField.placeholder">
                            </div>
                        </div>
                    </div>
                    <button @click="addListItem(field)">add list item</button>
                </div>
            </div>
        </div>
        <button @click="submitForm">Submit</button>
    </div>
    <script>
        const App = {
            data() {
                return {
                    template: <?php echo $strJsonFileContents; ?>
                }
            },
            methods: {
                submitForm() {
                    this.template.sections.forEach(function(section) {
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
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "generate.php", true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.send(JSON.stringify(this.template));
                },
                addListItem(field) {
                    if (field.items == null) {
                        field.items = [];
                    }
                    field.items.push(JSON.parse(JSON.stringify(field.fields)));
                },
                removeListItem(field, id) {
                    field.items.splice(id, 1);
                }
            }
        }

        const app = Vue.createApp(App);

        app.mount('#app');
    </script>
</body>

</html>