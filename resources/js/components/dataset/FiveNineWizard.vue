<template>
    <div class="pt-2 pl-2">
        <div class="wizard-circle wizard">
            <button type="button" class="float-left btn btn-sm btn-secondary theme-btn icon-btn-left" @click="goBack"  v-if="active_step <= 4 && startnow == false"><i class="bi bi-arrow-left"></i> Back</button> 
            <button type="button" class="float-right btn btn-sm btn-primary theme-btn icon-btn-right mr-2" @click="nextStep" v-if="active_step < 4">Next <i class="bi bi-arrow-right"></i></button> 
            <div class="steps clearfix">
                <ul role="tablist">
                    <li role="tab" class="first" :class="[(done_steps.indexOf(1) >= 0)?'done':(active_step == 1)?'current':'']">
                        <a id="steps-1" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-list-check"></i>
                            </span> 
                            <b>{{ recordCount }} </b> Records Found
                        </a>
                    </li>
                    <li role="tab" class="" :class="[(done_steps.indexOf(2) >= 0)?'done':(active_step == 2)?'current':'disabled']">
                        <a id="steps-2" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-pencil-square"></i>
                            </span>
                            Set List(s) Action 
                        </a>
                    </li>
                    <li role="tab" class="" :class="[(done_steps.indexOf(3) >= 0)?'done':(active_step == 3)?'current':'disabled']">
                        <a id="steps-3" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-check2-all"></i>
                            </span> Refine
                        </a>
                    </li>
                    <li role="tab" class="last" :class="[(done_steps.indexOf(4) >= 0)?'done':(active_step == 4)?'current':'disabled']">
                        <a id="steps-4" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-receipt"></i>
                            </span> Overview
                        </a>
                    </li>
                    <li role="tab" class="last" :class="[(done_steps.indexOf(5) >= 0)?'done':(active_step == 5)?'current':'disabled']">
                        <a id="steps-5" href="javascript:;">
                            <span class="step">
                                <i class="step-icon bi bi-hand-thumbs-up"></i>
                            </span> Status
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="position-relative wizard-content bg-white" :class="[(active_step >= 2)?'border mr-2':'']">
            <div class="table-responsive" v-if="active_step == 1">
                <div class="divtable border-top" v-if="recordCount >= 1">
                    <div class="divthead">
                        <div class="divthead-row">
                            <div class="divthead-elem wf-60">SNo</div>
                            <div class="divthead-elem wf-100">Record ID</div>
                            <div class="divthead-elem wf-200">Name</div>
                            <div class="divthead-elem wf-150">Number1</div>
                            <div class="divthead-elem wf-150">Number2</div>
                            <div class="divthead-elem wf-150">Number3</div>
                            <div class="divthead-elem wf-60">Ext1</div>
                            <div class="divthead-elem wf-60">Ext2</div>
                            <div class="divthead-elem wf-60">Ext3</div>
                            <div class="divthead-elem mwf-125">Company</div>
                            <div class="divthead-elem wf-80">Action</div>
                        </div>
                    </div>
                    <div class="divtbody custom-height">
                        <div class="divtbody-row" v-for="(record, rkey) in records" :key="'all-'+rkey">
                            <div class="divtbody-elem wf-60">{{ rkey+1 }}</div>
                            <div class="divtbody-elem wf-100">{{ record.record_id }}</div>
                            <div class="divtbody-elem wf-200">{{ record.first_name+' '+record.last_name }}</div>
                            <div class="divtbody-elem wf-150">{{ record.number1 }}</div>
                            <div class="divtbody-elem wf-150">{{ record.number2 }}</div>
                            <div class="divtbody-elem wf-150">{{ record.number3 }}</div>
                            <div class="divtbody-elem wf-60">{{ record.ext1 }}</div>
                            <div class="divtbody-elem wf-60">{{ record.ext2 }}</div>
                            <div class="divtbody-elem wf-60">{{ record.ext3 }}</div>
                            <div class="divtbody-elem mwf-125">{{ record.company }}</div>
                            <div class="divtbody-elem wf-80">
                                <button class="btn btn-sm btn-danger" type="button" @click="deleteRecord(rkey)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="alert alert-info" v-else>
                    No records found.
                </p>
            </div>
            <div class="p-3" v-else-if="active_step == 2">
                <div class="text-center p-2"  style="margin:auto">
                    <div class="wf-300 d-inline-block my-2" style="margin:auto">
                        <span class="btn theme-btn" :class="[(form.action == 'create')?'btn-dark':'btn-outline-dark']" @click="setAction('create')"> Create New List </span>
                    </div>
                    <div class="wf-300 d-inline-block my-2" style="margin:auto">
                        <span class="btn theme-btn" :class="[(form.action == 'update')?'btn-dark':'btn-outline-dark']" @click="setAction('update')"> Update List(s) </span>
                    </div>
                    <div class="wf-300 d-inline-block my-2" style="margin:auto">
                        <span class="btn theme-btn" :class="[(form.action == 'delete')?'btn-dark':'btn-outline-dark']" @click="setAction('delete')"> Delete From List(s) </span>
                    </div>
                </div>
                <div class="text-center p-4" v-if="form.action == 'create'">
                    <label for="list_name" class="text-uppercase fw-500">List Name</label>
                    <input type="text" v-model="form.list_name" id="list_name" class="form-control wf-300" placeholder="Enter name of new list" style="margin:auto">
                </div>
                <div class="text-center p-4" v-else-if="form.action == 'delete'">
                    <h5 class="mb-3 d-inline-block mr-2">Matched List(s) with these Contacts</h5>
                    <button @click="deMSelectAll" class="btn-btn-sm btn-warning theme-btn" type="button" v-if="isMListExist == true"> Deselect All </button>
                    <button @click="selectMAll" class="btn-btn-sm btn-primary theme-btn" type="button" v-else> Select All </button>
                    <div v-if="loader">
                        <p class="text-center">
                            <img :src="loader_url" alt="loading.."><br>
                            Please wait, we are getting Five9 Lists
                        </p>
                    </div>
                    <div class="" style="margin:auto;height: calc(75vh - 135px); overflow-y: auto;    overflow-x: hidden; padding-right:20px" v-else>
                        <button type="button" class="text-left btn theme-btn btn-block" style="margin:2px" :class="[(form.ac_list.indexOf(lname) >= 0)?'btn-warning':'btn-outline-primary']" @click="setList(lname)" v-for="(lname, lk) in five9_mlist" :key="'listid'+lk">
                        {{ lname.name }} 
                        <span class="float-right"> 
                            (<b>{{ lname.matched }}</b> matched out of <b>{{ lname.size }}</b>)
                        </span>
                        </button>
                    </div>
                </div>
                <div class="text-center p-4" v-else-if="form.action == 'update'">
                    <div class="row m-0">
                        <div class="col-sm-6 col-12">
                            <h5 class="mb-3 d-inline-block mr-2">Matched List(s) with these Contacts</h5>
                            <button @click="deMSelectAll" class="btn-btn-sm btn-warning theme-btn" type="button" v-if="isMListExist == true"> Deselect All </button>
                            <button @click="selectMAll" class="btn-btn-sm btn-primary theme-btn" type="button" v-else> Select All </button>
                            <div v-if="loader">
                                <p class="text-center">
                                    <img :src="loader_url" alt="loading.."><br>
                                    Please wait, we are getting Five9 Lists
                                </p>
                            </div>
                            <div class="" style="margin:auto;height: calc(75vh - 135px); overflow-y: auto;    overflow-x: hidden; padding-right:20px" v-else>
                                <button type="button" class="text-left btn theme-btn btn-block" style="margin:2px" :class="[(form.ac_list.indexOf(lname) >= 0)?'btn-warning':'btn-outline-primary']" @click="setList(lname)" v-for="(lname, lk) in five9_mlist" :key="'listid'+lk">
                                {{ lname.name }} 
                                <span class="float-right"> 
                                    (<b>{{ lname.matched }}</b> matched out of <b>{{ lname.size }}</b>)
                                </span>
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <h5 class="mb-3 d-inline-block mr-2">Unmatched List(s) with these Contacts</h5>
                            <button @click="deUmSelectAll" class="btn-btn-sm btn-warning theme-btn" type="button" v-if="isUmListExist == true"> Deselect All </button>
                            <button @click="selectUmAll" class="btn-btn-sm btn-primary theme-btn" type="button" v-else> Select All </button>
                            <div v-if="loader">
                                <p class="text-center">
                                    <img :src="loader_url" alt="loading.."><br>
                                    Please wait, we are getting Five9 Lists
                                </p>
                            </div>
                            <div class="" style="margin:auto;height: calc(75vh - 135px); overflow-y: auto;    overflow-x: hidden; padding-right:20px" v-else>
                                <button type="button" class="text-left btn theme-btn btn-block" style="margin:2px" :class="[(form.ac_list.indexOf(lname) >= 0)?'btn-warning':'btn-outline-primary']" @click="setList(lname)" v-for="(lname, lk) in five9_umlist" :key="'listid'+lk">
                                {{ lname.name }} ({{ lname.size }})
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="active_step == 3">
                <div v-if="loader">
                    <p class="text-center">
                        <img :src="loader_url" alt="loading.."><br>
                        Please wait, we are checking prospects with call logs
                    </p>
                </div>
                <div v-else>
                    <div class="row mx-0 my-2" v-show="refinedview == true">
                        <div class="col-6 fs-15">
                            Total Importable Records : <b> {{ form.records.length  }} </b>
                        </div>
                        <div class="col-6 text-right">
                          
                        </div>
                    </div>
                    <div class="divtable border-top" v-if="recordCount >= 1">
                        <div class="divthead">
                            <div class="divthead-row">
                                <div class="divthead-elem wf-60">SNo</div>
                                <div class="divthead-elem wf-100">Record ID</div>
                                <div class="divthead-elem wf-200">Name</div>
                                <div class="divthead-elem wf-150">Number1</div>
                                <div class="divthead-elem wf-150">Number2</div>
                                <div class="divthead-elem wf-150">Number3</div>
                                <div class="divthead-elem mwf-125">Company</div>
                                <div class="divthead-elem wf-80">Action</div>
                            </div>
                        </div>
                        <div class="divtbody custom-height-210" v-if="refinedview == true">
                            <div class="divtbody-row" v-for="(record, rkey) in records" :key="'all-'+rkey">
                                <div class="divtbody-elem wf-60">{{ rkey+1 }}</div>
                                <div class="divtbody-elem wf-100">{{ record.record_id }}</div>
                                <div class="divtbody-elem wf-200">{{ record.first_name+' '+record.last_name }}</div>
                                <div class="divtbody-elem wf-150">
                                    <span :class="[(meltNumbers.indexOf(parseInt(record.number1)) != -1)?'bg-warning py-1 px-2':'']"> {{ record.number1 }} </span>
                                </div>
                                <div class="divtbody-elem wf-150">
                                    <span :class="[(meltNumbers.indexOf(parseInt(record.number2)) != -1)?'bg-warning py-1 px-2':'']"> {{ record.number2 }} </span>
                                </div>
                                <div class="divtbody-elem wf-150">
                                    <span :class="[(meltNumbers.indexOf(parseInt(record.number3)) != -1)?'bg-warning py-1 px-2':'']"> {{ record.number3 }} </span>
                                </div>
                                <div class="divtbody-elem mwf-125">{{ record.company }}</div>
                                <div class="divtbody-elem wf-80">
                                    <button class="btn btn-sm btn-danger" type="button" @click="deleteFRecord(rkey)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="divtbody custom-height" v-else>
                            <div class="divtbody-row" v-for="(record, rkey) in records" :key="'all-'+rkey">
                                <div class="divtbody-elem wf-60">{{ rkey+1 }}</div>
                                <div class="divtbody-elem wf-100">{{ record.record_id }}</div>
                                <div class="divtbody-elem wf-200">{{ record.first_name+' '+record.last_name }}</div>
                                <div class="divtbody-elem wf-150">
                                    <span :class="[(meltNumbers.indexOf(parseInt(record.number1)) != -1)?'bg-warning py-1 px-2':'']"> {{ record.number1 }} </span>
                                </div>
                                <div class="divtbody-elem wf-150">
                                    <span :class="[(meltNumbers.indexOf(parseInt(record.number2)) != -1)?'bg-warning py-1 px-2':'']"> {{ record.number2 }} </span>
                                </div>
                                <div class="divtbody-elem wf-150">
                                    <span :class="[(meltNumbers.indexOf(parseInt(record.number3)) != -1)?'bg-warning py-1 px-2':'']"> {{ record.number3 }} </span>
                                </div>
                                <div class="divtbody-elem mwf-125">{{ record.company }}</div>
                                <div class="divtbody-elem wf-80">
                                    <button class="btn btn-sm btn-danger" type="button" @click="deleteRecord(rkey)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 text-center" v-else-if="active_step == 4">
                <h5 class="p-2">
                    Now you are ready to import contacts on Five9. Click on 'Start' button to continue.
                </h5>
                <p class="p-2 wf-300" style="margin:auto" v-if="startnow == false">
                    <button class="btn btn-primary theme-btn" type="button" @click="StartExport"> Start</button>
                </p>
                <div v-if="startnow && form.action == 'create'">
                    <p class="p-3">
                        <img :src="loader_url" alt="Please wait...">
                    </p>
                </div>
                <div v-else-if="startnow && form.action != 'create'">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>List Name</th>
                                <th class="wf-100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(aclst, ak) in  form.ac_list" :key="'fac'+ak">
                                <td class="text-left">
                                    <h5>{{ aclst.name }}</h5>
                                </td>
                                <td> 
                                    <span v-if="donelists.indexOf(aclst.name) >= 0" class="btn btn-sm btn-success theme-btn">
                                        Updated
                                    </span>
                                    <span v-else-if="plist == aclst" class="btn btn-sm btn-primary theme-btn">
                                        Processing
                                    </span>
                                    <span v-else class="btn btn-sm btn-danger theme-btn">
                                        Awaiting
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3 text-center" v-else-if="active_step == 5">
                <h5 class="alert alert-info wf-300" style="margin:40px auto 10px">Your import has been completed successfully.</h5>
                <h5 class="alert alert-danger wf-300" style="margin:10px auto"> <b>NOTE: </b> Please, do any other export activity only after {{ time }} minutes.</h5>
                <h5 class="alert alert-warning wf-300" style="margin:10px auto 40px"> You will be able to view the export report in EXPORT HISTORY section after {{ time }} minutes.</h5>
                <a href="/datasets" class="btn theme-btn btn-primary">Click here to continue</a>
            </div>
            <div class="p-3" v-else>
                <p class="alert alert-warning"> Something is missing</p>
            </div>
        </div>
    </div>
</template>
<script>

export default {
    props: {
        'data': [Array, Object],
        'step': Number,
    },
    data() {
        return {
            five9_mlist:'',
            five9_umlist:'',
            active_step:1,
            done_steps:[],
            next_step:2,
            prev_step:0,
            startnow:false,
            loader:false,
            loader_url: '/img/spinner.gif',
            donelists:[],
            meltNumbers:[],
            plist:'',
            refinedview:false,
            comparable_list:[],
            time:0,
            form: new Form({
                records:[],
                action: '',
                ac_list:[],
                acp_list:'',
                list_name:'',
                code : '',
            })
        }
    },
    computed: {
        numberStatus: {
            get: function () {
                return false
            },
            set: function (num) {
                return (this.meltNumbers.indexOf(num) == -1)?true:false
            }
        },
        records() {
            return this.data
        },
        recordCount() {
            return Object.keys(this.records).length
        },
        dlength() {
            return this.donelists.length
        },
        isMListExist() {
            let cmr = 0;
            let $this = this;
            if(this.five9_mlist == '') { return false} 
            else {
                this.five9_mlist.forEach((ele) => {
                    if($this.form.ac_list.indexOf(ele) >= 0) {
                        cmr++
                    }
                })
            }
            return (this.five9_mlist.length == cmr)?true:false
        },
        isUmListExist() {
            let cmr = 0;
            let $this = this;
            if(this.five9_umlist == '') { return false} 
            else {
                this.five9_umlist.forEach((ele) => {
                    if($this.form.ac_list.indexOf(ele) >= 0) {
                        cmr++
                    }
                })
            }
            return (this.five9_umlist.length == cmr)?true:false
        }
    },
    methods: {
        deleteRecord(rkey) {
            this.data.splice(rkey, 1)
        },
        goBack() {
            if(this.active_step == 1) {
                this.active_step = 0;
                this.done_steps = [];
                this.next_step = this.next_step - 1;
                this.prev_step = this.prev_step - 1;
                this.$parent.step = 0;
            }
            else if(this.active_step >= 2) {
                this.active_step = this.active_step - 1;
                this.done_steps.splice(this.done_steps.indexOf(this.active_step), 1);
                this.next_step = this.next_step - 1;
                this.prev_step = this.prev_step - 1;
            }
        },
        checkStep() {
            if(this.active_step == 3) {
                let $this = this;
                if(this.form.action == 'delete') {
                    return confirm('Are you sure, you want to continue?')
                }
                else {
                    if(this.refinedview == true) {
                        return confirm('Are you sure, you want to continue?')
                    }
                    else {
                        this.$swal({
                            title: 'Please wait! ',
                            text: "We are refining the records ?",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if(result.value == true) {
                                $this.refinedview = true
                                $this.refinedContacts()
                                return false
                            } 
                        })
                    }
                }
            }
            else if(this.active_step == 2) {
                if(this.form.action == '') {
                    Vue.$toast.error('Please select action to continue.');
                    return false;
                } else if(this.form.action == 'create' && this.form.list_name == '') {
                    Vue.$toast.error('Please enter list name to continue.');
                    return false;
                }
                else if(this.form.action != 'create' && this.form.ac_list.length == 0) {
                    Vue.$toast.error('Please select atleast 1 list.');
                    return false;
                } else if(this.form.action != 'create' && this.form.ac_list.length != 0) {
                    this.checkMatchedRecords()
                    return true
                } else {
                    return true
                }
            }
            else if(this.active_step == 1) {
                this.form.records = this.records;
                return true;
            }
            
        },
        refinedContacts() {
            let $this = this;
            let subrec;
            let newRecords = [];
            this.form.records.forEach((ele) => {
                subrec = ele;
                if(this.meltNumbers.indexOf(parseInt(subrec.number1)) >= 0) {
                    subrec.number1 = '';
                }
                if(this.meltNumbers.indexOf(parseInt(subrec.number2)) >= 0) {
                    subrec.number2 = '';
                }
                if(this.meltNumbers.indexOf(parseInt(subrec.number3)) >= 0) {
                    subrec.number3 = '';
                }
                if(subrec.number1 == '' && subrec.number2 != '') {
                    subrec.number1 = subrec.number2;
                    subrec.number2 = '';
                }
                else if(ele.number1 == '' && subrec.number2 == '' && subrec.number3 != '') {
                    subrec.number1 = subrec.number3;
                    subrec.number3 = '';
                }
                if(subrec.number1 != '' && subrec.number2 == '' && subrec.number3 != '') {
                    subrec.number2 = subrec.number3;
                    subrec.number3 = '';
                }
                if(subrec.number1 == '' && subrec.number2 == '' && subrec.number3 == '') {
                    // Do nothing
                } else {
                    newRecords.push(subrec);
                }
            })
            this.form.records = newRecords
        },
        nextStep() {
            if(this.active_step < 4) {
                if(this.checkStep() == true) {
                    this.done_steps.push(this.active_step);
                    this.active_step = this.active_step + 1;
                    this.next_step = this.next_step + 1;
                    this.prev_step = this.prev_step + 1;
                } else {
                    return false;
                }
            }
        },
        setAction(act) {
            this.form.action = act
            if((act == 'update' || act == 'delete') && (this.five9_mlist == '' || this.five9_mlist.length == 0)) {
                this.getList();
            }
        },
        setSubAction(sact) {
            this.form.action_type = sact
        },
        setList(lname) {
            if(this.form.ac_list.indexOf(lname) >= 0) {
               this.form.ac_list.splice(this.form.ac_list.indexOf(lname), 1)
            } else {
                this.form.ac_list.push(lname)
            }
        },
        getList() {
            this.loader = true
            this.form.post('/api/get-list-matched-data').then((response) => {
                this.five9_mlist = response.data.matched
                this.five9_umlist = response.data.notMatched
                this.loader = false
            })
        },
        StartExport() {
            this.startnow = true
            if(this.form.action == "create"){
                this.form.post('/api/export-graph-data-to-fivenine').then((response) => {
                    if(response.data.status == "success"){
                       this.active_step = 5;
                       this.done_steps.push(4);
                       this.done_steps.push(5);
                       this.next_step = 6;
                       this.prev_step = 4;
                    }
                })
            }else {
                let $this = this;
                $this.form.ac_list.forEach((ele) => {
                    $this.form.acp_list = ele.name
                    $this.plist = ele.name
                    this.form.post('/api/export-graph-data-to-fivenine').then((response) => {
                        if(response.data.status == "success"){
                            $this.donelists.push(response.data.list_name)
                            if($this.form.ac_list.length == $this.donelists.length) {
                                this.endSteps()
                                axios.get('/api/run-queue');
                            }
                        }
                    })
                })              
            }
        },
        checkMatchedRecords() {
            this.loader = true
            this.form.post('/api/get-number-based-on-disposition').then((response) => {
                this.meltNumbers = response.data
                this.loader = false
            })
        },
        deMSelectAll() {
            let $this = this
            this.five9_mlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) != -1) {
                    this.form.ac_list.splice($this.form.ac_list.indexOf(ele), 1)
                }
            })
        },
        selectMAll() {
            let $this = this
            this.five9_mlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) == -1) {
                    this.form.ac_list.push(ele)
                }
            })
        },
        deUmSelectAll() {
            let $this = this
            this.five9_umlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) != -1) {
                    this.form.ac_list.splice($this.form.ac_list.indexOf(ele), 1)
                }
            })
        },
        selectUmAll() {
            let $this = this
            this.five9_umlist.forEach((ele) => {
                if($this.form.ac_list.indexOf(ele) == -1) {
                    this.form.ac_list.push(ele)
                }
            })
        },
        deleteFRecord(rkey) {
            this.form.records.splice(rkey, 1)
        },
        endSteps() {
            this.active_step = 5;
            this.done_steps.push(4);
            this.done_steps.push(5);
            this.next_step = 6;
            this.prev_step = 4;
            this.time = this.form.ac_list.length+5;
            this.form.reset();
        }
    },
    mounted() {
        axios.get('/api/randon-string').then((response) => {
            this.form.code = response.data
        })
    }
}
</script>