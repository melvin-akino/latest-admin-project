<template>
  <div class="matchingFilters">
    <span>Filters</span>
    <template v-if="type=='leagues'">
      <div class="filters leagueMatchingFilters">
        <v-checkbox
          v-model="filters.matched"
          label="Show Matched"
          class="filterInput"
          :disabled="!filters.unmatched"
          @change="updateFilter('matched', filters.matched)"
        ></v-checkbox>
        <v-checkbox
          v-model="filters.unmatched"
          label="Show Unmatched"
          class="filterInput"
          :disabled="!filters.matched"
          @change="updateFilter('unmatched', filters.unmatched)"
        ></v-checkbox>
      </div>
    </template>
    <template v-else>
      <div class="filters eventMatchingFilters">
        <v-checkbox
          v-model="filters.league"
          label="By League Name"
          class="filterInput"
          @change="updateFilter('league', filters.league)"
        ></v-checkbox>
        <div class="subFilter mb-2">
          <v-autocomplete
            :items="primaryProviderLeagues"
            item-text="name"
            item-value="id"
            v-model="filters.leagueId"
            label="Select League"
            outlined
            dense
            background-color="#fff"
            class="filterInput leagueDropdown"
            :disabled="!filters.league"
            @change="getEvents"
          ></v-autocomplete>
        </div>
        <v-checkbox
          v-model="filters.schedule"
          label="By Schedule"
          class="filterInput"
          @change="updateFilter('schedule', filters.schedule)"
        ></v-checkbox>
        <div class="filters subFilter schedules">
          <v-checkbox
            v-model="filters.inplay"
            label="Inplay"
            class="filterInput"
            @change="updateFilter('inplay', filters.inplay)"
            :disabled="!filters.schedule"
          ></v-checkbox>
          <v-checkbox
            v-model="filters.today"
            label="Today"
            class="filterInput"
            @change="updateFilter('today', filters.today)"
            :disabled="!filters.schedule"
          ></v-checkbox>
          <v-checkbox
            v-model="filters.early"
            label="Early"
            class="filterInput"
            @change="updateFilter('early', filters.early)"
            :disabled="!filters.schedule"
          ></v-checkbox>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import Cookies from 'js-cookie'

export default {
  props: ['type'],
  name: 'MatchingFilters',
  data() {
    return {
      filters: {
        matched: null,
        unmatched: null,
        league: null,
        leagueId: null,
        masterLeagueId: null,
        schedule: null,
        inplay: null,
        today: null,
        early: null
      },
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['primaryProviderLeagues', 'matchingFilters'])
  },
  watch: {
    primaryProviderLeagues: {
      deep: true,
      handler(value) {
        if(this.filters.league && value.length != 0) {
          let initialSelected = value[0]
          this.filters.leagueId = initialSelected.id
          this.filters.masterLeagueId = initialSelected.master_league_id
          this.setFilter({ type: this.type, filter: 'leagueId', data: this.filters.leagueId })
          this.setFilter({ type: this.type, filter: 'masterLeagueId', data: this.filters.masterLeagueId })
        }
      }
    },
    'matchingFilters.events.masterLeagueId'() {
      this.getUnmatchedEventsByMasterLeague()
    },
    'matchingFilters.events.leagueId'(value) {
      this.getPrimaryProviderEventsByLeague({ paginated: true })
    }
  },
  mounted() {
    this.loadFilters()
  },
  methods: {
    ...mapMutations('masterlistMatching', { setFilter: 'SET_FILTER' }),
    ...mapActions('masterlistMatching', ['getPrimaryProviderMatchedLeagues', 'getUnmatchedEventsByMasterLeague', 'getPrimaryProviderEventsByLeague']),
    loadFilters() {
      Object.keys(this.matchingFilters[this.type]).map(key => {
        let exemptedKeys = ['leagueId', 'masterLeagueId']
        if(!exemptedKeys.includes(key)) {
          let data = Cookies.get(key) ? JSON.parse(Cookies.get(key)) : true
          this.filters[key] = data
          this.setFilter({ type: this.type, filter: key, data })
        }
      })

      if(this.type == 'events') {
        this.getPrimaryProviderMatchedLeagues()
      }
    },
    updateFilter(filter, data) {
      this.setFilter({ type: this.type, filter, data })
      Cookies.set(filter, data, { expires: 3650 })
    },
    getEvents() {
      if(this.filters.league) {
        this.filters.masterLeagueId = this.primaryProviderLeagues.filter(league => league.id == this.filters.leagueId)[0].master_league_id
        this.setFilter({ type: this.type, filter: 'leagueId', data: this.filters.leagueId })
        this.setFilter({ type: this.type, filter: 'masterLeagueId', data: this.filters.masterLeagueId })
      } else {
        this.filters.leagueId = null
        this.filters.masterLeagueId = null
      }
    }
  }
}
</script>

<style>
  .matchingFilters, .filterInput .v-label {
    font-size: 13px !important;
    color: #000000 !important;
  }

  .filterInput {
    margin-top: 8px;
    margin-bottom: 8px;
    margin-right: 24px;
    height: 30px;
    font-size: 13px !important;
  }

  .filters {
    margin-bottom: 20px;
    display: flex;
  }

  .leagueMatchingFilters, .eventMatchingFilters {
    justify-content: flex-start;
    align-items: flex-start;
  }

  .eventMatchingFilters {
    flex-direction: column;
  }

  .subFilter {
    margin-left: 35px;
  }
  
  .schedules {
    margin-top: -12px;
  }

  .leagueDropdown {
    width: 320px;
    margin-bottom: 8px;
  }
</style>