<template>
  <div class="adminLogs pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Activity Log"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="activityLog"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingActivityLog"
        loading-text="Loading activity log"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Activity Log for: {{adminUser.email}}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.description`]="{ item }">
          <log-details-dialog 
            v-if="item.old_data || item.new_data"
            :title="`Log Details ${item.created_at}`"
            :description="item.description"
            :log="item"
          >
          </log-details-dialog>
          <span v-else>{{item.description}}</span>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { getToken } from '../../../../helpers/token'
import { mapActions } from 'vuex'
import bus from '../../../../eventBus'

export default {
  name: 'AdminLogs',
  components: {
    LogDetailsDialog: () => import("../../component/LogDetailsDialog")
  },
  data:() => ({
    headers: [
      { text: 'MODULE', value: 'module' },
      { text: 'ACTION', value: 'action' },
      { text: 'DESCRIPTION', value: 'description' },
      { text: 'IP ADDRESS', value: 'ip_address' },
      { text: 'CREATED DATE', value: 'created_at' },
    ],
    search: '',
    adminUser: '',
    activityLog: [],
    isLoadingActivityLog: false
  }),
  mounted() {
    this.getAdminUser()
    this.getAdminActivityLog()
  },
  methods: {
    ...mapActions('auth', ['logoutOnError']),
    getAdminUser() {
      axios.get(`admin-user/${this.$route.params.id}`, { headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.adminUser = response.data
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    },
    getAdminActivityLog() {
      this.isLoadingActivityLog = true
      axios.get('admin-users/logs', { params: { id: this.$route.params.id }, headers: { 'Authorization': `Bearer ${getToken()}` } })
      .then(response => {
        this.activityLog = response.data
        this.isLoadingActivityLog = false
      })
      .catch(err => {
        this.logoutOnError(err.response.status)
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.message
        });
      })
    }
  }
}
</script>

<style>
.adminLogs p {
  margin-bottom: 0;
}

.adminLogs .v-toolbar__content {
  padding: 16px;
}
</style>
