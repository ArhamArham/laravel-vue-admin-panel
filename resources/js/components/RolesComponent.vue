<template>
  <div>
    <v-data-table
      :headers="headers"
      :loading="loading"
      loading-text="loading......"
      :items="roles.data"
      :items-per-page="5"
      sort-by="id"
      item-key="id"
      :options.sync="options"
      @pagination="paginate"
      :server-items-length="roles.total"
      class="elevation-1"
      :footer-props="{
      itemsPerPageOptions:[5,10,15],
      itemsPerPageText:'Roles Per Page',
      showCurrentPage:true,
      showFirstLastPage:true,
    }"
    >
      <template v-slot:top>
        <v-toolbar flat color="dark">
          <v-toolbar-title>Manage Role</v-toolbar-title>
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
              <v-btn color="primary" dark class="mb-2" v-on="on">New Role</v-btn>
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
                          v-model="editedItem.name"
                          :rules="nameRules"
                          label="Role name"
                        ></v-text-field>
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
    selected: [],
    options: {
      sortBy: ["name"],
      sortDesc: [false]
    },
    nameRules: [v => !!v || "Role name is required"],
    dialog: false,
    headers: [
      {
        text: "#",
        align: "start",
        sortable: false,
        value: "id"
      },
      { text: "Name", value: "name" },
      { text: "Created At", value: "created_at" },
      { text: "Updated At", value: "updated_at" },
      { text: "Actions", value: "actions", sortable: false }
    ],
    roles: [],
    editedIndex: -1,
    editedItem: {
      name: ""
    },
    defaultItem: {
      name: ""
    }
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Role" : "Edit Role";
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
    // selectAll(e){
    // 	this.selected=[]
    //     if(e.length > 0){
    // 		this.selected = e.map(val => val)
    // 	}

    // 	console.log(this.selected)
    // },
    // deleteAll(){

    // 	let delIds=this.selected.map((val,i) => {

    // 		//return this.roles.data.indexOf(val)
    // 		return this.roles.data.findIndex((element) => {
    // 			return element.id === val;
    // 		});
    // 			// console.log(this.roles.data.indexOf(val))
    //             //console.dir(index)
    //             // this.roles.data.splice(index, 1)
    // 		})
    // 	// let delIds=[0,2-1,4-2]
    // 	//console.log(delIds)
    // 			delIds.forEach((element,i) => {
    // 				console.log(element -i)
    // 				this.roles.data.splice(element-i, 1)
    // 				console.log(this.selected[0]=[])
    // 		});
    // 		this.selected.splice(1,1)
    //     let decide = confirm('Are you sure you want to delete these items?')
    //     if(decide){
    //         axios.post('api/roles/delete',{'roles':this.selected})
    //     .then(res => {
    // 		this.selected.map(val => {
    //             const index = this.roles.data.indexOf(val)
    //             //console.dir(index)
    //             this.roles.data.splice(index, 1)
    //         })
    //         this.text='Roles deleted successfully'
    //         this.snackbar=true
    //     }).catch(err => console.log(err.response))
    // }
    // },
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
        .get(`api/roles?page=${e.page}`, {
          params: {
            per_page: e.itemsPerPage,
            sort_by: sortBy,
            order_by: orderBy
          }
        })
        .then(res => {
          this.roles = res.data.roles;
        })
        .catch(err => console.dir(err.response));
    },
    searchIt(e) {
      if (e.length > 3) {
        axios
          .get(`api/roles/${e}`)
          .then(res => (this.roles = res.data.roles))
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
      this.editedIndex = this.roles.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.roles.data.indexOf(item);
      let decide = confirm("Are you sure you want to delete this item?");
      if (decide) {
        axios
          .delete("api/roles/" + item.id)
          .then(res => {
            this.roles.data.splice(index, 1);
            this.text = "Role " + res.data.role.name + " has been deleted";
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
          .put("api/roles/" + this.editedItem.id, {
            name: this.editedItem.name
          })
          .then(res => {
            Object.assign(this.roles.data[this.editedIndex], res.data.role);
            this.close();
            //this.roles.push(res.data.role)
          })
          .catch(err => console.log(err.response));
      } else {
        axios
          .post("api/roles", { name: this.editedItem.name })
          .then(res => {
            this.close();
            this.roles.data.push(res.data.role);
          })
          .catch(err => console.log(err.response));
      }
    }
  }
};
</script>

<style>
</style>