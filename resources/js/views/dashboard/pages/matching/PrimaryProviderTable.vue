<template>
  <v-data-table
    :items="[]"
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
        ></v-select>
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

</style>