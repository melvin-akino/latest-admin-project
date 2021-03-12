<template>
  <tr>
    <td class="text-start">#{{itemNumber + 1}}</td>
    <td class="text-start">{{item.name}}</td>
    <td class="text-start">
      <v-select
        :items="matchedData"
        item-value="id"
        item-text="name"
        :label="`Select matched ${dataType}`"
        dense
        clearable
        class="input"
        :disabled="matchingForm.add_master"
        @change="populateAlias"
        v-model="$v.matchingForm.primary_provider_id.$model"
        :error-messages="primaryProviderErrors"
        @input="$v.matchingForm.primary_provider_id.$touch()"
        @blur="$v.matchingForm.primary_provider_id.$touch()"
      ></v-select>
    </td>
    <td class="text-start">
        <v-text-field
        label="Alias"
        type="text"
        dense
        clearable
        class="input"
        v-if="dataType != 'events'"
        v-model="$v.matchingForm.master_alias.$model"
        :error-messages="aliasErrors"
        @input="$v.matchingForm.master_alias.$touch()"
        @blur="$v.matchingForm.master_alias.$touch()"
      >
      </v-text-field>
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
import { mapState, mapActions } from 'vuex'
import bus from '../../../../eventBus'
import { required, requiredIf } from 'vuelidate/lib/validators'

export default {
  props: ['dataType', 'dataTypeSingular', 'item', 'itemNumber'],
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
  validations: {
    matchingForm: {
      primary_provider_id: {
        required: requiredIf(function() {
          return !this.matchingForm.add_master
        })
      },
      master_alias: { required }
    }
  },
  computed: {
    ...mapState('masterlistMatching', ['matchedData']),
    primaryProviderErrors() {
      let errors = []
      if (!this.$v.matchingForm.primary_provider_id.$dirty) return errors
      !this.$v.matchingForm.primary_provider_id.required && errors.push('Matched league is required.')
      return errors
    },
    aliasErrors() {
      let errors = []
      if (!this.$v.matchingForm.master_alias.$dirty) return errors
      !this.$v.matchingForm.master_alias.required && errors.push('Alias is required.')
      return errors
    }
  },
  watch: {
    'matchingForm.add_master'(value) {
      if(value) {
        this.matchingForm.primary_provider_id = null
        this.matchingForm.master_alias = ''
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
    populateAlias() {
      if(this.matchingForm.primary_provider_id) {
        let alias = this.matchedData.filter(data => data.id == this.matchingForm.primary_provider_id)[0].name
        this.matchingForm.master_alias = alias
      }
    },
    async match() {
      try {
        if(!this.$v.matchingForm.$invalid) {
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: `Matching ${this.dataTypeSingular}...`
          });
          let payload = {
            data: this.initializeParams(),
            type: this.dataType,
            id: this.item.id
          }
          await this.matchData(payload)
          bus.$emit("SHOW_SNACKBAR", {
            color: "success",
            text: `Matched ${this.dataTypeSingular} successfully.`
          });
        } else {
          this.$v.matchingForm.$touch()
        }

      } catch(err) {
        bus.$emit("SHOW_SNACKBAR", {
          color: "error",
          text: err.response.data.errors[0] || err.response.data.message
        });
      }
    }
  }
}
</script>