<template>
  <tr>
    <td class="text-start">#{{$vnode.key + 1}}</td>
    <td class="text-start">{{item.name}}</td>
    <td class="text-start">
      <v-select
        :items="matchedData"
        item-value="id"
        item-text="name"
        :label="`Select matched ${dataType}`"
        dense
        class="input"
        v-model="matchingForm.primary_provider_id"
      ></v-select>
    </td>
    <td class="text-start">
        <v-text-field
        label="Alias"
        type="text"
        dense
        class="input"
        v-if="dataType != 'events'"
        v-model="matchingForm.master_alias"
      ></v-text-field>
    </td>
    <td class="text-start">
      <v-checkbox label="ADD TO MASTER" class="input" v-if="dataType != 'events'" v-model="matchingForm.add_master"></v-checkbox>
    </td>
    <td class="text-start">
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-btn v-on="on" icon small @click="match">
            <v-icon>mdi-arrow-decision</v-icon>
          </v-btn>
        </template>
        <span class="caption">Match {{dataTypeSingular}}</span>
      </v-tooltip>
    </td>
  </tr>
</template>

<script>
import { mapActions } from 'vuex'
import bus from '../../../../eventBus'
import { handleAPIErrors } from '../../../../helpers/errors'

export default {
  props: ['dataType', 'dataTypeSingular', 'matchedData', 'item'],
  name: 'MatchingTableRow',
  data() {
    return {
      matchingForm: {
        primary_provider_id: null,
        master_alias: '',
        add_master: false
      }
    }
  },
  methods: {
    ...mapActions('masterlistMatching', ['matchData']),
    initializeParams() {
      let params = {}
      this.$set(params, `primary_provider_${this.dataTypeSingular}_id`, this.matchingForm.primary_provider_id)
      this.$set(params, `match_${this.dataTypeSingular}_id`, this.item.id)
      this.$set(params, `master_${this.dataTypeSingular}_alias`, this.matchingForm.master_alias)
      this.$set(params, `add_master_${this.dataTypeSingular}`, this.matchingForm.add_master)
      return params
    },
    async match() {
      try {
        bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: `Matching ${this.dataTypeSingular}...`
        });
        let payload = {
          data: this.initializeParams(),
          index: this.$vnode.key,
          type: this.dataType
        }
        await this.matchData(payload)
        this.matchingForm.primary_provider_id = null,
        this.matchingForm.master_alias = '',
        this.matchingForm.add_master = false
         bus.$emit("SHOW_SNACKBAR", {
          color: "success",
          text: `Matched ${this.dataTypeSingular} successfully.`
        });

      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: handleAPIErrors(err)
        });
      }

    }
  }
}
</script>