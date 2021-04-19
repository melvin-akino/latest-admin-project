<template>
  <v-data-table
    :items="[]"
    :hide-default-footer="type=='leagues' ? true : false"
  >
    <template v-slot:top>
      <div class="matchingTableHeader">
        <p class="text-capitalize font-weight-medium">Primary Provider {{type}}</p>
      </div>
    </template>
    <template v-slot:header v-if="type=='leagues'">
      <div class="ma-4">
        <v-select
          :items="primaryProviderLeagues"
          item-text="name"
          item-value="id"
          v-model="leagueId"
          label="Select Primary Provider League"
          dense
          class="primaryProviderDropdown"
        ></v-select>
      </div> 
    </template>
    <template v-slot:[`body.append`] v-if="type=='leagues'">
      <div class="ma-4 matchBtn">
        <v-btn small dark class="matchBtn success text-capitalize">Match</v-btn>
      </div>
    </template>
  </v-data-table>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['type'],
  name: 'PrimaryProviderTable',
  data() {
    return {
      leagueId: null
    }
  },  
  computed: {
    ...mapState('masterlistMatching', ['primaryProviderLeagues'])
  }

}
</script>

<style>
  .primaryProviderDropdown, .primaryProviderDropdown .v-label {
    font-size: 13px !important;
  }

  .matchBtn {
    display: flex;
    justify-content: center;
  }

  .matchBtn .v-btn__content {
    font-size: 12px !important;
  }
</style>