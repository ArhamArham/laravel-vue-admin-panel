<template>
  <v-app id="inspire">
    <v-content>
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="4">
            <v-progress-linear
              :active="loading"
              :indeterminate="loading"
              color="primary"
              position="absolute"
            ></v-progress-linear>
            <v-card class="elevation-12">
              <v-toolbar color="primary" dark flat>
                <v-toolbar-title>Login form</v-toolbar-title>
                <v-spacer />
              </v-toolbar>
              <v-card-text>
                <v-form v-model="valid">
                  <v-text-field
                    label="E-mail"
                    v-model="email"
                    name="login"
                    tabindex="1"
                    :rules="emailRules"
                    required
                    prepend-icon="mdi-account-circle-outline"
                    type="text"
                  />

                  <v-text-field
                    id="password"
                    label="Password"
                    v-model="password"
                    :rules="passwordRules"
                    tabindex="2"
                    name="password"
                    prepend-icon="mdi-account-lock-outline"
                    type="password"
                    required
                    v-on:keyup.enter="onEnter"
                  />
                </v-form>
              </v-card-text>

              <v-card-actions>
                <v-spacer />
                <v-btn :disabled="!valid" tabindex="3" color="primary" block @click="login">Login</v-btn>
              </v-card-actions>
            </v-card>
            <v-snackbar v-model="snackbar">
              {{ text }}
              <v-btn color="pink" text @click="snackbar = false">Close</v-btn>
            </v-snackbar>
          </v-col>
        </v-row>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      valid: true,
      email: "",
      emailRules: [
        v => !!v || "E-mail is required",
        v => /.+@.+\..+/.test(v) || "E-mail must be valid"
      ],
      password: "",
      passwordRules: [v => !!v || "Password is required"],
      loading: false,
      snackbar: false,
      text: ""
    };
  },
  created() {
    if (localStorage.getItem("theme") == "false") {
      this.$vuetify.theme.dark = false;
    }
    if (localStorage.getItem("theme") == "true") {
      this.$vuetify.theme.dark = true;
    }
  },
  methods: {
    onEnter: function() {
      this.login();
    },
    login: function() {
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
      axios
        .post("api/login", { email: this.email, password: this.password })
        .then(res => {
          if (res.data.isAdmin) {
            localStorage.setItem("token", res.data.token);
            localStorage.setItem("loggedIn", true);
            this.$router
              .push("/admin/index")
              .then(res => console.log("LoggedIn Successfully"))
              .catch(err => console.log(err));
          } else {
            this.text = "You need to logged in as an Administrator";
            this.snackbar = true;
          }
        })
        .catch(err => {
          this.text = err.response.data.status;
          this.snackbar = true;
        });
    }
  },
  mounted() {
    this.snackbar = localStorage.getItem("loggedOut") ? true : false;
    this.text = "You`r loggedOut";
    localStorage.removeItem("loggedOut");
  },
  props: {
    source: String
  }
};
</script>

<style>
</style>