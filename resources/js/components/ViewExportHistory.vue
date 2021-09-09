<template>
    <div>
        <div class="top-form" v-show="step == 1">
            <div class="row m-0">
                <div class="col-md-6 col-12 pl-0">
                    <button class="btn btn-dark btn-sm mr-1" @click="verror = false" type="button">Total ({{ history.total }})</button>
                    <button class="btn btn-secondary disabled btn-sm mr-1" type="button">Inserted - {{ history.inserted }}</button>
                    <button class="btn btn-secondary disabled btn-sm mr-1" type="button">Updated - {{ history.updated }}</button>
                    <button class="btn btn-dark btn-sm mr-1" @click="verror = 2" type="button">Skipped - {{ history.skipped }}</button>
                    <button class="btn btn-sm mr-1" :class="[(verror)?'btn-danger':'btn-outline-danger']" type="button" @click="verror = 1">Error Report</button>
                        <img :src="loader_url" v-show="loader">
                </div>
                <div class="col-md-6 col-12 p-0 text-right">
                    <div class="dropdown d-inline-block" v-show="verror == false">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Select Fields
                        </button>
                        <div class="dropdown-menu p-0" aria-labelledby="dropdownMenuButton">
                            <p v-for="(value, i) in alloptions" :key="'ao-'+i" class="sel-fld" @click="toggleOption(value)" :class="[(options.indexOf(value) >= 0)?'selp':'']">
                                {{ value | titleFormat}}
                                <i class="bi bi-check2-circle" v-if="options.indexOf(value) >= 0"></i>
                                <i class="bi bi-x-circle" v-else></i>
                            </p>
                        </div>
                    </div>
                    <router-link class="btn btn-sm btn-dark theme-btn icon-btn" to="/export-history">
                        <i class="bi bi-arrow-left"></i> Back
                    </router-link>
                    <button v-show="recordContainer.length > 0" type="button" class="btn btn-sm btn-success theme-btn icon-btn" @click="generateRecords">
                        Next <i class="bi bi-arrow-right"></i> 
                    </button>
                    <br>
                    <span v-show="recordContainer.length > 0">
                        {{ recordContainer.length }} record(s) selected
                    </span>
                </div>
            </div>
        </div>
        <div v-if="step == 1">
            <div class="mapping-div p-1">
                <table class="table table-bordered table-striped" v-if="verror == 1">
                    <thead>
                        <tr>
                            <th class="wf-50">Sno</th>
                            <th>Number1</th>
                            <th>Error</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="importTroubles.length == 0">
                            <td colspan="5" class="text-center">No error or warning found.</td>
                        </tr>
                        <tr v-for="(importTrouble, key) in importTroubles" :key="'it-'+key" v-else>
                            <td>{{ key+1 }}</td>
                            <td>{{ importTrouble.key }}</td>
                            <td>{{ importTrouble.kind }}</td>
                            <td>{{ importTrouble.troubleMessage }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered table-striped" v-if="verror == 0">
                    <thead>
                        <tr>
                            <th class="wf-50">Sno</th>
                            <th v-for="(value, i) in options" :key="'value-'+i">{{ value | titleFormat}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(record, index) in newArray" :key="'record-'+index">
                            <td>{{ index + 1}}</td>
                            <td v-for="(value, i) in options" :key="'vk-'+i">{{ record[value] }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered table-striped" v-if="(this.importTroubles.length > 0) && (verror == 2)">
                    <thead>
                        <tr>
                            <th class="wf-50">
                                <input type="checkbox" name="" id="check-all" value="0" @click="addAndRemoveAllRecordToContainer">
                            </th>
                            <th class="wf-50">#</th>
                            <th>Number1</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Errro Type</th>
                            <th>Error Desc</th>
                        </tr>
                    </thead>
                    <tr v-for="(error,index) in errorArray" :key="'error-'+index">
                        <td class="text-center">
                            <input :id="'record-'+index" class="form-check-input ml-0" type="checkbox" :value="index" @click="addAndRemoveRecordToContainer(index)">
                        </td>
                        <td>{{ index+1 }}</td>
                        <td>{{ error.number1 }}</td>
                        <td>{{ error.first_name }}</td>
                        <td>{{ error.last_name }}</td>
                        <td>{{ error.errorType }}</td>
                        <td> <span v-if="error.rowNumber">Row Number {{ error.rowNumber }} </span>( {{ error.errorDesc }} )</td>
                    </tr>
                </table>
            </div>
        </div>
        <div v-else-if="step == 2">
            <div class="col-md-12 col-12 py-2">
                <span class="d-block">
                    <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 0)?'btn-dark':'btn-secondary']" @click="showrecords = 0">All Records [{{form.fdata.length }}]</button>          
                    <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 1)?'btn-dark':'btn-secondary']" @click="showrecords = 1">Unique Records [{{ unique_Records.length }}]</button>          
                    <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 2)?'btn-dark':'btn-secondary']" @click="showrecords = 2">Duplicate Records [{{ drcount }}]</button>     
                    <button type="button" class="float-right btn btn-sm btn-primary theme-btn icon-btn" @click="checkRecords">Next <i class="bi bi-arrow-right"></i></button>  
                </span>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-condensed" v-if="(showrecords == 0) || (showrecords == 1 && form.fdata.length == unique_Records.length)">
                        <thead>
                            <tr>
                                <th>SNo</th>
                                <th>Name</th>
                                <th>Number1</th>
                                <th>Number2</th>
                                <th>Number3</th>
                                <th>Company</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(record, rkey) in form.fdata" :key="'all-'+rkey">
                                <td>{{ rkey+1 }}</td>
                                <td>{{ record.first_name+' '+record.last_name }}</td>
                                <td>{{ record.number1 }}</td>
                                <td>{{ record.number2 }}</td>
                                <td>{{ record.number3 }}</td>
                                <td>{{ record.company }}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" type="button" @click="deleteRecord(rkey)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-striped table-condensed" v-if="showrecords == 1 && form.fdata.length > unique_Records.length">
                        <thead>
                            <tr>
                                <th>SNo</th>
                                <th>Name</th>
                                <th>Number1</th>
                                <th>Number2</th>
                                <th>Number3</th>
                                <th>Company</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(record, rkey) in unique_Records" :key="'unique-'+rkey">
                                <td>{{ rkey+1 }}</td>
                                <td>{{ record.first_name+' '+record.last_name }}</td>
                                <td>{{ record.number1 }}</td>
                                <td>{{ record.number2 }}</td>
                                <td>{{ record.number3 }}</td>
                                <td>{{ record.company }}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" type="button" @click="deleteUnRecord(record.number1)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <span v-else-if="(showrecords == 2) && (drcount >= 1)">
                        <table class="table table-condensed table-striped" v-for="(groupedRecord, index) in duplicate_Records" :key="'row'+index">                    
                            <thead>
                                <tr>
                                    <th colspan="6">Duplicate Number1:  {{ groupedRecord[0].number1 }}</th>
                                    <th width="wf-80">Action</th>
                                </tr>
                            </thead>                    
                            <tbody>
                                <tr v-for="(record,sno) in groupedRecord" :key="'in-row-'+sno">   
                                    <td>{{ sno+1}} </td>                      
                                    <td>{{ record.first_name +' '+ record.last_name }}</td>
                                    <td>{{ record.number1 }} </td>
                                    <td>{{ record.number2 }}</td>
                                    <td>{{ record.number3 }}</td>
                                    <td>{{ record.company }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger mr-1" type="button" @click="deleteGroupRecords(record.number1, record.number2, record.number3, record.first_name)"><i class="bi bi-x"></i> </button>
                                        <button  class="btn btn-sm btn-success" type="button" @click="swapRecords(record.number1, record.number2, record.number3)"><i class="bi bi-arrow-down-up"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </span>
                    <p class="alert alert-info" v-else-if="(duplicate_Records.length == 0) && (showrecords == 2)">
                        No duplicate records found.
                    </p>
                </div>
            </div>
        </div>
        <div class="row m-0" v-else-if="step == 3">
            <div class="col-sm-12 text-center p-3">
                <h4>Syncing Started</h4>
                <p class="alert alert-info">
                    <b> {{ unique_Records.length + duplicate_Records.length }} unique records will be imported. </b>
                </p>
            </div>
            <div class="col-md-3 col-12">
                
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group" v-if="form.destination == 'activecampaign'">
                    <label for="form_name">Enter List Name</label>
                    <input type="text" name="form_name" id="form_name" class="form-control" v-model="form.name">
                    <i class="text-warning">Keep this field empty if you do not want to create new list</i>
                </div>
                <div v-else-if="form.destination == 'five9'">
                    <div class="form-group">
                        <label for="form_name">Select List</label>
                        <select required name="lid" id="lid" class="form-control" v-model="form.lid">
                            <option value="">Select List</option>
                            <option value="0">Create New List</option>
                            <option :value="lname.name" v-for="(lname, lk) in five9_list" :key="'listid'+lk">
                                {{ lname.name }} ({{ lname.size }})
                            </option>
                        </select>
                    </div>
                    <div class="form-group" v-show="form.lid == '0'">
                        <label for="form_name">Enter List Name</label>
                        <input type="text" required name="form_list_name" id="form_list_name" class="form-control" v-model="form.name">
                    </div>
                    <div class="form-group">
                        <label for="form_name">Select Campaign</label>
                        <select required name="cid" id="cid" class="form-control" v-model="form.cid">
                            <option value="">Select Campaign</option>
                            <option value="0">Create New Campaign</option>
                            <option :value="cname.name" v-for="(cname, ck) in five9_ocampaigns" :key="'campaignid'+ck">
                                {{ cname.name }} ({{ cname.mode }}) - {{  cname.state }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group" v-show="form.cid == '0'">
                        <label for="form_name">Enter Campaign Name</label>
                        <input type="text" required name="form_Campaign_name" id="form_Campaign_name" class="form-control" v-model="form.campaign">
                    </div>
                </div>
                <img :src="loader_url" v-if="loader == 4">
                <button class="btn theme-btn btn-sm btn-primary" v-else type="button" @click="startSyncing"> Start Uploading</button>
                <p class="text-danger"> {{errornote }}</p>
            </div>
            <div class="col-md-3 col-12">
                
            </div>
        </div>
        <div class="m-0" v-else-if="step == 4">
            <h5 class="p-4 text-center text-dark">Import process has been completed successfully.<br></h5>
            <div class="p-4 text-center" style="font-size:16px;" v-html="report"></div>
        </div>
    </div>
</template>
<script>

export default {
  data(){
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            verror:0,
            callNowQueued : '',
            crmRecordsInserted : '',
            crmRecordsUpdated : '',
            importIdentifier : '',
            keyFields : '',
            listName : '',
            listRecordsDeleted : '',
            listRecordsInserted : '',
            recordDispositionsReset : '',
            success : '',
            uploadDuplicatesCount : '',
            uploadErrorsCount : '',
            importTroubles : [],
            records : [],
            options : ['number1', 'first_name', 'last_name'],
            alloptions : [],
            newArray : [],
            arrayRange : [],
            activeId:'',
            history:[],
            errorArray:[],
            step:1,
            showrecords:0,
            recordContainer:[],
            form: new Form({
                type:'csv',
                file:'',
                template_id:'',
                source:'',
                destination:'',
                fdata:[],
                report:'',
                fields:[],
                efields:[],
                formatter:[],
                records:'',
                checkall:false,
                name:'',
                campaign:'',
                lid:'',
                cid:''
            }),
            unique_Records: [],
            duplicate_Records: [],
            groupKey:[],
            errornote:'',
            five9_list:'',
            errorArrayExport : [],
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
            return this.form.fdata.length - this.unique_Records.length;
        }
    },
    methods: {
        
        generateRecords(){
            var t =this
            var formdata = []
            for(var i = 0; i <= t.recordContainer.length; i++){
                    for(var j = 0; j <= t.errorArrayExport.length; j++){
                        if(t.recordContainer[i] == j){
                            console.log(t.errorArrayExport[j])
                            formdata[j] = t.errorArrayExport[j]
                        }
                    }
            }
            this.form.fdata = formdata
            this.makingGroupedRecords()                         
        },
        makingGroupedRecords(){
            this.unique_Records = [];
            this.duplicate_Records = [];
            let gkey = this.form.fdata.reduce((acc, it) => {
                acc[it.number1] = acc[it.number1] + 1 || 1;
                return acc;
            }, {});
            this.groupKey = gkey;
            let gk = this;
            this.form.fdata.forEach(element => {
                if(gk.groupKey.hasOwnProperty(element.number1) && gk.groupKey[element.number1] == 1) {
                    gk.unique_Records.push(element);
                }
            });
            Object.keys(gkey).forEach(function(key) {
                if(gkey[key] >= 2) {
                    let grp = gk.form.fdata.filter(rec => { return rec.number1 == key });
                    gk.duplicate_Records.push(grp);
                }
            });
            this.step = 2
            this.form.destination = 'five9';
            axios.get('/api/get-type-template/'+this.form.destination).then((response) => {
                this.templates = response.data;
            })
            axios.get('/api/get-f9-list').then((response) => {
                this.five9_list = response.data;
            });
            axios.get('/api/get-f9-campaigns').then((response) => {
                this.five9_campaigns = response.data;
            });
        },
        checkRecords(){
            if(this.form.fdata.length > this.unique_Records) {
                let diff = this.form.fdata.length - this.unique_Records;
                    this.$toasted.show(diff +' records are still duplicate. We will convert them to '+ this.duplicate_Records.legth +' unique records.', { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
            }
            this.step = 3;            
        },
        swapRecords(n1, n2, n3){
            if(n2 == 0) {
                //Vue.$toast.error("Numbers can not be swapped. Number1 can not be 0 !!");
                this.$toasted.show("Numbers can not be swapped. Number1 can not be 0 !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
                return false;
            }
            if(n1 == n2) {
                //Vue.$toast.error("Numbers can not be swapped. Both numbers are same !!");
                this.$toasted.show("Numbers can not be swapped. Number1 can not be 0 !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
                return false;
            }
            let arr = this.form.fdata; let intrchnage = '';
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3) { 
                    arr[i].number1 = n2;
                    arr[i].number2 = n1;
                }
            }
            this.makingGroupedRecords();

            //Vue.$toast.info("Numbers swapped successfully !!");
            this.$toasted.show("Numbers swapped successfully !!", { 
                theme: "toasted-primary", 
                position: "bottom-center", 
                duration : 2000
            });
        },
        deleteGroupRecords(n1, n2, n3, p1) {
            let arr = this.form.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3 && arr[i].first_name === p1) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            //Vue.$toast.info("Record removed successfully !!");
            this.$toasted.show("Record removed successfully !!", { 
	            theme: "toasted-primary", 
	            position: "bottom-center", 
	            duration : 2000
            });
        },
        deleteRecord(cid) {
            let arr = this.form.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( i === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            //Vue.$toast.info("Record removed successfully !!");
            this.$toasted.show("Record removed successfully !!", { 
	            theme: "toasted-primary", 
	            position: "bottom-center", 
	            duration : 2000
            });
        },
        deleteUnRecord(cid) {
            arr = this.form.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( arr[i].number1 === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            //Vue.$toast.info("Record removed successfully !!");
            this.$toasted.show("Record removed successfully !!", { 
	            theme: "toasted-primary", 
	            position: "bottom-center", 
	            duration : 2000
            });
        },
        startSyncing() {
            if(this.form.destination == 'five9' && this.form.name == '' && this.form.lid == '0') {
                //Vue.$toast.warning("List name is mandatory !!");
                this.$toasted.show("List name is mandatory !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    duration : 2000
                });
                return false;
            }
            //this.loader = 4;
            this.form.post('/api/uploadContactsExport').then((response) => {
                this.loader = false;
                if(response.data.status == 'success'){
                    this.form.reset();
                    this.step = 4;
                    this.report = response.data.message;
                    this.identifier = response.data.identifier
                } else if(response.data.status == 'error'){
                    this.errornote = response.data.message;
                } else {
                    //Vue.$toast.warning(response.data.result);
                    this.$toasted.show(response.data.result, { 
                        theme: "toasted-primary", 
                        position: "bottom-center", 
                        duration : 2000
                    });
                }
            })
        },
        addAndRemoveAllRecordToContainer(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            if(document.getElementById("check-all").checked == true){
                for (var i = 0; i < aa.length; i++){                    
                    if((this.recordContainer.indexOf(parseInt(aa[i].value)) == -1)){
                        aa[i].checked = true;
                        this.recordContainer.push(Number(aa[i].value));
                    }else{
                        aa[i].checked = true;
                    }                    
                }
            }
            if(document.getElementById("check-all").checked == false){
                var record = [];
                for (var i = 0; i < aa.length; i++){
                    if((this.recordContainer.indexOf(parseInt(aa[i].value)) >= 0)){
                        this.recordContainer.splice(this.recordContainer.indexOf(parseInt(aa[i].value)), 1);
                        aa[i].checked = false;
                    }
                }
            }
        },
        addAndRemoveRecordToContainer(index){
            if((this.recordContainer.indexOf(parseInt(index)) == -1) && (document.getElementById("record-"+index).checked == true)){
                this.recordContainer.push(index);
            }else{
                this.recordContainer.splice(this.recordContainer.indexOf(parseInt(index)), 1);
            }
        }, 
        getReport(){
          this.loader = true;
            axios.get('/api/get-single-history/'+ this.activeId).then((response) => {    
                this.history = response.data;
            });
            axios.get('/api/get-reports-detail/'+ this.activeId).then((response) => {    
                this.keyFields = response.data.results.keyFields;
                this.listName = response.data.results.listName;
                this.uploadDuplicatesCount = response.data.results.uploadDuplicatesCount;
                this.uploadErrorsCount = response.data.results.uploadErrorsCount;
                this.errorArray = response.data.errorArray
                // this.form.fdata = response.data.errorArrayExport
                this.errorArrayExport = response.data.errorArrayExport
                this.form.efields = response.data.keys
                if(response.data.results.hasOwnProperty('importTroubles')) {
                    if(Array.isArray(response.data.results.importTroubles) === true) {
                        this.importTroubles = response.data.results.importTroubles;
                    } else {
                        this.importTroubles.push(response.data.results.importTroubles);
                    }
                } else {
                    this.importTroubles = [];    
                }
                this.alloptions = response.data.keys
                this.newArray = response.data.newArray
                this.loader = false;
            });
        },
        toggleVR() {
            if(this.verror == false) {
                this.verror = true;
            } else {
                this.verror = false;
            }
        },
        toggleOption(val) {
            let array = this.options;
            let index = array.indexOf(val);
            if (index > -1) {
                array.splice(index, 1);
            } else {
                array.push(val);
            }
        },
    },
    beforeMount() {
        this.activeId = this.$route.params.id;
    },
    mounted() {
        this.getReport();
    }
}  
</script>