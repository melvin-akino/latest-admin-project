<template>
  <v-data-table
    :headers="headers"
    :items="matchedData"
    item-key="master_league_id"
    :server-items-length="totalMatchedData"
    :options.sync="options"
    show-expand
    :loading="isLoadingMatchedData"
    :loading-text="`Loading ${type}`"
  >
    <template v-slot:top>
      <div class="matchingTableHeader">
        <p class="text-capitalize font-weight-medium">Matched {{type}}</p>
      </div>
    </template>
    <template v-slot:[`item.data`]="{ item }">
      <div class="ma-2" v-for="league in item.leagues" :key="league.id">
        <span class="badge" :class="[`${league.provider.toLowerCase()}`]">{{league.provider}}</span> {{league.name}} 
      </div>
    </template>
    <template v-slot:[`item.data-table-expand`]="{ expand, isExpanded }">
      <v-btn @click="expand(!isExpanded)" small dark class="success">See Events</v-btn>
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
  name: 'MatchedTable',
  data() {
    return {
      headers: [
        { text: 'Sort', value: 'data' },
        { text: '', value: 'data-table-expand' }
      ],
      options: {},
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['matchedData', 'isLoadingMatchedData', 'totalMatchedData'])
  },
  watch: {
    options(value) {
      let params = {
        page: value.page,
        limit: value.itemsPerPage != -1 ? value.itemsPerPage : null,
        sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
      }
      this.getMatchedLeagues(params)
    }
  },
  methods: {
    ...mapActions('masterlistMatching', ['getMatchedLeagues'])
  }

}
</script>

<style>

</style>