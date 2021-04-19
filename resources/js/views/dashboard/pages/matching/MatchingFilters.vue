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
          <v-select
            :items="primaryProviderLeagues"
            item-text="name"
            item-value="id"
            v-model="leagueId"
            label="Select League"
            outlined
            dense
            background-color="#fff"
            class="filterInput leagueDropdown"
            :disabled="!filters.league"
          ></v-select>
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
import { mapState, mapMutations } from 'vuex'
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
        schedule: null,
        inplay: null,
        today: null,
        early: null
      },
      leagueId: null
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['primaryProviderLeagues', 'matchingFilters'])
  },
  mounted() {
    this.loadFilters()
  },
  methods: {
    ...mapMutations('masterlistMatching', { setFilter: 'SET_FILTER' }),
    loadFilters() {
      Object.keys(this.matchingFilters[this.type]).map(key => {
        let data = Cookies.get(key) ? JSON.parse(Cookies.get(key)) : true
        this.filters[key] = data
        this.setFilter({ type: this.type, filter: key, data })
      })
    },
    updateFilter(filter, data) {
      this.setFilter({ type: this.type, filter, data })
      Cookies.set(filter, data, { expires: 3650 })
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