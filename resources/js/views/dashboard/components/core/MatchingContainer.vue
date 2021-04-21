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
    <confirm-dialog
      :title="`Confirm Matching of ${type}`"
      :type="type"
      :matching="true"
      @confirm="match"
      @close="cancelMatch"
    ></confirm-dialog>
  </v-container>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import bus from '../../../../eventBus'

export default {
  props: ['type'],
  name: 'MatchingContainer',
  components: {
    MatchingFilters: () => import('../../pages/matching/MatchingFilters'),
    UnmatchedTable: () => import('../../pages/matching/UnmatchedTable'),
    PrimaryProviderTable: () => import('../../pages/matching/PrimaryProviderTable'),
    MatchedTable: () => import('../../pages/matching/MatchedTable'),
    ConfirmDialog: () => import('../../component/ConfirmDialog')
  },
  computed: {
    ...mapState('masterlistMatching', ['matchingFilters'])
  },
  methods: {
    ...mapMutations('masterlistMatching', { setPrimaryProviderData: 'SET_PRIMARY_PROVIDER_DATA', setPrimaryProviderId: 'SET_PRIMARY_PROVIDER_ID', setMatchId: 'SET_MATCH_ID', }),
    ...mapActions('masterlistMatching', ['matchLeague', 'matchEvent']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG")
    },
    cancelMatch() {
      if(this.type=='events') {
        this.setMatchId(null)
        this.setPrimaryProviderId(null)
      }
    },
    async match() {
      bus.$emit("SHOW_SNACKBAR", {
        color: "success",
        text: 'Matching data...'
      });
      if(this.type=='leagues') {
        await this.matchLeague()
        this.setPrimaryProviderData([])
      } else {
        await this.matchEvent()
      }
      this.setPrimaryProviderId(null)
      this.setMatchId(null)
      this.closeDialog()      
      bus.$emit("SHOW_SNACKBAR", {
        color: "success",
        text: 'Matched data succesfully!'
      });
    }
  }
}
</script>