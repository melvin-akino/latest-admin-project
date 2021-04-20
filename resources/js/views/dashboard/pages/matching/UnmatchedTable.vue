<template>
  <v-data-table
    :headers="headers"
    :items="unmatchedData"
    item-key="id"
    :server-items-length="totalUnmatchedData"
    :options.sync="options"
    show-expand
    single-expand
    :expanded.sync="expanded"
    @item-expanded="getEvents"
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
          @keyup="search"
        ></v-text-field>
      </div>
    </template>
    <template v-slot:item="{ headers, item, expand, isExpanded }">
      <tr class="leagueRow" :class="{ 'selected' : leagueId == item.id }" @click="expand(!isExpanded)" v-if="type=='leagues'">
        <td>
          <div class="leagueData">
            {{item.name}} <span class="ml-4 badge" :class="[`${item.provider.toLowerCase()}`]">{{item.provider}}</span>
          </div>
        </td>
        <td>
          <v-btn icon small>
            <v-icon small v-if="isExpanded">mdi-chevron-up</v-icon>
            <v-icon small v-else>mdi-chevron-down</v-icon>
          </v-btn>
        </td>
      </tr>
      <tr class="eventRow" v-else>
        <td :colspan="headers.length">
          <div class="px-4 py-2 event">
            <p>provider: {{item.provider}}</p>
            <p>event id: {{item.event_identifier}}</p>
            <p>league: {{item.league_name}}</p>
            <p>home: {{item.team_home_name}}</p>
            <p>away: {{item.team_away_name}}</p>
            <p>ref schedule: {{item.ref_schedule}}</p>
          </div>
        </td>
      </tr>
    </template>
    <template v-slot:expanded-item="{ headers, item }" v-if="type=='leagues'">
      <td :colspan="headers.length" class="expanded" v-if="item.hasOwnProperty('events') && item.events.length != 0">        
        <div class="events" :class="{ 'mutiple' : item.events.length > 1 }">
          <div class="px-4 py-2 event" v-for="event in item.events" :key="event.id">
            <p>event id: {{event.event_identifier}}</p>
            <p>home: {{event.team_home_name}}</p>
            <p>away: {{event.team_away_name}}</p>
            <p>ref schedule: {{event.ref_schedule}}</p>
          </div>
        </div>
      </td>
    </template>
  </v-data-table>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'

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
      expanded: []
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['unmatchedData', 'isLoadingUnmatchedData', 'totalUnmatchedData'])
  },
  watch: {
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : null,
          sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
          searchKey: value.hasOwnProperty('searchKey') ? value.searchKey : ''
        }
        this.getUnmatchedData(params)
        this.leagueId = null
      }
    },
    leagueId(value) {
      this.setMatchId(value)
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { removeUnmatchedEventsForLeague: 'REMOVE_UNMATCHED_EVENTS_FOR_LEAGUE', setMatchId: 'SET_MATCH_ID' }),
    ...mapActions('masterlistMatching', ['getUnmatchedLeagues', 'getPrimaryProviderMatchedLeagues', 'getUnmatchedEventsByLeague', 'getUnmatchedEventsByMasterLeague']),
    getUnmatchedData(params) {
      if(this.type == 'leagues') {
        this.getUnmatchedLeagues(params)
        this.getPrimaryProviderMatchedLeagues()
      } else {
        this.getUnmatchedEventsByMasterLeague(params)
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

  .selected {
  background-color: #cce2ff;
  }
</style>