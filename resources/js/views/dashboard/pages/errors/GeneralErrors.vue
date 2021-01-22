<template>
  <div class="generalErrors pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Manage General Errors</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Errors"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
        <button-dialog icon="mdi-plus" label="New Error" width="600" @clearFilters="clearFilters">
          <general-error-form :update="false"></general-error-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="sortedGeneralErrors"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingGeneralErrors"
        loading-text="Loading General Errors"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Errors: {{ sortedGeneralErrors.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Edit">
            <general-error-form :update="true" :error-to-update="item"></general-error-form>
          </table-action-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from 'vuex'

export default {
  name: 'GeneralErrors',
  components: {
    ButtonDialog: () => import("../../component/ButtonDialog"),
    TableActionDialog: () => import("../../component/TableActionDialog"),
    GeneralErrorForm: () => import("../../components/forms/GeneralErrorForm")
  },
  data: () => ({
    headers: [
      { text: 'ERROR MESSAGES', value: 'error' },
      { text: '', value: 'actions' }
    ],
    search: '',
    page: null
  }),
  computed:{
    ...mapState('generalErrors', ['isLoadingGeneralErrors']),
    ...mapGetters('generalErrors', ['sortedGeneralErrors']),
  },
  mounted() {
    this.getGeneralErrors()
  },
  methods: {
    ...mapMutations('generalErrors', { setGeneralErrors: 'SET_GENERAL_ERRORS' }),
    ...mapActions('generalErrors', ['getGeneralErrors']),
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setGeneralErrors([])
    next()
  }
}
</script>

<style>
.generalErrors p {
  margin-bottom: 0;
}

.generalErrors .v-toolbar__content {
  padding: 16px;
}
</style>