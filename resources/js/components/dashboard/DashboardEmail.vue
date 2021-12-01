<template>
    <div>
        <div>
            <div class="filterbox">
                <div class="row mx-0 mb-1">
                    <div class="col-md-9 col-12 p-0 filter-btns-holder">
                        <span v-if="form.agentName" class="filter-btns row">
                            <span  class="text-dark mx-1 pointer-hand wf-180"> 
                                <b>Agent : {{ form.agentName }}</b>
                            </span>
                        </span>
                        <span v-if="form.type" class="filter-btns row">
                            <span  class="text-dark mx-1 pointer-hand wf-180"> 
                                <b>Emails Type : {{ form.type }}</b>
                            </span>
                        </span>
                        <span class="filter-btns row">
                            <span  class="text-dark mx-1 pointer-hand wf-180"> 
                                <b>Date : {{ form.dateRange.startDate.substring(0, 10) }} - {{ form.dateRange.endDate.substring(0, 10) }}</b>
                            </span>
                        </span>
                    </div>
                    <div class="col-md-3 col-12 p-0 text-right pt-2">
                        <img :src="loader_url" alt="Loading..." v-show="loader">
                        Showing <b>{{ totalNumberOfRecords | freeNumber }}</b> Results
                    </div>
                </div>
            </div>
            <div class="divtable border-top">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-60 text-center">Sno</div>
                        <div class="divthead-elem wf-150">Sender</div>
                        <div class="divthead-elem wf-150">Receiver</div>
                        <div class="divthead-elem mwf-250">Subject</div>
                        <div class="divthead-elem wf-175">Delivered At</div>
                        <div class="divthead-elem wf-175">Bounced At</div>
                        <div class="divthead-elem wf-175">Clicked At</div>
                        <div class="divthead-elem wf-175">Opened At</div>
                        <div class="divthead-elem wf-175">Replied At</div>
                    </div>
                </div>
                <div class="divtbody full-page-with-search">
                    <div class="divtbody-row" v-for="(record, index) in records.data" :key="'dsg-'+record.id">
                        <div class="divtbody-elem  wf-60 text-center">
                            {{ records.from + index  }}
                        </div>
                        <div class="divtbody-elem wf-150">
                            <span v-if="record.mailboxAddress" v-title="record.mailboxAddress">
                                {{ record.mailboxAddress }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem wf-150">
                            <span v-if="record.first_name" v-title="record.first_name">
                                {{ record.first_name }} {{ record.last_name }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem mwf-250">
                            <span v-if="record.subject" v-title="record.subject">
                                {{ record.subject }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.deliveredAt" v-title="record.deliveredAt">
                                {{ record.deliveredAt | logdateFull }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.bouncedAt" v-title="record.bouncedAt">
                                {{ record.bouncedAt | logdateFull }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.clickedAt" v-title="record.clickedAt">
                                {{ record.clickedAt | logdateFull}}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.openedAt" v-title="record.openedAt">
                                {{ record.openedAt | logdateFull }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.repliedAt" v-title="record.repliedAt">
                                {{ record.repliedAt | logdateFull }}
                            </span>
                            <span class="no-stage" v-else>---</span>
                        </div>
                    </div>
                </div>
                <div class="divtfoot border-top">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="form.recordPerPage" @change="getDatasetsCall">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </span>
                        <pagination :limit="5" :data="records" @pagination-change-page="getDatasetsCall"></pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import CallLog from './CallLog';
import EmailLog from './EmailLog';
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import 'vue-select/dist/vue-select.css';
import ExportWizard from '../dataset/FiveNineWizard';

export default {
    components:{CallLog, EmailLog, DateRangePicker, ToggleButton, ExportWizard},
    data() {
        return {
            loader:false,            
            records:{},
            totalRecords : 0,
            form: new Form({
                sortBy:'desc',
                recordPerPage:20,
                pageno:1,
                dateRange:{},
                agentName: '',
                type : '',
                page:1,
            }),
            loader_url: '/img/spinner.gif',
            totalNumberOfRecords:'',
        }
    },
    filters: {
           },
    computed: {
        
    },
    methods: {
        getDatasetsCall(page) {
            this.loader = true;
            this.$Progress.start();  
            this.form.page = page
            this.form.post('/api/get-emails').then((response) => {                
                this.records = response.data;
                this.totalRecords = response.data.total;                
                this.$Progress.finish();               
                this.totalNumberOfRecords = response.data.total;
                this.loader = false;
            });
        },
    },
    created(){

    },
    beforeMount() {
        if(this.datasets == '') {
           this.$store.dispatch('setDatasets');
        }
    },
    mounted() {
        if(this.filterItems == '') {
           this.$store.dispatch('setDatasetFilter');
        }
        //route
        const obj = this.$route.query
        var i
        for (const key of Object.keys(obj)) {
            i = key
        }
        var di = JSON.parse(i)
        if(di["dateRange"]){
            this.form.dateRange = di["dateRange"]
        }
        if(di["agentName"]){
            this.form.agentName = di["agentName"]
        }
        if(di["type"]){
            this.form.type = di["type"]
        }
        
        
        this.getDatasetsCall(1);
    }
}
</script>