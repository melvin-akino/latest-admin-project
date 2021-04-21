<template>
  <v-data-table
    :headers="headers"
    :items="type=='leagues' ? primaryProviderData : primaryProviderGroupedHourly"
    hide-default-header
    :hide-default-footer="type=='leagues' ? true : false"
    :disable-pagination="type=='leagues' ? true : false"
    :group-by="type=='events' ? 'hourly_schedule' : []"
    :server-items-length="type=='events' ? totalPrimaryProviderData : -1"
    :options.sync="options"
    :loading="type=='events' ? isLoadingPrimaryProviderData : false"
    :loading-text="`Loading ${type}`"
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
          @change="getPrimaryProviderEventsByLeague({ leagueId, paginated: false })"
        ></v-autocomplete>
      </div> 
    </template>
    <template v-slot:body="{ items }" v-if="type=='leagues'">
      <tbody :class="{ 'multiple': items.length > 4 }">
        <tr v-for="item in items" :key="item.id">
          <td>
            <div class="px-4 py-2 event">
              <p>event id: {{item.event_identifier}}</p>
              <p>home: {{item.team_home_name}}</p>
              <p>away: {{item.team_away_name}}</p>
              <p>ref schedule: {{item.ref_schedule}}</p>
            </div>
          </td>
        </tr>
      </tbody>
    </template>
    <template v-slot:[`group.header`]="{ headers, toggle, group }" v-if="type=='events'">
      <td :colspan="headers.length" @click="toggle" id="hourlyGroupHeader">
        <div class="px-4 py-2">{{group}}</div>
      </td>
    </template>
    <template v-slot:[`item.data`]="{ item }"  v-if="type=='events'">
      <div class="px-4 py-2 event">
        <p>event id: {{item.event_identifier}}</p>
        <p>home: {{item.team_home_name}}</p>
        <p>away: {{item.team_away_name}}</p>
        <p>ref schedule: {{item.ref_schedule}}</p>
      </div>
    </template>
    <template v-slot:footer v-if="type=='leagues'">
      <div class="matchBtn">
        <v-btn small dark class="my-4 success text-capitalize" :class="{ 'match': !matchId || !primaryProviderId }" :disabled="!matchId || !primaryProviderId">Match</v-btn>
        <confirm-dialog
          :title="`Confirm Matching of ${type}`"
          activator=".match"
          @confirm="match"
        >
          <div class="matchSummary" v-if="unmatch && primaryProvider">
            <p>league id: <span>{{unmatch.id}}</span> >> <span>{{primaryProvider.id}}</span></p>
            <p>name: <span>{{unmatch.name}}</span> >> <span>{{primaryProvider.name}}</span></p>
          </div>
        </confirm-dialog>
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
  components: {
    ConfirmDialog: () => import('../../component/ConfirmDialog')
  },
  data() {
    return {
      headers: [
        { text: 'Sort', value: 'data' }
      ],
      options: {},
      leagueId: null
    }
  },  
  computed: {
    ...mapState('masterlistMatching', ['primaryProviderLeagues', 'primaryProviderData', 'totalPrimaryProviderData', 'isLoadingPrimaryProviderData',  'unmatchedData', 'primaryProviderId', 'matchId']),
    unmatch() {
      if(this.matchId) {
        return this.unmatchedData.filter(data => data.id == this.matchId)[0]
      }
    },
    primaryProvider() {
      if(this.primaryProviderId) {
        return this.primaryProviderLeagues.filter(data => data.id == this.primaryProviderId)[0]
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
    leagueId(value) {
      this.setPrimaryProviderId(value)
      if(!value) {
        this.setPrimaryProviderData([])
      }
    },
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : null,
          sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
          paginated: true
        }
        this.getPrimaryProviderEventsByLeague(params)
      }
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setPrimaryProviderId: 'SET_PRIMARY_PROVIDER_ID', setPrimaryProviderData: 'SET_PRIMARY_PROVIDER_DATA' }),
    ...mapActions('masterlistMatching', ['getPrimaryProviderEventsByLeague', 'matchLeague']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG")
    },
    async match() {
      bus.$emit("SHOW_SNACKBAR", {
        color: "success",
        text: 'Matching data...'
      });
      if(this.type=='leagues') {
        await this.matchLeague()
      }
      this.closeDialog()
      this.leagueId = null
      this.setPrimaryProviderData([])
      bus.$emit("SHOW_SNACKBAR", {
        color: "success",
        text: 'Matched data succesfully!'
      });
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

  .matchSummary p {
    margin: 0;
    font-size: 14px !important;
  }

  .matchSummary span {
    font-weight: 600;
  }

  #hourlyGroupHeader {
    color: #ffffff !important;
    background-color: #e9954b;
    cursor: pointer;
  }

</style>