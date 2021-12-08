<template>
    <div>
        <div class="filterbox">
            <div style="white-space: nowrap; overflow-x: auto; height: 50px;">
                <button class="btn btn-sm m-1 theme-btn" :class="[(form.field == field)?'btn-dark':'btn-outline-dark ']" v-for="(field,index) in form.fields" :key="'input-fields-'+index" @click="getData(field)">
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
                                </div>
                                <div class="divthead-elem mwf-100">
                                    Record ({{form.allRecordsContainer.length | freeNumber}}/{{form.records.length | freeNumber}})
                                    <i class="bi bi-arrow-up" :class="[(recordSorting == 'field')?'text-dark':'']" v-if="recordSorting=='field' && recordSortingType=='desc'" @click="recordSorting='field',recordSortingType='asc'"></i>
                                    <i class="bi bi-arrow-down" :class="[(recordSorting == 'field')?'text-dark':'']" v-else @click="recordSorting='field',recordSortingType='desc'"></i>
                                </div>
                                <div class="divthead-elem wf-100 text-center">
                                    Count
                                    <i class="bi bi-arrow-up" :class="[(recordSorting == 'total')?'text-dark':'']"  @click="recordSorting='total',recordSortingType='asc'" v-if="recordSorting=='total' &&recordSortingType=='desc'"></i>
                                    <i class="bi bi-arrow-down" :class="[(recordSorting == 'total')?'text-dark':'']" v-else @click="recordSorting='total',recordSortingType='desc'"></i>
                                </div>
                            </div>
                        </div>
                        <div class="divtbody fit-content">
                            <div class="divtbody-row" v-for="(field,index) in formrecords" :key="'input-field-record-'+index">
                                <div class="divtbody-elem wf-50">
                                    <span v-if="form.allRecordsContainer.length > 0 && form.allRecordsContainer.indexOf(field.field) > -1">
                                        <input type="checkbox" name="records[]" :value="field.field" @click="selectAllSingleCheckbox"  class="all-record" :id="'all-record-' + index" :checked="true">
                                    </span>
                                    <span v-else>
                                        <input type="checkbox" name="records[]" :value="field.field" @click="selectAllSingleCheckbox"  class="all-record" :id="'all-record-' + index">
                                    </span>
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
                <div class="col-md-9 p-0">
                    <div class="border-bottom pr-3" v-show="(transferBtn || resetBtn || meargeBtn || updateBtn)">
											<div class="mb-2">
                        <toggle-button @change="toggle = !toggle"  :margin="3" :width="235" :height="30" :labels="{checked: 'Unique Count Based Graph', unchecked: 'Unique Record Based Graph'}" :switch-color="{checked: '#800080', unchecked: '#27408B'}" :color="{checked: '#E599E5', unchecked: '#4E9FFE'}" />
											
                        <div class="float-right" >
                            <div class="dropdown show mb-3">
                                <a class="btn btn-secondary theme-btn dropdown-toggle btn-block text-left wf-150" href="javascipt:void(0)" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </a>
                                <div class="dropdown-menu text-left" aria-labelledby="dropdownMenuLink">
                                    <button type="button" class="dropdown-item text-uppercase" v-show="resetBtn" @click="resetDateAction">Reset All Data</button>
                                    <button type="button" class="dropdown-item text-uppercase" v-show="meargeBtn" @click="mearge">Merge Data</button>
                                    <button type="button" class="dropdown-item text-uppercase" v-show="updateBtn" @click="update">Update Data</button>
                                    <button type="button" class="dropdown-item text-uppercase" v-show="transferBtn" @click="showTransfer">Transfer Data</button>
                                </div>
                            </div>
														</div>
                        </div>
												<div class="slide-action" :class="[(showaction)?'slidein-action':'slideout-action']">
														<div class="text-right">
															<i class="bi bi-x-circle fs-18 cursor-pointer" @click="showaction=false"></i>
														</div>
														<div class="row m-0">
																<div class="col-md-12 pt-2">
																		<button type="button" class="btn btn-sm theme-btn btn-info btn-block mt-2 mb-1" @click="setPrimary" v-show="primaryBtn && form.meargRecords.length >= 2">Set Primary For merging</button>
																		<div v-show="primaryContainer && form.meargRecords.length >= 2">
																				<h5>Select value to overright Others:</h5>
																				<div style="max-height:calc(50vh); overflow:auto">
																						<table class="table table-bordered table-condensed table-striped m-0">
																								<tbody>
																										<tr v-for="(field,index) in form.meargRecords" :key="'primary-input-' + index">
																												<td>
																														<label class="form-check-label" for="'primary-' + index">{{ field }}</label>
																												</td>
																												<td style="width:40px">
																														<input type="radio" name="primaryInput" @click="setPrimaryField(field)"  :id="'primary-' + index"> 
																												</td>
																										</tr>
																								</tbody>
																						</table>
																				</div>
																				<div class="text-right">
																								<button type="button" class="btn btn-sm theme-btn btn-primary my-3" @click="meargRecordsAction">Start Merging</button>
																				</div>
																		</div>
																</div>
																<div class="col-md-12 pt-2">
																		<div v-show="updateContainer && form.meargRecords.length > 0">
																				<h5>Enter Value to update this Field</h5>
																				<input type="text" class="form-control" v-model="form.update">
																				<button type="button" class="btn btn-sm theme-btn btn-primary my-3" v-show="form.update" @click="updateRecordsAction">
																						Update This Field
																				</button>
																		</div>
																</div>
																<div class="col-md-12 pt-2">
																		<div v-show="transferContainer && form.meargRecords.length > 0">
																				<div class="form-group">
																				<h5>Select Empty Field or Add New Field</h5>
																				<select class="form-control" v-model="form.nullField">
																						<option value="">Select</option>
																						<option v-for="(field,index) in form.nullFields" :key="index" :value="field">{{ field | inputFields }}</option>
																				</select>
																				</div>
																				<input type="text" class="form-control" v-model="form.newNullField" placeholder="enter temp field name" >
																				<button type="button" class="btn btn-sm theme-btn btn-primary my-3" v-show="form.newNullField || form.nullField" @click="transferRecordsAction">
																						Transfer
																				</button>
																		</div>
																</div>
														</div>
												</div>
                    </div>
                    <div class="row my-2 mx-0">
											<div class="col-12 col-sm-12 col-md-6">
                        <div v-if="toggle">
                            <apexchart @dataPointSelection="getProspectsBasedOnField" v-if="form.records.length > 0" type="pie" :options="chartOptionsDefault" :series="seriesDefault"></apexchart>
                        </div>
                        <div v-else>
                            <apexchart @dataPointSelection="getProspectsBasedOnTotal" v-if="form.countGraphSeries.length > 0"  type="pie" :options="chartOptionsCount" :series="seriesCount"></apexchart>
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
                <!-- <div class="col-md-2 fit-content pt-2"> -->
            </div>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker'
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
import { ToggleButton } from 'vue-js-toggle-button'
export default {
    components:{DateRangePicker, ToggleButton},
    data(){
        return {
            prospects : [],
            toggle : true,
            recordSorting : "field",
            recordSortingType : "asc",
            loader:false,
            loader_url: '/img/spinner.gif',
            meargeBtn : false,
            primaryBtn : false,
            updateBtn : false,
            resetBtn : false,
            transferBtn : false,
            meargeInput : false,
            updateInput : false,
            primaryContainer : false,
            updateContainer : false,
						showaction:false,
            transferContainer : false,
            display_names:{},
            form : new Form({
                stage : [],
                tableName : 'contacts',
                fields : [],
                field : '',
                fieldName : '',
                fieldNameContainer : [],
                records : [],
                updateRecords : '',
                meargRecords : [],
                primary : '',
                update : '',
                clusterType : '',
                nullFields : [],
                nullField : '',
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
            $('.all-record').prop('checked', false);
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
            this.form.post('/api/get-table-fields').then((response) => {
                this.form.fields = response.data
            })
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
            this.form.primary = ''
            this.form.update = ''
            this.meargeBtn = true
            this.primaryBtn = false
            this.updateBtn = true
            this.meargeInput = false
            this.updateInput = false
            this.primaryContainer = false
            this.updateContainer = false
            this.transferContainer = false
 
            this.form.allRecordsContainer = [];
            
            // this.form.nullFields = []
            this.form.nullField = ''
            this.form.newNullField = ''
            this.form.clusterType = ''
            this.getNullFields()
        },
        setPrimary(){
            this.primaryContainer = true
        },
        getData(field){
            this.afterAction()
            this.loader = true
            this.form.field = field
            this.form.post('/api/get-table-data').then((response) => {
                this.form.records = response.data.results
                if(response.data.stage){
                    this.form.stage = response.data.stage
                }
                this.form.defaultGraphSeries = []
                this.form.defaultGraphLabels = []
                this.form.countGraphSeries = []
                this.form.countGraphLabels = []
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
                if(this.form.stage){
                    for(const key of Object.keys(this.form.countGraphLabels)){
                      //  console.log(this.form.countGraphLabels[key])
                    }
                }
                this.meargeBtn = true
                this.updateBtn = true
                this.resetBtn = true
                this.clusterBtn =  true
                this.transferBtn = true
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
					this.showaction = true
            this.meargeInput = true
            this.primaryBtn = true
            this.updateInput = false
            this.updateContainer = false
            this.transferContainer = false
        },
        update(){
					this.showaction = true
            this.updateInput = true
            this.meargeInput = false
            this.primaryBtn = false
            this.updateContainer = true
            this.primaryContainer = false
            this.transferContainer = false
        },
        showTransfer(){
					this.showaction = true
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
                    this.form.post('/api/mearge-records').then((response) => {
                        this.afterAction()
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
                    this.form.post('/api/reset-records').then((response) => {
                        this.afterAction()
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
                confirmButtonText: 'Yes, Merge it!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.form.post('/api/update-records').then((response) => {
                        this.afterAction()
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
                    this.form.post('/api/transfer-records').then((response) => {
                        if(response.data.status == "exists"){
                            this.$swal('Transfered!', 'Tmp Field already exists.', 'error')
                        }else{
                            this.afterAction()
                            this.getData(this.form.field)
                            this.$swal('Transfered!', 'Records has been transfered successfully.', 'success')
                        }
                    })
                }
            })
        },
        getNullFields(){
            this.form.post('/api/get-null-fields').then((response) => {
                this.form.nullFields = response.data
            })
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
            if(this.form.allRecordsContainer.indexOf(field) == -1){
                this.form.allRecordsContainer.push(field)
            }
        },
        removeFromContainer(field){
            var index = this.form.meargRecords.indexOf(field)
            if(Number(index) > -1){
                this.form.meargRecords.splice(index, 1)
            }
            var index = this.form.allRecordsContainer.indexOf(field)
            if(Number(index) > -1){
                this.form.allRecordsContainer.splice(index, 1)
            }
        },
        getProspectsBasedOnField(e, chartContext, config){
            var field = this.form.defaultGraphLabels[config.dataPointIndex]
            this.form.fieldName = field
            this.prospects = []
            this.form.post('/api/get-field-based-data').then((response) => {
                this.prospects = response.data
            })
        },
        getProspectsBasedOnTotal(e, chartContext, config){
        //    console.log(config.dataPointIndex)
            var total = this.form.countGraphLabels[config.dataPointIndex]// total
            var fre = this.form.countGraphSeries[config.dataPointIndex]// frequency
            this.form.fieldNameContainer = []
            for(const key of Object.keys(this.form.records)){
                if(this.form.records[key]["total"] == total){
                    this.form.fieldNameContainer[this.form.fieldNameContainer.length] = this.form.records[key]["field"]
                }
            }
            this.prospects = []
            this.form.post('/api/get-count-based-data').then((response) => {
                this.prospects = response.data
            })
        }
    },
    created() {
        axios.get('/api/get-display-names').then((response) => {this.display_names = response.data })
        this.getTableFields()
        // this.getNullFields()
    }
}
</script>
