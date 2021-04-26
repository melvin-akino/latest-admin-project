<template>
  <v-data-table
    :headers="headers"
    :items="displayMatchedData"
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
    <template v-slot:[`item.data`]="{ item }">
      <v-row v-if="type=='leagues'">
        <v-switch
          :input-value="item.is_priority"
          @change="setMasterLeaguePriority({ masterLeagueId: item.master_league_id, isPriority: $event })"
          class="ml-2"
          color="primary"
        />
        <div class="ma-2">
          <div class="ma-2" v-for="league in item.leagues" :key="league.id">
            <span class="badge unmatchBtn matched mr-4" :class="[`${league.provider.toLowerCase()}`]"  @click="nonPrimaryProviders.includes(league.provider) && confirmUnmatch(item.leagues, league.id)">
              {{league.provider}} <v-icon v-if="nonPrimaryProviders.includes(league.provider)" color="#ffffff" small>mdi-close</v-icon>
            </span> 
            {{league.name}}
          </div>
        </div>
      </v-row>
      <v-row v-else>
        <div class="ma-2">{{item.master_league_name}}</div>
      </v-row>
    </template>
    <template v-slot:[`item.data-table-expand`]="{ item, expand, isExpanded }">
      <div class="mr-4">
        <v-btn v-if="type=='leagues'" @click="loadEventsMatching(item)" small dark class="seeEvents success text-capitalize">See Events</v-btn>
        <v-btn v-else @click="expand(!isExpanded)" small dark class="seeEvents success text-capitalize">See Events</v-btn>
      </div>
    </template>
    <template v-slot:expanded-item="{ headers, item }" v-if="type=='events'">
      <td :colspan="headers.length" v-if="item.events.length != 0">
        <div class="matchedEvents" v-for="(events, index) in item.groupedEvents" :key="index">
          <div class="matchedEvent ma-4" v-for="event in events" :key="event.id">
            <div class="matchedEventDetails provider mr-4">
              <span class="badge matched" :class="[`${event.provider.toLowerCase()}`]">{{event.provider}}</span>
            </div>
            <div class="matchedEventDetails details mr-4">
              <p>{{event.league_name}}</p>
              <p>{{event.ref_schedule}}</p>
              <p>{{event.team_home_name}} vs {{event.team_away_name}}</p>
            </div>
            <div class="matchedEventDetails button">
              <v-btn v-if="nonPrimaryProviders.includes(event.provider) && events.length > 1" outlined color="error" class="unmatchBtn text-capitalize" small @click="confirmUnmatch(events, event.id)">Unmatch</v-btn>
            </div>
          </div>
        </div>
      </td>
      <td :colspan="headers.length" class="noEventsExpanded" v-else>
        <div class="px-4 py-2">No active events available for this league</div>
      </td>
    </template>
  </v-data-table>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../../eventBus'
import _ from 'lodash'

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
      secondaryProvider: null,
      searchKey: ''
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['matchedData', 'isLoadingMatchedData', 'totalMatchedData']),
    ...mapState('providers', ['providers']),
    nonPrimaryProviders() {
      return this.providers.map(provider => provider.alias)
    },
    displayMatchedData() {
      return this.matchedData.map(data => {
        this.$set(data, 'groupedEvents', _.groupBy(data.events, 'master_event_id'))
        return { ...data }
      })
    },
  },
  watch: {
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : this.totalMatchedData,
          sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
          searchKey: value.searchKey
        }
        this.setTableParams({ type: 'matchedDataParams', data: params })
        this.getMatchedData()
      }
    },
    searchKey(value) {
      this.$set(this.options, 'searchKey', value)
      this.options.page = 1
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setTableParams: 'SET_TABLE_PARAMS', setUnmatchingData: 'SET_UNMATCHING_DATA', setFilter: 'SET_FILTER' }),
    ...mapActions('masterlistMatching', ['getMatchedLeagues', 'getMatchedEvents', 'setMasterLeaguePriority']),
    confirmUnmatch(data, id) {
      this.primaryProvider = data.filter(item => !this.nonPrimaryProviders.includes(item.provider))[0]
      this.secondaryProvider = data.filter(item => item.id == id)[0]
      this.setUnmatchingData({ data_id: this.secondaryProvider.id, provider_id: this.secondaryProvider.provider_id, sport_id: this.secondaryProvider.sport_id })
      bus.$emit("OPEN_MATCHING_DIALOG", { secondaryProvider: this.secondaryProvider, primaryProvider: this.primaryProvider, confirmMessage: `Confirm Unmatching of ${this.type}`, matchingType: 'unmatch' })
    },
    getMatchedData() {
      if(this.type=='leagues') {
        this.getMatchedLeagues()
      } else {
        this.getMatchedEvents()
      }
    },
    loadEventsMatching(data) {
      let masterLeagueId =  data.master_league_id 
      let leagueId = data.leagues.length != 0 ? data.leagues[0].id : null
      this.$router.push({ path: 'events', query: { masterLeagueId, leagueId } })
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

  .matchedEvents, .matchedEvent {
    display: flex;
  }

  .matchedEvent {
    align-items: center;
  }

  .matchedEventDetails p {
    margin: 0;
  }

  .matchedEventDetails.details {
    width: 200px;
  }

  .matchedEventDetails > .unmatchBtn {
    width: 84px;
  }
</style>