<template>
  <div class="admin pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Admin Users List</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search User"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
        <button-dialog icon="mdi-plus" label="New User" width="600" @clearFilters="clearFilters">
          <admin-form :update="false"></admin-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="adminUsers"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingAdminUsers"
        loading-text="Loading Admin Users"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Accounts: {{ adminUsers.length }}</p>
          </v-toolbar>
        </template>
        <!-- <template v-slot:[`item.role`]="{ item }">
          <v-select :items="adminRoles" item-text="text" item-value="value" dense v-model="item.role" @change="updateAdminRole(item.id, item.role)"></v-select>
        </template> -->
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="adminStatus" dense v-model="item.status" @change="updateAdminStatus(item)"></v-select>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Edit"  style="z-index:1;">
            <admin-form :update="true" :admin-to-update="item"></admin-form>
          </table-action-dialog>
          <v-tooltip bottom style="z-index:2;">
            <template v-slot:activator="{ on }">
              <v-btn icon v-on="on" :to="`admin/logs/${item.id}`" target="_blank">
                <v-icon small>mdi-format-list-bulleted</v-icon>
              </v-btn>
            </template>
            <span class="caption">Activity Log</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import bus from '../../../../eventBus'

export default {
  name: "Admin",
  components: {
    ButtonDialog: () => import("../../component/ButtonDialog"),
    TableActionDialog: () => import("../../component/TableActionDialog"),
    AdminForm: () => import("../../components/forms/AdminForm")
  },
  data: () => ({
    headers: [
      { text: "USERNAME", value: "email", width: "15%" },
      { text: "FULL NAME", value: "name", width: "15%" },
      { text: "ROLE", value: "role" },
      { text: "STATUS", value: "status" },
      { text: "CREATED DATE", value: "created_at", width: "15%" },
      { text: "LAST ACCESS DATE", value: "last_access_date", width: "15%" },
      {
        text: "OPTIONS",
        value: "actions",
        width: "10%",
        align: "center",
        sortable: false
      }
    ],
    search: "",
    page: null
  }),
  computed: {
    ...mapState("admin", ["adminUsers", "adminStatus", "isLoadingAdminUsers"])
  },
  mounted() {
    this.getAdminUsers()
  },
  methods: {
    ...mapMutations("admin", { setAdminUsers: "SET_ADMIN_USERS" }),
    ...mapActions("admin", ["getAdminUsers", "manageAdminUser"]),
    updateAdminRole(id, role) {
      this.$store.commit('admin/UPDATE_ADMIN_ROLE', { id, role })
    },
    async updateAdminStatus(adminUser) {
      try {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Updating admin user status..."
          });
          await this.manageAdminUser(adminUser)
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: "Admin status updated."
          });
        } catch(err) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "error",
            text: err.response.data.message
          });
        }
    },
    clearFilters() {
      this.search = ""
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setAdminUsers([])
    next()
  }
};
</script>

<style>
.admin p {
  margin-bottom: 0;
}

.admin .v-toolbar__content {
  padding: 16px;
}
</style>
