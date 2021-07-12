<template>
  <div class="aliases">
    <v-container>
      <p class="text-subtitle-1 text-uppercase">{{type}} aliases</p>
      <v-row>
        <v-col cols="12" md="4">
          <v-select :items="types" dense outlined background-color="#fff" v-model="type" label="Select Type" class="typeFilter" @change="changeType"></v-select>
        </v-col>
      </v-row>
      <v-toolbar flat color="transparent">
        <v-spacer></v-spacer>
        <v-text-field
          v-model="searchKey"
          append-icon="mdi-magnify"
          label="Search"
          hide-details
          dense
          class="subtitle-1"
          style="max-width: 200px;"
        ></v-text-field>
      </v-toolbar>
      <v-data-table
        :headers="headers"
        :items="matchedData"
        :item-key="type=='leagues' ? 'master_league_id' : 'master_team_id'"
        :server-items-length="totalMatchedData"
        :options.sync="options"
        :loading="isLoadingMatchedData"
        :loading-text="`Loading ${type}`"
      >
        <template v-slot:item="{ item }">
          <alias-row :type="type" :item="item"></alias-row>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import AliasRow from '../../components/lists/AliasRow'

export default {
  components: {
    AliasRow
  },
  data: () => ({
    headers: [ 
      { text: 'Sort', value: 'data'},
      { text: 'Alias', value: 'alias' },
      { text: '', value: 'actions' },
    ],
    types: [
      { text: 'Leagues', value: 'leagues' },
      { text: 'Teams', value: 'teams' },
    ],
    type: 'leagues',
    options: {},
    searchKey: ''
  }),
  computed: {
    ...mapState('masterlistMatching', ['matchedData', 'isLoadingMatchedData', 'totalMatchedData'])
  },
  watch: {
    options: {
      deep: true,
      handler(value) {
        let params = {
          page: value.page,
          limit: value.itemsPerPage != -1 ? value.itemsPerPage : this.totalMatchedData,
          sortOrder: value.sortDesc[0] ? 'desc' : 'asc',
          searchKey: value.searchKey
        }
        this.setTableParams({ type: 'matchedDataParams', data: params })
        this.getMatchedData()
      }
    },
    searchKey(value) {
      this.$set(this.options, 'searchKey', value)
      this.options.page = 1
    }
  },
  methods: {
    ...mapMutations('masterlistMatching', { setTableParams: 'SET_TABLE_PARAMS',  setMatchedData: 'SET_MATCHED_DATA', setIsLoadingMatchedData: 'SET_IS_LOADING_MATCHED_DATA', setTotalMatchedData: 'SET_TOTAL_MATCHED_DATA' }),
    ...mapActions('masterlistMatching', ['getMatchedLeagues', 'getMatchedTeams']),
    getMatchedData() {
      if(this.type == 'leagues') {
        this.getMatchedLeagues()
      } else {
        this.getMatchedTeams()
      }
    },
    resetTable() {
      this.setMatchedData([])
      this.setIsLoadingMatchedData(true)
      this.setTotalMatchedData(0)
    },
    changeType() {
      this.resetTable()
      this.options.page = 1
      this.getMatchedData()
    }
  },
  beforeRouteLeave(to, from, next) {
    this.resetTable()
    next()
  }
}
</script>

<style>
.aliases .typeFilter, .aliases .v-text-field__slot, .aliases .v-text-field__slot label {
  font-size: 13px !important;
}
</style>