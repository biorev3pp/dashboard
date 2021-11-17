<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-3 col-12 pl-0">
                    <label for="" class="text-uppercase m-0"> Detailed Logs Of </label>
                    <select name="t" id="t" v-model="form.t" readonly="readonly" class="form-control">
                        <option value="contact">Prospects</option>
                        <option value="call">Five9 Calls</option>
                        <option value="email-all">All Outreach Emails</option>
                        <option value="email-single">Only One-Off Emails</option>
                        <option value="email-sequence">All Sequence Emails</option>
                    </select>
                </div>
                <div class="col-md-3 col-12 pl-0">
                    <label for="" class="text-uppercase m-0"> Detailed Logs Options </label>
                    <select name="v" id="v" v-model="form.v" readonly="readonly" class="form-control">
                        <option :value="ok" v-for="(option, ok) in options" :key="ok">{{ option }}</option>
                    </select>
                </div>
                <div class="col-md-3 col-12 pl-0">
                    <label for="" class="text-uppercase m-0"> Date Range </label>
                    <input type="text" class="form-control" readonly="readonly" v-model="daterange">
                </div>
                <div class="col-md-3 col-12 p-0 text-right">
                    <label for="" class="text-uppercase m-0 d-block" >Total </label>
                    <p class="setfont"><b>{{ records.length | freeNumber }}</b> Records</p>
                </div>
            </div>
        </div>  
        <div class="log-div">
            <div class="table-responsive" v-if="form.t == 'call'">
                <div class="divtable border-top">
                    <div class="divthead">
                        <div class="divthead-row">
                            <div class="divthead-elem cursor-pointer wf-175" @click="sort('timestamp')" v-bind:class="[sortBy === 'timestamp' ? sortDirection : '']">Call TimeStamp</div>
                            <div class="divthead-elem cursor-pointer wf-175" @click="sort('agent_name')" v-bind:class="[sortBy === 'agent_name' ? sortDirection : '']">Agent</div>
                            <div class="divthead-elem cursor-pointer wf-100" @click="sort('record_id')" v-bind:class="[sortBy === 'record_id' ? sortDirection : '']">Record ID</div>
                            <div class="divthead-elem cursor-pointer mwf-200" @click="sort('name')" v-bind:class="[sortBy === 'customer_name' ? sortDirection : '']">Name</div>
                            <div class="divthead-elem cursor-pointer wf-150" @click="sort('dnis')" v-bind:class="[sortBy === 'dnis' ? sortDirection : '']">Number</div>
                            <div class="divthead-elem cursor-pointer wf-100" @click="sort('talk_time')" v-bind:class="[sortBy === 'talk_time' ? sortDirection : '']">Talk Time</div>
                            <div class="divthead-elem cursor-pointer wf-200" @click="sort('disposition')" v-bind:class="[sortBy === 'disposition' ? sortDirection : '']">Disposition</div>
                        </div>
                    </div>
                    <div class="divtbody sync-divt-content">
                        <div class="divtbody-row" v-for="record in records" :key="'dsg-'+record.id">
                            <div class="divtbody-elem wf-175">{{ record.timestamp }}</div>
                            <div class="divtbody-elem wf-175">{{ record.agent_name }}</div>
                            <div class="divtbody-elem wf-100">{{ record.record_id }}</div>
                            <div class="divtbody-elem mwf-200">
                                <router-link target="_blank" :to="'/prospects/' + record.record_id+'?view=call'" class="text-capitalize"><b>{{ (record.contact_data)?record.contact_data.first_name:'' }} {{ (record.contact_data)?record.contact_data.last_name:'' }} </b></router-link>
                                
                                <small class="fw-500" v-title="(record.contact_data)?record.contact_data.title:''" v-if="(record.contact_data)?record.contact_data.title:''">{{ (record.contact_data)?record.contact_data.title:'' }}  in </small> 
                                <span class="company-sm" v-title="(record.contact_data)?record.contact_data.company:''" v-if="(record.contact_data)?record.contact_data.company:''">{{ (record.contact_data)?record.contact_data.company:'' }}</span>
                            </div>
                            <div class="divtbody-elem wf-150">
                                <span class="phn-tag" v-if="record.number_type == 'm'">MP</span>
                                <span class="phn-tag" v-if="record.number_type == 'hq'">HP</span>
                                <span class="phn-tag" v-if="record.number_type == 'd'">WP</span>
                                {{ record.dnis }}
                            </div>
                            <div class="divtbody-elem wf-100">{{ record.talk_time }}</div>
                            <div class="divtbody-elem wf-200"> {{ record.disposition }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive" v-else-if="form.t == 'email-all' || form.t == 'email-single' || form.t == 'email-sequence'">
                <div class="divtable border-top">
                    <div class="divthead">
                        <div class="divthead-row">
                            <div class="divthead-elem cursor-pointer wf-100"  @click="sort('mailboxAddress')" v-bind:class="[sortBy === 'mailboxAddress' ? sortDirection : '']">Sender</div>
                            <div class="divthead-elem cursor-pointer wf-100" @click="sort('contact_id')" v-bind:class="[sortBy === 'contact_id' ? sortDirection : '']">
                                Record ID
                            </div>
                            <div class="divthead-elem cursor-pointer wf-300" @click="sort('prospect.first_name')" v-bind:class="[sortBy === 'prospect.first_name' ? sortDirection : '']">
                                Name                            
                            </div>
                            <div class="divthead-elem cursor-pointer mwf-200" @click="sort('subject')" v-bind:class="[sortBy === 'subject' ? sortDirection : '']">
                                Subject
                            </div>
                            <div class="divthead-elem cursor-pointer wf-220">
                                Status                   
                            </div>
                            <div class="divthead-elem cursor-pointer wf-80" @click="sort('createdAt')" v-bind:class="[sortBy === 'createdAt' ? sortDirection : '']">
                                Date
                            </div>
                        </div>
                    </div>
                    <div class="divtbody sync-divt-content">
                        <div class="divtbody-row" v-for="record in records" :key="'dsg-'+record.id">
                            <div class="divtbody-elem  wf-100">
                                <span class="stage-och stage-8 fw-500 border" v-title="record.mailboxAddress"> {{ getName(record.mailboxAddress) | capitalize }}</span>
                            </div>
                            <div class="divtbody-elem  wf-100">
                                {{ (record.prospect)?record.prospect.record_id:'' }}
                            </div>
                            <div class="divtbody-elem wf-300">
                                <router-link target="_blank" :to="'/prospects/' + ((record.prospect)?record.prospect.record_id:'')+'?view=email'" class="text-capitalize"><b>{{ (record.prospect)?record.prospect.first_name:'' }} {{ (record.prospect)?record.prospect.last_name:'' }} </b></router-link>
                                <br>
                                <small class="fw-500" v-title="(record.prospect)?record.prospect.title:''" v-if="(record.prospect)?record.prospect.title:''">{{ (record.prospect)?record.prospect.title:'' }}  in </small> 
                                <span class="company-sm" v-title="(record.prospect)?record.prospect.company:''" v-if="(record.prospect)?record.prospect.company:''">{{ (record.prospect)?record.prospect.company:'' }}</span>
                            </div>
                            <div class="divtbody-elem mwf-200">
                                {{ record.subject }}
                            </div>
                            <div class="divtbody-elem wf-220">
                                <span class="stack-box email-rlog">
                                    <span class="stack-row d-block m-0" :class="[(record.deliveredAt)?'active':'']">
                                        <em :class="(record.deliveredAt)?'active':'deactive'"><i :class="(record.deliveredAt)?'active':'deactive'" v-title="myDateFormat('Delivered on ', record.deliveredAt)" class="status-icon bi bi-envelope-fill"></i></em>
                                        <em :class="(record.openedAt)?'active':'deactive'"><i :class="(record.openedAt)?'active':'deactive'" v-title="myDateFormat('Opened on ', record.openedAt)" class="status-icon bi bi-envelope-open-fill"></i></em>
                                        <em :class="(record.clickedAt)?'active':'deactive'"><i :class="(record.clickedAt)?'active':'deactive'" v-title="myDateFormat('Clicked on ', record.clickedAt)" class="status-icon bi bi-hand-index-fill"></i></em>
                                        <em :class="(record.repliedAt)?'active':'deactive'"><i :class="(record.repliedAt)?'active':'deactive'" v-title="myDateFormat('Replied on ', record.repliedAt)" class="status-icon bi bi-reply-fill"></i></em>
                                        <em :class="(record.bouncedAt)?'active':'deactive'"><i :class="(record.bouncedAt)?'active':'deactive'" v-title="myDateFormat('Bounced on ', record.bouncedAt)" class="status-icon bi bi-box-arrow-up-right"></i></em>
                                        <em :class="(record.unsubscribedAt)?'active':'deactive'"><i :class="(record.unsubscribedAt)?'active':'deactive'" v-title="myDateFormat('Unsubscribed on ', record.unsubscribedAt)" class="status-icon bi bi-box-arrow-right"></i></em>
                                    </span>
                                </span>       
                            </div>
                            <div class="divtbody-elem wf-80">
                                <span class="light-text" v-title="record.createdAt">
                                    {{ record.createdAt | sidedate }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive" v-else-if="form.t == 'contact'">
                <div class="divtable border-top">
                    <div class="divthead">
                        <div class="divthead-row">
                            <div class="divthead-elem cursor-pointer  wf-100"  @click="sort('record_id')" v-bind:class="[sortBy === 'record_id' ? sortDirection : '']">
                                Record ID                        
                            </div>
                            <div class="divthead-elem mwf-200"  @click="sort('first_name')" v-bind:class="[sortBy === 'first_name' ? sortDirection : '']">
                                Name                            
                            </div>
                            <div class="divthead-elem cursor-pointer  wf-175"  @click="sort('outreach_tag')" v-bind:class="[sortBy === 'outreach_tag' ? sortDirection : '']">
                                Tag                        
                            </div>
                            <div class="divthead-elem cursor-pointer  wf-175"  @click="sort('stage_name')" v-bind:class="[sortBy === 'stage_name' ? sortDirection : '']">
                                Stage                        
                            </div>
                            <div class="divthead-elem cursor-pointer  wf-150">
                                Call Stack  
                            </div>
                            <div class="divthead-elem cursor-pointer  wf-220">
                                Email Stack                   
                            </div>
                            <div class="divthead-elem cursor-pointer  wf-150"  @click="sort('outreach_created_at')" v-bind:class="[sortBy === 'outreach_created_at' ? sortDirection : '']">
                                Time Stack
                            </div>
                        </div>
                    </div>
                    <div class="divtbody sync-divt-content">
                        <div class="divtbody-row" v-for="record in records" :key="'dsg-'+record.id">
                            <div class="divtbody-elem  wf-100">
                                {{ record.record_id }}
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
                                <span v-show="record.old_stage_name" :class="record.old_stage_css" v-title="record.old_stage_name">
                                    {{ record.old_stage_name }}
                                </span><br>
                                <i class="bi bi-arrow-down ml-3" v-show="record.old_stage_name"></i><br>
                                <span v-if="record.stage_name" :class="record.stage_css" v-title="record.stage_name">
                                    {{ record.stage_name }}
                                </span>
                                <span class="stage-och stage-1" v-else>No Stage</span>
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
            dbrecords : [],
            loader:false,
            sortDirection:'',
            sortBy:'',
            loader_url: '/img/spinner.gif',
            eoptions:{1:'Total Delivered', 2:'Total Opened', 3:'Total Clicked', 4:'Total Replied', 5:'Total Bounced'},
            poptions:{1:'Total Prospect', 2: 'New Prospect', 3: 'Stage Update', 4:'Contact No Update', 5: 'Custom Field update'},
            coptions:{1:'Total Calls', 2:'Answered - Mobile Phone', 3:'Answered - Home Phone', 4:'Answered - Work Phone', 5:'Not Answered'},
            form: new Form({
                t:'',
                v:'',
                e:'',
                s:'',
            }),
        }
    },
    filters: {
    },
    computed: {
        records() {
            return this.dbrecords.sort((p1,p2) => {
                let modifier = 1;
                if(this.sortDirection === 'desc') modifier = -1;
                if(p1[this.sortBy] < p2[this.sortBy]) return -1 * modifier; if(p1[this.sortBy] > p2[this.sortBy]) return 1 * modifier;
                return 0;
            });
        },
        options() {
            if(this.form.t == 'contact') {
                return this.poptions;
            } else if(this.form.t == 'call') {
                return this.coptions;
            } else {
                return this.eoptions;
            }
        },
        daterange() {
            return this.$options.filters.setbtdate(this.form.s)+' - '+this.$options.filters.setbtdate(this.form.e);
        }
    },
    methods: {
        sort(s){
            if(s === this.sortBy) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            }
            this.sortBy = s;
        },
        myDateFormat(txt, val) {
            if(val) {
                return txt+' '+this.$options.filters.logdateFull(val);
            }
            else {
                return false;
            }
        },
        getName(name) {
            let n = name.split('@');
            return n[0];
        },
        getData() {
            this.$Progress.start();  
            var path = this.$route.fullPath.split("?")
            this.form.post('/api/get-log-details?'+path[1])
                .then((response) => {
                    this.dbrecords = response.data;
                     this.$Progress.finish();  
                })
        }
    },
    beforeMount() {
        this.form.fill(this.$route.query);
    },
    mounted() {
        //console.log()
        this.getData();
    }
}
</script>