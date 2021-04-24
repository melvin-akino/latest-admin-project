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
      :type="type"
      :matching="true"
      :width="700"
      @confirm="confirm"
      @close="cancel"
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
    ...mapState('masterlistMatching', ['matchingFilters', 'unmatchingData'])
  },
  methods: {
    ...mapMutations('masterlistMatching', { setPrimaryProviderData: 'SET_PRIMARY_PROVIDER_DATA', setPrimaryProviderId: 'SET_PRIMARY_PROVIDER_ID', setMatchId: 'SET_MATCH_ID', setUnmatchingData: 'SET_UNMATCHING_DATA'}),
    ...mapActions('masterlistMatching', ['matchLeague', 'matchEvent', 'unmatchLeague', 'unmatchEvent']),
    closeDialog() {
      bus.$emit("CLOSE_DIALOG")
    },
    cancel() {
      if(this.type=='events') {
        this.setMatchId(null)
        this.setPrimaryProviderId(null)
      }
      this.setUnmatchingData(null)
    },
    async match() {
      try {
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
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors[Object.keys(err.response.data.errors)[0]][0] : err.response.data.message
        });
      }
    },
    async unmatch() {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: 'Unmatching data...'
        });
        if(this.type=='leagues') {
          await this.unmatchLeague()
        } else {
          await this.unmatchEvent()
        }
        this.setUnmatchingData(null)
        this.closeDialog()      
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: 'Unmatched data succesfully!'
        });
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors[Object.keys(err.response.data.errors)[0]][0] : err.response.data.message
        });
      }
    },
    confirm() {
      if(this.unmatchingData) {
        this.unmatch()
      } else {
        this.match()
      }
    }
  }
}
</script>