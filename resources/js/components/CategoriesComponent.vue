<template>
  <div>
    <v-data-table
      :headers="headers"
      :loading="loading"
      loading-text="loading......"
      :items="categories.data"
      :items-per-page="5"
      sort-by="id"
      item-key="id"
      :options.sync="options"
      @pagination="paginate"
      :server-items-length="categories.total"
      class="elevation-1"
      :footer-props="{
      itemsPerPageOptions:[5,10,15],
      itemsPerPageText:'Categories Per Page',
      showCurrentPage:true,
      showFirstLastPage:true,
    }"
    >
      <template v-slot:top>
        <v-toolbar flat color="dark">
          <v-toolbar-title>Manage Category</v-toolbar-title>
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
              <v-btn color="primary" dark class="mb-2" v-on="on">New Category</v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" sm="12">
                      <v-form ref="form" v-model="valid">
                        <v-text-field
                          v-model="editedItem.title"
                          label="Category name"
                          :success-messages="title_success"
                          :error-messages="title_error"
                          @blur="checkTitle"
                          :rules="[rules.required,rules.min]"
                        />
                        <v-textarea
                          v-model="editedItem.description"
                          label="Description"
                          :rules="[rules.required,rules.max]"
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
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
        <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
      </template>

      <template v-slot:no-data>No data Availabe</template>
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
    valid: true,
    text: "",
    snackbar: false,
    title_success: "",
    title_error: "",
    selected: [],
    options: {
      sortBy: ["title"],
      sortDesc: [false]
    },
    rules: {
      required: v => !!v || "This field is required",
      min: v => v.length >= 3 || "Minimun 3 characters required",
      max: v => v.length <= 250 || "Maximum 250 characters required"
    },
    dialog: false,
    headers: [
      {
        text: "#",
        align: "start",
        sortable: false,
        value: "id"
      },
      { text: "Title", value: "title" },
      { text: "Description", value: "description" },
      { text: "Created At", value: "created_at" },
      { text: "Updated At", value: "updated_at" },
      { text: "Actions", value: "actions", sortable: false }
    ],
    categories: [],
    editedIndex: -1,
    editedItem: {
      title: "",
      description: ""
    },
    defaultItem: {
      title: "",
      description: ""
    }
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New category" : "Edit category";
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
    paginate(e) {
      console.dir(this.options);
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
        .get(`api/categories?page=${e.page}`, {
          params: {
            per_page: e.itemsPerPage,
            sort_by: sortBy,
            order_by: orderBy
          }
        })
        .then(res => {
          this.categories = res.data.categories;
        })
        .catch(err => console.dir(err.response));
    },
    checkTitle() {
      axios
        .post("api/title/validate", {
          title: this.editedItem.title,
          id: this.editedItem.id
        })
        .then(res => {
          console.log(res.data.message);
          this.title_success = res.data.message;
          this.title_error = "";
        })
        .catch(err => {
          this.title_success = "";
          this.title_error = "Title already taken";
        });
    },
    searchIt(e) {
      if (e.length > 3) {
        axios
          .get(`api/categories/${e}`)
          .then(res => (this.categories = res.data.categories))
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
      this.editedIndex = this.categories.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.categories.data.indexOf(item);
      let decide = confirm("Are you sure you want to delete this item?");
      if (decide) {
        axios
          .delete("api/categories/" + item.id)
          .then(res => {
            this.categories.data.splice(index, 1);
            this.text =
              "Category " + res.data.category.title + " has been deleted";
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
          .put("api/categories/" + this.editedItem.id, this.editedItem)
          .then(res => {
            Object.assign(
              this.categories.data[this.editedIndex],
              res.data.category
            );
            this.close();
            //this.categories.push(res.data.category)
          })
          .catch(err => console.log(err.response));
      } else {
        axios
          .post("api/categories", this.editedItem)
          .then(res => {
            this.close();
            this.categories.data.push(res.data.category);
          })
          .catch(err => console.log(err.response));
      }
    }
  }
};
</script>

<style>
</style>