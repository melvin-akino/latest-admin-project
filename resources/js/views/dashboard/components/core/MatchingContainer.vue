<template>
  <v-container>
    <matching-filters :type="type"></matching-filters>
    <v-row>
      <v-col cols="12" md="6">
        <unmatched-table v-if="type=='leagues' ? matchingFilters.leagues.unmatched : true" :type="type"></unmatched-table>
      </v-col>
      <v-col cols="12" md="6">
        <primary-provider-table v-if="type=='leagues' ? matchingFilters.leagues.unmatched : true" :type="type"></primary-provider-table>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <matched-table  v-if="type=='leagues' ? matchingFilters.leagues.matched : true" :type="type"></matched-table>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['type'],
  name: 'MatchingContainer',
  components: {
    MatchingFilters: () => import('../../pages/matching/MatchingFilters'),
    UnmatchedTable: () => import('../../pages/matching/UnmatchedTable'),
    PrimaryProviderTable: () => import('../../pages/matching/PrimaryProviderTable'),
    MatchedTable: () => import('../../pages/matching/MatchedTable')
  },
  computed: {
    ...mapState('masterlistMatching', ['matchingFilters'])
  }
}
</script>