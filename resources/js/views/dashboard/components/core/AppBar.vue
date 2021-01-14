<template>
  <v-app-bar id="app-bar" app color="primary" flat height="75" dark>
    <v-btn
      class="mr-3"
      icon
      small
      @click="setDrawer(!drawer)"
      dark
    >
      <v-icon v-if="value">
        mdi-view-quilt
      </v-icon>

      <v-icon v-else>
        mdi-dots-vertical
      </v-icon>
    </v-btn>

    <v-toolbar-title
      class="hidden-sm-and-down font-weight-regular"
      v-text="$route.name"
    />

    <v-spacer />

    <div class="mx-3" />

    <!-- <v-btn class="ml-2" min-width="0" text to="/pages/user">
      <v-icon>mdi-account</v-icon>
    </v-btn> -->
    <v-btn class="mr-3" icon small title="Logout" @click="logout">
      <v-icon>mdi-logout</v-icon>
    </v-btn>
  </v-app-bar>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import Cookies from 'js-cookie'
import bus from '../../../../eventBus'

export default {
  name: "DashboardCoreAppBar",
  props: ["value"],
  computed: {
    ...mapState(["drawer"])
  },

  methods: {
    ...mapMutations({
      setDrawer: "SET_DRAWER"
    }),
    ...mapActions('auth', { adminLogout: 'logout' }),
    async logout() {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Logging out..."
        });
        let response = await this.adminLogout()
        Cookies.remove('access_token')
        Cookies.remove('wallet_token')
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: response
        });
        this.$router.push('/login')
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      }
    }
  }
};
</script>
