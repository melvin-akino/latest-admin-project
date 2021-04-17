<template>
  <matching-container :type="type"></matching-container>
</template>

<script>
import { mapMutations } from 'vuex' 

export default {
  name: 'LeaguesMatching',
  components: {
    MatchingContainer: () => import('../../components/core/MatchingContainer')
  },
  computed: {
    type() {
      return this.$route.meta.type
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setUnmatchedData: 'SET_UNMATCHED_DATA', setIsLoadingUnmatchedData: 'SET_IS_LOADING_UNMATCHED_DATA' })
  },
  beforeRouteLeave(to, from, next) {
    this.setUnmatchedData([])
    this.setIsLoadingUnmatchedData(true)
    next()
  }
}
</script>

<style>

</style>