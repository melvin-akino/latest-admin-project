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
        :items="activityLogs"
        :search="search"
        :items-per-page="10"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Activity Log for: {{adminEmail}}</p>
          </v-toolbar>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  name: 'AdminLogs',
  data:() => ({
    headers: [
      { text: 'MODULE', value: 'module' },
      { text: 'ACTION', value: 'action' },
      { text: 'DESCRIPTION', value: 'description' },
      { text: 'IP ADDRESS', value: 'ip_address' },
      { text: 'CREATED DATE', value: 'created_date' },
    ],
    search: '',
    adminEmail: '',
    activityLogs: []
  }),
  computed: {
    ...mapState('admin', ['admin'])
  },
  mounted() {
    this.getAdminDetails()
  },
  methods: {
    getAdminDetails() {
      let admin = this.admin.filter(admin => admin.id == this.$route.params.id)[0]
      this.adminEmail = admin.email
      this.activityLogs = admin.activity_logs
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
