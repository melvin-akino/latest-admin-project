<template>
  <div class="providerErrors pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Manage Provider Errors</p>
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
          <provider-error-form :update="false" :general-errors="generalErrors"></provider-error-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="sortedProviderErrors"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingProviderErrors"
        loading-text="Loading Provider Errors"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Errors: {{ sortedProviderErrors.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Edit">
            <provider-error-form :update="true" :error-to-update="item" :general-errors="generalErrors"></provider-error-form>
          </table-action-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from 'vuex'

export default {
  name: 'ProviderErrors',
  components: {
    ButtonDialog: () => import("../../component/ButtonDialog"),
    TableActionDialog: () => import("../../component/TableActionDialog"),
    ProviderErrorForm: () => import("../../components/forms/ProviderErrorForm")
  },
  data: () => ({
    headers: [
      { text: 'PROVIDER MESSAGE', value: 'message' },
      { text: 'ERROR MESSAGE', value: 'error' },
      { text: 'ACTIONS', value: 'actions' },
    ],
    search: '',
    page: null
  }),
  computed: {
    ...mapState('providerErrors', ['isLoadingProviderErrors']),
    ...mapState('generalErrors', ['generalErrors']),
    ...mapGetters('providerErrors', ['sortedProviderErrors'])
  },
  mounted() {
    this.getProviderErrors()
    this.getGeneralErrors()
  },
  methods: {
    ...mapMutations('providerErrors', { setProviderErrors: 'SET_PROVIDER_ERRORS'  }),
    ...mapMutations('generalErrors', { setGeneralErrors: 'SET_GENERAL_ERRORS' }),
    ...mapActions('providerErrors', ['getProviderErrors']),
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
    this.setProviderErrors([])
    this.setGeneralErrors([])
    next()
  }
}
</script>

<style>
.providerErrors p {
  margin-bottom: 0;
}

.providerErrors .v-toolbar__content {
  padding: 16px;
}
</style>