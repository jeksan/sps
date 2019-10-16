<script>
    import Autocomplete from 'vuejs-auto-complete';
    import Datepicker from 'vuejs-datepicker';
    import axios from 'axios';
    import moment from 'moment';
    import { clientsApiUrl, reportApiUrl, reportExportApiUrl } from "../constants";

    export default {
        name: "Report",
        data () {
            return {
                reportData: null,
                dataFetching: false,
                error: false,
                selectedClient: null,
                periodStart: null,
                periodEnd: null,
            }
        },
        components: {
            Autocomplete,
            Datepicker
        },
        computed: {
            datePickerFormat() {
                return 'dd.MM.yyyy';
            },
            apiUrl () {
                return `${clientsApiUrl}?search=`;
            },
            clientInfo(){
                if (this.reportData) {
                    const { purse, balance, currencyCode, sumOperations, sumOperationsUSD } = this.reportData;
                    return { purse, balance, currencyCode, sumOperations, sumOperationsUSD };
                }
                return false;
            },
            operationHistory() {
                if (this.reportData && this.reportData.history) {
                    return {
                        tableHeader: {
                            date: 'Date',
                            operation: 'Operation',
                            amount: 'Amount',
                        },
                        history: this.reportData.history
                    };
                }
                return false;
            },
            showOperationHistory() {
                return this.operationHistory.history.length > 0;
            },
            showActionBar() {
                return Boolean(this.selectedClient);
            }
        },
        methods: {
            handleSelectClient(item) {
                if (item) {
                    this.selectedClient = item.value;
                }
            },
            handleClickClear()
            {
                this.resetData();
            },
            handleClickFetchData() {
                const self = this;
                self.dataFetching = true;
                self.error = false;
                axios.get(reportApiUrl, { params : {
                    'client-id' : this.selectedClient,
                    'period-start' : this.periodStart,
                    'period-end' : this.periodEnd,
                }})
                    .then(function ({ data }) {
                        self.reportData = data;
                    })
                    .catch(function (error) {
                        self.error = true;
                        console.log(error);
                    })
                    .finally(function () {
                        self.dataFetching = false;
                    });
            },
            handleClickExport()
            {
                let exportUrl = `${reportExportApiUrl}?client_id=${this.selectedClient}`;
                    this.periodStart && (exportUrl += '&')
                window.location.href = ;
            },
            resetData() {
                this.selectedClient = null;
                this.reportData = null;
                this.periodStart = null;
                this.periodEnd = null;
            },
        }
    }
</script>

<template>
    <div>
        <div class="container">
            <div class="row">
                <h5>Client</h5>
                <autocomplete
                    ref="autocomplete"
                    :source="apiUrl"
                    results-property="data"
                    results-display="name"
                    placeholder="Select client"
                    input-class="form-control"
                    @selected="handleSelectClient"
                    @clear="handleClickClear"/>
            </div>
            <div class="row">
                <h5>Period</h5>
                <div class="row">
                    <div class="col-1">
                        from
                    </div>
                    <div class="col-5">
                        <datepicker
                            :format="datePickerFormat"
                            input-class="form-control"
                            clear-button="true"
                            bootstrap-styling="true"
                            v-model="periodStart"
                            name="period-start"/>
                    </div>
                    <div class="col-1">
                        to
                    </div>
                    <div class="col-5">
                        <datepicker
                            :format="datePickerFormat"
                            :input-class="form-control"
                            clear-button="true"
                            bootstrap-styling="true"
                            v-model="periodEnd"
                            name="period-end"/>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end" v-show="showActionBar">
                <button
                    class="btn btn-primary mr-2"
                    @click="handleClickFetchData">Generate</button>
                <button
                    class="btn btn-success"
                    @click="handleClickExport">Export</button>
            </div>
        </div>

        <div v-if="error && !dataFetching" class="alert alert-danger" role="alert" >
            Oops! Something went wrong. Please try again later.
        </div>

        <div v-if="dataFetching && !error"
             class="spinner-border text-primary" role="status">
            <span class="sr-only">Fetching data...</span>
        </div>

        <div v-if="!dataFetching && clientInfo && !error" class="container">
            <hr/>

            <div v-if="clientInfo" class="row">
                <dl class="col form-group">
                    <dt>Purse number</dt>
                    <dd>{{ clientInfo.purse }}</dd>
                    <dt>Currency</dt>
                    <dd> {{ clientInfo.currencyCode }}</dd>
                    <dt>Balance</dt>
                    <dd> {{ clientInfo.balance }}</dd>
                </dl>
                <dl class="col form-group">
                    <dt> Amount operations</dt>
                    <dd> {{ clientInfo.sumOperations }}</dd>
                    <dt> Amount operations USD</dt>
                    <dd> {{ clientInfo.sumOperationsUSD }}</dd>
                </dl>
            </div>

            <h5 v-if="showOperationHistory">Operations history</h5>

            <table v-if="showOperationHistory" class="table">
                <thead>
                    <th v-for="colLabel in operationHistory.tableHeader">{{ colLabel }}</th>
                </thead>
                <tr v-for="(row, index) in operationHistory.history" :key="index">
                    <td v-for="(value, key) in operationHistory.tableHeader">
                        <template v-if="key === 'amount'">
                            <span :class="row[key] > 0 ? 'text-success' : 'text-danger'">
                                {{ row[key] }}
                            </span>
                        </template>
                        <template v-else>
                            {{ row[key] }}
                        </template>
                    </td>
                </tr>
            </table>

            <div v-else class="alert alert-primary" role="alert">
                There is no data. Try change filter
            </div>
        </div>
    </div>
</template>

<style>
    .row {
        margin-bottom: 10px;
    }
</style>
