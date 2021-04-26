<template>
    <div class="history pa-6">
        <v-container>
            <v-toolbar flat color="transparent">
                <v-autocomplete :items="matchingFilters" v-model="searchKey" label="-- Select Filter --" clearable></v-autocomplete>
            </v-toolbar>
            <v-data-table :headers="headers" :items="activityLog" :items-per-page="10" :loading="isLoadingActivityLog" loading-text="Loading Matching History log" :server-items-length="totalRows" :options.sync="options">
                <template v-slot:[`item.log_name`]="{ item }">
                    <div :class="{'match': item.action == 'Matched', 'unmatch': item.action == 'Unmatched' }">
                        {{ item.log_name }}
                    </div>
                </template>

                <template v-slot:[`item.description`]="{ item }">
                    <div v-if="item.options.type == 'leagues'" class="events-desc">
                        <table>
                            <tr>
                                <td>{{ item.description.master.provider }}</td> <td>PROVIDER</td> <td>{{ item.description.raw.provider }}</td>
                            </tr>
                            <tr>
                                <td>{{ item.description.master.name }}</td> <td>LEAGUE NAME</td> <td>{{ item.description.raw.name }}</td>
                            </tr>
                        </table>
                    </div>

                    <div v-else-if="item.options.type == 'event' || item.options.type == 'league'">
                        <span><em>{{ item.description }}</em></span>
                    </div>

                    <div v-else class="events-desc">
                        <table>
                            <tr>
                                <td>{{ item.description[0].primary_alias }}</td> <td>PROVIDER</td> <td>{{ item.description[0].secondary_alias }}</td>
                            </tr>
                            <tr>
                                <td>{{ item.description[1].primary_event_id }}</td> <td>EVENT ID</td> <td>{{ item.description[1].secondary_event_id }}</td>
                            </tr>
                            <tr>
                                <td>{{ item.description[2].primary_league_name }}</td> <td>LEAGUE NAME</td> <td>{{ item.description[2].secondary_league_name }}</td>
                            </tr>
                            <tr>
                                <td>{{ item.description[3].primary_team_home_name }}</td> <td>HOME TEAM</td> <td>{{ item.description[3].secondary_team_home_name }}</td>
                            </tr>
                            <tr>
                                <td>{{ item.description[4].primary_team_away_name }}</td> <td>AWAY TEAM</td> <td>{{ item.description[4].secondary_team_away_name }}</td>
                            </tr>
                            <tr>
                                <td>{{ item.description[5].primary_ref_schedule }}</td> <td>SCHEDULE</td> <td>{{ item.description[5].secondary_ref_schedule }}</td>
                            </tr>
                        </table>
                    </div>
                </template>
                <!-- <template v-slot:[`item.unmatch`]="{ item }">
                    <v-btn v-if="item.action == 'Matched'" outlined color="error" class="unmatchBtn text-capitalize" small @click="unmatchEntry(item.options.type, item.options.provider_id, item.options.sport_id, item.options.raw_id)">Unmatch</v-btn>
                </template> -->
            </v-data-table>
        </v-container>
    </div>
</template>

<script>
    import { getToken } from '../../../../helpers/token'
    import { mapActions } from 'vuex'
    import bus from '../../../../eventBus'

    export default {
        name: 'History',
        components: {
            //
        },
        data:() => ({
            headers: [
                { text: 'LOG TYPE', value: 'log_name' },
                { text: 'ACTION', value: 'action', sortable: false },
                { text: 'DESCRIPTION', value: 'description', sortable: false, align: 'center' },
                { text: 'IP ADDRESS', value: 'ip_address', sortable: false, align: 'center' },
                { text: 'CREATED DATE', value: 'created_at' },
                // { text: '', value: 'unmatch' },
            ],
            activityLog: [],
            isLoadingActivityLog: false,
            matchingFilters: [
                'Leagues',
                'Events',
            ],
            fromAutoMatching: ['league', 'event'],
            totalRows: 0,
            searchKey: '',
            options: {},
        }),
        methods: {
            ...mapActions('auth', ['logoutOnError']),
            getMatchingHistoryLog(params) {
                this.isLoadingActivityLog = true
                axios.get('matching/history', { params: params, headers: { 'Authorization': `Bearer ${getToken()}` } })
                    .then(response => {
                        this.totalRows = response.data.total
                        this.activityLog = response.data.pageData
                        this.isLoadingActivityLog = false
                    })
                    .catch(err => {
                        this.logoutOnError(err.response.status)
                        bus.$emit("SHOW_SNACKBAR", {
                            color: "error",
                            text: err.response.data.message
                        });
                    })
            },
            unmatchEntry(dataType, providerId, sportId, rawId) {
                // confirm
                // axios.post(`${ dataType }/unmatch`, { params: {}, headers: { 'Authorization': `Bearer ${getToken()}` } })
                //     .then(response => {
                //         console.log(response)
                //     })
                //     .catch(err => {
                //         bus.$emit("SHOW_SNACKBAR", {
                //             color: "error",
                //             text: err.response.data.message
                //         });
                //     })
            }
        },
        watch: {
            options: {
                deep: true,
                handler(value) {
                    let params = {
                        page: value.page,
                        limit: value.itemsPerPage != -1 ? value.itemsPerPage : this.totalRows,
                        sortOrder: value.sortDesc[0] ? 'asc' : 'desc',
                        searchKey: value.searchKey
                    }

                    this.getMatchingHistoryLog(params)
                }
            },
            searchKey(value) {
                this.$set(this.options, 'searchKey', value)
                this.options.page = 1
            },
        },
    }
</script>

<style lang="scss">
    .history .v-toolbar__content {
        width: 10vw;
    }

    .history p {
        margin-bottom: 0;
    }

    .history .v-toolbar__content {
        padding: 16px;
    }

    .events-desc table {
        width: 100%;

        tr {
            td {
                text-align: left;
                width: 40%;
            }

            td:first-child {
                text-align: right;
            }

            td:nth-child(2) {
                width: 20% !important;

                color: #C6C6C6 !important;
                text-align: center;
            }
        }
    }
</style>
