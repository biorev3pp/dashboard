<template>
    <div>
        <div class="detail-page">
            <div class="row m-0">
                <div class="col-md-9 pl-0  px-2  mt-2">      
                    <b-tabs content-class="detail-tabber" nav-class="link-tabber">
                        <b-tab title="Overview" :active="active_view == 'overview'">
                            <div class="row m-0">
                                <div class="col-md-12">

                                </div>
                                <div class="col-md-6 col-sm-12 pr-1">
                                    <div class="card mb-3">
                                        <div class="card-header hide" >
                                            <b class="card-title m-0 text-uppercase"> Timezone</b>
                                            <b class="float-right"> {{ prospect.custom29 }} </b>
                                        </div>
                                        <div class="card-body p-0 custom-auto-height text-center">
                                            <p class="mt-4 mb-2"> {{ prospect.timeZone }} 
                                                <span v-show="prospect.timediff"> ({{ prospect.timediff }})</span> </p>
                                            <h2 class="mb-4"> {{ prospect.localtime }}</h2>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-header hide" >
                                            <b class="card-title m-0 text-uppercase"> General Info</b>
                                        </div>
                                        <div class="card-body p-0 custom-auto-height">
                                            <table class="table table-bordered table-striped table-condensed m-0">
                                                <tbody>
                                                    <tr><th width="50%">First Name</th><td>{{ prospect.first_name }}</td></tr>
                                                    <tr><th>Last Name</th><td>{{ prospect.last_name }}</td></tr>
                                                    <tr><th>Source</th><td>{{ prospect.source }}</td></tr>
                                                    <tr><th>Address</th><td>{{ prospect.address }}</td></tr>
                                                    <tr><th>City</th><td>{{ prospect.city }}</td></tr>
                                                    <tr><th>Zipcode</th><td>{{ prospect.zip }}</td></tr>
                                                    <tr><th>State</th><td>{{ prospect.state }}</td></tr>
                                                    <tr><th>Country</th><td>{{ prospect.country }}</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-header hide" >
                                            <b class="card-title m-0 text-uppercase"> Employment</b>
                                        </div>
                                        <div class="card-body p-0 custom-auto-height">
                                            <table class="table table-bordered table-striped table-condensed m-0">
                                                <tbody>
                                                    <tr>
                                                        <th width="50%">Title </th> <td>{{ prospect.title }}</td></tr>
                                                    <tr>
                                                        <th>Company </th> <td>{{ prospect.company }}</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-6 col-sm-12 pl-1">
                                    <div class="card">
                                        <div class="card-header hide" >
                                            <b class="card-title m-0 text-uppercase"> Custom Fields</b>
                                        </div>
                                        <div class="card-body p-0 custom-height">
                                            <table class="table table-bordered table-striped table-condensed m-0">
                                                <tbody>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom1">
                                                        <th width="50%">Purchase Authorization</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom1)?prospect.customfields.custom1:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom2">
                                                        <th>Department</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom2)?prospect.customfields.custom2:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom3">
                                                        <th>Job Function</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom3)?prospect.customfields.custom3:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom4">
                                                        <th>Supplemental Email</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom4)?prospect.customfields.custom4:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom5">
                                                        <th>Company HQ Phone</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom5)?prospect.customfields.custom5:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom6">
                                                        <th>Education</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom6)?prospect.customfields.custom6:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom7">
                                                        <th>Employment History</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom7)?prospect.customfields.custom7:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom8">
                                                        <th>Interested In</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom8)?prospect.customfields.custom8:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom9">
                                                        <th>Industry</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom9)?prospect.customfields.custom9:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom10">
                                                        <th>Primary Industry</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom10)?prospect.customfields.custom10:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom11">
                                                        <th>Company Revenue</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom11)?prospect.customfields.custom11:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom12">
                                                        <th>Company Rev Range</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom12)?prospect.customfields.custom12:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom13">
                                                        <th>Marketing Budget</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom13)?prospect.customfields.custom13:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom18">
                                                        <th>ZoomInfo Contact ID</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom18)?prospect.customfields.custom18:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom19">
                                                        <th>ZoomInfo Accuracy</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom19)?prospect.customfields.custom19:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom20">
                                                        <th>ZoomInfo Score</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom20)?prospect.customfields.custom20:'-':'--' }}</td>
                                                    </tr>
                                                    <tr v-show="prospect.customfields && prospect.customfields.custom35">
                                                        <th>Custom 35</th>
                                                        <td>{{ (prospect.customfields)?(prospect.customfields.custom35)?prospect.customfields.custom35:'-':'--' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </b-tab>
                        <b-tab title="Calls" :active="active_view == 'call'" >
                                <div class="full-width text-center" v-if="callLoader">
                                    <div class="spinner-border" role="status">
                                    <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <!-- <div class="table-responsive"  v-if="!callLoader">
                                    <div class="synops">          
                                        <div class="inner-synop cursor-pointer" :class="[(callStageCounter == 'all')?'active':'']" @click="showAllCalls">
                                            <h4 class="number">{{ calls.length | freeNumber }} </h4><p>Total</p>
                                        </div>
                                        <div class="inner-synop cursor-pointer" v-for="(call, i) in callStage"  :key="'row-'+i" @click="showCallStage(call.id)" :class="[(callStageCounter == call.id)?'active':'']">
                                            <h4 class="number">{{ call.count | freeNumber }} </h4><p>{{ call.stage }}</p>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="table-responsive table-fit-content pt-2"  v-else>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Agent Name</th>
                                                <th>Campaign</th>
                                                <th>List Name</th>
                                                <th>Disposition</th>
                                                <th>Dnis</th>
                                                <th>Talk Time</th>
                                                <td>Timestamp</td>
                                            </tr>
                                        </thead>
                                        <tbody v-if="calls.length >= 1">
                                            <tr v-for="call in calls" :key="'call'+call.id">
                                                <td>{{ call.agent_name }}</td>
                                                <td>{{ call.campaign }}</td>
                                                <td>{{ call.list_name }}</td>
                                                <td>{{ call.disposition }}</td>
                                                <td>{{ call.dnis }}</td>
                                                <td>{{ call.talk_time }}</td>
                                                <td>{{ call.timestamp }}</td>
                                            </tr>
                                        </tbody>
                                        <tbody v-else>
                                            <tr>
                                                <td colspan="7">
                                                    No calls data found
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                        </b-tab>
                        <b-tab title="Emails" :active="active_view == 'email'">
                            <div class="full-width text-center" v-if="emailLoader">
                                <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="table-responsive"  v-if="!emailLoader">
                                <div class="synops border-bottom-4">          
                                    <div class="inner-synop cursor-pointer">
                                        <h4 class="number">{{ prospect.email_delivered | freeNumber }} </h4>
                                        <p>Total Delivered</p>
                                    </div>
                                    <div class="inner-synop cursor-pointer">
                                        <h4 class="number">{{ prospect.email_opened | freeNumber }} </h4>
                                        <p>Total Opened</p>
                                    </div>
                                    <div class="inner-synop cursor-pointer">
                                        <h4 class="number">{{ prospect.email_clicked | freeNumber }} </h4>
                                        <p>Total Clicked</p>
                                    </div>
                                    <div class="inner-synop cursor-pointer">
                                        <h4 class="number">{{ prospect.email_replied | freeNumber }} </h4>
                                        <p>Total Replied</p>
                                    </div>
                                    <div class="inner-synop cursor-pointer">
                                        <h4 class="number">{{ prospect.email_bounced | freeNumber }} </h4>
                                        <p>Total Bounced</p>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="table-responsive table-fit-content pt-2" v-if="!emailLoader">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th class="wf-80">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="email in allProspectEmailsData" :key="email.id">
                                            <td>
                                                <span class="text-secondary"></span> {{ email.name }}
                                            </td>
                                            <td>{{ email.subject }}</td>
                                            <td>
                                                <span v-if="email.delivered || email.state == 'delivered'" v-title="'deliverd at : ' +  email.delivered "><i class="bi bi-envelope"></i></span>
                                                <span v-if="email.state == 'opened'" v-title="'Opened at : ' + email.openedAt "><i class="bi bi-eye"></i></span>
                                                <!-- <i class="bi bi-link"></i> -->
                                            </td>
                                            <td>{{ email.date | date02 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </b-tab>
                    </b-tabs>         
                </div>
                <div class="col-md-3 pl-0  px-2 pt-4 pb-2">
                    <div class="right-section">
                        <div class="text-center">
                            <span class="img-title"> {{ prospect_title }} </span> 
                            <h2>{{ prospect.first_name }} {{prospect.last_name }}</h2>
                            <h6 class="text-muted">{{ prospect.company }}</h6>
                            <h6 class="text-muted">{{ prospect.designation }}</h6>
                            <p>
                                <a :href="'http://'+prospect.websiteUrl1" target="_blank">
                                    <i class="bi bi-link icon-icon text-dark"></i>
                                </a>
                                <a :href="prospect.linkedInUrl" target="_blank">
                                    <i class="bi bi-linkedin text-dark icon-icon"></i>
                                </a>
                            </p>
                        </div>
                        <div class="border-top py-2">
                            <b class="my-1">CONTACT</b> :<br>
                            <ul class="nostylelist p-0 m-0">
                                <li v-for="(bemail, bk) in bemails" :key="'be'+bk">
                                    <b class="icon-icon-2 mt-2" v-title="'Business Email'"> BE </b> <span>{{ bemail }}</span>
                                </li>
                                <li v-show="prospect.mobilePhones">
                                    <b class="icon-icon-2 mt-2" v-title="'Mobile Phone'"> MP </b> 
                                    <span>{{ prospect.mobilePhones }}</span>
                                </li>
                                <li v-show="prospect.homePhones">
                                    <b class="icon-icon-2 mt-2" v-title="'Home Phone'"> HP </b> 
                                    <span>{{ prospect.homePhones }}</span>
                                </li>
                                <li v-show="prospect.workPhones">
                                    <b class="icon-icon-2 mt-2" v-title="'Work Phone'"> WP </b> 
                                    <span>{{ prospect.workPhones }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="border-top pt-2">
                            <b class="mt-1 mb-2 d-block">TIME STACK : </b>
                            <p class="">
                                <span class="mr-1" v-if="prospect.outreach_touched_at" v-title="myDateFormat('Touched', prospect.outreach_touched_at)"><i class="text-icon">TD</i></span>
                                <span class="mr-1" v-if="prospect.outreach_created_at" v-title="myDateFormat('Created', prospect.outreach_created_at)"><i class="text-icon">CD</i></span>
                                <span v-if="prospect.last_agent_dispo_time" v-title="myDateFormat('Last Called ', prospect.last_agent_dispo_time)"><i class="text-icon">CT</i></span>
                            </p>
                        </div>
                        <div class="border-top pt-2">
                            <b class="mt-1 mb-2 d-block">LAST CALL ACTIVITY: </b>
                            <ul class="nostylelist p-0 m-0">
                                <li>
                                    <b class="icon-icon-2 mt-2" v-title="'Last Call by Agent'"> LA </b> <span>{{ prospect.last_agent }}</span>
                                </li>
                                <li>
                                    <b class="icon-icon-2 mt-2" v-title="'Last Disposition'"> LD </b> <span>{{ prospect.last_dispo }}</span>
                                </li>
                                <li>
                                    <b class="icon-icon-2 mt-2" v-title="myDateFormat('Last Called ', prospect.last_agent_dispo_time)"> LC </b> <span>{{ prospect.last_agent_dispo_time | logdateYearFull }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

export default {
    
    data() {
        return {
            callStageCounter : 'all',
            emailLoader : true,
            callLoader : true,
            prospect: {},
            ownername: null,
            accountname: null,
            stagename: null,
            personaname: null,
            calls:[],
            emailsData : [],
            emails:[],
            emailState : {'drafted' : 0, 'scheduled' : 0 , 'delivering' : 0, 'delivered' : 0, 'unopened' : 0, 'opened' : 0, 'clicked' : 0, 'replied' : 0, 'positive-reply' : 0, 'failed' : 0, 'bounced' : 0, 'opted-out' : 0},
            allProspectEmailsData : [],
            events:[],
            callDispositions:[],
            callPurposes:[],
            callCounter : [],
            dispositions : [],
            dispositionsKeyArray : [],
            callCounter : [],
            callStage : {},
            callCounted : 0,
            users : [],
            active_view:'overview'
        }
    },
    computed: {
        bemails() {
            if(this.prospect.emails) {
                let be =  this.prospect.emails;
                be = be.split(',');
                return be;
            } else {
                return [];
            }
        },
        prospect_title() {
            if(this.prospect.first_name || this.prospect.last_name) {
                let ifl = '';
                ifl = this.prospect.first_name.substr(0, 1)+this.prospect.last_name.substr(0, 1);
                return ifl;
            } else {
                return 'NN';
            }
        }
    },
    methods: {
        myDateFormat(txt, val) {
            return txt+' '+this.$options.filters.convertInDayMonth(val)+' ago';
        },
        getOutreach() {
            let path = this.$route.params.id;
            this.$Progress.start();
            axios.get('/api/get-outreach-prospect-details/'+path).then((response) => {
                this.prospect = response.data.results;
                this.$Progress.finish();
            });
        },
        getCallsInfo(){
            let id = this.$route.params.id;
            axios.get('/api/get-outreach-prospect-details-calls/'+ id).then((response) => {
                this.calls = response.data.details.reverse();
                this.callLoader = false;
            });
        },
        getEmailInfo(){
            let id = this.$route.params.id;
            axios.get('/api/get-outreach-prospect-details-emails/'+ id).then((response) => {
                this.emails = response.data.details.data
                var included = response.data.details.included
                var emails = this.emails
                var emailsData = []
                for(var i = 0; i < emails.length; i++){
                    var state = emails[i]['attributes']['state'] 
                     if(emails[i]['attributes']['deliveredAt']){
                        this.emailState['delivered'] = this.emailState['delivered'] + 1 
                    }              
                    var name = ''
                    var prospectiD = emails[i]['relationships']['prospect']['data']['id']
                    for(const key in this.users){
                        if(emails[i]['attributes']['mailboxAddress'] == this.users[key]["email"]){
                            name = this.users[key]["name"]
                            break
                        }
                    }
                    for(const key in this.emailState){
                        if((key == state) && (state != 'delivered')){
                            this.emailState[key] = this.emailState[key] + 1
                        }
                    }
                    
                    emailsData[i] = {
                        "id" : emails[i]['id'],
                        "subject" : emails[i]['attributes']['subject'],
                        "prospectiD" : prospectiD,
                        "name" : name,
                        "date" : emails[i]['attributes']['createdAt'],
                        "mailboxAddress" : emails[i]['attributes']['mailboxAddress'],
                        "delivered" : emails[i]['attributes']['deliveredAt'],
                        "state" : emails[i]['attributes']['state'],
                        "openedAt" : emails[i]['attributes']['openedAt'],
                    }
                }
                this.allProspectEmailsData = emailsData
                this.emailLoader = false
            });
        },
        getCallDispositions(){
            axios.get('/api/get-call-dispositions').then((response) => {
                this.dispositions = response.data.details.data;
                this.dispositions.map((element) => {
                    this.dispositionsKeyArray[element.id] = 0
                })
            });
        }, 
        showCallStage(dis){
            console.log(dis)
            if(!dis){
                dis = 10
            }
            this.callStageCounter =  dis
            $(".call-dispo").css("display", "none")
            $(".call-dispo-" + dis).css("display", "table-row")
        },
        showAllCalls(){
            this.callStageCounter =  'all'
            $(".call-dispo").css("display", "table-row")
        }
    },
    beforeMount() {
        if(this.$route.query.hasOwnProperty('view')) {
            this.active_view = this.$route.query.view;
        }
    },
    created() {
        this.getOutreach();
    },
    mounted() {
        this.getCallsInfo();
        this.getEmailInfo();
        this.getCallDispositions();
        // this.getCallPurpose();
    }
}
</script>
<style>
.nostylelist{
    list-style: none;
}
</style>