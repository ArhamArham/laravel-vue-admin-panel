<template>
  <div>
    <v-data-table
      :headers="headers"
      @pagination="paginate"
      :loading="loading"
      loading-text="loading......"
      :items="users.data"
      :items-per-page="5"
      :server-items-length="users.total"
      sort-by="id"
      item-key="id"
      :options.sync="options"
      class="elevation-1"
      :footer-props="{
      itemsPerPageOptions:[5,10,15],
      itemsPerPageText:'Users Per Page',
      showCurrentPage:true,
      showFirstLastPage:true,
    }"
    >
      <template v-slot:top>
        <v-toolbar flat color="dark">
          <v-toolbar-title>Manage User</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-text-field
            @input="searchIt"
            append-icon="mdi-magnify"
            label="Search..."
            single-line
            hide-details
          ></v-text-field>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on }">
              <!-- <v-btn color="primary" dark class="mb-2 ml-4" @click="deleteAll">Delete</v-btn> -->
              <v-btn color="primary" dark class="mb-2" v-on="on">New User</v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" sm="12">
                      <v-form v-model="valid" ref="form">
                        <v-text-field
                          v-model="editedItem.name"
                          :rules="[rules.required,rules.min]"
                          label="Name"
                        />
                        <v-text-field
                          label="E-mail"
                          v-model="editedItem.email"
                          name="login"
                          :success-messages="email_success"
                          :error-messages="email_error"
                          @blur="checkEmail"
                          :rules="[rules.required,rules.validEmail]"
                          required
                          type="text"
                        />
                        <v-text-field
                          v-if="editedIndex == -1"
                          id="password"
                          label="Password"
                          v-model="editedItem.password"
                          :rules="[rules.required]"
                          type="password"
                          required
                        />
                        <v-text-field
                          v-if="editedIndex == -1"
                          id="password"
                          label="Retype Password"
                          v-model="editedItem.retypepassword"
                          :rules="[rules.required,passwordMatch]"
                          name="password"
                          type="password"
                          required
                        />
                        <v-select
                          :items="roles"
                          v-model="editedItem.role"
                          :rules="[rules.required]"
                          label="Role"
                        />
                      </v-form>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                <v-btn color="blue darken-1" text :disabled="!valid" @click="save">Save</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.role="{ item }">
        <v-edit-dialog
          large
          block
          persistent
          :return-value.sync="item.role"
          @save="updateRole(item)"
        >
          {{item.role}}
          <template v-slot:input>
            <h2>Change Role</h2>
          </template>
          <template v-slot:input>
            <v-select :items="roles" v-model="item.role" :rules="[rules.required]" label="Role" />
          </template>
        </v-edit-dialog>
      </template>
      <template v-slot:item.photo="{ item }">
        <v-edit-dialog>
          <v-list-item-avatar>
            <v-img :src="item.photo" aspect-ratio="1" class="grey rounded lighten-2" />
          </v-list-item-avatar>
          <template v-slot:input>
            <v-file-input
              v-model="editedItem.photo"
              label="Select Photo"
              placeholder="Upload Avatar"
              accept="image/jpg, image/png, image/jpeg"
              @change="uploadPhoto(item)"
            />
          </template>
        </v-edit-dialog>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
        <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
      </template>

      <template v-slot:no-data>
        <v-btn color="primary" @click="initialize">Reset</v-btn>
      </template>
    </v-data-table>
    <v-snackbar v-model="snackbar">
      {{text}}
      <v-btn color="pink" text @click="snackbar = false">Close</v-btn>
    </v-snackbar>
  </div>
</template>
<script>
export default {
  data: () => ({
    loading: false,
    email_success: "",
    email_error: "",
    valid: true,
    text: "",
    snackbar: false,
    selected: [],
    roles: [],
    rules: {
      required: v => !!v || "This field is required",
      min: v => v.length >= 5 || "Minimun 5 characters required",
      validEmail: v => /.+@.+\..+/.test(v) || "E-mail must be valid"
    },
    options: {
      sortBy: ["name"],
      sortDesc: [false]
    },
    dialog: false,
    headers: [
      {
        text: "#",
        align: "start",
        sortable: false,
        value: "id"
      },
      { text: "Name", value: "name" },
      { text: "Email", value: "email" },
      { text: "Role", value: "role" },
      { text: "Photo", value: "photo", sortable: false },
      { text: "Created At", value: "created_at" },
      { text: "Updated At", value: "updated_at" },
      { text: "Actions", value: "actions", sortable: false }
    ],
    users: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      name: "",
      email: "",
      password: "",
      retypepassword: "",
      role: "",
      photo: null
    },
    defaultItem: {
      id: "",
      name: "",
      email: "",
      password: "",
      retypepassword: "",
      role: "",
      photo: null
    }
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New User" : "Edit User";
    },
    passwordMatch() {
      return this.editedItem.password != this.editedItem.retypepassword
        ? "Password does not match"
        : true;
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
    }
  },

  created() {
    this.initialize();
  },

  methods: {
    resetValidation() {
      this.$refs.form.resetValidation();
    },
    uploadPhoto(item) {
      if (this.editedItem.photo != null) {
        const index = this.users.data.indexOf(item);
        let formData = new FormData();
        formData.append(
          "photo",
          this.editedItem.photo,
          this.editedItem.photo.name
        );
        formData.append("user", item.id);
        axios
          .post("api/user/photo", formData)
          .then(res => {
            this.users.data[index].photo = res.data.user.photo;
            this.editedItem.photo = null;
          })
          .catch(err => {
            console.error(err.response);
          });
      }
    },
    updateRole(item) {
      const index = this.users.data.indexOf(item);
      axios
        .post("api/user/role", { role: item.role, user: item.id })
        .then(res => {
          this.text =
            res.data.user.name + "'s Role updated to " + res.data.user.role;
          this.snackbar = true;
        })
        .catch(err => {
          this.text = "You cant do this!";
          this.snackbar = true;
          this.users[index].role = err.response.data.user.role;
        });
    },
    checkEmail() {
      if (/.+@.+\..+/.test(this.editedItem.email)) {
        axios
          .post("api/email/validate", {
            email: this.editedItem.email,
            id: this.editedItem.id
          })
          .then(res => {
            console.log(res.data.message);
            this.email_success = res.data.message;
            this.email_error = "";
          })
          .catch(err => {
            this.email_success = "";
            this.email_error = "Email already taken";
          });
      }
    },
    paginate(e) {
      let sortBy =
        this.options.sortBy.length == 0 ? "id" : this.options.sortBy[0];
      let orderBy =
        this.options.sortDesc.length > 0 && this.options.sortDesc[0]
          ? "asc"
          : "desc";
      if (sortBy == "id") {
        orderBy = "asc";
      }
      axios
        .get(`api/users?page=${e.page}`, {
          params: {
            per_page: e.itemsPerPage,
            sort_by: sortBy,
            order_by: orderBy
          }
        })
        .then(res => {
          this.users = res.data.users;
          this.roles = res.data.roles;
        })
        .catch(err => console.dir(err.response));
    },
    searchIt(e) {
      if (e.length > 3) {
        axios
          .get(`api/users/${e}`)
          .then(res => (this.users = res.data.users))
          .catch(err => console.dir(err.response));
      }
      if (e.length <= 0) {
        this.paginate(e);
      }
    },
    initialize() {
      // Add a request interceptor
      axios.interceptors.request.use(
        config => {
          // Do something before request is sent
          this.loading = true;
          return config;
        },
        error => {
          this.loading = false;
          return Promise.reject(error);
        }
      );

      // Add a response interceptor
      axios.interceptors.response.use(
        response => {
          this.loading = false;
          return response;
        },
        error => {
          this.loading = false;
          return Promise.reject(error);
        }
      );
    },

    editItem(item) {
      this.editedIndex = this.users.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.users.data.indexOf(item);
      let decide = confirm("Are you sure you want to delete this item?");
      if (decide) {
        axios
          .delete("api/users/" + item.id)
          .then(res => {
            this.users.data.splice(index, 1);
            this.text = "user " + res.data.user.name + " has been deleted";
            this.snackbar = true;
          })
          .catch(err => console.log(err.response));
      }
    },

    close() {
      this.dialog = false;
      this.resetValidation();
      //this.editedItem.name=''
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      if (this.editedIndex > -1) {
        axios
          .put("api/users/" + this.editedItem.id, this.editedItem)
          .then(res => {
            Object.assign(this.users.data[this.editedIndex], res.data.user);
            this.close();
            //this.users.push(res.data.user)
          })
          .catch(err => console.log(err.response));
      } else {
        axios
          .post("api/users", this.editedItem)
          .then(res => {
            this.close();
            this.users.data.push(res.data.user);
          })
          .catch(err => console.log(err.response));
      }
    }
  }
};
</script>

<style>
</style>