<template>
    <div>
        <div>
            <div class="table-responsive border-bottom">
                <div class="synops">          
                    <div class="inner-synop cursor-pointer">
                        <h4 class="number">{{ active_list.size | freeNumber }} </h4><p>Total</p>
                    </div>
                    <div class="inner-synop cursor-pointer" v-if="stageDetails == ''">
                        <img :src="loader_url" alt="Loading...">
                    </div>
                    <div v-else class="inner-synop cursor-pointer" v-for="(stageDetail, i) in stageDetails"  :key="'row-'+i">
                        <h4 class="number">{{ stageDetail.count | freeNumber }} </h4>
                        <p>{{ stageDetail.stage }}</p>
                    </div>
                </div>
            </div>
            <div class="row m-0">
                <div class="col-6 pt-2 pb-1">
                    <router-link link class="text-uppercase text-dark mr-2 fw-500" to="/five9/lists">
                        <i class="bi bi-arrow-left"></i>  Back to list
                    </router-link> | <h5 class="d-inline-block mt-1 ml-2">LIST NAME: {{ active_list.name }} </h5>
                </div>
                <div class="col-6 text-right pt-2">
                    <img :src="loader_url" alt="Loading..." v-show="loader">
                    <span>Showing</span>
                    <span><b>{{ totalNumberOfRecords | freeNumber }}</b> Results</span>
                </div>
            </div>
            <div class="divtable border-top">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-45 text-center">
                            <input type="checkbox" name="" id="check-all" value="0" aria-label="...">
                        </div>
                        <div class="divthead-elem mwf-200">
                            Name                            
                        </div>
                        <div class="divthead-elem wf-175">
                            Tag                        
                        </div>
                        <div class="divthead-elem wf-175">
                            Stage                        
                        </div>
                        <div class="divthead-elem wf-150">
                            Call Stack  
                        </div>
                        <div class="divthead-elem wf-220">
                            Email Stack                   
                        </div>
                        <div class="divthead-elem wf-150">
                            Time Stack
                        </div>
                    </div>
                </div>
                <div class="divtbody only-divt-content">
                    <div class="divtbody-row" v-for="record in records.data" :key="'dsg-'+record.id">
                        <div class="divtbody-elem  wf-45 text-center">
                            <div class="form-check">
                                <input :id="'record-'+record.id" class="form-check-input me-1" type="checkbox" :value="record.id">
                            </div>
                        </div>
                        <div class="divtbody-elem mwf-200">
                            <router-link target="_blank" :to="'prospects/' + record.record_id" class="text-capitalize"><b>{{ record.first_name }} {{ record.last_name }} </b></router-link>
                            <br>
                            <small class="fw-500" v-title="record.title" v-if="record.title">{{ record.title }}  in </small> 
                            <span class="company-sm" v-title="record.company" v-if="record.company">{{ record.company }}</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.outreach_tag">
                                <label class=" alert alert-primary m-1 py-1 px-2" v-for="(tag, ti) in (record.outreach_tag.split(','))" v-title="tag" :key="'otg'+ti">{{ tag }}</label>
                            </span>    
                        </div>
                        <div class="divtbody-elem wf-175">
                            <span v-if="record.stage_name" :class="record.stage_css" v-title="record.stage_name">
                                {{ record.stage_name }}
                            </span>
                            <span class="no-stage" v-else>No Stage</span>
                        </div>
                        <div class="divtbody-elem wf-150">
                            <span class="stack-box call-log">
                                <label for="call">
                                    <i class="bi bi-telephone-fill"></i>
                                </label>
                                <call-log :call="record.mcall_attempts" :rcall="record.mcall_received" :title="record.mobilePhones" :fnumber="record.mnumber" :label="'MP'"></call-log>
                                <call-log :call="record.wcall_attempts" :rcall="record.wcall_received" :title="record.workPhones" :label="'WP'"></call-log>
                                <call-log :call="record.hcall_attempts" :rcall="record.hcall_received" :title="record.homePhones" :label="'HP'"></call-log>
                            </span>
                        </div>
                        <div class="divtbody-elem wf-220">
                            <span class="stack-box email-log">
                                <label for="email">
                                    <i class="bi bi-envelope-fill text-primary"></i>
                                </label>
                                <email-log :te="record.email_delivered" :tc="record.email_clicked" :to="record.email_opened" :tb="record.email_bounced" :tr="record.email_replied" :title="record.emails" :label="'B'"></email-log>
                                <email-log :te="0" :tc="0" :to="0" :tb="0" :tr="0" :title="record.supplemental_email?record.supplemental_email:''" :label="'S'"></email-log>
                            </span>
                        </div>                             
                        <div class="divtbody-elem  wf-150">
                            <span class="stack-box">
                                <label for="email">
                                    <i class="bi bi-clock-fill text-success"></i>
                                </label>
                                <span :class="(record.outreach_created_at)?'active':''" v-title="myDateFormat('Created', record.outreach_created_at)">C</span>
                                <span :class="(record.outreach_touched_at)?'active':''" v-title="myDateFormat('Touched', record.outreach_touched_at)">T</span>
                                <span :class="(record.last_update_at)?'active':''" v-title="myDateFormat('Updated', record.last_update_at)">U</span>
                                <span :class="(record.last_agent_dispo_time)?'active':''" v-title="myDateFormat('Last Called ', record.last_agent_dispo_time)">CT</span>
                            </span>
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
import CallLog from '../dataset/CallLog';
import EmailLog from '../dataset/EmailLog';

export default {
    components:{CallLog, EmailLog},
    data() {
        return {
            loader:false,
            records:{},
            loader_url: '/img/spinner.gif',
            totalNumberOfRecords:'',
            active_list:'',
            stageDetails:'',
            form:new Form({
                recordPerPage:20,
                page:1,
                sortBy:'',
                list_id:this.$route.params.id
            })
        }
    },
    filters: {
           },
    computed: {
        
    },
    methods: {       
        myDateFormat(txt, val) {
            return txt+' '+this.$options.filters.convertInDayMonth(val)+' ago';
        },
        getDatasetsCall(page) {
            this.loader = true;
            this.$Progress.start();  
            this.form.page = page
            this.form.post('/api/get-list-based-all-prospects').then((response) => {                
                this.records = response.data;
                this.$Progress.finish();               
                this.totalNumberOfRecords = response.data.total;
                this.loader = false;
            });
        },
        getStageListPropects() {
            axios.get('/api/get-list-based-stages/'+this.$route.params.id).then((response) => {                
                this.stageDetails = response.data;
            });
        },
        updateSorting(by) {
            this.form.sortBy = by;
            this.getDatasetsCall(1);
        },
        refreshAll() {
            this.getDatasetsCall(1);
        },
        
    },
    created(){
        this.getStageListPropects();
        this.getDatasetsCall(1);
    },
    beforeMount() {
        let lid = this.$route.params.id
        axios.get('/api/get-f9-list-details/'+lid).then((response) => {
            this.active_list = response.data
         })
    }

}
</script>