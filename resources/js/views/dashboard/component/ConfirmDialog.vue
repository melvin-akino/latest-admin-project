<template>
  <v-dialog v-model="dialog" width="500" :activator="activator" @click:outside="cancel">
    <v-card>
      <v-toolbar color="primary" dark height="40px">
        <v-toolbar-title class="text-uppercase subtitle-1"
          >{{title ? title : confirmMessage}}</v-toolbar-title
        >
        <v-spacer></v-spacer>
        <v-btn @click="cancel" icon>
          <v-icon dark>mdi-close-circle</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text v-if="message">
        {{message}}
      </v-card-text>
      <v-card-text v-else-if="matching && unmatch && primaryProvider">
        <div class="matchSummary" v-if="matchingType=='match'">
          <div v-if="type=='leagues'">
            <p>league id: <span>{{unmatch.id}}</span> >> <span>{{primaryProvider.id}}</span></p>
            <p>name: <span>{{unmatch.name}}</span> >> <span>{{primaryProvider.name}}</span></p>
          </div>
          <div v-else>
            <p>event_id: <span>{{unmatch.event_identifier}}</span> >> <span>{{primaryProvider.event_identifier}}</span></p>
            <p>league: <span>{{unmatch.league_name}}</span> >> <span>{{primaryProvider.league_name}}</span></p>
            <p>home team: <span>{{unmatch.team_home_name}}</span> >> <span>{{primaryProvider.team_home_name}}</span></p>
            <p>away team: <span>{{unmatch.team_away_name}}</span> >> <span>{{primaryProvider.team_away_name}}</span></p>
            <p>ref_schedule: <span>{{unmatch.ref_schedule}}</span> >> <span>{{primaryProvider.ref_schedule}}</span></p>
          </div>
        </div>
        <div class="matchSummary" v-else>
          <p>Are you sure you want to unmatch these {{type}}?</p>
          <div v-if="type=='leagues'">
            <p>{{primaryProvider.provider}}: <span>{{primaryProvider.name}}</span></p>
            <p>{{unmatch.provider}}: <span>{{unmatch.name}}</span></p>
          </div>
        </div>
      </v-card-text>
      <v-card-text v-else>
        <slot></slot>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn dark right class="red darken-2" @click="cancel"
          >Cancel</v-btn
        >
        <v-btn dark right class="success" @click="confirm">Confirm</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import bus from '../../../eventBus'

export default {
  name: 'ConfirmDialog',
  props: ['title', 'message', 'activator', 'matching', 'type'],
  data: () => ({
    dialog: false,
    unmatch: null,
    primaryProvider: null,
    confirmMessage: '',
    matchingType: ''
  }),
  mounted() {
    bus.$on('OPEN_MATCHING_DIALOG', ({ unmatch, primaryProvider, confirmMessage, matchingType }) => {
      this.dialog = true
      this.unmatch = unmatch
      this.primaryProvider = primaryProvider
      this.confirmMessage = confirmMessage
      this.matchingType = matchingType
    })

    bus.$on('CLOSE_DIALOG', () => {
      this.dialog = false
    })
  },
  methods: {
    cancel() {
      this.dialog = false
      this.$emit('close')
    },
    confirm() {
      this.$emit('confirm')
    }
  }
}
</script>

<style>
.v-dialog > .v-card > .v-card__text {
  font-weight: 400;
  font-size: 15px;
}
</style>
