<template>
  <v-container fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="6">
        <v-card class="elevation-12">
          <v-toolbar color="primary" dark flat>
            <v-toolbar-title>User Settings</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <v-card-text>
            <v-row align="center" justify="center">
              <v-img
                :src="editedItem.photo"
                aspect-ratio="1"
                class="grey lighten-2"
                max-width="300"
                max-height="250"
              ></v-img>
            </v-row>
            <v-form v-model="valid" ref="form">
              <v-text-field
                v-model="editedItem.name"
                :rules="[rules.required,rules.min]"
                prepend-icon="mdi-account-circle-outline"
                label="Username"
              />
              <v-text-field
                label="E-mail"
                name="login"
                v-model="editedItem.email"
                :success-messages="email_success"
                :error-messages="email_error"
                @blur="checkEmail"
                :rules="[rules.required,rules.validEmail]"
                required
                prepend-icon="mdi-account-circle-outline"
                type="text"
              />
              <v-file-input
                v-model="editedItem.thumbnail"
                label="Select Photo"
                placeholder="Upload Avatar"
                accept="image/jpg, image/png, image/jpeg"
                @change="uploadPhoto"
              />
              <v-text-field
                id="password"
                label="Previous Password"
                v-model="editedItem.password"
                name="password"
                @blur="password_error_reset"
                :error-messages="password_error"
                prepend-icon="mdi-account-lock-outline"
                type="password"
                required
              />
              <v-text-field
                v-if="editedItem.password != null"
                id="new_password"
                label="New Password"
                name="new_password"
                :rules="[rules.required]"
                v-model="editedItem.new_password"
                prepend-icon="mdi-account-lock-outline"
                type="password"
              />
              <v-select
                v-model="editedItem.role"
                :rules="[rules.required]"
                :items="roles"
                label="Role"
                prepend-icon="mdi-account-badge-outline"
              />
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-spacer />
            <v-btn color="primary" block :disabled="!valid" @click="update">update</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
    <v-snackbar v-model="snackbar">
      {{text}}
      <v-btn color="pink" text @click="snackbar = false">Close</v-btn>
    </v-snackbar>
  </v-container>
</template>

<script>
export default {
  data: () => ({
    text: "",
    snackbar: false,
    valid: true,
    password_error: "",
    email_success: "",
    email_error: "",
    roles: [],
    rules: {
      required: v => !!v || "This field is required",
      min: v => v.length >= 5 || "Minimun 5 characters required",
      validEmail: v => /.+@.+\..+/.test(v) || "E-mail must be valid"
    },
    editedItem: {
      id: "",
      name: "",
      email: "",
      password: "",
      retypepassword: "",
      role: "",
      photo: "",
      thumbnail: null
    }
  }),
  created() {
    this.initialize();
  },
  methods: {
    password_error_reset() {
      this.password_error = "";
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
    initialize() {
      axios
        .get("api/settings")
        .then(res => {
          this.editedItem = res.data.user;
          this.roles = res.data.roles;
        })
        .catch(err => console.dir(err.response));
    },
    uploadPhoto() {
      if (this.editedItem.thumbnail != null) {
        let formData = new FormData();
        formData.append(
          "photo",
          this.editedItem.thumbnail,
          this.editedItem.thumbnail.name
        );
        formData.append("user", this.editedItem.id);
        axios
          .post("api/user/photo", formData)
          .then(res => {
            this.editedItem.photo = res.data.user.photo;
            this.editedItem.thumbnail = null;
          })
          .catch(err => {
            console.error(err.response);
          });
      }
    },
    update() {
      axios
        .put("api/updatesettings/" + this.editedItem.id, this.editedItem)
        .then(res => {
          this.text = "User details successfully updated";
          this.snackbar = true;
          this.editedItem = res.data.user;
        })
        .catch(err => {
          console.log(err.response);
          if (err.response.data[0] == "message") {
            this.password_error = err.response.data[1];
          }
        });
    },
    resetValidation() {
      this.$refs.form.resetValidation();
    }
  }
};
</script>

<style>
</style>