<template>
    <div>
         <div class="row">
            <div class="col-md-9 pl-0 bg-white px-2 py2">
                <div class="px-3">
                    <b-tabs content-class="mt-3">
                        <b-tab title="OverView" active  class="px-2">
                            <div class="row">      
                                <div class="col-md-6 col-sm-12">
                                    <div class="card account-custom">
                                        <div class="card-header hide" >Overview</div>
                                        <div class="card-body" id="demo2">                                             
                                            <p>OWNER : {{ accountInfo.owner }}</p>                                            
                                            <p> TAGS :  {{ accountInfo.tags }}</p>                                            
                                            <p> ABOUT : {{ accountInfo.description }}</p>                                            
                                            <p>Industry : {{ accountInfo.industry }}</p>
                                        </div>
                                    </div>
                                </div>                      
                                <div class="col-md-6 col-sm-12">
                                    <div class="card account-custom">
                                        <div class="card-header hide" >Account Custom Fields</div>
                                        <div class="card-body" id="demo2">
                                            <p>Business Volume : {{ accountInfo.custom3 }}</p>
                                            <p>Purchase Timeframe : {{ accountInfo.custom1 }}</p>
                                            <p>Purchase Authorization : {{ accountInfo.custom2 }}</p>
                                            <p>Department : {{ accountInfo.custom4 }}</p>
                                            <p>SkypeId : {{ accountInfo.custom5 }}</p>
                                            <p v-for="item in items" :key="item">
                                                <span v-if="accountInfo['custom'+item]"> Custom{{ item }} : {{ accountInfo["custom"+item]  }} </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>                           
                            
                        </b-tab>
                        <b-tab title="Prospects">
                            <div class="full-width text-center" v-if="prospectLoader">
                                <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="row" v-if="!prospectLoader">>
                                <div class="col-md-12 col-sm-12">
                                    <div class="card  account-custom">
                                        <div class="card-header hide">                                            
                                            <span class="pull-left">Account Prospects</span>    
                                        </div>
                                        
                                        <div class="card-body" id="demo1">
                                            <div class="table-responsive">
                                                <div class="synops">          
                                                    <div class="inner-synop cursor-pointer" :class="[(activeProspectStage == 'all')?'active':'']" @click="showAllProspect">
                                                        <h4 class="number">{{ accountInfo.prospects.length | freeNumber }} </h4><p>Total</p>
                                                    </div>
                                                    <div class="inner-synop cursor-pointer" v-for="(stageDetail, i) in stageDetails"  :key="'row-'+i" :class="[(stageDetail.oid == activeProspectStage)?'active':'']" @click="showProspect(stageDetail.oid)">
                                                        <h4 class="number">{{ stageDetail.count | freeNumber }} </h4><p>{{ stageDetail.stage }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-stripped">
                                                    <tbody >
                                                        <tr v-for="prospect in prospectsWithCompleteTask" :key="prospect.record_id" :class="'row-prospect row-prospect-' + prospect.stage">
                                                            <td> 
                                                                <router-link :to="'/prospects/' + prospect.record_id" class="">{{ prospect.first_name }}  {{ prospect.last_name }}</router-link> </td>
                                                            <td>
                                                                <span v-if="prospect.stage_data" :class="prospect.stage_data.css">
                                                                    {{ prospect.stage_data.stage }}
                                                                </span>
                                                                <span class="" v-title="prospect.designation" v-if="prospect.designation">{{ prospect.designation }}  in </span> 
                                                                <span class=" border border-light rounded px-2" v-title="prospect.company" v-if="prospect.company">{{ prospect.company }}</span>
                                                            </td>
                                                            <td>
                                                                <span v-if="prospect.emails" v-title="prospect.emails"><i class="bi bi-envelope"></i> </span>
                                                                <span v-if="prospect.mobilePhones" v-title="prospect.mobilePhones"><i class="bi bi-phone"></i> </span>
                                                                <span v-if="prospect.tasks_not_complete.length > 0" v-title="prospect.tasks_not_complete.length + ' Active Task'"><i class="bi bi-bookmark-check"></i> </span>
                                                                <!-- <span v-if="prospect.emails" v-title="prospect.emails"><i class="bi bi-reply"></i> </span> -->
                                                                
                                                            </td>
                                                            <td>
                                                                {{ accountInfo.createdAt | convertInDayMonth }}
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                    
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </b-tab>
                        <!-- <b-tab title="Sequences">

                        </b-tab>
                        <b-tab title="Tasks">

                        </b-tab>
                        <b-tab title="Opprotunities">

                        </b-tab> -->
                        <b-tab title="Emails">
                            <div class="loader text-center" v-if="emailLoader">
                                <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="row" v-if="!emailLoader">
                                <div class="table-responsive">
                                    <div class="synops">          
                                        <div class="inner-synop cursor-pointer">
                                            <h4 class="number">{{ allProspectEmailsData.length | freeNumber }} </h4><p>Total</p>
                                        </div>
                                        <div class="inner-synop cursor-pointer" v-for="(stageDetail, i) in emailState"  :key="'row-'+i">
                                            <h4 class="number">{{ stageDetail | freeNumber }} </h4><p>{{ i.charAt(0).toUpperCase() + i.slice(1) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="card  account-custom">
                                        <div class="card-header hide">                                            
                                            <span class="pull-left">Account Emails</span>    
                                        </div>                                        
                                        <div class="card-body" id="demo3">
                                            <div class="table-responsive">
                                                <table class="table table-stripped">
                                                    <tbody>
                                                        <tr v-for="(email, index) in allProspectEmailsData" :key="email.id">
                                                            <td>
                                                                <span class="text-secondary">To:</span> {{ email.name }}
                                                            </td>
                                                            <td>{{ email.subject }}</td>
                                                            <td>
                                                                <span v-title="email.mailboxAddress"><i class="bi bi-envelope"></i></span>
                                                            </td>
                                                            <td>{{ email.date | date02 }}</td>

                                                        </tr>
                                                    </tbody>
                                                    
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab title="Calls">
                            <div class="loader text-center" v-if="callLoader">
                                <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="row" v-if="!callLoader">
                                <div class="col-md-12 col-sm-12">
                                    <div class="card  account-custom">                                        
                                        <div class="card-header hide">                                            
                                            <span class="pull-left">Account Calls</span>    
                                        </div>                                        
                                        <div class="card-body" id="demo5">
                                            <div class="table-responsive">
                                                <div class="synops">          
                                                    <div class="inner-synop cursor-pointer" :class="[(activeCallDispo == 'all')?'active':'']" @click="showAllCalls">
                                                        <h4 class="number">{{ totalCalls | freeNumber }} </h4><p>Total</p>
                                                    </div>
                                                    <div class="inner-synop cursor-pointer" v-for="(stageDetail, i) in callDispo" :key="'row-' + stageDetail.id" @click="showCallByCallDisposition(stageDetail.id)" :class="[(activeCallDispo == stageDetail.id)?'active':'']">
                                                        <h4 class="number">{{ stageDetail.count | freeNumber }} </h4><p>{{ stageDetail.name }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-stripped">
                                                    <tbody >
                                                        <tr v-for="call in overviewCalls" :key="call.id" :class="'call-dispo call-dispo-' + call.callDispositionId">
                                                            <td>
                                                                <span v-if="call.direction == 'inbound'"><i class="bi bi-arrow-down-left"></i></span>
                                                                <span v-if="call.direction == 'outbound'"><i class="bi bi-arrow-up-right"></i></span>
                                                                {{ call.name }}
                                                            </td>
                                                            <td>
                                                                <!-- <span v-if="call.state != 'canceled'"> -->
                                                                <span class="bg-secondary  text-white rounded-sm py-1 px-2  mx-2" v-if="call.callPurpose != ''">{{ call.callPurpose }}</span>
                                                                <span class="bg-success  text-white rounded-sm py-1 px-2 mx-2" v-if="call.callDisposition != ''">{{ call.callDisposition }} </span>
                                                                <!-- </span> -->
                                                            </td>
                                                            <td>
                                                                {{ call.answeredAt | date03(call.completedAt) }}
                                                            </td>
                                                            <td>
                                                                <span class="bg-primary text-white rounded-sm py-1 px-2  mx-2">
                                                                {{  call.state.charAt(0).toUpperCase() + call.state.slice(1) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <!-- <i class="bi bi-lightning"></i> -->
                                                                <span v-if="call.recordingUrl">
                                                                    <a :href="call.recordingUrl" target="_blank"><i class="bi bi-caret-right-square-fill"></i></a>
                                                                </span>

                                                                <span v-if="call.user.length > 0" v-title="call.user + '(' + call.from + ')'">
                                                                    <i class="bi bi-person-circle"></i>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                {{ call.createdAt | setusdateSlash }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <!-- <b-tab title="Meetings">

                        </b-tab>
                        <b-tab title="Activity">

                        </b-tab> -->
                    </b-tabs>
                </div>                
            </div>
            <div class="col-md-3 p-3">
                <div class="text-center">
                    
                    <img :src="accountImage" class="rounded-circle" alt="Cinque Terre" style="height:70px;"> 
                    <h3>{{ accountInfo.name }}</h3>
                    <p>@{{ accountInfo.websiteUrl }}</p>
                    <p>
                        <a :href="'http://'+accountInfo.websiteUrl" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link" viewBox="0 0 16 16">
                                <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9c-.086 0-.17.01-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                                <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4.02 4.02 0 0 1-.82 1H12a3 3 0 1 0 0-6H9z"/>
                            </svg>
                        </a>
                        <a :href="accountInfo.linkedInUrl" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16">
                                <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/>
                            </svg>
                        </a>
                    </p>
                </div>
                OVERVIEW:
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                    </svg> <router-link :to="'/accounts/' + accountId" class="">{{ accountInfo.prospects.length }} Prospects</router-link></p>
                
                <p> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stopwatch" viewBox="0 0 16 16">
                        <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5V5.6z"/>
                        <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64a.715.715 0 0 1 .012-.013l.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354a.512.512 0 0 1-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5zM8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3z"/>
                    </svg> Last contacted :  {{ accountInfo.touchedAt | convertInDayMonth}}</p>
                

            </div>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import downloadExcel from "vue-json-excel";
import 'vue-select/dist/vue-select.css';

export default {
    components: { DateRangePicker, ToggleButton, downloadExcel},
    data() {
        return {
            callCounted : 0,
            emailState : {'drafted' : 0, 'scheduled' : 0 , 'delivering' : 0, 'delivered' : 0, 'unopened' : 0, 'opened' : 0, 'clicked' : 0, 'replied' : 0, 'positive-reply' : 0, 'failed' : 0, 'bounced' : 0, 'opted-out' : 0},
            callLoader : true,
            emailLoader : true,
            activeProspectStage : 'all',
            prospectsWithCompleteTask : [],
            activeCallDispo : 'all',
            prospectLoader : true,
            totalCalls : 0,
            callDispo : {},
            overviewCalls : [],
            items : [6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36],
            accountInfo : [],
            accountId : '',
            accountImage : '/img/account.png',
            filter_expand:true,
            stageDetails:[],
            totalNumberOfRecords : 0,
            loader:false,
            view:'list',
            step:0,
            page:{page:1,start:1,end:1,count:1,total:1,pager:1},
            paginationArray: {},
            records:{},
            start : 0,
            end : 0,
            totalRecords : 0,
            allProspects:{},
            active_row:{
                id:'',
                type:''
            },
            form: new Form({
                account_id : '',
                sortType:'outreach_touched_at',
                sortBy:'desc',
                dateRange:{},
                recordPerPage:10,
                outreach:1,
                activecampaign:0,
                five9:0,
                page:1,
                stage:'all',
                textSearch:'',
                filter:'',
                filterType:'',
                dropdown: '',
                filterOption:'is',
                filterText:'',
                filterConditionsArray : [],
                viewName : '',
                sharing : 'private',
            }),
            sform: new Form({
                template_id:'',
                source:'',
                destination:'five9',
                fdata:[],
                report:'',
                fields:['record_id','firstName','lastName','email','mobilePhones','workPhones','homePhones','stage','state','address','outreach_tag','zip','city','company'],
                efields:[],
                records:'',
                name:'',
                campaign:'',
                lid:'',
                cid:''
            }),
            export_data:[],
            outreach:'outreach.png',
            five9:'five9-light.png',
            activecampaign:'activecampaign2.png',
            all:'round.png',
            calls:{},
            emails:{},
            callDispositions:{},
            callPurposes:{},
            recordContainer:[],
            emailDelivered:0,
            emailOpened:0,
            emailClicked:0,
            emailReplied:0,
            owner : 0,
            options: [],
            climit:'',
            templates:{},
            export_data:[],
            channel_name: '',
            channel_fields: [],
            channel_entries: [],
            five9_list:'',
            five9_campaigns:[],
            report:'',
            loader_url: '/img/spinner.gif',
            testing:[],
            groupedRecords:[],
            showDuplicate:true,
            showUnique:true,
            duplicateRecordCounter:0,
            uniqueRecordCounter:0,
            activities: [],
            mailingList:[],
            activityMeta:'',
            mapped_ranges:[],
            json_fields: {
                "Record Id" : "record_id",
                "First Name": "first_name",
                "Last Name": "last_name",
                "Number 1" : "mobilePhones",
                "Number 2" : "workPhones",
                "Number 3" : "homePhones",
                Company : "company",
                Street : "address",
                City : "city",
                State : "state",
                Zip : "zip",
                Emails: "emails",
                "Personal Notes" : "personal_note",
                "Dial Attempts" : "dial_attempts",
                "Last Agent" : "last_agent",
                "Last Agent Dispo Timestamp" : "last_agent_dispo_time",
                "Last Dispo" : "last_dispo",
                Tag : "outreach_tag",
                Stage : "stage"
            },
            json_data: [],
            json_meta: [
                [
                    {
                    key: "charset",
                    value: "utf-8",
                    },
                ],
            ],
            filename: '',
            reports : {
                header : {
                    values : {}
                }
            },
            paginationRecordPerPage:20,
            filter : false,
            filterInput : false,
            filterDropdown : false,
            filterDateRange : false,
            filterItems : [],
            filterOptions : [],
            selects : [],
            filterConditionsObject : {},            
            filterConditionsArrayOld : [],
            filterBtn : true,
            views : [],
            view : '',
            showView : false, //control appearance of view controls
            addView:false,
            selectedOptions : [],
            selectedOptionsId : [],
            showrecords:0,
            unique_Records: [],
            duplicate_Records: [],
            groupedRecords : [],
            groupKey:[],
            filterItemsIds : [],
            //
            allProspectIds : [],
            allProspectEmails : [],
            allProspectEmailsData : [],
            users: [],
            userInfomations : [],
        }
    },
    filters: {
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        },
        titleFormat: function (str) {
            let nstr = str.charAt(0).toUpperCase() + str.slice(1);
            return nstr.replace('_', ' ');
        },
        setTime: function(params) {
             return params;
        } ,
        phoneFormatted: function (str) {
            if(str != null && str != 0 && str != 'undefined' && str != '') {
                let fst_str = str.substr(0, 1);
                const regex = / /gi;
                str = str.replace(regex, '');
                if(fst_str == '+') {
                    str = str.substr(1, str.length-1);
                }
                fst_str = str.substr(0, 1);
                if(fst_str == 1) {
                    str = str.substr(1, str.length-1);
                }
                str = str.substr(0, 10);
                if(str.length != 10) {
                    return 0;
                } else {
                    return parseInt(str);
                }
            } else {
                return 0;
            }
        },
    },
    computed: {
        ac_options() {
            let acf = this.$store.getters.currentConfig.activecampaign_fields;
            return acf.split(',');
        },
        or_options() {
            let acf = this.$store.getters.currentConfig.outreach_fields;
            return acf.split(',');
        },
        f9_options() {
            let acf = this.$store.getters.currentConfig.five9_fields;
            return acf.split(',');
        },
        five9_ocampaigns () {
            return this.five9_campaigns.filter((cam) => { return cam.type == 'OUTBOUND' });
        },
        drcount() {
            return this.sform.fdata.length - this.unique_Records.length;
        }
    },
    methods: {
        removeSelectedOption(index){
            this.selectedOptions.splice(index,1)
            this.selectedOptionsId.splice(index,1)
        },
        getSelectedOptions(){            
            var id = this.form.dropdown
            var data = this.selects.filter(function(e){
                return e.oid == id
            })
            if(this.selectedOptions.indexOf(data[0].stage) == -1){
                this.selectedOptions.push(data[0].stage)
                this.selectedOptionsId.push(data[0].oid)
            }
        },
        selectView(){
            this.filter = false
            if(this.view == null){
                this.form.sortType = 'outreach_touched_at';
                this.form.sortBy = 'asc';
                this.form.dateRange = {};
                this.form.recordPerPage = 20;
                this.form.outreach = 1;
                this.form.activecampaign = 0;
                this.form.five9 = 0;
                this.form.page = 1;
                this.form.stage = 'all';
                this.form.textSearch = '';
                this.form.filter = '';
                this.form.filterType = '';
                this.form.dropdown = ''
                this.form.filterOption = 'is';
                this.form.filterText = '';
                this.form.filterConditionsArray = [];
                this.form.viewName = '';
            }else{
                var view = this.view.id
                var currentView = this.views.filter(function(e){
                    return e.id == view
                });
                var obj = JSON.parse(currentView[0].view_data);
                this.form.sortType = obj.sortType
                this.form.sortBy = obj.sortBy
                this.form.dateRange = {}
                this.form.recordPerPage = obj.recordPerPage
                this.form.outreach = obj.outreach
                this.form.activecampaign = obj.activecampaign
                this.form.five9 = obj.five9
                this.form.page = 1
                this.form.stage = obj.stage
                this.form.textSearch = obj.textSearch
                this.form.filter = obj.filter
                this.form.filterType = obj.filterType
                this.form.dropdown = obj.dropdown
                this.form.filterOption = obj.filterOption
                this.form.filterText = obj.filterText
                this.form.filterConditionsArray = obj.filterConditionsArray
            }
            var items = this.form.filterConditionsArray.filter(function(e) {
                return e
            }).map(function(e){
                return e.condition
            })
            this.filterItemsIds = this.filterItemsAll.filter(function(e){
                if(items.indexOf(e.filter_key) !== -1){
                    return e
                }
            }).map(function(e){
                return e.id
            })
            var filterItemsIds = this.filterItemsIds
            this.filterItems = this.filterItems.filter(function(e){
                if(filterItemsIds.indexOf(e.id) !== -1){
                    return e
                }
            })
            this.getOutreachData(1)
        },
        showViewPopup() {
            $('#addView').modal('show');
        },
        saveView(){
            if(this.form.viewName == ''){
                this.$toasted.show("Please enter name!!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
            } else {
                this.form.post('/api/save-view').then((response) => {
                    this.$toasted.show("View saved successfully!!", { 
                        theme: "bubble", 
                        position: "bottom-center", 
                        duration : 2000
                    });
                    this.getallview();
                    $('#addView').modal('hide');
                    this.form.viewName = '';
                    this.showView = false;
                    this.view = response.data.results;
                    
                })
            }
        },
        removeFilter(index){
            this.form.filterConditionsArray.splice(index, 1)
            this.getOutreachData(1);
        },
        updateFilter(){            
            for(const i in this.form.filterConditionsArray){
                if(this.form.filterConditionsArray[i]["condition"] == this.form.filter){
                    if(this.form.filterConditionsArray[i]["type"] == "textbox"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.form.filterText

                    }
                    if(this.form.filterConditionsArray[i]["type"] == "dropdown"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.selectedOptionsId.join(',')            
                        this.form.filterConditionsArray[i]['textConditionLabel'] = this.form.filter +' '+ this.form.filterOption +' '+ this.selectedOptions.join(', ')
                    }
                    if(this.form.filterConditionsArray[i]["type"] == "calendar"){

                        this.form.filterConditionsArray[i]["formula"] = this.form.filterOption
                        this.form.filterConditionsArray[i]["textCondition"] = this.form.filterText
                        var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                        var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                        this.form.filterConditionsArray[i]['textCondition'] = startDate+'--'+endDate
                        this.form.filterConditionsArray[i]['textConditionLabel'] = startDate.substring(0,10)                        
                    }
                    this.form.filterText = ''
                    this.form.filter = ''
                    this.form.dropdown = ''
                    this.filter = false
                    this.filterInput = false
                    this.filterDropdown = false
                    this.filterDateRange = false
                    this.filterBtn = true
                    this.getOutreachData(1)
                }
            }
        },
        showFilterDetails(filter, index){
            if(filter.type == 'textbox'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filter = true
                this.filterInput = true
                this.filterDropdown = false
                this.filterDateRange = false
            }
            if(filter.type == 'dropdown'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.dropdown = filter.textCondition
                this.filter = true
                this.filterInput = false
                this.filterDropdown = true
                this.filterDateRange = false
                let api = filter.api
                axios.get(api).then((response) => {
                    this.selects = response.data.results;
                });
                this.selectedOptions = []
                this.selectedOptionsId = []
                var newRecords = filter.textCondition.split(",")
                for(var i = 0; i <= newRecords.length; i++){
                    var newStage = this.selects.filter(function(e){
                        return e.oid == newRecords[i]
                    })
                    if(newStage.length > 0){
                        this.selectedOptionsId.push(newRecords[i])
                        this.selectedOptions.push(newStage[0].stage)
                    }
                }
            }
            if(filter.type == 'calendar'){
                this.form.filter = filter.condition
                this.form.filterOption = filter.formula
                this.form.filterText = filter.textCondition
                this.filter = true
                this.filterInput = false
                this.filterDropdown = false
                this.filterDateRange = true
            }
            this.filterBtn = false            
        },
        createFilter(){           
            if(this.form.filter.filter_type == 'textbox'){
                var textCondition = this.form.filterText  
                var textConditionLabel =this.form.filter.filter +' '+ this.form.filterOption +' '+ this.form.filterText
                var api = '';
            }
            if(this.form.filter.filter_type == 'calendar'){
                    var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                    var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                    var textCondition = startDate+'--'+endDate
                    var textConditionLabel = this.form.filter.filter +' '+ startDate.substring(0,10)+ ' to ' + endDate.substring(0,10)
                    var api = '';
            }
            if(this.form.filter.filter_type == 'dropdown'){

                var formdropdown = this.form.dropdown
                var textCondition = this.selectedOptionsId.join(',')
                var textConditionLabel = this.form.filter.filter +' '+ this.form.filterOption +' ' + this.selectedOptions.join(', ')
                var api = this.form.filter.api
            }
            this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                'type' : this.form.filter.filter_type,
                'condition' : this.form.filter.filter_key,
                'conditionText' : this.form.filter.filter,
                'formula' : this.form.filterOption,
                'textCondition' : textCondition ,
                'textConditionLabel' : textConditionLabel,
                'api' : api,
            }   
            this.filterItemsIds[this.filterItemsIds.length] = this.form.filter.id                 
            this.form.filterText = ''
            this.form.filter = ''
            this.form.dropdown = ''
            this.filter = false
            this.filterInput = false
            this.filterDropdown = false
            this.filterDateRange = false
            this.getOutreachData(1)
        },
        showFilterOption(){
            if(this.form.filter == null){
                this.filterInput = false;
                this.filterDropdown = false;
                this.filterDateRange = false;
            }else{
                this.filterOptions = this.form.filter.filter_option.split(',')
                if(this.form.filter.filter_type == 'textbox'){
                    this.filterInput = true;
                    this.filterDropdown = false;
                    this.filterDateRange = false;
                    var oldData = this.form.filterConditionsArray.filter(function(e){
                        return ((e.type == filterType) && (e.condition == filterKey))
                    })
                    if(oldData.length > 0){                        
                        this.showFilterDetails(oldData[0], 1)
                    }
                }
                if(this.form.filter.filter_type == 'calendar'){
                    this.filterInput = false;
                    this.filterDropdown = false;
                    this.filterDateRange = true;
                }
                if(this.form.filter.filter_type == 'dropdown'){                    
                    var filterType = this.form.filter.filter_type
                    var filterKey = this.form.filter.filter_key
                    var oldData = this.form.filterConditionsArray.filter(function(e){
                        return ((e.type == filterType) && (e.condition == filterKey))
                    })
                    if(oldData.length > 0){                        
                        this.showFilterDetails(oldData[0], 1)
                    }
                    this.selects = []
                    this.filterInput = false;
                    this.filterDropdown = true;
                    this.filterDateRange = false;
                    let api = this.form.filter.api
                    axios.get(api).then((response) => {
                        this.selects = response.data.results;
                    });
                }
            }
        },
        showFilter(){
            axios.get('/api/get-all-filter').then((response) => {
                this.filterItems = response.data.items
                this.filterItemsAll = response.data.items
                this.filter = true
                var filterItemsIds = this.filterItemsIds
                //remove filter item form filter-item array
                if(filterItemsIds.length > 0){
                    this.filterItems = this.filterItems.filter(function(e) {
                        if(filterItemsIds.indexOf(e.id) == -1){
                            return e
                        }
                    })
                }
            });
        },
        reset () {
            this.item = {}
        },
        selectFromParentComponent1 () {
            // select option from parent component
            this.item = this.options[0]
        },
        filterStage(sid) { return false
            if(sid == 'all') {
                this.form.stage = 'all';
                var data = this.form.filterConditionsArray.filter(function(e){
                    return e.condition != "stage"
                })
                this.form.filterConditionsArray = []
                this.form.filterConditionsArray = data;
                this.getFilterData();
            }
            else {
                this.form.stage = sid;
                var stage = this.stageDetails.filter(function(e){
                return e.oid == sid
                })
                axios.get('/api/get-all-filter').then((response) => {
                    this.filterItems = response.data.items
                    var stageDetail = this.filterItems.filter(function(e){
                        return e.filter_key == "stage"
                    })
                    if(this.form.filterConditionsArray.length == 0){

                    } else {
                        var data = this.form.filterConditionsArray.filter(function(e){
                            return e.condition != "stage"
                        })
                        this.form.filterConditionsArray = []
                        this.form.filterConditionsArray = data
                    }
                    this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                        'type' : stageDetail[0].filter_type,
                        'condition' : stageDetail[0].filter_key,
                        'conditionText' : stageDetail[0].filter,
                        'formula' : "is",
                        'textCondition' : sid ,
                        'textConditionLabel' : stageDetail[0].filter +' is '+ stage[0].stage,
                        'api' : stageDetail[0].api
                    }     
                    this.getFilterData();
                });
            }
        },
        filterTag(filed, fieldName, formula) { // like tag, outrech_tag, is contain
            axios.get('/api/get-all-filter').then((response) => {
                this.filterItems = response.data.items
                var fieldDetail = this.filterItems.filter(function(e){
                    return e.filter_key == fieldName
                })
                if(this.form.filterConditionsArray.length == 0){

                } else {
                    var data = this.form.filterConditionsArray.filter(function(e){
                        return e.condition != fieldName
                    })
                    this.form.filterConditionsArray = []
                    this.form.filterConditionsArray = data
                }
                this.form.filterConditionsArray[this.form.filterConditionsArray.length] = {
                    'type' : fieldDetail[0].filter_type,
                    'condition' : fieldDetail[0].filter_key,
                    'conditionText' : fieldDetail[0].filter,
                    'formula' : formula,
                    'textCondition' :  filed,
                    'textConditionLabel' : fieldDetail[0].filter +' '+ formula +' '+ filed,
                    'api' : fieldDetail[0].api
                }     
                this.getFilterData(1);
            });
        },        
        pageReset() {
            window.location.reload();
        },
        StartExport() {
            this.loader = true;
            this.step = 1;
            this.sform.destination = 'five9';
            axios.get('/api/get-f9-list').then((response) => {
                this.five9_list = response.data;
            });
            axios.get('/api/get-f9-campaigns').then((response) => {
                this.five9_campaigns = response.data;
            });
            let opts = this.f9_options;
            let vm = this;
            opts.forEach(function (key) {
                vm.sform.efields.push(key);
            });
            //this.sform.records = this.recordContainer;
            this.makingTestRecords();
            this.makingGroupedRecords();
        },
        makingTestRecords(){
            for(var i = 0; i < this.recordContainer.length; i++){    
                
                let mobilePhones = 0;
                if(this.allProspects[this.recordContainer[i]].mobilePhones){
                    mobilePhones = this.allProspects[this.recordContainer[i]].mobilePhones;                   
                    mobilePhones = mobilePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    mobilePhones = mobilePhones.replace("-", '')
                    mobilePhones = mobilePhones.substring(0,10)
                    if(mobilePhones.length == 10){
                        mobilePhones = parseInt(mobilePhones)
                    }else{
                        mobilePhones = 0;
                    }
                }
                let workPhones = 0;
                if(this.allProspects[this.recordContainer[i]].workPhones){
                    workPhones = this.allProspects[this.recordContainer[i]].workPhones;                   
                    workPhones = workPhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    workPhones = workPhones.replace("-", '')
                    workPhones = workPhones.substring(0,10)
                    if(workPhones.length == 10){
                        workPhones = parseInt(workPhones)
                    }else{
                        workPhones = 0;
                    }
                }
                let homePhones = 0;
                if(this.allProspects[this.recordContainer[i]].homePhones){
                    homePhones = this.allProspects[this.recordContainer[i]].homePhones;                   
                    homePhones = homePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    homePhones = homePhones.replace("-", '')
                    homePhones = homePhones.substring(0,10)
                    if(homePhones.length == 10){
                        homePhones = parseInt(homePhones)
                    }else{
                        homePhones = 0;
                    }
                }
                
                
                if(this.allProspects[this.recordContainer[i]].first_name){
                    var firstName = this.allProspects[this.recordContainer[i]].first_name;
                }else{
                    var firstName = null;
                }
                if(this.allProspects[this.recordContainer[i]].last_name){
                    var lastName = this.allProspects[this.recordContainer[i]].last_name;
                }else{
                    var lastName = null;
                }
                
                if(this.allProspects[this.recordContainer[i]].state){
                    var addressState = this.allProspects[this.recordContainer[i]].state;
                }else{
                    var addressState = null;
                }
                if(this.allProspects[this.recordContainer[i]].address){
                    var addressStreet = this.allProspects[this.recordContainer[i]].address;
                    addressStreet = addressStreet.replace(/[,]/g, "")
                }else{
                    var addressStreet = null;
                }
                if(this.allProspects[this.recordContainer[i]].outreach_tag){
                    
                    var tags = this.allProspects[this.recordContainer[i]].outreach_tag;
                }else{
                    var tags = null;
                }
                if(this.allProspects[this.recordContainer[i]].zip){
                    var addressZip = this.allProspects[this.recordContainer[i]].zip;
                }else{
                    var addressZip = null;
                }
                if(this.allProspects[this.recordContainer[i]].city){
                    var addressCity = this.allProspects[this.recordContainer[i]].city;
                }else{
                    var addressCity = null;
                }
                if(this.allProspects[this.recordContainer[i]].company){
                    var company = this.allProspects[this.recordContainer[i]].company;
                }else{
                    var company = null;
                }
                if(this.allProspects[this.recordContainer[i]].record_id){
                    var record_id = this.allProspects[this.recordContainer[i]].record_id;
                }else{
                    var record_id = null;
                }
                let emails = null;
                if(this.allProspects[this.recordContainer[i]].emails){
                    emails = this.allProspects[this.recordContainer[i]].emails
                    if(emails.search(',') > 0){                         
                        emails = emails.substring(0,emails.search(','));
                    } else {
                        emails = this.allProspects[this.recordContainer[i]].emails
                    }
                }
                if(this.allProspects[this.recordContainer[i]].stage_data){
                    var stage = this.allProspects[this.recordContainer[i]].stage_data.stage;
                }else{
                    var stage = null;
                }              
                //['first_name','last_name','email','number1','number2','number3','Stage','state','street','tag','zip','city','company'],
                let n1, n2, n3;
                if(mobilePhones == 0 && workPhones != 0  && homePhones != 0) {
                    n1 = workPhones;
                    n2= homePhones;
                    n3 = 0;
                } 
                else if(mobilePhones == 0 && workPhones == 0 && homePhones != 0) {
                    n1 = homePhones;
                    n2= 0;
                    n3 = 0;
                } 
                else if(mobilePhones == 0 && workPhones != 0 && homePhones == 0) {
                    n1 = workPhones;
                    n2= 0;
                    n3 = 0;
                } 
                else {
                    n1 = mobilePhones;
                    n2= workPhones;
                    n3 = homePhones;
                }
                this.sform.fdata[i] = {
                    "record_id" : record_id,
                    "first_name" : firstName,
                    "last_name" : lastName,
                    "number1" : n1,
                    "number2" : n2,
                    "number3" : n3,
                    "state" : addressState,
                    "Stage" : stage,
                    "street": addressStreet,
                    "tag" : tags,
                    "zip" : addressZip,
                    "city" : addressCity,
                    "company" : company,
                    "email" : emails,
                }
                // this.testing[i] = {
                //     "firstName" : firstName,
                //     "lastName" : lastName,
                //     "mobilePhones" : mobilePhones,
                //     "homePhones" : homePhones,
                //     "workPhones" : workPhones,
                //     "addressState" : addressState,
                //     "Stage" : stage,
                //     "addressStreet": addressStreet,
                //     "tags" : tags,
                //     "addressZip" : addressZip,
                //     "addressCity" : addressCity,
                //     "company" : company,
                //     "email" : emails,
                // }
            }
        },
        makingGroupedRecords(){
            this.unique_Records = [];
            this.duplicate_Records = [];
            let gkey = this.sform.fdata.reduce((acc, it) => {
                acc[it.number1] = acc[it.number1] + 1 || 1;
                return acc;
            }, {});
            this.groupKey = gkey;
            let gk = this;
            this.sform.fdata.forEach(element => {
                if(gk.groupKey.hasOwnProperty(element.number1) && gk.groupKey[element.number1] == 1) {
                    gk.unique_Records.push(element);
                }
            });
            Object.keys(gkey).forEach(function(key) {
                if(gkey[key] >= 2) {
                    let grp = gk.sform.fdata.filter(rec => { return rec.number1 == key });
                    gk.duplicate_Records.push(grp);
                }
            });
        },
        addAndRemoveAllRecordToContainer(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            if(document.getElementById("check-all").checked == true){
                for (var i = 0; i < aa.length; i++){
                    if(parseInt(aa[i].value) > 1){
                        if((this.recordContainer.indexOf(parseInt(aa[i].value)) == -1)){
                            aa[i].checked = true;
                            this.recordContainer.push(Number(aa[i].value));
                        }else{
                            aa[i].checked = true;
                        }
                    }
                }
            }
            if(document.getElementById("check-all").checked == false){
                var record = [];
                for (var i = 0; i < aa.length; i++){
                    if(Number(aa[i].value) > 0){
                        if((this.recordContainer.indexOf(parseInt(aa[i].value)) >= 0)){
                            this.recordContainer.splice(this.recordContainer.indexOf(parseInt(aa[i].value)), 1);
                            aa[i].checked = false;
                        }
                    }
                }
            }
        },
        addAndRemoveRecordToContainer(id){
            if((this.recordContainer.indexOf(parseInt(id)) == -1) && (document.getElementById("record-"+id).checked == true)){
                this.recordContainer.push(id);
            }else{
                this.recordContainer.splice(this.recordContainer.indexOf(parseInt(id)), 1);
            }
        },       
        updateSorting(by) {
            this.form.sortBy = by;
            this.getOutreachData(1);
        },
        showActivity(rid, rtype, recordId) { 
            this.loader = 5;
            if(rtype == 1)
            {
                axios.get('/api/get-outreach-prospect-activities/'+recordId).then((response) => {
                    this.activities = response.data.details.data;
                    this.mailingList = response.data.details.included;
                    this.activityMeta = response.data.details.meta;
                });
                axios.get('/api/get-outreach-prospect-details-calls/'+recordId).then((response) => {
                    this.calls = response.data.details.data;
                    this.callDispositions = response.data.callDispositionArray;
                    this.callPurposes = response.data.callPurposeArray;

                });
                this.emails = {};
                this.emailDelivered = 0;
                this.emailOpened = 0;
                this.emailClicked = 0;
                this.emailReplied = 0;
                axios.get('/api/get-outreach-prospect-details-emails/'+recordId).then((response) => {
                    this.emails = response.data.details.data;
                            this.emailDelivered = 0;
                            this.emailOpened = 0;
                            this.emailClicked = 0;
                            this.emailReplied = 0;
                    for (let i = 0; i < this.emails.length; i++) {
                        if(this.emails[i].attributes.deliveredAt){
                            this.emailDelivered +=1;     
                        }
                        if(this.emails[i].attributes.openedAt){
                            this.emailOpened +=1;     
                        }
                        if(this.emails[i].attributes.clickedAt){
                            this.emailClicked +=1;     
                        }
                        if(this.emails[i].attributes.repliedAt){
                            this.emailReplied +=1;     
                        }
                    }
                    this.loader = false;
                });
            }       
            if(rtype == 3){
                
            }      
            $('.addiv').hide();
            $('.divtbody-additional').hide();
            this.active_row.id = recordId;
            this.active_row.type = rtype;
            $('#act'+rtype+rid).show();
            $('#action'+rid).show();
        },
        closeActivity() {
            this.active_row.id = '';
            this.active_row.type = '';
            $('.addiv').hide();
            $('.divtbody-additional').hide();
        },
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        changeView() {
            if(this.view == 'list') {
                this.view = 'graph';
            } else {
                this.view = 'list';
            }
        },
        updateType(typ) {
            if(this.form.types.indexOf(typ) == -1) {
                this.form.types.push(typ);
            } else {
                this.form.types.splice(this.form.types.indexOf(typ), 1);
            }
        },
        getFilterData(){
            this.form.sortType = 'outreach_touched_at'
            this.form.sortBy = 'asc'
            this.totalNumberOfRecords = '-';    
            this.getOutreachData(1);
        },
        getOutreachData(pno) {
            var path = this.$route.path.split("/")
            var id = path[path.length-1]
            this.form.account_id = path[path.length-1]
            this.loader = true;
            this.form.page = pno
            document.getElementById("check-all").checked = false;
            this.$Progress.start();            
            this.form.post('/api/get-account-prospects').then((response) => {                
                this.records = response.data.results;
                this.totalRecords = response.data.page.total;                
                this.$Progress.finish();
                let newRecords = response.data.results.data;
                for(const key in newRecords){
                    this.allProspects[newRecords[key]['id']] = newRecords[key];
                }                
                this.totalNumberOfRecords = response.data.page.total;
                this.loader = false;
            });
        },        
        backStep(){
            this.step = 1;
        },
        selectAllRecords()
        {
            this.loader = true;
            this.$Progress.start();
            let query = '';
            query = query + 'stage='+this.form.stage
            if(this.form.dateRange.startDate){
                var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                query = query + "&startDate="+startDate+'&endDate='+endDate
            }
            if(this.form.textSearch){
                query = query + '&textSearch='+this.form.textSearch
            }            
            query = query + '&sort='+this.form.sortType+'&sortby='+this.form.sortBy+'&page='+1
            axios.get('/api/get-outreach-all-prospects-to-export?'+query).then((response) => {
                var aa = document.querySelectorAll("input[type=checkbox]");
                document.getElementById('check-all').checked = true;
                for (var i = 0; i < aa.length; i++){
                    if(parseInt(aa[i].value) > 1){
                        aa[i].checked = true;
                    }
                }                
                let newRecords = response.data.results;
                this.allProspects = {};
                this.recordContainer = []
                for(const key in newRecords){
                    this.allProspects[newRecords[key]['id']] = newRecords[key];
                    this.recordContainer.push(newRecords[key]['id']);                    
                }
                this.totalNumberOfRecords = response.data.totalRecords;   
                this.$Progress.finish();  
                this.loader = false;        
            });
        },
        startDownload(){
            this.loader = 4;
            let date = new Date();
            this.filename = 'prospects-export-'+date.getMonth()+'-'+date.getDate()+'-'+date.getFullYear()+'-'+date.getHours()+'-'+date.getMinutes()+'-'+date.getSeconds()+'.csv';
            this.json_data = [];
            for(var i = 0; i < this.recordContainer.length; i++){
                let record_id = '';
                if(this.allProspects[this.recordContainer[i]].record_id){
                    record_id = this.allProspects[this.recordContainer[i]].record_id;
                }
                let mobilePhones = '';
                if(this.allProspects[this.recordContainer[i]].mobilePhones){
                    mobilePhones = this.allProspects[this.recordContainer[i]].mobilePhones;                   
                    mobilePhones = mobilePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    mobilePhones = mobilePhones.replace("-", '')
                    mobilePhones = mobilePhones.substring(0,10)
                    if(mobilePhones.length == 10){
                        mobilePhones = parseInt(mobilePhones)
                    }else{
                        mobilePhones = 0;
                    }
                }
                let workPhones = '';
                if(this.allProspects[this.recordContainer[i]].workPhones){
                    workPhones = this.allProspects[this.recordContainer[i]].workPhones;                   
                    workPhones = workPhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    workPhones = workPhones.replace("-", '')
                    workPhones = workPhones.substring(0,10)
                    if(workPhones.length == 10){
                        workPhones = parseInt(workPhones)
                    }else{
                        workPhones = 0;
                    }
                }
                let homePhones = '';
                if(this.allProspects[this.recordContainer[i]].homePhones){
                    homePhones = this.allProspects[this.recordContainer[i]].homePhones;                   
                    homePhones = homePhones.replace(/[^0-9]/g, "").replace(/ +/g, "");
                    homePhones = homePhones.replace("-", '')
                    homePhones = homePhones.substring(0,10)
                    if(homePhones.length == 10){
                        homePhones = parseInt(homePhones)
                    }else{
                        homePhones = 0;
                    }
                }
                                
                let firstName = '';
                if(this.allProspects[this.recordContainer[i]].first_name){
                    firstName = this.allProspects[this.recordContainer[i]].first_name;
                    firstName = firstName.replace(/[^a-zA-Z0-9\s]/g, "");
                    firstName = firstName.replace("'", '')
                    firstName = firstName.replace("&", '')
                }
                let lastName = '';
                if(this.allProspects[this.recordContainer[i]].last_name){
                    lastName = this.allProspects[this.recordContainer[i]].last_name;
                    lastName = lastName.replace(/[^a-zA-Z0-9\s]/g, "");
                    lastName = lastName.replace("'", '')
                    lastName = lastName.replace("&", '')
                }
                let  addressState = '';
                if(this.allProspects[this.recordContainer[i]].state){
                    addressState = this.allProspects[this.recordContainer[i]].state;
                    addressState = addressState.replace(/[^a-zA-Z0-9\s]/g, "");
                    addressState = addressState.replace("'", '')
                    addressState = addressState.replace("&", '')
                }
                let addressStreet = '';
                if(this.allProspects[this.recordContainer[i]].address){
                    addressStreet = this.allProspects[this.recordContainer[i]].address;
                    addressStreet = addressStreet.replace(/[^a-zA-Z0-9\s]/g, "")
                    addressStreet = addressStreet.replace("'", '')
                    addressStreet = addressStreet.replace("&", '')
                }
                let tags = '';
                if(this.allProspects[this.recordContainer[i]].outreach_tag){                    
                    tags = this.allProspects[this.recordContainer[i]].outreach_tag;
                    tags = tags.replace(/[^a-zA-Z0-9\s]/g, "")
                    tags = tags.replace("'", '')
                    tags = tags.replace("&", '')
                }
                let addressZip = '';
                if(this.allProspects[this.recordContainer[i]].zip){
                    addressZip = this.allProspects[this.recordContainer[i]].zip;
                    addressZip = addressZip.replace(/[^a-zA-Z0-9\s]/g, "")
                    addressZip = addressZip.replace("'", '')
                    addressZip = addressZip.replace("&", '')
                }
                let addressCity = '';
                if(this.allProspects[this.recordContainer[i]].city){
                    addressCity = this.allProspects[this.recordContainer[i]].city;
                    addressCity = addressCity.replace(/[^a-zA-Z0-9\s]/g, "")
                    addressCity = addressCity.replace("'", '')
                    addressCity = addressCity.replace("&", '')
                }
                let company = '';
                if(this.allProspects[this.recordContainer[i]].company){
                    company = this.allProspects[this.recordContainer[i]].company;
                    //company = company.replace(/[^a-zA-Z0-9\s]/g, "")
                    company = company.replace("'", '')
                    company = company.replace("&", '')
                }
                
                let emails = null;
                if(this.allProspects[this.recordContainer[i]].emails){
                    emails = this.allProspects[this.recordContainer[i]].emails
                    if(emails.search(',') > 0){                         
                        emails = emails.substring(0,emails.search(','));
                    } else {
                        emails = this.allProspects[this.recordContainer[i]].emails
                    }
                }
                let stage = '';
                if(this.allProspects[this.recordContainer[i]].stage_data){
                    stage = this.allProspects[this.recordContainer[i]].stage_data.stage;
                    //stage = stage.replace(/[^a-zA-Z0-9\s]/g, "")
                    stage = stage.replace("'", '')
                    stage = stage.replace("&", '')
                }
                let personal_note = '';
                if(this.allProspects[this.recordContainer[i]].personal_note){
                    personal_note = this.allProspects[this.recordContainer[i]].personal_note;
                    personal_note = personal_note.replace(/\r?\n|\r/g, "")
                    personal_note = personal_note.replace("'", '')
                    personal_note = personal_note.replace("&", '')
                }
                let dial_attempts = '';
                if(this.allProspects[this.recordContainer[i]].dial_attempts){
                    dial_attempts = this.allProspects[this.recordContainer[i]].dial_attempts;
                }
                let last_agent = '';
                if(this.allProspects[this.recordContainer[i]].last_agent){
                    last_agent = this.allProspects[this.recordContainer[i]].last_agent;
                }
                let last_agent_dispo_time = '';
                if(this.allProspects[this.recordContainer[i]].last_agent_dispo_time){
                    last_agent_dispo_time = this.allProspects[this.recordContainer[i]].last_agent_dispo_time;
                }
                let last_dispo = '';
                if(this.allProspects[this.recordContainer[i]].last_dispo){
                    last_dispo = this.allProspects[this.recordContainer[i]].last_dispo;
                    last_dispo = last_dispo.replace(/[^a-zA-Z0-9\s]/g, "")
                    last_dispo = last_dispo.replace("'", '')
                    last_dispo = last_dispo.replace("&", '')
                }
                let outreach_tag = '';
                if(this.allProspects[this.recordContainer[i]].outreach_tag){
                    outreach_tag = this.allProspects[this.recordContainer[i]].outreach_tag;
                    outreach_tag = outreach_tag.replace("'", '')
                    outreach_tag = outreach_tag.replace(/[#,]/g, ' ')
                    outreach_tag = outreach_tag.replace("&", '')
                }
                this.json_data[i] = {
                    "record_id" : record_id,
                    "first_name" : firstName,
                    "last_name" : lastName,
                    "mobilePhones" : mobilePhones,
                    "homePhones" : homePhones,
                    "workPhones" : workPhones,
                    "state" : addressState,
                    "stage" : stage,
                    "address": addressStreet,
                    "tags" : tags,
                    "zip" : addressZip,
                    "city" : addressCity,
                    "company" : company,
                    "emails" : emails,
                    "personal_note" : personal_note,
                    "dial_attempts" : dial_attempts,
                    "last_agent" : last_agent,
                    "outreach_tag" : outreach_tag,
                    "last_agent_dispo_time" : last_agent_dispo_time,
                    "last_dispo" : last_dispo,
                }
            }
        },
        finishdownload(){
            this.loader = false;
        },       
        outreachallupdate(){
            axios.get('/api/outreachallupdate').then((response) => {
            //this.stageDetails = response.data;
            });
        },
        getallview(){
            axios.get('/api/get-all-views').then((response) => {
                this.views = response.data.results;
            });
        },
        refreshAll() {
            this.getOutreachData(1);
        },
        deleteGroupRecords(n1, n2, n3, p1) {
            let arr = this.sform.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3 && arr[i].first_name === p1) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        deleteRecord(cid) {

            let arr = this.sform.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( i === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        deleteUnRecord(cid) {
            arr = this.sform.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( arr[i].number1 === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        setFileFields() {
            let fdt = []; let fnw = [];
            let $this = this;
            $this.sform.fdata = [];
            $this.sform.fdata =  $this.parse_csv;
            $this.sform.fdata.forEach(function (key, index) {
                fnw = [];
                $this.parse_header.forEach(function(element, ekey) {
                    if($this.sform.fields.indexOf(element) >= 0) {
                        let nkey = $this.sform.efields[$this.sform.fields.indexOf(element)];
                        let ov = '';
                        if(nkey == 'number1' || nkey == 'number2' || nkey == 'number3') {
                            ov = $this.sformatNumber($this.sform.fdata[index][element]);
                        } else {
                            ov = $this.sform.fdata[index][element];
                        }
                        $this.sform.fdata[index][nkey] = ov;
                        delete $this.sform.fdata[index][element];
                    }
                    else {
                      delete $this.sform.fdata[index][element];
                    }
                    /* let ov = ''; let val = '';
                    if($this.sform.fields[ekey] != '') {
                        val = (key[$this.sform.fields[ekey]])?key[$this.sform.fields[ekey]]:'';
                        if(element == 'number1' || element == 'number2' || element == 'number3') {
                            ov = $this.sformatNumber(val);
                        } else {
                            ov = val;
                        }
                        fnw[element] = ov;
                    } */
                });
                
            });
            this.makingGroupedRecords();
            this.loader = false;
        },
        startSyncing() {
            if(this.sform.destination == 'five9' && this.sform.name == '' && this.sform.lid == '0') {
                Vue.$toast.warning("List name is mandatory !!");
                return false;
            }
            this.loader = 4;
            this.sform.post('/api/uploadContactsDashboard').then((response) => {
                this.loader = false;
                //this.form.reset();
                //this.step = 4;
                //this.report = response.data.result.message;
                if(response.data.status == 'success'){
                    this.sform.reset();
                    this.step = 3;
                    this.report = response.data.message; //"Inserted: "+ response.data.listRecordsInserted+", Updated: "+response.data.crmRecordsUpdated;
                } else if(response.data.status == 'error'){
                    this.errornote = response.data.message; //"Inserted: "+ response.data.listRecordsInserted+", Updated: "+response.data.crmRecordsUpdated;
                } else {
                    Vue.$toast.warning(response.data.result);
                }
            })
        },
        checkRecords(){
            if(this.sform.fdata.length > this.unique_Records) {
                let diff = this.sform.fdata.length - this.unique_Records;
                Vue.$toast.info(diff +' records are still duplicate. We will convert them to '+ this.duplicate_Records +' unique records.', { duration: 8000 });
            }
            this.step = 2;            
        },
        swapRecords(n1, n2, n3){
            if(n2 == 0) {
                Vue.$toast.error("Numbers can not be swapped. Number1 can not be 0 !!");
                return false;
            }
            if(n1 == n2) {
                Vue.$toast.error("Numbers can not be swapped. Both numbers are same !!");
                return false;
            }
            let arr = this.sform.fdata; let intrchnage = '';
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3) { 
                    arr[i].number1 = n2;
                    arr[i].number2 = n1;
                }
            }
            this.makingGroupedRecords();

            Vue.$toast.info("Numbers swapped successfully !!");
        }, 
        //details
        toggle(id){
            if($("#demo" + id).is(":visible") == true){
                $("#demo" + id).css('display', 'none')
            } else {
                $("#demo" + id).css('display', 'block')
            }
        },
        getAllProspectsEmails(){
            axios.get('/api/get-all-prospects-email?ids=' + this.allProspectIds.toString()).then((response) => {
                this.allProspectEmails = response.data.emails
                var emails = this.allProspectEmails.data
                var included = this.allProspectEmails.included
                var owner = response.data.owner
                var emailsData = []
                for(var i = 0; i < emails.length; i++){
                    var state = emails[i]['attributes']['state']      
                    if(emails[i]['attributes']['deliveredAt']){
                        this.emailState['delivered'] = this.emailState['delivered'] + 1 
                    }              
                    var name = ''
                    var prospectiD = emails[i]['relationships']['prospect']['data']['id']
                    for(const key in included){
                        if(prospectiD == included[key]['id']){
                            name = included[key]['attributes']['firstName'] + ' ' +included[key]['attributes']['lastName']
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
                    }
                }                
                this.allProspectEmailsData = emailsData
                this.emailLoader =  false
            })
        },
        getAllUsers(){
            axios.get('/api/get-all-users').then((response) => {
                this.users = response.data.users
                this.userInfomations = this.users.data.map(e => ({ value: e.id, name: e.attributes.name, email : e.attributes.email }))
            })
        },
        getAllProspectCalls(){
            axios.get('/api/get-all-prospects-calls?ids=' + this.allProspectIds.toString()).then((response) => {
                this.calls = response.data.calls.data.reverse();
                var calls = this.calls
                this.totalCalls = this.calls.length
                var included = response.data.calls.included
                //overviewCalls
                var overviewCalls = {}
                for(var i = 0; i < calls.length; i++){
                    var callDisposition = ''
                    var callPurpose = ''
                    var user = ''
                    var callDispositionId = 0
                    var callPurposeId = 0
                    var userId = 0
                    var prospectiD = calls[i]['relationships']['prospect']['data']['id']
                    
                    if(calls[i]['relationships']['user']['data']){
                        userId = calls[i]['relationships']['user']['data']['id']
                    }
                    if(calls[i]['relationships']['callDisposition']['data']){
                        callDispositionId = calls[i]['relationships']['callDisposition']['data']['id']
                    }
                    if(calls[i]['relationships']['callPurpose']['data']){
                        callPurposeId = calls[i]['relationships']['callPurpose']['data']['id']
                    }
                    for(const key in included){
                        if( (prospectiD == included[key]['id']) && (included[key]['type'] == 'prospect') ){
                            var name = included[key]['attributes']['firstName'] + ' ' + included[key]['attributes']['lastName']
                            break
                        }
                    }
                    for(const key in included){
                        if( ( parseInt(callDispositionId) > 0) && (callDispositionId == included[key]['id']) && (included[key]['type'] == 'callDisposition') ){
                            callDisposition = included[key]['attributes']['name']
                            break
                        }
                    }
                    for(const key in included){
                        if( ( parseInt(callPurposeId) > 0) && (callPurposeId == included[key]['id']) && (included[key]['type'] == 'callPurpose') ){
                            callPurpose = included[key]['attributes']['name']
                            break
                        }
                    }
                    for(const key in included){
                        if( (userId == included[key]['id']) && (included[key]['type'] == 'user') ){
                            user = included[key]['attributes']['name']
                            break
                        }
                    }
                    if( parseInt(callDispositionId) > 0){
                        for(const key in this.callDispo){
                            if(this.callDispo[key]['id'] == parseInt(callDispositionId)){
                                this.callDispo[key]['count'] = this.callDispo[key]['count'] + 1
                            }
                        }
                    }
                    var answeredAt = calls[i]["attributes"]["answeredAt"]
                    var completedAt = calls[i]["attributes"]["completedAt"]
                    var createdAt = calls[i]["attributes"]["createdAt"]
                    var stateChangedAt = calls[i]["attributes"]["stateChangedAt"]
                    var updatedAt = calls[i]["attributes"]["updatedAt"]
                    
                    overviewCalls[i] = {
                        "id" : calls[i]["id"],
                        "name" : name,
                        "direction" : calls[i]["attributes"]["direction"],
                        "to" : calls[i]["attributes"]["to"],
                        "from" : calls[i]["attributes"]["from"],
                        "callDisposition" : callDisposition,
                        "callPurpose" : callPurpose,
                        "answeredAt" : calls[i]["attributes"]["answeredAt"],
                        "completedAt" : calls[i]["attributes"]["completedAt"],
                        "createdAt" : calls[i]["attributes"]["createdAt"],
                        "stateChangedAt" : calls[i]["attributes"]["stateChangedAt"],
                        "updatedAt" : calls[i]["attributes"]["updatedAt"],
                        "state" : calls[i]["attributes"]["state"],
                        "recordingUrl" : calls[i]["attributes"]["recordingUrl"],
                        "user" : user,
                        "callDispositionId" : callDispositionId
                    }
                }
                for(const key in this.callDispo){
                    this.callCounted = this.callCounted + this.callDispo[key]['count']
                }
                if(this.totalCalls == this.callCounted){
                    this.callDispo[0] = {
                        "name" : "Not Logged",
                        "count" : 0,
                        "id" : 0,
                    }
                }else{
                    var diff = this.totalCalls - this.callCounted
                    this.callDispo[9] = {
                        "name" : "Not Logged",
                        "count" : diff,
                        "id" : 0,
                    }
                }
                this.overviewCalls = overviewCalls
                this.callLoader = false
            })
        },
        getCallDispositions(){
            axios.get('/api/get-call-dispositions').then((response) => {
                this.callDispositions = response.data.details.data;
                var callDispo = {}
                for(const key in this.callDispositions){
                    callDispo[key] = {
                        'id' : this.callDispositions[key]['id'],
                        'name' : this.callDispositions[key]['attributes']['name'],
                        'count' : 0
                    }
                }
                this.callDispo = callDispo
            });
        },
        getCallPurpose(){
            axios.get('/api/get-call-purpose').then((response) => {
                this.callPurposes = response.data.details.data;
            });
        },
        showCallByCallDisposition(callDisId){
            this.activeCallDispo = callDisId
            $(".call-dispo").css("display", "none")
            $(".call-dispo-" + callDisId).css("display", "table-row")
        },
        showAllCalls(){
            this.activeCallDispo = 'all'
            $(".call-dispo").css("display", "table-row")
        },
        showProspect(pid){
            this.activeProspectStage = pid
            $(".row-prospect").css('display', 'none')
            $(".row-prospect-" + pid).css('display', 'table-row')
        },
        showAllProspect(){
            this.activeProspectStage = 'all'
            $(".row-prospect").css('display', 'table-row')
        }
    },
    mounted() {
        //this.getOutreachData(1);
        this.$Progress.start();   
        var path = this.$route.path.split("/")
        var id = path[path.length-1]
        this.accountId = id
        axios.get('/api/get-account-info/' + path[path.length - 1]).then((response) => {
            this.accountInfo = response.data.results
            this.prospectsWithCompleteTask = response.data.prospectsWithCompleteTask
            this.allProspectIds = this.prospectsWithCompleteTask.map(function(e){
                return e.record_id
            })
            
            this.getCallDispositions()
            this.getCallPurpose()
            this.getAllProspectCalls()
            this.getAllUsers()
            this.getAllProspectsEmails()
        })
        axios.get('/api/get-all-stages-account/' + path[path.length-1]).then((response) => {
            this.stageDetails = response.data;
            this.prospectLoader = false
        });
        this.$Progress.finish();
        // axios.get('/api/get-mapping-range').then((response) => {
        //     this.mapped_ranges = response.data;
        // });
        // this.getallview()
        // axios.get('/api/get-all-filter').then((response) => {
        //     this.filterItems = response.data.items
        //     this.filterItemsAll = response.data.items
        // });
    }
}
</script>
<style>
    .pointer-hand{
        cursor: pointer;
    }
    .border-none{
        border:none !important;
        margin-right:3px;
    }
    .account-custom{
        max-height: 650px;
        overflow: auto;
    }
    .hide{
        cursor: pointer;
    }
    .circle{
        border-radius: 50%;
        width: 16px;
        height: 16px;
        padding: 2px;
        background: #fff;
        border: 3px solid #000;
        color: #000;
        text-align: center;
        font: 16px Arial, sans-serif;
    }
    .full-width{
        width: 100%;
    }
    .loader{
        width:100%;
    }
</style>