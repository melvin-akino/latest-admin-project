<template>
  <div class="masterlistMatching pa-4">
    <router-view></router-view>
  </div>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'MasterlistMatching',
  mounted() {
    this.getPrimaryProviderMatchedLeagues()
  },
  methods: {
    ...mapActions('masterlistMatching', ['getPrimaryProviderMatchedLeagues'])
  }
}
</script>

<style>
  .matchingTableHeader {
    padding: 12px;
    height: 56px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px !important;
    border-left: solid #e9954b 6px;
    border-bottom: solid rgba(0, 0, 0, 0.12) 1px;
  }

  .matchingTableHeader p {
    margin-bottom: 0;
  }

  .v-data-table > .v-data-table__wrapper tbody tr.v-data-table__expanded__content {
    box-shadow: none;
  }

  .badge {
    padding: 3px 5px;
    margin-left: 24px;
    color: #ffffff;
    border-radius: 0.25rem;
  }

  .hg {
    background-color: #8B0000;
  }

  .isn {
    background-color: #3651ed;
  }

  .pin {
    background-color: #ed8936;
  }
</style>