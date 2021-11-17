<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-3 col-12 pl-0">
                    <input type="file" name="file" id="file" class="form-control p-a-3" @change="loadCSV">
                </div>
                <div class="col-md-3 col-12 pl-0">
                    <select name="destination" id="destination" v-model="form.destination" class="form-control" @change="startMapping">
                        <option value="">Select Location</option>
                        <option value="five9">FIVE9</option>
                    </select>
                </div>
                <div class="col-md-6 col-12 p-0">
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="startMapping" v-show="step == 0">
                        start mapping <i class="bi bi-arrow-right"></i>
                    </button>
                    <img :src="loader_url" v-show="loader == true">
                    <button v-if="step == 1" type="button" class="btn btn-primary icon-btn-right theme-btn float-right" @click="ChooseTemplate(3)">
                        <i class="bi bi-arrow-right"></i> Next
                    </button>
                    <button v-if="step == 2" type="button" class="btn btn-dark icon-btn theme-btn float-right mx-1" @click="backToStepOne">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                    <button v-if="step == 3" type="button" class="btn btn-dark icon-btn theme-btn float-right mx-1" @click="backToStepTwo">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                    <!-- <button type="button" class="btn btn-danger icon-btn theme-btn float-right" @click="resetForm">
                        <i class="bi bi-arrow-counterclockwise"></i> Start Again
                    </button> -->
                </div>
            </div>
        </div>
        <div class="mapping-div">
            <div class="row m-0" v-if="parse_header.length >= 1 && step == 1">
                <div class="col-md-9 col-12">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>{{ form.destination | uppercase }} Field </th>
                                 <th>File Field</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(key, kkey) in options" :key="kkey">
                                <td> {{ kkey+1 }} </td>
                                <td>{{ key | titleFormat }}</td>
                                <td class="p-1">
                                    <select :name="'map_field['+kkey+']'" :id="'map_field_'+kkey" class="form-control" v-model="tform.sfields[kkey]">
                                        <option value="">Select Relevent Field</option>
                                        <option v-for="option in parse_header" :value="option" :key="option">{{ option | titleFormat }}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>  
                </div>
                <div class="col-md-3 col-12 py-3" v-show="form.destination">
                    <h5>Sync Data By Template</h5>
                    <img :src="loader_url" v-if="loader == 3">
                    <span v-else>
                        <button class="btn btn-sm btn-danger mb-2 text-uppercase btn-block" type="button"  @click="smartMapping"> Smart Mapping </button>
                        <div class="form-group">
                            <label for="template"></label>
                            <select name="template_id" id="template_id" class="form-control" v-model="form.template_id" @change="applyTemplate">
                                <option value="">Select Or Create Template</option>
                                <option v-for="template in templates" :value="template.id" :key="template.id">{{ template.name }}</option>
                            </select>
                        </div>
                        <img :src="loader_url" v-if="loader == 2">
                        <span v-else>
                            <!-- <button v-show="form.template_id >= 1" class="btn btn-sm btn-success mb-2 btn-block" type="button" @click="ChooseTemplate(1)"> Proceed With Selection</button> -->
                            <input type="text" name="name" id="name" placeholder="Enter New Template Name" v-model="tform.name" class="form-control mb-1">
                            <button class="btn btn-sm btn-primary mb-2 text-uppercase btn-block" type="button"  @click="ChooseTemplate(2)"> Save As New </button>
                            <!-- <button class="btn btn-sm btn-dark mb-2 text-uppercase btn-block" type="button"  @click="ChooseTemplate(3)"> Proceed without save </button> -->
                        </span>
                    </span>
                </div>
            </div>
            <div class="row m-0" v-else-if="parse_header.length >= 1 && step == 2">
                <div class="col-md-12 col-12 py-2">
                    <span class="d-block">
                        <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 0)?'btn-dark':'btn-secondary']" @click="showrecords = 0">All Records [{{form.fdata.length }}]</button>          
                        <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 1)?'btn-dark':'btn-secondary']" @click="showrecords = 1">Unique Records [{{ unique_Records.length }}]</button>          
                        <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 2)?'btn-dark':'btn-secondary']" @click="showrecords = 2">Duplicate Records [{{ drcount }}]</button>     
                        <button type="button" class="btn btn-sm theme-btn mr-1" :class="[(showrecords == 2)?'btn-dark':'btn-secondary']" @click="showrecords = 3">Non USA Contact [{{ non_usa_records.length }}]</button>     
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
                                    <th>Contry</th>
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
                                    <td>{{ record.country }}</td>
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
                                    <th>Country</th>
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
                                    <td>{{ record.country }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" type="button" @click="deleteUnRecord(record.number1)">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-striped table-condensed" v-if="showrecords == 3">
                            <thead>
                                <tr>
                                    <th>SNo</th>
                                    <th>Name</th>
                                    <th>Number1</th>
                                    <th>Number2</th>
                                    <th>Number3</th>
                                    <th>Country</th>
                                    <th>
                                        <button class="btn btn-sm btn-danger" type="button" @click="deleteAllNonUsaRecors" v-if="non_usa_records.length > 0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" type="button" v-else>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, rkey) in non_usa_records" :key="'unique-'+rkey">
                                    <td>{{ rkey+1 }}</td>
                                    <td>{{ record.first_name+' '+record.last_name }}</td>
                                    <td>{{ record.number1 }}</td>
                                    <td>{{ record.number2 }}</td>
                                    <td>{{ record.number3 }}</td>
                                    <td>{{ record.country }}</td>
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
                            <v-select 
                            label="name" 
                            class="form-control" 
                            :options="five9_list_new" 
                            v-model="formlid"                            
                            @input="showList"
                            >
                            </v-select>
                            <!-- <select required name="lid" id="lid" class="form-control" v-model="form.lid">
                                <option value="">Select List</option>
                                <option value="0">Create New List</option>
                                <option :value="lname.name" v-for="(lname, lk) in five9_list" :key="'listid'+lk">
                                    {{ lname.name }} ({{ lname.size }})
                                </option>
                            </select> -->
                        </div>
                        <div class="form-group" v-show="form.lid == '0'">
                            <label for="form_name">Enter List Name</label>
                            <input type="text" required name="form_list_name" id="form_list_name" class="form-control" v-model="form.name">
                        </div>
                        <div class="form-group">
                            <label for="form_name">Select Campaign</label>
                            <v-select 
                            label="details" 
                            class="form-control" 
                            :options="five9_ocampaigns_new" 
                            v-model="formcid"                            
                            @input="showCampaign"
                            >
                            </v-select>                            
                            <!-- <select required name="cid" id="cid" class="form-control" v-model="form.cid">
                                <option value="">Select Campaign</option>
                                <option value="0">Create New Campaign</option>
                                <option :value="cname.name" v-for="(cname, ck) in five9_ocampaigns" :key="'campaignid'+ck">
                                    {{ cname.name }} ({{ cname.mode }}) - {{  cname.state }}
                                </option>
                            </select>  -->                           
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
                <h5 class="p-4 text-center text-dark">Import process has been completed successfully.<br>
                </h5>
                <div class="p-4 text-center" style="font-size:16px;" v-html="report"></div>
            </div>
            <div class="row m-0" v-else>
                <p class="p-4 text-center text-danger">Select CSV file and destination to start mapping ... </p>
            </div>
        </div>
    </div>
</template>
<script>
import 'vue-search-select/dist/VueSearchSelect.css'
export default {
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            step:0,
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
            tform: new Form({
                dfields:[],
                sfields:[],
                formatter:[],
                template_id:'',
                source:'',
                name:''
            }),
            options: [],
            climit:'',
            templates:{},
            export_data:[],
            channel_name: '',
            channel_fields: [],
            channel_entries: [],
            parse_header: [],
            parse_csv: [],
            sortOrders:{},
            sortKey: '',
            report:'',
            errornote:'',
            five9_list:'',
            five9_campaigns:[],
            mapped_ranges:[],
            testing: [],
            groupedRecords : [],
            groupKey:[],
            showrecords:0,
            unique_Records: [],
            duplicate_Records: [],
            non_usa_records : [],
            // new variables
            unique_RecordsBack : [],
            duplicate_RecordsBack : [],
            groupKeyBack : [],
            formcid:'',
            formlid:'',
            countries: [],
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
        phoneFormatted: function (str) {
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
            var newArray = this.five9_campaigns.filter((cam) => { return cam.type == 'OUTBOUND' });
            return newArray
        },
        five9_list_new(){
            var newArray = []
            newArray[0] = {value : 0, name : "Create New List"}
            for(var i = 1; i <= this.five9_list.length; i++){
                if(this.five9_list[i]){
                    newArray[i] = {
                        value : this.five9_list[i].name,
                        name : this.five9_list[i].name +' ('+ this.five9_list[i].size+ ')'
                    }
                }
            }
            return newArray
        },
        five9_ocampaigns_new () {
            var newCampaignList = this.five9_campaigns.filter((cam) => { return cam.type == 'OUTBOUND' });
            var newArray = []
            newArray[0] = {value: 0, name: "Create New Campaign", details: "Create New Campaign"}
            for(var i = 1; i < newCampaignList.length; i++){
                if(newCampaignList[i]){
                    newArray[i] ={
                                    value: newCampaignList[i].name,
                                    name: newCampaignList[i].name,
                                    details : newCampaignList[i].name +'('+newCampaignList[i].mode+') '+newCampaignList[i].state,
                                }
                }
            }
            return newArray;
        },
        drcount() {
            return this.form.fdata.length - this.unique_Records.length;
        }
    },
    methods: {
        showList(value){
            this.form.lid = this.formlid.value;
        },
        showCampaign(value){
            this.form.cid = this.formcid.value;
        },
        // backToStepZero(){
        //     this.form.destination = ''

        // },
        loadCSV(e) {
            this.loader = true;
            var vm = this
            if (window.FileReader) {
                var reader = new FileReader();
                reader.readAsText(e.target.files[0]);
                // Handle errors load
                reader.onload = function(event) {
                    var csv = event.target.result;
                    vm.form.file = csv;
                    vm.parse_csv = vm.csvJSON(csv); 
                };
                reader.onerror = function(evt) {
                    if(evt.target.error.name == "NotReadableError") {
                        Vue.$toast.warning("Can't read the file  !!");
                    }
                };
            } else {
                Vue.$toast.warning("FileReader is not supported in this browser !!");
            }
            this.loader = false;
        },
        csvJSON(csv) {
            var vm = this;
            vm.tform.sfields = [];
            vm.tform.dfields = [];
            vm.form.fields = [];
            var lines = csv.split("\n")
            var result = []
            var headers = lines[0].split(",");
            vm.parse_header = lines[0].split(",");
            for(var i = 0; i < vm.parse_header.length; i++){
                if(vm.parse_header[i]){
                    vm.parse_header[i] = vm.parse_header[i].trim()
                }
            }
            
            lines[0].split(",").forEach(function (key) {
                vm.sortOrders[key] = 1;
            })
            
            lines.map(function(line, indexLine){
                if (indexLine < 1) return // Jump header line
                
                var obj = {}
                var currentline = line.split(",")
                
                headers.map(function(header, indexHeader){
                obj[header.trim()] = currentline[indexHeader];
                })
                
                result.push(obj)
            })
      
            result.pop() // remove the last item because undefined values
            //console.log(result);
           return result // JavaScript object
        },
        startMapping() {
            let vm = this;
            if(this.form.destination == '') {
                Vue.$toast.warning("Please select the destination to start mapping !!");
                this.options = [];
            } else if(this.form.destination == 'activecampaign') {
                this.climit = this.$store.getters.currentConfig.activecampaign_maxsync;
                this.options = this.ac_options;
                axios.get('/api/get-type-template/'+this.form.destination).then((response) => {
                    this.templates = response.data;
                })
                this.step = 1;
            } else if(this.form.destination == 'outreach') {
                this.climit = this.$store.getters.currentConfig.outreach_maxsync;
                this.options = this.or_options;
                axios.get('/api/get-type-template/'+this.form.destination).then((response) => {
                    this.templates = response.data;
                })
                this.step = 1;
            } else if(this.form.destination == 'five9') {
                this.climit = this.$store.getters.currentConfig.five9_maxsync;
                this.options = this.f9_options;
                this.options.forEach(function (key, index) {
                    vm.tform.sfields.push('');
                    vm.tform.dfields.push(key);
                });
                axios.get('/api/get-type-template/'+this.form.destination).then((response) => {
                    this.templates = response.data;
                })
                axios.get('/api/get-f9-list').then((response) => {
                    this.five9_list = response.data;
                });
                axios.get('/api/get-f9-campaigns').then((response) => {
                    this.five9_campaigns = response.data;
                });
                
                this.step = 1;
            } else {
                this.options = [];
            }
            
        },
        resetForm() {
            window.location.reload();
        },
        backToStepOne(){
            var vm = this
            this.tform.source = ''
            this.form.efields = []
            this.form.fdata = []
            this.tform.name = ''
            vm.parse_csv = this.csvJSON(vm.form.file)
            this.startMapping()
            vm.step = 1
        },
        smartMapping(){
            this.loader = 3;
            let t = this;
            for(let i = 0; i < t.options.length; i++){ // form database                
                for (let index = 0; index < t.parse_header.length; index++) { // form csv file : top row
                    let tfd = t.tform.dfields[i].toLowerCase(); // form database

                    if(t.mapped_ranges[tfd].indexOf(t.parse_header[index]) >= 0) {
                        t.tform.sfields[i] = t.parse_header[index];
                    }
                }
            }
            this.loader = false;
        },
        applyTemplate() {
            this.loader = 2;
            let vm = this;
            let tmplat = vm.templates.filter(function(item) {  return (item.id == vm.form.template_id)?item:''});
            if(tmplat == '') {
                Vue.$toast.warning('This template is not a valid template. Please change the template to apply.');
                return false;
            } else {
                let mapd = JSON.parse(tmplat[0].mapped);
                let df = mapd.dest;
                let sf = mapd.sourced;
                if(df.length != vm.tform.dfields.length) {
                    Vue.$toast.warning('This template is not a valid template. Please change the template to apply.');
                    return false;
                }
                df.forEach(function (key, index) {
                    if(vm.parse_header.includes(sf[index])) {
                        vm.tform.sfields[index] = sf[index];
                    }
                });
            }
            this.loader = false;
        },
        ChooseTemplate(type) {
            this.loader = 3;
            this.tform.source = this.form.destination;
            this.form.efields = this.tform.dfields;
            this.form.fields = this.tform.sfields;
            if(this.tform.source == 'five9') {
                if(this.form.efields.includes('number1') == false) {
                    Vue.$toast.warning("Field name 'Number 1' is required to start mapping !!");
                    return false;
                }
            }
            if(this.form.fields.length == 0) {
                Vue.$toast.error("Please select atleast one fields to start syncing !!");
                return false;
            }
            if(type == 2) {
                if(this.tform.name == '') {
                    Vue.$toast.error("Please enter name for new template !!");
                }
                else {
                    this.tform.post('/api/add-template')
                    .then((responseTemp) => {
                        axios.get('/api/get-type-template/'+this.form.destination).then((response) => {
                            this.templates = response.data;
                            this.form.template_id = responseTemp.data.id
                        })
                    });
                }
            } else if(type == 3 || type == 1) {
                this.step = 2;
            }
            this.setFileFields();
        },
        setFileFields() {            
            let fdt = []; let fnw = [];
            let $this = this;
            $this.form.fdata = [];
            $this.form.fdata =  $this.parse_csv;
            $this.form.fdata.forEach(function (key, index) {
                fnw = [];
                $this.parse_header.forEach(function(element, ekey) {
                    if($this.form.fields.indexOf(element) >= 0) {
                        let nkey = $this.form.efields[$this.form.fields.indexOf(element)];
                        let ov = '';
                        if(nkey == 'number1' || nkey == 'number2' || nkey == 'number3') {
                            ov = $this.FormatNumber($this.form.fdata[index][element]);
                        } else {
                            ov = $this.form.fdata[index][element];
                        }
                        $this.form.fdata[index][nkey] = ov;
                        delete $this.form.fdata[index][element];
                    }
                    else {
                      delete $this.form.fdata[index][element];
                    }
                });                
            });
            this.makingGroupedRecords();
            this.loader = false;
        },
        makingGroupedRecords(){
            this.unique_Records = [];
            this.duplicate_Records = [];
            this.non_usa_records = [];
            let gkey = this.form.fdata.reduce((acc, it) => {
                acc[it.number1] = acc[it.number1] + 1 || 1;
                return acc;
            }, {});
            this.groupKey = gkey;
            let gk = this;
            //non_usa_records
            this.form.fdata.forEach(element => {
                if(gk.groupKey.hasOwnProperty(element.number1) && gk.groupKey[element.number1] == 1) {
                    gk.unique_Records.push(element);
                }
                if((element.country) && (element.country != '') && this.countries.indexOf(element.country) == -1){
                    this.non_usa_records.push(element)
                }
            });
            Object.keys(gkey).forEach(function(key) {
                if(gkey[key] >= 2) {
                    let grp = gk.form.fdata.filter(rec => { return rec.number1 == key });
                    gk.duplicate_Records.push(grp);
                }
            });
        },
        deleteAllNonUsaRecors(){
            var data = []
            var counter = 0
            this.form.fdata.forEach( (element,index) => {
                if((element.country) && (element.country != '') && (this.countries.indexOf(element.country) >= 0)){
                    data[counter++] = element
                }
            });
            this.form.fdata = []
            this.form.fdata = data
            this.makingGroupedRecords();
            Vue.$toast.info("All Non-USA contacts removed successfully !!");
        },
        checkRecords(){
            if(this.form.fdata.length > this.unique_Records) {
                let diff = this.form.fdata.length - this.unique_Records;
                Vue.$toast.info(diff +' records are still duplicate. We will convert them to '+ this.duplicate_Records +' unique records.', { duration: 8000 });
            }            
            this.step = 3;            
        },
        deleteGroupRecords(n1, n2, n3, p1) {
            let arr = this.form.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3 && arr[i].first_name === p1) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        deleteRecord(cid) {
            let arr = this.form.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( i === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        deleteUnRecord(cid) {
            let arr = this.form.fdata;
            for( var i = 0; i < arr.length; i++){ 
                if ( arr[i].number1 === cid) { 
                    arr.splice(i, 1); 
                }
            }
            this.makingGroupedRecords();
            Vue.$toast.info("Record removed successfully !!");
        },
        backToStepTwo(){
            this.loader = true
            this.step = 2
            this.loader = false
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
            let arr = this.form.fdata; let intrchnage = '';
            for( var i = 0; i < arr.length; i++){ 
                if (arr[i].number1 === n1 && arr[i].number2 === n2 && arr[i].number3 === n3) { 
                    arr[i].number1 = n2;
                    arr[i].number2 = n1;
                }
            }
            this.makingGroupedRecords();

            Vue.$toast.info("Numbers swapped successfully !!");
        }, 
        FormatNumber(str) {
            if(str == '' || str == 'undefined' || str == null) {
                return 0;
            }
            if(str.length == 10){
                return str
            }
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
        },

        startSyncing() {
            if(this.form.destination == 'five9' && this.form.name == '' && this.form.lid == '0') {
                Vue.$toast.warning("List name is mandatory !!");
                return false;
            }
            this.loader = 4;
            this.form.post('/api/uploadContactsExport').then((response) => {
                this.loader = false;
                if(response.data.status == 'success'){
                    this.form.reset();
                    this.step = 4;
                    this.report = response.data.message;
                } else if(response.data.status == 'error'){
                    this.errornote = response.data.message;
                } else {
                    Vue.$toast.warning(response.data.result);
                }
            })
        },
        goBack() {
            this.step = this.step - 1;
        },
        
        uploadCSV(e) {
            let file = e.target.files[0];
            let reader = new FileReader();
            let pi = this.form;
            reader.onloadend = (file) => {
                pi.file = reader.result;
            }
            reader.readAsDataURL(file);
        },
        
    },
    mounted() {
        axios.get('/api/get-mapping-range').then((response) => {
            this.mapped_ranges = response.data;
        });
        axios.get('/api/get-f9-countries').then((response) => {
            this.countries = response.data.countries.value.split(",");
        });
    }
}
</script>