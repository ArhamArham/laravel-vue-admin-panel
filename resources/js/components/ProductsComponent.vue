                <template>
  <div>
    <v-data-table
      :headers="headers"
      :loading="loading"
      loading-text="loading......"
      :items="products.data"
      :items-per-page="5"
      sort-by="id"
      item-key="id"
      :options.sync="options"
      @pagination="paginate"
      :server-items-length="products.total"
      class="elevation-1"
      :footer-props="{
                    itemsPerPageOptions:[5,10,15],
                    itemsPerPageText:'Products Per Page',
                    showCurrentPage:true,
                    showFirstLastPage:true,
                    }"
    >
      <template v-slot:top>
        <v-toolbar flat color="dark">
          <v-toolbar-title>Manage Product</v-toolbar-title>
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
              <!--<v-btn color="primary" dark class="mb-2 ml-4" @click="deleteAll">Delete</v-btn>-->
              <v-btn color="primary" dark class="mb-2" v-on="on">New product</v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" sm="12">
                      <v-form ref="form">
                        <v-text-field
                          v-model="editedItem.name"
                          label="Name"
                          required
                          type="text"
                          :rules="[rules.required,rules.min]"
                        />
                        <v-text-field
                          label="Price"
                          v-model="editedItem.price"
                          name="Price"
                          type="text"
                          :rules="[rules.required]"
                        />
                        <v-file-input
                          v-if="editedIndex==-1"
                          v-model="editedItem.photo"
                          label="Select Photo"
                          placeholder="Upload Avatar"
                        />
                        <v-select
                          :items="selectCategories"
                          v-model="editedItem.categories"
                          label="Categories"
                          :rules="[rules.required]"
                        />
                        <v-text-field
                          id="Discount"
                          label="discount"
                          v-model="editedItem.discount"
                          type="text"
                          :rules="[rules.required]"
                          required
                        />
                        <v-textarea
                          id="Description"
                          label="Description"
                          v-model="editedItem.description"
                          name="Description"
                          type="text"
                          required
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
      <template v-slot:item.thumbnail="{ item }">
        <v-edit-dialog>
          <v-list-item-avatar>
            <v-img :src="item.thumbnail" aspect-ratio="1" class="grey rounded lighten-2" />
          </v-list-item-avatar>
          <template v-slot:input>
            <v-file-input
              v-model="editedItem.photo"
              label="Select Photo"
              placeholder="Upload Product Image"
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
    selected: [],
    selectCategories: [],
    rules: {
      required: v => !!v || "This field is required",
      min: v => v.length >= 3 || "Minimun 3 characters required",
      max: v => v.length <= 500 || "Maximum 500 characters required"
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
      { text: "Price", value: "price" },
      { text: "Thumbnail", value: "thumbnail", sortable: false },
      { text: "Category", value: "categories", sortable: false },
      { text: "Discount", value: "discount" },
      { text: "Description", value: "description", sortable: false },
      { text: "Created At", value: "created_at" },
      { text: "Updated At", value: "updated_at" },
      { text: "Actions", value: "actions", sortable: false }
    ],
    products: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      name: "",
      price: "",
      photo: null,
      categories: "",
      discount: "",
      description: "",
      created_at: "",
      updated_at: ""
    },
    defaultItem: {
      id: "",
      name: "",
      price: "",
      photo: null,
      categories: "",
      discount: "",
      description: "",
      created_at: "",
      updated_at: ""
    }
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Product" : "Edit Product";
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
    uploadPhoto(item) {
      if (this.editedItem.photo != null) {
        const index = this.products.data.indexOf(item);
        let formData = new FormData();
        formData.append(
          "photo",
          this.editedItem.photo,
          this.editedItem.photo.name
        );
        formData.append("product", item.id);
        axios
          .post("api/product/photo", formData)
          .then(res => {
            this.products.data[index].thumbnail = res.data.product.thumbnail;
            this.editedItem.photo = null;
          })
          .catch(err => {
            console.error(err.response);
          });
      }
    },
    resetValidation() {
      this.$refs.form.resetValidation();
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
        .get(`api/products?page=${e.page}`, {
          params: {
            per_page: e.itemsPerPage,
            sort_by: sortBy,
            order_by: orderBy
          }
        })
        .then(res => {
          this.products = res.data.products;
          this.selectCategories = res.data.categories;
          //console.log(selCats)
        })
        .catch(err => console.dir(err.response));
    },
    searchIt(e) {
      if (e.length > 3) {
        axios
          .get(`api/products/${e}`)
          .then(res => (this.products = res.data.products))
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
      this.editedIndex = this.products.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.products.data.indexOf(item);
      let decide = confirm("Are you sure you want to delete this item?");
      if (decide) {
        axios
          .delete("api/products/" + item.id)
          .then(res => {
            this.products.data.splice(index, 1);
            this.text =
              "product " + res.data.product.name + " has been deleted";
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
          .put("api/products/" + this.editedItem.id, this.editedItem)
          .then(res => {
            Object.assign(
              this.products.data[this.editedIndex],
              res.data.product
            );
            this.close();
          })
          .catch(err => console.log(err.response));
      } else {
        if (this.editedItem.photo != null) {
          let formData = new FormData();
          formData.append(
            "photo",
            this.editedItem.photo,
            this.editedItem.photo.name
          );
          formData.append("name", this.editedItem.name);
          formData.append("price", this.editedItem.price);
          formData.append("discount", this.editedItem.discount);
          formData.append("category", this.editedItem.categories);
          formData.append("description", this.editedItem.description);
          axios
            .post("api/products", formData)
            .then(res => {
              this.products.data.push(res.data.product);

              this.close();
              this.editedItem.photo = null;
            })
            .catch(err => {
              console.error(err.response);
            });
        }
      }
    }
  }
};
</script>
<style>
</style>