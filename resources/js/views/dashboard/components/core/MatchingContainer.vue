<template>
  <v-container>
    <matching-filters :type="type"></matching-filters>
    <v-row>
      <v-col cols="12" md="6">
        <unmatched-table :type="type"></unmatched-table>
      </v-col>
      <v-col cols="12" md="6">
        <primary-provider-table :type="type"></primary-provider-table>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <matched-table :type="type"></matched-table>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  props: ['type'],
  name: 'MatchingContainer',
  components: {
    MatchingFilters: () => import('../../pages/matching/MatchingFilters'),
    UnmatchedTable: () => import('../../pages/matching/UnmatchedTable'),
    PrimaryProviderTable: () => import('../../pages/matching/PrimaryProviderTable'),
    MatchedTable: () => import('../../pages/matching/MatchedTable')
  },
}
</script>