<template>
    <div>
        <div class="filterbox">
            <div style="white-space: nowrap; overflow-x: auto; height: 50px;">
                <button class="btn btn-sm m-1 theme-btn" :class="[(form.field == field)?'btn-dark':'btn-outline-dark ']" v-for="(field,index) in form.fields" :key="'input-fields-'+index" @click="getData(field, 1)">
                    {{ (display_name_key.indexOf(field) >= 0)? display_name_value[display_name_key.indexOf(field)]:field | inputFields }}
                </button>
            </div>
        </div>
        <div class="bg-white pt-2 overflow-hidden">
            <p class="p-4" v-if="loader">
                <img :src="loader_url" alt="loading...">
            </p>
            <div class="row m-0" v-else>
                <div class="col-md-3">
                    <div class="divtable border" v-if="form.field">
                        <div class="divthead">
                            <div class="divthead-row">
                                <div class="divthead-elem wf-50">
                                    <input :checked="form.records.length == form.meargRecords.length" type="checkbox" id="all-record-checkbox" @click="selectAllRecords"> 
                                </div>
                                <div class="divthead-elem mwf-100">
                                    Record ({{form.meargRecords.length | freeNumber}}/{{form.records.length | freeNumber}})
                                    <i class="bi bi-arrow-up" :class="[(recordSorting == 'field')?'text-dark':'']" v-if="recordSorting=='field' && recordSortingType=='desc'" @click="recordSorting='field',recordSortingType='asc'"></i>
                                    <i class="bi bi-arrow-down" :class="[(recordSorting == 'field')?'text-dark':'']" v-else @click="recordSorting='field',recordSortingType='desc'"></i>
                                </div>
                                <div class="divthead-elem wf-100 text-center">
                                    Count
                                    <i class="bi bi-arrow-up" :class="[(recordSorting == 'total')?'text-dark':'']"  @click="recordSorting='total',recordSortingType='desc'" v-if="recordSorting=='total' &&recordSortingType=='asc'"></i>
                                    <i class="bi bi-arrow-down" :class="[(recordSorting == 'total')?'text-dark':'']" v-else @click="recordSorting='total',recordSortingType='asc'"></i>
                                </div>
                            </div>
                        </div>
                        <div class="divtbody fit-content">
                            <div class="divtbody-row" v-for="(field,index) in formrecords" :key="'input-field-record-'+index">
                                <div class="divtbody-elem wf-50">
                                    <input type="checkbox" name="records[]" :value="field.field" @click="selectAllSingleCheckbox"  class="all-record" :id="'all-record-' + index" :checked="form.meargRecords.length > 0 && form.meargRecords.indexOf(field.field) > -1">
                                </div>
                                <div class="divtbody-elem mwf-100"> 
                                    <span v-if="form.stage.length > 0">
                                        {{ form.stage[field.field] }} 
                                    </span>
                                    <span v-else>
                                        {{ field.field }} 
                                    </span>
                                </div>
                                <div class="divtbody-elem wf-100 text-center"> <b> {{ field.total | freeNumber }} </b> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9 p-0" v-show="(transferBtn || resetBtn || meargeBtn || updateBtn || tagBtn)">
                    <div class="border-bottom pr-3" v-show="form.records.length && form.field">
                        <div class="mb-1">
                            <toggle-button @change="toggle = !toggle"  :margin="3" :width="235" :height="30" :labels="{checked: 'Unique Record Based Graph', unchecked: 'Unique Count Based Graph'}" :switch-color="{checked: '#800080', unchecked: '#27408B'}" :color="{checked: '#E599E5', unchecked: '#4E9FFE'}" />
                            <select style="width:130px; display: none;" class="form-control btn btn-secondary theme-btn  btn-block text-left wf-150" v-model="form.allStage" @change="getData(form.field, 2)" v-if="form.field != 'stage'">
                                <option value="">Select Stage</option>
                                <option v-for="(stage, index) in stages" :key="'stage-' + index" :value="stage.oid">{{ stage.name }}</option>
                            </select>
                            <div class="float-right" v-show="form.meargRecords.length">
                                <i class="bi bi-gear-wide-connected icn-spinner cursor-pointer" @click="showaction = true"></i>
                            </div>
                        </div>
                        <div class="slide-action" :class="[(showaction)?'slidein-action':'slideout-action']">
                            <div class="float-left outer-icon">
                                <i class="bi bi-gear-wide-connected"></i>
                            </div>
                            <h5 class="text-center my-2 ml-1  mr-4"> {{ this.totalCount | freeNumber }} prospects will be modified. </h5>
                            <div class="float-right outer-icon-right">
                                 <i class="bi bi-x-circle fs-18 cursor-pointer" @click="showaction=false"></i>
                            </div>
                            <div class="clearfix"></div>
                            <div class="action-btns" v-if="action_status === ''">
                                <div class="action-btn" @click="action_status = 'resetBtn'">
                                    <i class="bi bi-x-octagon"></i>
                                    <span> Reset Data </span>
                                </div>
                                <div class="action-btn" @click="action_status = 'mergeBtn'">
                                    <i class="bi bi-tools"></i>
                                    <span>Merge Data </span>
                                </div>
                                <div class="action-btn" @click="action_status = 'updateBtn'">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Update Data </span>
                                </div>
                                <div class="action-btn" @click="action_status = 'transferBtn'">
                                    <i class="bi bi-arrow-left-right"></i>
                                    <span>Transfer Data </span>
                                </div>
                                <div class="action-btn" @click="action_status = 'tagBtn'" v-if="enableTag == true">
                                    <i class="bi bi-funnel"></i>
                                    <span>Refine Tag </span>
                                </div>
                            </div>

                            <div class="action-data" v-else>
                                <div class="action-title">
                                    <i class="bi bi-arrow-left-square cursor-pointer fs-20" @click="action_status = ''"></i>
                                    <span class="fs-20 ml-3" v-if="action_status == 'resetBtn'">Reset Data </span>
                                    <span class="fs-20 ml-3" v-else-if="action_status == 'mergeBtn'">Merge Data </span>
                                    <span class="fs-20 ml-3" v-else-if="action_status == 'updateBtn'">Update Data </span>
                                    <span class="fs-20 ml-3" v-else-if="action_status == 'transferBtn'">Transfer Data </span>
                                    <span class="fs-20 ml-3" v-else-if="action_status == 'tagBtn'">Refine Tag Data </span>
                                    <span class="fs-20 ml-3" v-else>No Action Defined </span>
                                </div>
                                <div class="action-content p-2">
                                    <div class="pt-2" v-if="action_status == 'resetBtn'">
                                        <div class="alert alert-warning">
                                            <h4><i class="bi bi-exclamation-triangle-fill"></i> Are you sure?</h4>
                                            <p class="fs-14">By this action, Corresponding column will be set to NULL for the selected record values.<br> And you will not be able to revert it at this moment.</p>
                                            <p class="fs-14">Click on <b>YES</b> button to continue.</p>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                    <button class="btn theme-btn btn-warning" type="button" @click="action_status = ''">No</button>
                                            </div>
                                            <div class="col-6 text-right">
                                                    <button class="btn theme-btn btn-primary" type="button" @click="resetDateAction">Yes</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-2"  v-else-if="action_status == 'mergeBtn'">
                                        <div v-if="form.meargRecords.length >= 2">
                                            <h5>Select value to overright Others:</h5>
                                            <div style="max-height:calc(50vh); overflow:auto">
                                                <table class="table table-bordered table-condensed table-striped m-0">
                                                    <tbody>
                                                        <tr v-for="(field,index) in form.meargRecords" :key="'primary-input-' + index">
                                                            <td>
                                                                <label class="form-check-label" for="'primary-' + index">{{ field }}</label>
                                                            </td>
                                                            <td class="wf-50">
                                                                <input type="radio" name="primaryInput" @click="setPrimaryField(field)"  :id="'primary-' + index"> 
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="text-right" v-if="form.primary">
                                                <button type="button" class="btn btn-sm theme-btn btn-primary my-3" @click="meargRecordsAction">Start Merging</button>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning fs-14" v-else><i class="bi bi-exclamation-triangle-fill"></i>  Please select atleast 2 records for merging</div>
                                    </div>
                                    <div class="pt-2" v-else-if="action_status == 'updateBtn'">
                                        <div v-show="form.meargRecords.length > 0">
                                            <h5>Enter Value to update this Field</h5>
                                            <input type="text" class="form-control" v-model="form.update">
                                            <button type="button" class="btn btn-sm theme-btn btn-primary my-3" v-show="form.update" @click="updateRecordsAction">
                                                    Update This Field
                                            </button>
                                        </div>
                                    </div>
                                    <div class="pt-2" v-else-if="action_status == 'transferBtn'">
                                        <button type="button" class="btn mr-1" :class="[(form.emptySet)?'btn-dark':'btn-outline-dark']" @click="getNullFields">Empty Fields Set</button>
                                        <button type="button" class="btn mr-1" :class="[(form.allSet)?'btn-dark':'btn-outline-dark']" @click="getNullFieldsAll">All Fields Set</button>
                                        <button type="button" class="btn" :class="[(form.newSet)?'btn-dark':'btn-outline-dark']" @click="getNewSet">Add New Field</button>
                                        <div v-show="form.meargRecords.length > 0">
                                            <div class="form-group mt-3">
                                            <span class="p-3" v-if="tloader == 4">
                                                <img :src="loader_url" />
                                            </span>
                                            <select class="form-control" v-model="form.nullField" v-show="form.emptySet || form.allSet" @change="getOverwriteInfo">
                                                <option value="">Select</option>
                                                <optgroup :label="index" v-for="(field,index) in form.nullFields" :key="index">
                                                    <option v-for="(fval,fin) in field" :key="'field'+fin" :value="fval.field">{{ fval.label | capitalize }}</option>
                                                </optgroup>
                                                
                                            </select>
                                            <input type="text" class="form-control" v-model="form.newNullField" placeholder="enter temp field name" v-show="form.newSet" @keyup="form.copyMoveStep = true">
                                            </div>
                                            <div class="p-3" v-if="tloader == 5">
                                                <img :src="loader_url" />
                                            </div>
                                            <div class="row my-2" v-show="form.copyMoveStep">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-sm btn-block theme-btn my-1" :class="[(form.copyMoveType == 'copy')?'btn-dark':'btn-outline-dark']" @click="form.copyMoveType = 'copy'">Copy Data</button>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <button type="button" class="btn btn-sm btn-block theme-btn my-1" :class="[(form.copyMoveType == 'move')?'btn-dark':'btn-outline-dark']" @click="form.copyMoveType = 'move'">Move Data</button>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <button type="button" class="btn btn-sm theme-btn btn-primary my-3" v-show="form.copyMoveType" @click="transferRecordsAction">
                                                    Transfer Data
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-2" v-else-if="action_status == 'tagBtn'">
                                        <div class="row mb-2">
                                            <div class="col-12 col-sm-6">
                                                <h5>Select Tag to keep</h5>
                                                <!-- <div class="input-group mb-3">
                                                    <input type="text" class="form-control" placeholder="Add New Tag to Add" v-model="new_tag" />
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary plus-btn" type="button" @click="addnewTag">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </div> -->
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <button class="btn btn-sm btn-primary theme-btn btn-block py-1" type="button" v-show="this.form.tagRecords.length >= 1" @click="tagRefineRecordsAction">Start Refining</button>
                                            </div>
                                        </div>
                                        
                                        <button class="tags-btn" type="button" v-for="ut in uniqueTags" :key="'ot'+ut" :class="[(form.tagRecords.indexOf(ut) >= 0)?'btn-added':'btn-not-added']" @click="toggleTag(ut)">
                                            {{ ut }}
                                        </button>
                                    </div>
                                    <div class="pt-2" v-else>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center" v-show="progress">
                                <radial-progress-bar 
                                    :startColor="startColor"
                                    :innerStrokeWidth="5"
                                    :stopColor="stopColor"
                                    :diameter="100"
                                    :completed-steps="completedSteps"
                                    :total-steps="totalSteps">
                                    <h5>{{ completedSteps }}/{{ totalSteps }}</h5>
                                </radial-progress-bar>
                            </div>
                        </div>
                    </div>
                    <div class="row my-2 mx-0">
                        <div class="col-12 col-sm-12 col-md-6">
                            <div v-if="toggle">
                                <apexchart @dataPointSelection="getProspectsBasedOnField" v-if="form.records.length > 0" width="95%" type="pie" :options="chartOptionsDefault" :series="seriesDefault"></apexchart>
                            </div>
                            <div v-else>
                                <apexchart @dataPointSelection="getProspectsBasedOnTotal" v-if="form.countGraphSeries.length > 0" width="95%" type="pie" :options="chartOptionsCount" :series="seriesCount"></apexchart>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-6">
                            <div class="divtable border-top fit-cluster" v-if="form.field">
                                <div class="divthead">
                                    <div class="divthead-row">
                                        <div class="divthead-elem wf-50">
                                            SNo
                                        </div>
                                        <div class="divthead-elem wf-150">
                                            Name                            
                                        </div>
                                        <div class="divthead-elem mwf-100">
                                            Company                        
                                        </div>
                                        <div class="divthead-elem wf-100">
                                            Stage
                                        </div>
                                        <div class="divthead-elem wf-80">
                                            Contacts
                                        </div>
                                    </div>
                                </div>
                                <div class="divtbody custom-height-220">
                                    <div class="divtbody-row" v-for="(record, rid) in prospects" :key="'dsg-'+rid">
                                        <div class="divtbody-elem wf-50">
                                            {{ rid+1 }}
                                        </div>
                                        <div class="divtbody-elem wf-150">
                                            {{ record.first_name }} {{ record.last_name }}
                                        </div>
                                        <div class="divtbody-elem mwf-100">
                                            {{ record.company }}
                                        </div>
                                        <div class="divtbody-elem wf-100">
                                            <span v-if="record.stage_data" :class="record.stage_data.css">
                                            {{ record.stage_data.name }}
                                            </span>
                                            <span class="no-stage" v-else>No Stage</span>
                                        </div>
                                        <div class="divtbody-elem wf-80">
                                            <span v-if="record.mobilePhones" v-title="record.mobilePhones"><i class="bi bi-telephone-fill px-1"></i></span>
                                            <span v-else><i class="bi bi-telephone px-1"></i></span>
                                            <span v-if="record.email || record.emails" v-title="record.emails?record.emails:record.email">
                                                    <i class="bi bi-envelope-fill px-1"></i>
                                            </span>
                                            <span v-else><i class="bi bi-envelope px-1"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker'
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
import { ToggleButton } from 'vue-js-toggle-button'
import RadialProgressBar from 'vue-radial-progress';
export default {
    components:{DateRangePicker, ToggleButton, RadialProgressBar},
    data(){
        return {
            progress:false,
            startColor: "#3490dc",
            stopColor: "#f8f9fa",
            completedSteps: 0,
            totalSteps: 0,
            stages : [],
            prospects : [],
            toggle : true,
            recordSorting : "field",
            recordSortingType : "asc",
            loader:false,
            loader1:false,
            tloader:false,
            loader_url: '/img/spinner.gif',
            meargeBtn : false,
            primaryBtn : false,
            updateBtn : false,
            resetBtn : false,
            tagBtn : false,
            enableTag : false,
            transferBtn : false,
            meargeInput : false,
            updateInput : false,
            showaction:false,
            action_status: '',
            primaryContainer : false,
            updateContainer : false,
            transferContainer : false,
            display_names:{},
            newTags : [],
            new_tag:'',
            form : new Form({
                allStage : '',
                stage : [],
                tableName : 'contacts',
                fields : [],
                field : '',
                fieldName : '',
                fieldNameContainer : [],
                records : [],
                updateRecords : '',
                meargRecords : [],
                tagRecords: [],
                primary : '',
                update : '',
                clusterType : '',
                transferActionType : null,
                nullFields : [],
                emptySet : false,
                copyMoveStep : false,
                copyMoveType : null,
                allSet : false,
                newSet : false,
                newNullField : '',
                previousRecord : '',
                defaultGraphSeries : [],
                defaultGraphLabels : [],
                countGraphSeries : [],
                countGraphLabels : [],
            })
        }
    },
    computed: {
        uniqueTags() {
            if(this.form.field == 'outreach_tag') {
                let ot = this.form.meargRecords.join(',');
                if(this.newTags.length >= 1) {
                    let otn = this.newTags.join(',');
                    ot = ot+','+otn;
                }
                ot = ot.split(',')
                ot = ot.filter(function(el, index, arr) {
                    return index == arr.indexOf(el);
                });
                return ot;
            } else {
                return [];
            }
        },
        totalCount() {
            let sm = 0;
            if(this.form.records.length >= 1) {
                this.form.records.forEach(item => {
                    if(this.form.meargRecords.indexOf(item.field) >= 0) {
                        sm = sm + item.total;
                    }
                })
            }
            return sm;
        },
        seriesDefault(){
            if(this.form.records.length > 0){
                return this.form.defaultGraphSeries
            }else{
                return []
            }
        },
        chartOptionsDefault(){
            if(this.form.records.length > 0){
                return  {
                    legend: {
                        show: false,
                        position: 'bottom',
                        horizontalAlign: 'center', 
                        floating: false,
                        fontSize: '10px',
                        fontFamily: 'inherit',
                        fontWeight: 300,
                    },
                    colors : ['#3490dc', '#6574cd' ,'#9561e2', '#f66d9b', '#e3342f', '#f6993f', '#ffed4a' ,'#38c172' ,'#4dc0b5' ,'#6cb2eb'],
                    stroke: {
                        show: false,
                        curve: 'stepline',
                        lineCap: 'butt',
                        width: 1,
                        dashArray: 0,      
                    },
                    chart: {
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                        fontFamily: 'inherit',
                    },
                    labels: this.form.defaultGraphLabels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                // width: 200,
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                }
            }else{
                return  []
            }
        },
        seriesCount(){
            if(this.form.countGraphSeries.length > 0){
                return this.form.countGraphSeries
            }else{
                return []
            }
        },
        chartOptionsCount(){
            if(this.form.countGraphLabels.length > 0){
                return  {
                    legend: {
                        show: false,
                        position: 'bottom',
                        horizontalAlign: 'center', 
                        floating: false,
                        fontSize: '10px',
                        fontFamily: 'inherit',
                        fontWeight: 300,
                    },
                    colors : ['#3490dc', '#6574cd' ,'#9561e2', '#f66d9b', '#e3342f', '#f6993f', '#ffed4a' ,'#38c172' ,'#4dc0b5' ,'#6cb2eb'],
                    stroke: {
                        show: false,
                        curve: 'stepline',
                        lineCap: 'butt',
                        width: 1,
                        dashArray: 0,      
                    },
                    chart: {
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                        fontFamily: 'inherit',
                    },
                    labels: this.form.countGraphLabels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                // width: 200,
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                }
            }else{
                return  []
            }
        },
        formrecords(){
            //$('.all-record').prop('checked', false);
            return _.orderBy(this.form.records, this.recordSorting, this.recordSortingType);
        },
        display_name_key() {
            return Object.keys(this.display_names)
        },
        display_name_value() {
            return Object.values(this.display_names)
        }
    },
    filters: {
        inputFields(text){
            if(text.indexOf("_")){
                return text.replaceAll("_", " ").toUpperCase()
            }
            return text.toUpperCase()
        }
    },
    methods: {
        getTableFields(){
            this.loader = true
            this.form.post('/api/get-table-fields').then((response) => {
                this.form.fields = response.data
                this.loader = false
            })
        },
        toggleTag(record) {
            if(this.form.tagRecords.length == 0) {
                this.form.tagRecords.push(record)
            }
            else if(this.form.tagRecords.indexOf(record) == -1) {
                this.form.tagRecords.push(record)
            }else{
                var eleIndex = this.form.tagRecords.indexOf(record);
                this.form.tagRecords.splice(eleIndex, 1)
            }
        },
        addnewTag() {
            if(this.new_tag == '') {
                Vue.$toast.error('Please enter the tag name.');
                return false
            }
            this.newTags.push(this.new_tag);
            this.new_tag = '';
            Vue.$toast.success('new tag added in the list.');
        },
        reset(){
            this.form.tableName = ''
            this.form.fields = []
            this.form.field = ''
            this.form.records = []
            this.form.updateRecords = ''
            this.form.meargRecords = []
            this.form.primary = ''
            this.form.update = ''
            this.meargeBtn = false
            this.primaryBtn = false
            this.updateBtn = false
            this.transferBtn = false
            this.resetDate = false
            this.meargeInput = false
            this.updateInput = false
            this.primaryContainer = false
            this.updateContainer = false
            this.transferContainer = false
        },
        afterAction() {
            this.form.updateRecords = ''
            this.form.meargRecords = []
            this.form.tagRecords = []
            this.form.primary = ''
            this.form.update = ''
            this.meargeBtn = false
            this.primaryBtn = false
            this.updateBtn = false
            this.meargeInput = false
            this.updateInput = false
            this.primaryContainer = false
            this.updateContainer = false
            this.transferContainer = false
            this.form.emptySet = false
            this.form.allSet = false
            this.form.newSet = false
            this.form.copyMoveStep = false
            this.form.copyMoveType = null
            this.form.nullField = ''
            this.form.newNullField = ''
            this.form.clusterType = ''
        },
        setPrimary(){
            this.primaryContainer = true
        },
        getData(field, status){
            this.afterAction()
            this.loader = true
            this.prospects = []
            this.form.field = field
            if(status == 1){
                this.form.allStage = ''
            }
            this.form.defaultGraphSeries = []
            this.form.defaultGraphLabels = []
            this.form.countGraphSeries = []
            this.form.countGraphLabels = []
            this.form.stage = []
            this.toggle = true
            this.form.post('/api/get-table-data').then((response) => {
                this.form.records = response.data.results
                if(response.data.stage){
                    this.form.stage = response.data.stage
                }
                for(const key of Object.keys(this.form.records)){
                    this.form.defaultGraphSeries[key] = this.form.records[key]["total"]
                    this.form.defaultGraphLabels[key] = this.form.records[key]["field"]
                    if(this.form.countGraphLabels.indexOf(this.form.records[key]["total"]) > -1){
                        var index = this.form.countGraphLabels.indexOf(this.form.records[key]["total"])
                        this.form.countGraphSeries[index] = 1 + Number(this.form.countGraphSeries[index])
                    } else {
                        var l = this.form.countGraphLabels.length
                        this.form.countGraphLabels[l] = Number(this.form.records[key]["total"])
                        this.form.countGraphSeries[l] = 1
                    }
                }
                if(response.data.stage){
                    for(const key of Object.keys(this.form.defaultGraphLabels)){
                        
                        this.form.defaultGraphLabels[key] = this.form.stage[this.form.defaultGraphLabels[key]]
                    }
                }
                this.showaction = false
                this.meargeBtn = true
                this.updateBtn = true
                this.resetBtn = true
                this.clusterBtn =  true
                this.transferBtn = true
                if(this.form.field == 'outreach_tag') {
                    this.enableTag = true
                } else {
                    this.enableTag = false
                }
                this.loader = false
            })
        },
        setMeargeRecords(record){
            if(this.form.meargRecords.length == 0){
                this.form.meargRecords.push(record)
            }else{
                if(this.form.meargRecords.indexOf(record) == -1){
                    this.form.meargRecords.push(record)
                }else{
                    var eleIndex = this.form.meargRecords.indexOf(record);
                    this.form.meargRecords.splice(eleIndex, 1)
                }
            }
        },
        setUpdateRecords(records){
            this.form.updateRecords = records
        },
        mearge(){
            this.meargeInput = true
            this.primaryBtn = true
            this.updateInput = false
            this.updateContainer = false
            this.transferContainer = false
        },
        update(){
            this.updateInput = true
            this.meargeInput = false
            this.primaryBtn = false
            this.updateContainer = true
            this.primaryContainer = false
            this.transferContainer = false
        },
        showTransfer(){
            this.transferContainer = true
            this.updateContainer = false
            this.primaryContainer = false
        },
        setPrimaryField(field){
            this.form.primary = field
        },
        meargRecordsAction(){
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Merge it!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.totalSteps = this.totalCount
                    this.completedSteps = 1
                    this.progress = true
                    window.setInterval(() => {
                        this.incCompledSteps();
                    }, 500)
                    this.form.post('/api/mearge-records').then((response) => {
                        this.afterAction()
                        this.completedSteps = 0
                        this.totalSteps = 0
                        this.progress = false
                        this.getData(this.form.field)
                        this.$swal('Merged!', 'Records has been merged successfully.', 'success')  
                    })
                }
            })
        },
        resetDateAction(){
            if(this.form.meargRecords.length == 0){
                this.$swal('Warning!', 'Please select record(s) first.', 'error') 
                return false
            }
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Reset All!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.totalSteps = this.totalCount
                    this.completedSteps = 1
                    this.progress = true
                    window.setInterval(() => {
                        this.incCompledSteps();
                    }, 500)
                    this.form.post('/api/reset-records').then((response) => {
                        this.afterAction()
                        this.completedSteps = 0
                        this.totalSteps = 0
                        this.progress = false
                        this.getData(this.form.field)
                        this.$swal('Reset!', 'Records has been reset successfully.', 'success')  
                    })
                }
            })
        },
        updateRecordsAction(){
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.totalSteps = this.totalCount
                    this.completedSteps = 1
                    this.progress = true
                    window.setInterval(() => {
                        this.incCompledSteps();
                    }, 500)
                    this.form.post('/api/update-records').then((response) => {
                        this.afterAction()
                        this.completedSteps = 0
                        this.totalSteps = 0
                        this.progress = false
                        this.getData(this.form.field)
                        this.$swal('Updated!', 'Records has been updated successfully.', 'success')  
                    })
                }
            })
        },
        transferRecordsAction(){
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Transfer All!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.totalSteps = this.totalCount
                    this.completedSteps = 1
                    this.progress = true
                    window.setInterval(() => {
                        this.incCompledSteps();
                    }, 500)
                    this.form.post('/api/transfer-records').then((response) => {
                        if(response.data.status == "exists"){
                            this.$swal('Transfered!', 'Tmp Field already exists.', 'error')
                        }else{
                            this.afterAction()
                            this.completedSteps = 0
                            this.totalSteps = 0
                            this.progress = false
                            this.getData(this.form.field)
                            this.$swal('Transfered!', 'Records has been transfered successfully.', 'success')
                        }
                    })
                }
            })
        },
        tagRefineRecordsAction() {
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Refine All Tags!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.totalSteps = this.totalCount
                    this.completedSteps = 1
                    this.progress = true
                    window.setInterval(() => {
                        this.incCompledSteps();
                    }, 500)
                    this.form.post('/api/refinetag-records').then((response) => {
                         if(response.data.status == "exists"){
                            this.$swal('Refine ERROR!', 'Tmp Field already exists.', 'error')
                        }else{
                            this.afterAction()
                            this.completedSteps = 0
                            this.totalSteps = 0
                            this.progress = false
                            this.getData(this.form.field)
                            this.$swal('Refine Tags!', 'Tags have been refined successfully.', 'success')
                        } 
                    })
                }
            })
        },
        getNullFields(){
            this.tloader = 4;
            this.form.copyMoveType = null
            this.form.copyMoveStep = false
            this.form.emptySet = false
            this.form.allSet = false
            this.form.newSet = false
            this.form.nullFields = []
            this.form.post('/api/get-null-fields').then((response) => {
                this.form.nullFields = response.data
                this.form.emptySet = true
                this.form.allSet = false
                this.tloader = false
                this.form.newSet = false
            })
        },
        getNullFieldsAll(){
            this.form.copyMoveType = null
            this.form.copyMoveStep = false
            this.form.emptySet = false
            this.tloader = 4
            this.form.allSet = false
            this.form.newSet = false
            this.form.nullFields = []
            this.form.post('/api/get-null-fields-all').then((response) => {
                this.form.nullFields = response.data
                this.form.emptySet = false
                this.form.allSet = true
                this.form.newSet = false
                this.tloader = false
            })
        },
        getNewSet(){
            this.form.copyMoveType = null
            this.form.copyMoveStep = false
            this.form.emptySet = false
            this.form.allSet = false
            this.form.newSet = true
        },
        getOverwriteInfo(){
            if(this.form.allSet){
                this.tloader = 5;
                this.form.copyMoveStep = false
                this.form.post('/api/get-overwrite-info').then((response) => {
                    if(response.data.overwrite >= 0){
                        var message = '';
                        if(response.data.nullValues >= 0){
                            message += response.data.nullValues + ' record(s) are empty. <br>';
                        }
                        if(response.data.overwrite >= 0){
                            message += response.data.overwrite + ' record(s) will overwrite.';
                        }
                        
                        this.$swal({
                            title: 'Are you sure?',
                            html: message,
                            icon: 'warning',
                            showDenyButton: true,
                            denyButtonText: `Skip`,
                            denyButtonColor : '#9561e2',
                            showCancelButton: true,
                            cancelButtonText : 'Cancel It',
                            cancelButtonColor: '#d33',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, Overwrite'
                            }).then((result) => {
                                this.tloader = false;
                            if (result.isConfirmed) {
                                this.form.transferActionType = 'overwrite';
                                this.form.copyMoveStep = true
                            } else if (result.isDenied) {
                                this.form.transferActionType = 'skip';
                                this.form.copyMoveStep = true
                            } else if(result.isCanceled){
                                this.form.copyMoveStep = false
                            }
                        })
                    }else{

                    }
                    
                });
            }else{
                this.form.copyMoveStep = true
            }
        },
        selectAllRecords(event){
            if(document.getElementById("all-record-checkbox").checked == true){
                //select all
                for(const key of Object.keys(this.form.records)){
                    this.addInContainer(this.form.records[key]["field"])
                }
            }else{
                //uncheck all
                for(const key of Object.keys(this.form.records)){
                    this.removeFromContainer(this.form.records[key]["field"])
                }
            }
        },
        selectAllSingleCheckbox(event){
            if(event.shiftKey){
                var start = this.form.previousRecord
                var indexArray = event.target.attributes.id.textContent.split("-")
                var end = Number(indexArray[indexArray.length-1])

                for (const key of Object.keys(this.formrecords)) {
                    if(key > start && key < end){
                        if(document.getElementById("all-record-" + start).checked == true){
                            document.getElementById("all-record-" + key).checked = true
                            this.addInContainer(this.formrecords[key]["field"])
                        }else{
                            document.getElementById("all-record-" + key).checked = false
                            this.removeFromContainer(this.formrecords[key]["field"])
                        }
                    }
                    if(key == end){
                        if(document.getElementById("all-record-" + start).checked == true){
                            this.addInContainer(this.formrecords[key]["field"])
                        }else{
                            this.removeFromContainer(this.formrecords[key]["field"])
                        }
                    }
                }
            } else {
                var indexArray = event.target.attributes.id.textContent.split("-")
                this.form.previousRecord = Number(indexArray[indexArray.length-1])
                if(document.getElementById("all-record-" + this.form.previousRecord).checked == true){
                    this.addInContainer(event.target.value)
                } else {
                    this.removeFromContainer(event.target.value)
                }
            }
        },
        addInContainer(field){
            if(this.form.meargRecords.indexOf(field) == -1){
                this.form.meargRecords.push(field)
            }
        },
        removeFromContainer(field){
            var index = this.form.meargRecords.indexOf(field)
            if(Number(index) > -1){
                this.form.meargRecords.splice(index, 1)
            }
        },
        getProspectsBasedOnField(e, chartContext, config){
            this.loader1 = true
            var field = this.form.defaultGraphLabels[config.dataPointIndex]
            this.form.meargRecords = []
            if(this.form.field == 'stage'){
                var index = this.form.stage.indexOf(field)
                this.addInContainer(index.toString())
            }else{
                this.addInContainer(field)
            }
            this.form.fieldName = field
            this.prospects = []
            this.form.post('/api/get-field-based-data').then((response) => {
                this.prospects = response.data
                this.loader1 = false
            })
        },
        getProspectsBasedOnTotal(e, chartContext, config){
            var total = this.form.countGraphLabels[config.dataPointIndex]// total
            var fre = this.form.countGraphSeries[config.dataPointIndex]// frequency
            this.form.fieldNameContainer = []
            this.form.meargRecords = []
            for(const key of Object.keys(this.form.records)){
                if(this.form.records[key]["total"] == total){
                    this.form.fieldNameContainer[this.form.fieldNameContainer.length] = this.form.records[key]["field"]
                    this.addInContainer(this.form.records[key]["field"])
                }
            }
            
            this.prospects = []
            this.form.post('/api/get-count-based-data').then((response) => {
                this.prospects = response.data
            })
        },
        getStages(){
            axios.get('/api/get-all-outreach-stages').then((response) => {
                this.stages = response.data.results
            })
        },
        incCompledSteps(){
            if(this.completedSteps < this.totalSteps - 2){
                this.completedSteps =  this.completedSteps  + 1
            }else{
                return false;
            }
        },
    },
    created() {
        axios.get('/api/get-display-names').then((response) => {this.display_names = response.data })
        this.getTableFields()
        this.getStages()
        // this.getNullFields()
    }
}
</script>
