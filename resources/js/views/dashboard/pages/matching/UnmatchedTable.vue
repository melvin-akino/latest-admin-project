<template>
  <v-data-table
    :headers="headers"
    :items="unmatchedData"
    item-key="id"
    :server-items-length="totalUnmatchedData"
    :options.sync="options"
    show-expand
    :loading="isLoadingUnmatchedData"
    :loading-text="`Loading ${type}`"
  >
    <template v-slot:top>
      <div class="matchingTableHeader">
        <p class="text-capitalize font-weight-medium">Unmatched {{type}}</p>
        <v-text-field
          v-model="searchKey"
          append-icon="mdi-magnify"
          label="Search"
          hide-details
          dense
          class="subtitle-1 unmatchedSearch"
          @keyup="search"
        ></v-text-field>
      </div>
    </template>
    <template v-slot:[`item.data`]="{ item }">
      <div class="leagueData" v-if="type=='leagues'">
        {{item.name}} <span class="badge" :class="[`${item.provider.toLowerCase()}`]">{{item.provider}}</span>
      </div>
    </template>
    <template v-slot:expanded-item="{ headers }" v-if="type=='leagues'">
      <td :colspan="headers.length">
        <!-- events here -->
      </td>
    </template>
  </v-data-table>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  props: ['type'],
  name: 'UnmatchedTable',
  data() {
    return {
      headers: [
        { text: 'Sort', value: 'data' },
        { text: '', value: 'data-table-expand' }
      ],
      options: {},
      searchKey: ''
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['unmatchedData', 'isLoadingUnmatchedData', 'totalUnmatchedData'])
  },
  watch: {
    options(value) {
      let params = {
        page: value.page,
        limit: value.itemsPerPage != -1 ? value.itemsPerPage : null,
        sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
        searchKey: value.hasOwnProperty('searchKey') ? value.searchKey : ''
      }
      this.getUnmatchedData(params)
    }
  },
  methods: {
    ...mapActions('masterlistMatching', ['getUnmatchedLeagues']),
    getUnmatchedData(params) {
      if(this.type == 'leagues') {
        this.getUnmatchedLeagues(params)
      }
    },
    search() {
      if(this.searchKey) {
        this.$set(this.options, 'searchKey', this.searchKey)
      } else {
        this.$delete(this.options, 'searchKey')
      }
      this.options.page = 1
    }
  }
}
</script>

<style>
  .unmatchedSearch {
    padding-top: 0;
    max-width: 240px;
  }
</style>