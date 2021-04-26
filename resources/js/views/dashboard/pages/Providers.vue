<template>
  <div class="providers pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Provider List</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Providers"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
        <button-dialog icon="mdi-plus" label="New Bookmaker" width="600" @clearFilters="clearFilters">
          <provider-form :update="false" :currencies="currencies"></provider-form>
        </button-dialog>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="sortedProviders"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingProviders"
        loading-text="Loading Provider Accounts"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Providers: {{ sortedProviders.length }}</p>
          </v-toolbar>
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <v-select :items="providerStatus" dense v-model="item.is_enabled" @change="updateProviderStatus(item)"></v-select>
        </template>
        <template v-slot:[`item.updated_at`]="{ item }">
          <span v-if="!item.updated_at">-</span>
          <span v-else>{{item.updated_at}}</span>
        </template>
        <template v-slot:[`item.actions`]="{ item }" class="actions">
          <table-action-dialog icon="mdi-pencil" width="600" tooltipText="Edit">
            <provider-form :update="true" :provider-to-update="item" :currencies="currencies"></provider-form>
          </table-action-dialog>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from 'vuex'
import bus from '../../../eventBus'
import { handleAPIErrors } from '../../../helpers/errors'

export default {
  name: 'Providers',
  components: {
    ButtonDialog: () => import("../component/ButtonDialog"),
    TableActionDialog: () => import("../component/TableActionDialog"),
    ProviderForm: () => import("../components/forms/ProviderForm"),
  },
  data: () => ({
    headers: [
      { text: 'NAME', value: 'name' },
      { text: 'ALIAS', value: 'alias' },
      { text: 'STATUS', value: 'status', width: '15%' },
      { text: 'CREATED', value: 'created_at' },
      { text: 'MODIFIED', value: 'updated_at' },
      { text: '', value: 'actions' }
    ],
    search: '',
    page: null
  }),
  computed:{
    ...mapState('providers', ['isLoadingProviders', 'providerStatus']),
    ...mapState('currencies', ['currencies']),
    ...mapGetters('providers', ['sortedProviders']),
  },
  mounted() {
    this.getProviders()
    this.getCurrencies()
  },
  methods: {
    ...mapMutations('providers', { setProviders: 'SET_PROVIDERS' }),
    ...mapActions('providers', ['getProviders', 'updateProvider']),
    ...mapActions('currencies', ['getCurrencies']),
    async updateProviderStatus(provider) {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: "Updating provider status..."
        });
        let response = await this.updateProvider(provider)
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: response
        });
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }
    },
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setProviders([])
    next()
  }
}
</script>

<style>
.providers p {
  margin-bottom: 0;
}

.providers .v-toolbar__content {
  padding: 16px;
}
</style>