<template>
  <div class="currencies pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Manage Currencies</p>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Currencies"
          hide-details
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="currencies"
        :search="search"
        :items-per-page="10"
        :loading="isLoadingCurrencies"
        loading-text="Loading Currencies"
        :page="page"
        @pagination="getPage"
      >
        <template v-slot:top>
          <v-toolbar flat color="primary" height="40px" dark>
            <p class="subtitle-1">Total Currencies: {{ currencies.length }}</p>
          </v-toolbar>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'

export default {
  name: 'Currencies',
  data: () => ({
    headers: [
      { text: 'NAME', value: 'name' },
      { text: 'ENABLED', value: 'is_enabled' },
      { text: 'CREATED DATE', value: 'created_at' },
    ],
    search: '',
    page: null
  }),
  computed:{
    ...mapState('wallet', ['currencies', 'isLoadingCurrencies']),
  },
  mounted() {
    this.getCurrencies()
  },
  methods: {
    ...mapMutations('wallet', { setCurrencies: 'SET_CURRENCIES' }),
    ...mapActions('wallet', ['getCurrencies']),
    clearFilters() {
      this.search = ''
      this.page = 1
    },
    getPage(pagination) {
      this.page = pagination.page
    }
  },
  beforeRouteLeave(to, from, next) {
    this.setCurrencies([])
    next()
  }
}
</script>

<style>
.currencies p {
  margin-bottom: 0;
}

.currencies .v-toolbar__content {
  padding: 16px;
}
</style>