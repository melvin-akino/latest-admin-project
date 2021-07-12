<template>
  <tr>
    <td>
      <div class="ma-2" v-if="type=='leagues'">
        <div class="ma-2" v-for="league in item.leagues" :key="league.id">
          <span class="badge mr-4" :class="[`${league.provider.toLowerCase()}`]">
            {{league.provider}}
          </span> 
          {{league.name}}
        </div>
      </div>
      <div class="ma-2" v-if="type=='teams'">
        <div class="ma-2" v-for="team in item.teams" :key="team.id">
          <span class="badge mr-4" :class="[`${team.provider.toLowerCase()}`]">
            {{team.provider}}
          </span> 
          {{team.name}}
        </div>
      </div>
    </td>
    <td>
      <v-text-field
        v-model="row.alias"
        label="Alias"
        @input="edit = true"
      ></v-text-field>
    </td>
    <td>
      <v-btn v-if="edit" icon small color="success" @click="submit">
        <v-icon>mdi-check-bold</v-icon>
      </v-btn>
      <v-btn v-if="edit" icon small @click="cancelEdit">
        <v-icon>mdi-undo</v-icon>
      </v-btn>
    </td>
  </tr>
</template>

<script>
import bus from '../../../../eventBus'
import { mapActions } from 'vuex'

export default {
  props: ['type', 'item'],
  data: () => ({
    edit: false
  }),
  computed: {
    row() {
      return { ...this.item }
    }
  },
  watch: {
    row() {
      this.edit = false
    }
  },
  methods: {
    ...mapActions('masterlistMatching', ['updateLeagueAlias', 'updateTeamAlias']),
    cancelEdit() {
      this.edit = false
      this.row.alias = this.item.alias
    },
    async submit() {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: `Updating ${this.type} alias...`
        });
        let response
        if(this.type=='leagues') {
          response = await this.updateLeagueAlias({ id: this.row.master_league_id, alias: this.row.alias })
        } else {
          response = await this.updateTeamAlias({ id: this.row.master_team_id, alias: this.row.alias })
        }
        this.edit = false
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: response
        });
      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.hasOwnProperty('errors') ? err.response.data.errors[0] : err.response.data.message
        });
      }
    }
  }

}
</script>