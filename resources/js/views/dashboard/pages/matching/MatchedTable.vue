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
      <v-row>
        <v-switch
          :input-value="item.is_priority"
          @change="setMasterLeaguePriority({ masterLeagueId: item.master_league_id, isPriority: $event })"
          class="ml-2"
          color="primary"
        />
        <div class="ma-2">
          <div class="ma-2" v-for="league in item.leagues" :key="league.id">
            <span class="badge unmatchBtn matched mr-4" :class="[`${league.provider.toLowerCase()}`]"  @click="confirmUnmatch(item.leagues)">
              {{league.provider}} <v-icon v-if="nonPrimaryProviders.includes(league.provider)" color="#ffffff" small>mdi-close</v-icon>
            </span> 
            {{league.name}}
          </div>
        </div>
      </v-row>
    </template>
    <!-- <template v-slot:[`item.data-table-expand`]="{ expand, isExpanded }">
      <v-btn @click="expand(!isExpanded)" small dark class="seeEvents success text-capitalize">See Events</v-btn>
    </template>
    <template v-slot:expanded-item="{ headers }">
      <td :colspan="headers.length">
      </td>
    </template> -->
  </v-data-table>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../../eventBus'

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
      primaryProvider: null,
      secondaryProvider: null
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['matchedData', 'isLoadingMatchedData', 'totalMatchedData']),
    ...mapState('providers', ['providers']),
    nonPrimaryProviders() {
      return this.providers.map(provider => provider.alias)
    }
  },
  watch: {
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : null,
          sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
        }
        this.setTableParams({ type: 'matchedDataParams', data: params })
        this.getMatchedLeagues()
      }
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setTableParams: 'SET_TABLE_PARAMS', setUnmatchingData: 'SET_UNMATCHING_DATA' }),
    ...mapActions('masterlistMatching', ['getMatchedLeagues', 'setMasterLeaguePriority']),
    confirmUnmatch(leagues) {
      this.primaryProvider = leagues.filter(league => !this.nonPrimaryProviders.includes(league.provider))[0]
      this.secondaryProvider = leagues.filter(league => this.nonPrimaryProviders.includes(league.provider))[0]
      this.setUnmatchingData({ data_id: this.secondaryProvider.id, provider_id: this.secondaryProvider.provider_id, sport_id: this.secondaryProvider.sport_id })
      bus.$emit("OPEN_MATCHING_DIALOG", { unmatch: this.secondaryProvider, primaryProvider: this.primaryProvider, confirmMessage: `Confirm Unmatching of ${this.type}`, matchingType: 'unmatch' })
    }
  }

}
</script>

<style>
  .seeEvents .v-btn__content, .unmatchBtn > .v-icon {
    font-size: 12px !important;
  }

  .unmatchBtn {
    cursor: pointer;
  }
</style>