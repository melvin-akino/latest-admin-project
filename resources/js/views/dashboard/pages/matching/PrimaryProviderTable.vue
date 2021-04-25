<template>
  <v-data-table
    :headers="headers"
    v-model="selected"
    :items="type=='leagues' ? primaryProviderData : primaryProviderGroupedHourly"
    hide-default-header
    :hide-default-footer="type=='leagues' ? true : false"
    :disable-pagination="type=='leagues' ? true : false"
    :group-by="type=='events' ? 'hourly_schedule' : []"
    :server-items-length="type=='events' ? totalPrimaryProviderData : -1"
    :options.sync="options"
    :loading="type=='events' ? isLoadingPrimaryProviderData : false"
    :loading-text="`Loading ${type}`"
    single-select
    @item-selected="selectEvent"
    class="primaryProviderTable"
  >
    <template v-slot:top>
      <div class="matchingTableHeader">
        <p class="text-capitalize font-weight-medium">Primary Provider {{type}}</p>
      </div>
    </template>
    <template v-slot:header v-if="type=='leagues'">
      <div class="mx-4 mt-4">
        <v-autocomplete
          :items="primaryProviderLeagues"
          item-text="name"
          item-value="id"
          v-model="leagueId"
          label="Select Primary Provider League"
          dense
          clearable
          class="primaryProviderDropdown"
          @change="getPrimaryProviderEventsByLeague({ leagueId, type })"
        ></v-autocomplete>
      </div> 
    </template>
    <template v-slot:body="{ headers, items }" v-if="type=='leagues'">
      <tbody :class="{ 'multiple': items.length > 4 }"  v-if="items.length != 0">
        <tr v-for="item in items" :key="item.id">
          <td>
            <div class="px-4 py-2 event">
              <p>event id: {{item.event_identifier}}</p>
              <p>home: {{item.team_home_name}}</p>
              <p>away: {{item.team_away_name}}</p>
              <p>game schedule: {{item.game_schedule}}</p>
              <p>ref schedule: {{item.ref_schedule}}</p>
            </div>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td :colspan="headers.length">
            <div class="px-4 py-2">No data available</div>
          </td>
        </tr>
      </tbody>
    </template>
    <template v-slot:[`group.header`]="{ headers, toggle, group }" v-if="type=='events'">
      <td :colspan="headers.length" @click="toggle" id="hourlyGroupHeader">
        <div class="px-4 py-2">{{group}}</div>
      </td>
    </template>
    <template v-slot:item="{ headers, item, select, isSelected }"  v-if="type=='events'">
      <tr :class="{ 'selected' : eventId == item.id, 'match' : eventId == item.id }" @click="select(!isSelected)">
        <td :colspan="headers.length">
          <div class="px-4 py-2 event">
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
    <template v-slot:footer v-if="type=='leagues'">
      <div class="matchBtn">
        <v-btn small dark class="my-4 success text-capitalize match" :disabled="!matchId || !primaryProviderId" @click="confirmMatching">Match</v-btn>
      </div>
    </template>
  </v-data-table>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../../eventBus'
import moment from 'moment'

export default {
  props: ['type'],
  name: 'PrimaryProviderTable',
  data() {
    return {
      headers: [
        { text: 'Sort', value: 'data' }
      ],
      options: {},
      leagueId: null,
      eventId: null,
      selected: []
    }
  },  
  computed: {
    ...mapState('masterlistMatching', ['primaryProviderLeagues', 'primaryProviderData', 'totalPrimaryProviderData', 'isLoadingPrimaryProviderData',  'unmatchedData', 'primaryProviderId', 'matchId', 'matchingFilters']),
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
    primaryProviderGroupedHourly() {
      let data = []
      if(this.type == 'events') {
        this.primaryProviderData.map(event => {
          let obj = { ...event }
          this.$set(obj, 'hourly_schedule', moment(event.ref_schedule).startOf('hour').format('YYYY-MM-DD HH:mm:ss'))
          data.push(obj)
        })
      }
      return data
    }
  },
  watch: {
    options: {
      deep: true,
      handler(value) {
        if(this.type=='events') {    
          let params = {
            page: value.page,
            limit: value.itemsPerPage != -1 ? value.itemsPerPage : this.totalPrimaryProviderData,
            sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
            paginated: true
          }      
          this.setTableParams({ type: 'primaryProviderDataParams', data: params })
          this.getPrimaryProviderData()
        }
      }
    },
    primaryProviderData: {
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
      this.setPrimaryProviderId(value)
      if(!value) {
        this.setPrimaryProviderData([])
      }
    },
    eventId(value) {
      this.setPrimaryProviderId(value)
      if(!value) {
        this.selected = []
      }
    },
    primaryProviderId(value) {
      if(value && this.matchId && this.type=='events') {
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
    ...mapMutations('masterlistMatching', { setPrimaryProviderId: 'SET_PRIMARY_PROVIDER_ID', setPrimaryProviderData: 'SET_PRIMARY_PROVIDER_DATA', setTableParams: 'SET_TABLE_PARAMS' }),
    ...mapActions('masterlistMatching', ['getPrimaryProviderMatchedLeagues', 'getPrimaryProviderEventsByLeague']),
    async getPrimaryProviderData() {
      await this.getPrimaryProviderMatchedLeagues()
      if(this.type=='events') {
        this.getPrimaryProviderEventsByLeague({ leagueId: this.matchingFilters.events.leagueId, type: this.type })
      }
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
  .primaryProviderTable > .v-data-table__wrapper table tr td {
    padding: 0 !important;
  }

  .primaryProviderDropdown, .primaryProviderDropdown .v-label {
    font-size: 13px !important;
  }

  .primaryProviderTable tbody.multiple {
    display: block;
    max-height: 374px;
    overflow-y: scroll;
  }

  .primaryProviderTable tbody.multiple tr {
    display: table;
    width: 100%;
    table-layout: fixed;
  }

  .matchBtn {
    display: flex;
    justify-content: center;
    border-top: solid rgba(0, 0, 0, 0.12) 1px;
  }

  .matchBtn > .theme--dark.v-btn.v-btn--disabled:not(.v-btn--flat):not(.v-btn--text):not(.v-btn--outlined) {
    background-color: #4caf50 !important;
  }

  .matchBtn .v-btn__content {
    font-size: 12px !important;
  }

  #hourlyGroupHeader {
    color: #ffffff !important;
    background-color: #e9954b;
    cursor: pointer;
  }

</style>