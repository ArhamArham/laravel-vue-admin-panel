<template>
  <v-app id="inspire">
    <v-navigation-drawer v-model="drawer" app clipped>
      <v-list dense>
        <v-list-item v-for="item in items" :key="item.text" link :to="item.action">
          <v-list-item-action>
            <v-icon>mdi-{{ item.icon }}</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>{{ item.text }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item class="mt-4" link>
          <v-list-item-action>
            <v-icon color="grey darken-1">mdi-account-circle-outline</v-icon>
          </v-list-item-action>
          <v-list-item-title class="grey--text text--darken-1">User Info</v-list-item-title>
        </v-list-item>
        <v-list>
          <v-list-item link>
            <v-list-item-avatar>
              <img :src="user.photo" :alt="user.photo" />
            </v-list-item-avatar>
            <v-list-item-title>{{user.name}}</v-list-item-title>
          </v-list-item>
        </v-list>
        <v-list-item class="mt-4" link to="/admin/settings">
          <v-list-item-action>
            <v-icon color="grey darken-1">mdi-plus-circle-outline</v-icon>
          </v-list-item-action>
          <v-list-item-title class="grey--text text--darken-1">Settings</v-list-item-title>
        </v-list-item>
        <v-switch color="info" v-model="theme" class="mx-2" label="Dark Theme"></v-switch>
        <v-list-item link @click="logout">
          <v-list-item-action>
            <v-icon color="grey darken-1">mdi-settings</v-icon>
          </v-list-item-action>
          <v-list-item-title class="grey--text text--darken-1">Logout</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app clipped-left color="primary" dense>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <router-link to="/admin/index">
        <v-icon class="mx-4" large>mdi-laravel</v-icon>
      </router-link>
      <v-toolbar-title class="mr-12 align-center">
        <span class="title">Vue Admin Panel</span>
      </v-toolbar-title>
    </v-app-bar>

    <v-content>
      <router-view></router-view>
      <v-snackbar v-model="snackbar">
        You'r logged in successfully
        <v-btn color="pink" text @click="snackbar = false">Close</v-btn>
      </v-snackbar>
    </v-content>
  </v-app>
</template>

<script>
export default {
  props: {
    source: String
  },
  data: () => ({
    user: [],
    theme: false,
    snackbar: false,
    drawer: null,
    items: [
      { icon: "account", text: "Users", action: "/admin/users" },
      { icon: "post-outline", text: "Products", action: "/admin/products" },
      {
        icon: "briefcase-edit-outline",
        text: "Categories",
        action: "/admin/categories"
      },
      { icon: "account-badge-outline", text: " Roles", action: "/admin/roles" }
    ]
  }),
  watch: {
    theme: function(old, newval) {
      localStorage.setItem("theme", old);
      this.$vuetify.theme.dark = old;
    }
  },
  created() {
    this.initialize();
    this.$vuetify.theme.dark = false;
  },
  mounted() {
    if (localStorage.getItem("theme") == "false") {
      this.theme = false;
    }
    if (localStorage.getItem("theme") == "true") {
      this.theme = true;
    }
    this.snackbar = localStorage.getItem("loggedIn") ? true : false;
    localStorage.removeItem("loggedIn");
  },
  methods: {
    initialize() {
      axios
        .get("api/admindetails")
        .then(res => {
          this.user = res.data.user;
        })
        .catch(err => {
          console.error(err);
        });
    },
    logout: function() {
      localStorage.removeItem("token");
      localStorage.setItem("loggedOut", true);
      this.$router
        .push("/login")
        .then(res => console.log("You`r LoggedOut"))
        .catch(err => console.log(err));
    }
  }
};
</script>

<style>
</style>