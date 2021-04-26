<template>
  <v-data-table
    :headers="headers"
    :items="unmatchedData"
    item-key="id"
    :hide-default-header="type=='events' ? true : false"
    :server-items-length="totalUnmatchedData"
    :options.sync="options"
    show-expand
    single-expand
    :expanded.sync="expanded"
    @item-expanded="getEvents"
    single-select
    @item-selected="selectEvent"
    v-model="selected"
    @pagination="clearSelected"
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
        ></v-text-field>
      </div>
    </template>
    <template v-slot:item="{ headers, item, expand, isExpanded, select, isSelected }">
      <tr class="leagueRow" :class="{ 'selected' : leagueId == item.id }" @click="expand(!isExpanded)" v-if="type=='leagues'">
        <td>
          <div class="leagueData">
            <span class="mr-4 badge" :class="[`${item.provider.toLowerCase()}`]">{{item.provider}}</span> {{item.name}}
          </div>
        </td>
        <td>
          <v-btn icon small>
            <v-icon small v-if="isExpanded">mdi-chevron-up</v-icon>
            <v-icon small v-else>mdi-chevron-down</v-icon>
          </v-btn>
        </td>
      </tr>
      <tr class="eventRow" :class="{ 'selected' : eventId == item.id }" v-else @click="select(!isSelected)">
        <td :colspan="headers.length">
          <div class="px-4 py-2 event">
            <p>provider: {{item.provider}}</p>
            <p>event id: {{item.event_identifier}}</p>
            <p>league: {{item.league_name}}</p>
            <p>home: {{item.team_home_name}}</p>
            <p>away: {{item.team_away_name}}</p>
            <p>game schedule: {{item.game_schedule}}</p>
            <p>ref schedule: {{item.ref_schedule}}</p>
          </div>
        </td>
      </tr>
    </template>
    <template v-slot:expanded-item="{ headers, item }" v-if="type=='leagues'">
      <tr v-if="item.hasOwnProperty('events')">
        <td :colspan="headers.length" class="expanded" v-if="item.events.length != 0">        
          <div class="events" :class="{ 'mutiple' : item.events.length > 1 }">
            <div class="px-4 py-2 event" v-for="event in item.events" :key="event.id">
              <p>event id: {{event.event_identifier}}</p>
              <p>home: {{event.team_home_name}}</p>
              <p>away: {{event.team_away_name}}</p>
              <p>game schedule: {{event.game_schedule}}</p>
              <p>ref schedule: {{event.ref_schedule}}</p>
            </div>
          </div>
        </td>
        <td :colspan="headers.length" class="noEventsExpanded" v-else>
          <div class="px-4 py-2">No active events available for this league</div>
        </td>
      </tr>
    </template>
  </v-data-table>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../../eventBus'

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
      searchKey: '',
      leagueId: null,
      eventId: null,
      expanded: [],
      selected: []
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['unmatchedData', 'isLoadingUnmatchedData', 'totalUnmatchedData', 'primaryProviderLeagues', 'primaryProviderData', 'primaryProviderId', 'matchId']),
    secondaryProvider() {
      if(this.matchId) {
        return this.unmatchedData.filter(data => data.id == this.matchId)[0]
      }
    },
    primaryProvider() {
      if(this.primaryProviderId) {
        if(this.type=='leagues') {
          return this.primaryProviderLeagues.filter(data => data.id == this.primaryProviderId)[0]
        } else {
          return this.primaryProviderData.filter(data => data.id == this.primaryProviderId)[0]
        }
      }
    },
  },
  watch: {
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : this.totalUnmatchedData,
          sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
          searchKey: value.searchKey
        }
        this.setTableParams({ type: 'unmatchedDataParams', data: params })
        this.getUnmatchedData()
        this.leagueId = null
      }
    },
    searchKey(value) {
      this.$set(this.options, 'searchKey', value)
      this.options.page = 1
    },
    unmatchedData: {
      deep: true,
      handler(value) {
        if(this.type=='events') {
          this.eventId = null
        }
        if(value.length == 0) {
          this.options.page = 1
        }
      }
    },
    leagueId(value) {
      this.setMatchId(value)
    },
    eventId(value) {
      this.setMatchId(value)
      if(!value) {
        this.selected = []
      }
    },
    matchId(value) {
      if(value && this.primaryProviderId && this.type=='events') {
        this.confirmMatching()
      } else {
        if(this.type=='leagues') {
          this.leagueId = value
        } else {
          this.eventId = value
        }
      }
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { removeUnmatchedEventsForLeague: 'REMOVE_UNMATCHED_EVENTS_FOR_LEAGUE', setMatchId: 'SET_MATCH_ID', setTableParams: 'SET_TABLE_PARAMS' }),
    ...mapActions('masterlistMatching', ['getUnmatchedLeagues', 'getPrimaryProviderMatchedLeagues', 'getUnmatchedEventsByLeague', 'getUnmatchedEventsByMasterLeague']),
    async getUnmatchedData() {
      await this.getPrimaryProviderMatchedLeagues()
      if(this.type == 'leagues') {
        this.getUnmatchedLeagues()
      } else {
        this.getUnmatchedEventsByMasterLeague()
      }
    },
    getEvents(data) {
      let { item, value } = data
      if(value) {
        this.getUnmatchedEventsByLeague({ leagueId: item.id, paginated: false })
        this.leagueId = item.id
      } else {
        this.removeUnmatchedEventsForLeague(item.id)
        this.leagueId = null
        this.expanded = []
      }
    },
    clearSelected() {
      this.expanded = []
      this.unmatchedData.map(data => {
        if(this.type=='leagues') {
          this.removeUnmatchedEventsForLeague(data.id)
        }
      })
    },
    selectEvent(data) {
      let { item, value } = data
      if(value) {
        this.eventId = item.id
      } else {
        this.eventId = null
      }
    },
    confirmMatching() {
      bus.$emit("OPEN_MATCHING_DIALOG", { secondaryProvider: this.secondaryProvider, primaryProvider: this.primaryProvider, confirmMessage: `Confirm Matching of ${this.type}`, matchingType: 'match' })
    }
  }
}
</script>

<style>
  .unmatchedSearch .v-label, .unmatchedSearch input {
    font-size: 13px !important;
  }

  .unmatchedSearch {
    padding-top: 0;
    max-width: 240px;
  }

  .expanded {
    padding: 0 !important;
  }

  .events {
    background-color: #cce2ff;
  }

  .events.multiple {
    height: 190px;
    overflow-y: scroll;
  }

  .leagueRow, .eventRow {
    cursor: pointer;
  }
</style>