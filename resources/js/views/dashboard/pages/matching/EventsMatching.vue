<template>
  <matching-container :type="type"></matching-container>
</template>

<script>
import { mapMutations } from 'vuex' 

export default {
  name: 'EventsMatching',
  components: {
    MatchingContainer: () => import('../../components/core/MatchingContainer')
  },
  computed: {
    type() {
      return this.$route.meta.type
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setUnmatchedData: 'SET_UNMATCHED_DATA', setIsLoadingUnmatchedData: 'SET_IS_LOADING_UNMATCHED_DATA', setTotalUnmatchedData: 'SET_TOTAL_UNMATCHED_DATA', setPrimaryProviderData: 'SET_PRIMARY_PROVIDER_DATA', setIsLoadingPrimaryProviderData: 'SET_IS_LOADING_PRIMARY_PROVIDER_DATA', setTotalPrimaryProviderData: 'SET_TOTAL_PRIMARY_PROVIDER_DATA', setFilter: 'SET_FILTER', setPrimaryProviderId: 'SET_PRIMARY_PROVIDER_ID', setMatchId: 'SET_MATCH_ID', setMatchedData: 'SET_MATCHED_DATA', setIsLoadingMatchedData: 'SET_IS_LOADING_MATCHED_DATA', setTotalMatchedData: 'SET_TOTAL_MATCHED_DATA' })
  },
  beforeRouteLeave(to, from, next) {
    this.setUnmatchedData([])
    this.setIsLoadingUnmatchedData(true)
    this.setTotalUnmatchedData(0)
    this.setPrimaryProviderData([])
    this.setIsLoadingPrimaryProviderData(true)
    this.setTotalPrimaryProviderData(0)
    this.setMatchedData([])
    this.setIsLoadingMatchedData(true)
    this.setTotalMatchedData(0)
    this.setFilter({ type: 'events', filter: 'leagueId', data: null })
    this.setFilter({ type: 'events', filter: 'masterLeagueId', data: null })
    this.setPrimaryProviderId(null)
    this.setMatchId(null)
    next()
  }
}
</script>

<style>

</style>