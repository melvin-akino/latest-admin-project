<template>
  <div class="masterlistMatching pa-6">
    <v-container>
      <v-toolbar flat color="transparent">
        <p class="text-h4 text-uppercase">Masterlist Matching</p>
      </v-toolbar>
      <v-tabs
        hide-slider
        class="mt-4"
      >
        <v-tab to="leagues" @change="changeType('leagues')">
          <v-badge color="error" content="!" :value="hasRawLeagues">
            Leagues
          </v-badge>
        </v-tab>
        <v-tab to="teams" @change="changeType('teams')">
          <v-badge color="error" content="!" :value="hasRawTeams">
            Teams
          </v-badge>
        </v-tab>
        <!-- <v-tab to="events" @change="changeType('events')">Events</v-tab> -->
      </v-tabs>
      <v-tabs
        hide-slider
        class="mt-4"
      >
        <v-tab v-for="provider in providers" :key="provider.id" @change="changeProvider(provider.id, provider.alias)">
          <v-badge color="error" :content="provider[`raw_${matching.type}`]" :value="provider[`raw_${matching.type}`]">
            {{provider.alias}}
          </v-badge>
        </v-tab>
      </v-tabs>
      <router-view></router-view>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'

export default {
  name: 'MasterlistMatching',
  data() {
    return {
      matching: {
        type: '',
        provider_id: null,
        provider_alias: ''
      }
    }
  },
  computed: {
    ...mapState('providers', ['providers']),
    matchingParams() {
      return { ...this.matching }
    },
    hasRawLeagues() {
      return this.checkIfHasRawData('leagues')
    },
    hasRawTeams() {
      return this.checkIfHasRawData('leagues')
    }
  },
  watch: {
    providers(value) {
      if(value.length != 0 && !this.matching.provider_id) {
        this.changeProvider(value[0].id, value[0].alias)
      }
    },
    matchingParams: {
      deep: true,
      handler(value, oldValue) {
        if(value.type && value.provider_id) {
          this.getRawData(value)
        }
      }
    },
    'matchingParams.type'(value) {
      this.getMatchedData(value)
    }
  },
  mounted() {
    this.getProviders(true)
  },
  methods: {
     ...mapMutations('masterlistMatching', { setRawData: 'SET_RAW_DATA', setMatchedData: 'SET_MATCHED_DATA' }),
    ...mapActions('providers', ['getProviders']),
    ...mapActions('masterlistMatching', ['getRawData', 'getMatchedData']),
    changeType(type) {
      this.matching.type = type
    },
    changeProvider(id, alias) {
      this.matching.provider_id = id
      this.matching.provider_alias = alias
    },
    checkIfHasRawData(type) {
      if(this.providers.length != 0) {
        let raw = this.providers.map(provider => provider[`raw_${type}`])
        return raw.some(count => count != 0)
      }
    }
  },
   beforeRouteLeave(to, from, next) {
    this.setRawData([])
    this.setMatchedData([])
    next()
  }
}
</script>

<style>
  .v-tab--active {
    background-color: #e9954b;
    color: #ffffff !important;
  }
</style>