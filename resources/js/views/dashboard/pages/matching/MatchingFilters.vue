<template>
  <div class="matchingFilters">
    <span>Filters</span>
    <template v-if="type=='leagues'">
      <div class="filters leagueMatchingFilters">
        <v-checkbox
          v-model="filters.matched"
          label="Show Matched"
          class="filterInput"
        ></v-checkbox>
        <v-checkbox
          v-model="filters.unmatched"
          label="Show Unmatched"
          class="filterInput"
        ></v-checkbox>
      </div>
    </template>
    <template v-else>
      <div class="filters eventMatchingFilters">
        <v-checkbox
          v-model="filters.league"
          label="By Leagues"
          class="filterInput"
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
          ></v-select>
        </div>
        <v-checkbox
          v-model="filters.schedule"
          label="By Schedule"
          class="filterInput"
        ></v-checkbox>
        <div class="filters subFilter schedules">
          <v-checkbox
            v-model="filters.inplay"
            label="Inplay"
            class="filterInput"
          ></v-checkbox>
          <v-checkbox
            v-model="filters.today"
            label="Today"
            class="filterInput"
          ></v-checkbox>
          <v-checkbox
            v-model="filters.early"
            label="Early"
            class="filterInput"
          ></v-checkbox>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['type'],
  name: 'MatchingFilters',
  data() {
    return {
      filters: {
        matched: true,
        unmatched: true,
        league: true,
        schedule: true,
        inplay: true,
        today: true,
        early: true
      },
      leagueId: null
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['primaryProviderLeagues'])
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