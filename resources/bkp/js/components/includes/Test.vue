<template>
    <div>
        <div v-if="step == 0">
            <div class="synops">          
                <div class="inner-synop">
                    <h4 class="number">{{ totalNumberOfRecords | freeNumber }} </h4><p>Total</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ notStarted | freeNumber }} </h4><p>Not Started</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ approaching | freeNumber }} </h4><p>Approaching</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ replied | freeNumber }} </h4><p>Replied</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ unresponsive | freeNumber }} </h4><p>Unresponsive</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ doNotContact | freeNumber }} </h4><p>Do Not Contact</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ badContactInfoMissingEmail | freeNumber }} </h4><p> Missing Email</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ badContactInfoMissingPhone | freeNumber }} </h4><p>Missing Phone</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ interested | freeNumber }} </h4><p>Interested</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ notInterested | freeNumber }} </h4><p>Not Interested</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ clients | freeNumber }} </h4><p>Clients</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ schedulingMetting | freeNumber }} </h4><p>Scheduling Meeting</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ meetingBooked | freeNumber }} </h4><p>Meeting Booked</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ negotiating | freeNumber }} </h4><p>Negotiating</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ emailFollowup | freeNumber }} </h4><p>Email Followup</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ phoneFollowup | freeNumber }} </h4><p>Phone Followup</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ inQueueofProspectContactEveryMonth | freeNumber }} </h4><p>In Queue</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ forwardedtoColleagueFriendDoNotContactAgain | freeNumber }} </h4><p>Do Not Contact</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ notAnswered | freeNumber }} </h4><p>Not Answered</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ irrelevantLeadDNC | freeNumber }} </h4><p>Irrelevant Lead DNC</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ clientLOST | freeNumber }} </h4><p>Client LOST</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ unresponsiveEmailSequence | freeNumber }} </h4><p>Unresponsive Email</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ badContactInfoMissingEmailPhone | freeNumber }} </h4><p>Missing Email/Phone</p>
                </div>
                <div class="inner-synop">
                    <h4 class="number">{{ noStage | freeNumber }} </h4><p>No Stage</p>
                </div>
            </div>
            <div class="filterbox">
                <div class="row m-0">
                    <div class="col-md-2 col-12 pl-0">
                        <select v-model="form.stage" class="form-control">
                            <option value="">Select Stage</option>
                            <option value="no_stage">No Stage</option>
                            <option  v-for="stage in stages" :key="'stage-'+stage.stage" :value="stage.stage">{{ stage.stage }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-12 pl-0">
                        <input type="text" v-model="form.search" placeholder="enter something" class="form-control">
                    </div>
                    <div class="col-md-3 col-12 pl-0">
                        <date-range-picker ref="picker" :locale-data="{ firstDay: 1, format: 'dd-mm-yyyy HH:mm:ss' }" :timePicker24Hour="false" :showWeekNumbers="true" :showDropdowns="true"  :dateFormat="dateFormat" :autoApply="false" v-model="form.dateRange" >
                            <template v-slot:input="picker">
                                <span v-if="form.dateRange.startDate">
                                    {{ picker.startDate | setusdate }} - {{ picker.endDate | setusdate }}
                                </span>
                                <span v-else>
                                    Select Date Range
                                </span>
                            </template>
                        </date-range-picker>
                    </div>
                    <div class="col-md-5 col-12 p-0">
                        <button type="button" class="btn btn-outline-dark icon-btn theme-btn mr-3" @click="getFilterData(1)">
                            <i class="bi bi-funnel-fill"></i> Filter
                        </button>
                        <!-- <toggle-button :value="false" @change="changeView" :width="135" :height="25" :labels="{checked: 'Graphical View', unchecked: 'List View'}" :color="{checked:'#55185d', unchecked: '#666666', disabled: '#CCCCCC'}" switch-color="#ffed4a" /> -->
                        <!-- <span class=" icon-btn cursor-pointer p-l-10 fs-14 p-t-5">
                            <i class="bi bi-arrow-clockwise m-r-5"></i> Refresh
                        </span> -->
                        <span class="float-right" v-if="recordContainer.length >= 1">
                            <span class="float-left d-inline-block m5">
                                <b>{{ recordContainer.length | freeNumber }}</b> Records Selected
                            </span>
                            <div class="dropdown d-inline-block">
                                <a class="btn btn-outline-danger btn-sm theme-btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> EXPORT </a>
                                <div class="dropdown-menu source-dropdown" aria-labelledby="dropdownMenuLink2">
                                    <!-- <a href="#" class="dropdown-item text-uppercase">Export and Sync in Outreach</a>
                                    <a href="#" class="dropdown-item text-uppercase">Export and Sync in Activecampaign</a> -->
                                    <a href="javascript:;" @click="StartExport" class="dropdown-item text-uppercase">Export and Sync in Five9</a>
                                    <hr class="dropdown-divider">
                                    <a href="#" class="dropdown-item text-uppercase">Export In CSV File</a>
                                </div>
                            </div>
                        </span>
                    </div>
                </div>
            </div> 
            <div class="divtable">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-45 text-center">
                            <input type="checkbox" name="" id="check-all" value="0" aria-label="..." @click="addAndRemoveAllRecordToContainer">
                        </div>
                        <div class="divthead-elem">
                            Name
                            <i class="bi bi-sort-down" v-if="form.sortBy == 'desc'" :class="[(form.sortType == 'first_name')?'active':'']" @click="updateSorting('first_name', 'desc')"></i>
                            <i class="bi bi-sort-up-alt" v-else :class="[(form.sortType == 'first_name')?'active':'']" @click="updateSorting('first_name', 'asc')"></i>
                        </div>
                        <div class="divthead-elem wf-250">
                            Email
                        </div>
                        <div class="divthead-elem wf-175">
                            Phone
                        </div>
                        <div class="divthead-elem wf-140">
                            Activity
                        <i class="bi bi-sort-down" v-if="form.sortBy == 'desc'" :class="[(form.sortType == 'engage_score')?'active':'']" @click="updateSorting('engage_score', 'desc')"></i>
                        <i class="bi bi-sort-up-alt" v-else :class="[(form.sortType == 'engage_score')?'active':'']" @click="updateSorting('engage_score', 'asc')"></i>
                        </div>
                        <div class="divthead-elem wf-140">
                            Last Connected
                        <i class="bi bi-sort-down" v-if="form.sortBy == 'desc'" :class="[(form.sortType == 'last_outreach_engage')?'active':'']" @click="updateSorting('last_outreach_engage', 'desc')"></i>
                        <i class="bi bi-sort-up-alt" v-else :class="[(form.sortType == 'last_outreach_engage')?'active':'']" @click="updateSorting('last_outreach_engage', 'asc')"></i>
                        </div>
                        <div class="divthead-elem wf-150">
                            Action
                        </div>
                    </div>
                </div>
                <div class="divtbody  fit-divt-content">
                    <div class="divtbody-row" v-for="(record, i) in records.data" :key="record.id" :class="[(active_row.id == record.id)?'expended':'']">
                        <div class="divtbody-elem  wf-45 text-center">
                            <div class="form-check">
                                <input :id="'record-'+record.id" class="form-check-input me-1" type="checkbox" :value="record.id" @click="addAndRemoveRecordToContainer(record.id)">
                            </div>
                        </div>
                        <div class="divtbody-elem">
                            <b>{{ record.first_name }}{{ record.last_name }} </b>
                            <span  v-if="record.stage !== null">
                            <span class="badge badge-danger" v-if="record.stage == 'Cold / Not Started'">Cold Lead</span>
                            <span class="badge badge-warning" v-else-if="record.stage == 'Interested'">Warm Lead</span>
                            <span class="badge badge-success" v-else-if="record.stage == 'Negotiating'">Hot Lead</span>
                            <span v-else></span>
                            <br>
                            </span>
                            <!-- <small class="fw-500" v-if="record.occupation">{{ record.attributes.occupation }}  in</small>  -->
                            <small class="fw-500" v-if="record.company">{{ record.company }}</small>
                        </div>
                        <div class="divtbody-elem wf-250">
                            {{ record.emails }}
                        </div>
                        <div class="divtbody-elem  wf-175">
                            {{ record.number1 }}
                        </div>
                        <div class="divtbody-elem  wf-140">
                            {{ record.engage_score }}
                        </div>
                        <div class="divtbody-elem  wf-140">
                            <!-- {{ record.last_outreach_engage | convertInDayMonth }} -->
                            {{ record.last_outreach_engage  }}
                        </div>
                        <div class="divtbody-elem action wf-150">
                            <a href="javascript:;" :class="[(active_row.id == record.id && active_row.type == 1)?'active':'']" @click="showActivity(i, 1, record.id)" class="shutter-link outreach">&nbsp; </a>
                            <a href="javascript:;" :class="[(active_row.id == i && active_row.type == 2)?'active':'']" @click="showActivity(i, 2, record.id)" class="shutter-link five9"></a>
                            <a href="javascript:;" :class="[(active_row.id == i && active_row.type == 3)?'active':'']" @click="showActivity(i, 3, record.id)" class="shutter-link activecampaign"></a>
                            <a href="javascript:;" :class="[(active_row.id == i && active_row.type == 4)?'active':'']" @click="showActivity(i, 4, record.id)" class="shutter-link all"></i></a>
                        </div>
                        
                    </div>
                </div>
                <div class="divtfoot">
                    <div class="text-center py-2">
                        <pagination :limit="10" :data="records" @pagination-change-page="getOutreachData"></pagination>
                    </div>                    
                </div>
            </div>
        </div>
        <div class="row m-0" v-if="step == 1">
            <div class="col-6 text-center p-2 bg-secondary text-white my-2">
                <h5 class="m-2">
                    Start Field Mapping
                </h5>
            </div>
            <div class="col-6 text-center p-2 bg-secondary text-white my-2">
                <h5 class="m-2">
                    <b>{{ recordContainer.length | freeNumber }}</b> Records Selected
                </h5>
            </div>
            
            <div class="col-md-9 col-12">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sno</th>
                            <th>Fine9 Field</th>
                            <th>Outreach Field </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(key, kkey) in options" :key="'th'+kkey">
                            <td>{{ kkey + 1 }}</td>
                            <td> {{ key | titleFormat }}</td>
                            <td>
                                <div class="row m-0">
                                    <span class="col-md-7 col-sm-6 col-12">
                                        <select :name="'map_field['+kkey+']'" :id="'map_field_'+kkey" @change="setMapField(key, kkey)" class="form-control" v-model="tform.dfields[kkey]">
                                            <option value="">Select Relevent Field</option>
                                            <option v-for="option in parse_header" :value="option" :key="option">{{ option | titleFormat }}</option>
                                        </select>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>  
            </div>
            <div class="col-md-3 col-12 py-3" v-show="sform.destination">
                <button type="button" @click="smartMapping">Smart Mapping</button>
                <h5>Sync Data By Template</h5>
                <div class="form-group">
                    <label for="template"></label>
                    <select name="template_id" id="template_id" class="form-control" v-model="sform.template_id" @change="applyTemplate">
                        <option value="">Select Or Create Template</option>
                            <option v-for="template in templates" :value="template.id" :key="template.id">{{ template.name }}</option>
                    </select>
                </div>
                <img :src="loader_url" v-if="loader == 2">
                <span v-else>
                    <button v-show="sform.template_id >= 1" class="btn btn-sm btn-success mb-2 btn-block" type="button" @click="ChooseTemplate(1)"> Proceed With Selection</button>
                    <input type="text" name="name" id="name" placeholder="Enter New Template Name" v-model="tform.name" class="form-control mb-1">
                    <button class="btn btn-sm btn-primary mb-2 text-uppercase btn-block" type="button"  @click="ChooseTemplate(2)"> Save As New and Proceed </button>
                    <button class="btn btn-sm btn-dark mb-2 text-uppercase btn-block" type="button"  @click="ChooseTemplate(3)"> Proceed without save </button>
                </span>
            </div>
        </div>
        <div class="row m-0" v-else-if="parse_header.length >= 1 && step == 2">
            <div class="col-6 text-center p-2 bg-secondary text-white my-2">
                <h5 class="m-2"  @click="backStep">
                    Comparision
                </h5>
            </div>
            <div class="col-6 text-center p-2 bg-secondary text-white my-2">
                <h5 class="m-2">
                    <b>{{ recordContainer.length | freeNumber }}</b> Records Selected
                </h5>
                <button type="button" @click="showAllRecords">All</button>          
            <button type="button" @click="showDuplicateRecords">Unique [{{ uniqueRecordCounter }}]</button>          
            <button type="button" @click="showUniqueRecords">Duplicate [{{ duplicateRecordCounter }}]</button>     
            <button type="button" @click="checkRecords">Next</button>   
            </div>            
            <div class="col-md-12 col-12">
                <table v-show="showUnique" class="table table-bordered table-striped">                    
                    <thead>
                        <tr>
                            <!-- <th width="70px">Sno</th> -->
                            <th v-show="isExist('firstName')" width="200px">First Name</th>
                            <th v-show="isExist('lastName')" width="200px">Last Name</th>
                            <th v-show="isExist('mobilePhones')" width="200px">Mobile</th>
                            <th v-show="isExist('workPhones')" width="200px">Work Phone</th>
                            <th v-show="isExist('emails')" width="200px">Emails</th>
                            <th v-show="isExist('company')" width="200px">Company</th>
                            <th v-show="isExist('addressStreet')" width="200px">Street</th>
                            <th v-show="isExist('addressCity')" width="200px">City</th>
                            <th v-show="isExist('addressState')" width="200px">State</th>
                            <th v-show="isExist('addressZip')" width="200px">Zip</th>
                            <th v-show="isExist('tags')" width="200px">Tags</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(groupedRecord, index) in groupedRecords" :key="'row'+index" v-if="groupedRecord.length == 1">
                            <td v-show="isExist('firstName')" width="200px">{{ groupedRecord[0].firstName }}</td>
                            <td v-show="isExist('lastName')" width="200px">{{ groupedRecord[0].lastName }}</td>
                            <td v-show="isExist('mobilePhones')" width="200px">{{ groupedRecord[0].mobilePhones }}</td>
                            <td v-show="isExist('workPhones')" width="200px">{{ groupedRecord[0].workPhones }}</td>
                            <td v-show="isExist('emails')" width="200px">{{ groupedRecord[0].email }}</td>
                            <td v-show="isExist('company')" width="200px">{{ groupedRecord[0].company }}</td>
                            <td v-show="isExist('addressStreet')" width="200px">{{ groupedRecord[0].addressStreet }}</td>
                            <td v-show="isExist('addressCity')" width="200px">{{ groupedRecord[0].addressCity }}</td>
                            <td v-show="isExist('addressState')" width="200px">{{ groupedRecord[0].addressState }}</td>
                            <td v-show="isExist('addressZip')" width="200px">{{ groupedRecord[0].addressZip }}</td>
                            <td v-show="isExist('tags')" width="200px">{{ groupedRecord[0].tags }}</td>
                        </tr>
                    </tbody>
                </table>          
                <table v-show="showDuplicate" class="table table-bordered table-striped" v-for="(groupedRecord, index) in groupedRecords" :key="'row'+index" v-if="groupedRecord.length > 1">                    
                    <thead>
                        <tr>
                            <!-- <th width="70px">Sno</th> -->
                            <th v-show="isExist('firstName')" width="200px">First Name</th>
                            <th v-show="isExist('lastName')" width="200px">Last Name</th>
                            <th v-show="isExist('mobilePhones')" width="200px">Mobile</th>
                            <th v-show="isExist('workPhones')" width="200px">Work Phone</th>
                            <th v-show="isExist('emails')" width="200px">Emails</th>
                            <th v-show="isExist('company')" width="200px">Company</th>
                            <th v-show="isExist('addressStreet')" width="200px">Street</th>
                            <th v-show="isExist('addressCity')" width="200px">City</th>
                            <th v-show="isExist('addressState')" width="200px">State</th>
                            <th v-show="isExist('addressZip')" width="200px">Zip</th>
                            <th v-show="isExist('tags')" width="200px">Tags</th>
                            <th width="100px">Action</th>
                        </tr>
                    </thead>                    
                    <tbody>
                        <tr v-for="(record,sno) in groupedRecord" :key="'in-row-'+sno">                         
                            <td width="200px" v-show="isExist('firstName')">{{ record.firstName }}</td>
                            <td width="200px" v-show="isExist('lastName')">{{ record.lastName }}</td>
                            <td width="200px" v-show="isExist('mobilePhones')">{{ record.mobilePhones }}</td>
                            <td width="200px" v-show="isExist('workPhones')">{{ record.workPhones }}</td>
                            <td width="200px" v-show="isExist('emails')">{{ record.email }}</td>
                            <td width="200px" v-show="isExist('company')">{{ record.company }}</td>
                            <td width="200px" v-show="isExist('addressStreet')">{{ record.addressStreet }}</td>
                            <td width="200px" v-show="isExist('addressCity')">{{ record.addressCity }}</td>
                            <td width="200px" v-show="isExist('addressState')">{{ record.addressState }}</td>
                            <td width="200px" v-show="isExist('addressZip')">{{ record.addressZip }}</td>
                            <td width="200px" v-show="isExist('tags')">{{ record.tags }}</td>
                            <td>
                                <a class="px-2 text-danger" href="javascript:void(0)" @click="deleteRecords(record)"><i class="bi bi-trash"></i></a>
                                <a  class="px-2 text-info" href="javascript:void(0)" @click="swapRecords(record)"><i class="bi bi-arrow-down-up"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row m-0" v-else-if="parse_header.length >= 1 && step == 3">
            <div class="col-6 text-center p-2 bg-secondary text-white my-2">
                <h5 class="m-2">
                    Start Syncing
                </h5>
            </div>
            <div class="col-6 text-center p-2 bg-secondary text-white my-2">
                <h5 class="m-2">
                    <b>{{ recordContainer.length | freeNumber }}</b> Records Selected
                </h5>
            </div>
            
            <div class="col-md-3 col-12">
                
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group" v-if="sform.destination == 'activecampaign'">
                    <label for="form_name">Enter List Name</label>
                    <input type="text" name="form_name" id="form_name" class="form-control" v-model="sform.name">
                    <i class="text-warning">Keep this field empty if you do not want to create new list</i>
                </div>
                <div v-else-if="sform.destination == 'five9'">
                    <div class="form-group">
                        <label for="form_name">Select List</label>
                        <select required name="lid" id="lid" class="form-control" v-model="sform.lid">
                            <option value="">Select List</option>
                            <option value="0">Create New List</option>
                            <option :value="lname.name" v-for="(lname, lk) in five9_list" :key="'listid'+lk">
                                {{ lname.name }} ({{ lname.size }})
                            </option>
                        </select>
                    </div>
                    <div class="form-group" v-show="sform.lid == '0'">
                        <label for="form_name">Enter List Name</label>
                        <input type="text" required name="form_list_name" id="form_list_name" class="form-control" v-model="sform.name">
                    </div>
                    <div class="form-group">
                        <label for="form_name">Select Campaign</label>
                        <select required name="cid" id="cid" class="form-control" v-model="sform.cid">
                            <option value="">Select Campaign</option>
                            <option value="0">Create New Campaign</option>
                            <option :value="cname.name" v-for="(cname, ck) in five9_ocampaigns" :key="'campaignid'+ck">
                                {{ cname.name }} ({{ cname.mode }}) - {{  cname.state }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group" v-show="sform.cid == '0'">
                        <label for="form_name">Enter Campaign Name</label>
                        <input type="text" required name="form_Campaign_name" id="form_Campaign_name" class="form-control" v-model="sform.campaign">
                    </div>
                </div>
                <img :src="loader_url" v-if="loader == 1">
                <button class="btn theme-btn btn-sm btn-primary" type="button" @click="startSyncing"> Start Uploading</button>
            </div>
            <div class="col-md-3 col-12">
                
            </div>
        </div>
        <div class="row m-0" v-else-if="step == 4">
            <p class="p-4 text-center text-success">Import process has been completed successfully.<br>
                {{ report }}
            </p>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';
import { ToggleButton } from 'vue-js-toggle-button';
import Synops from './outreach/Synop.vue';
export default {
    components: { DateRangePicker, ToggleButton, Synops },
    data() {
        return {
            totalNumberOfRecords : 0,
            approaching: 0,
            notStarted:0,
            replied:0,
            unresponsive:0,
            doNotContact:0,
            badContactInfoMissingEmail:0,
            badContactInfoMissingPhone:0,
            interested:0,
            notInterested:0,
            clients:0,
            schedulingMetting:0,
            meetingBooked:0,
            negotiating:0,
            emailFollowup:0,
            phoneFollowup:0,
            inQueueofProspectContactEveryMonth:0,
            forwardedtoColleagueFriendDoNotContactAgain:0,
            notAnswered:0,
            irrelevantLeadDNC:0,
            clientLOST:0,
            unresponsiveEmailSequence:0,
            badContactInfoMissingEmailPhone:0,
            noStage:0,
            loader:false,
            view:'list',
            step:0,
            //page:10,
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
            parse_header:['firstName','lastName','mobilePhones','workPhones','addressState','addressStreet','tags','addressZip','addressCity','company','email'],
            form: new Form({
                sortType:'last_outreach_engage',
                sortBy:'desc',
                dateRange:{},
                outreach:1,
                activecampaign:0,
                five9:0,
                page:1,
                stage:'',
            }),
            sform: new Form({
                template_id:'',
                source:'',
                destination:'five9',
                fdata:[],
                report:'',
                fields:[],
                efields:[],
                records:'',
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
            stages: [],
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
            return this.five9_campaigns.filter((cam) => { return cam.type == 'OUTBOUND' });
        },
    },
    methods: {
        // StartExport () {
        //     this.step = 1;
        //     //this.form.destination = 'five9';
        //     this.ChooseTemplate(3);
        // },
        StartExport() {
            this.step = 1;
            this.sform.destination = 'five9';
            this.climit = this.$store.getters.currentConfig.five9_maxsync;
            this.options = this.f9_options; 
            axios.get('/api/get-type-template/'+this.sform.destination).then((response) => {
                this.templates = response.data;
            });
            this.sform.records = this.recordContainer;
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
        updateSorting(typ, by) {
            if(this.form.sortType == typ && (this.form.sortBy == by) && (this.form.sortBy == 'desc')){
                this.form.sortBy = 'asc';
            }
            else if(this.form.sortType == typ && (this.form.sortBy == by) && (this.form.sortBy == 'asc')){
                this.form.sortBy = 'desc';
            }
            else {
                this.form.sortType = typ;
                this.form.sortBy = by;
            }
            this.getOutreachData(this.page)
        },
        showActivity(rid, rtype, recordId) { 
            if(rtype == 1){
                //prospect
                axios.get('/api/get-call-dispositions').then((response) => {
                    this.callDispositions = response.data.details.data;
                });
                axios.get('/api/get-call-purpose').then((response) => {
                    this.callPurposes = response.data.details.data;
                });
                axios.get('/api/get-outreach-prospect-details-calls/'+recordId).then((response) => {
                    this.calls = response.data.details.data;
                });
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
                });
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
            this.form.sortType = 'last_outreach_engage'
            this.form.sortBy = 'desc'
            this.owner = 0;
            this.totalNumberOfRecords = '-';
            this.approaching = '-';
            this.notStarted = '-';
            this.replied = '-';
            this.unresponsive = '-';
            this.doNotContact = '-';
            this.badContactInfoMissingEmail = '-';
            this.badContactInfoMissingPhone = '-';
            this.interested = '-';
            this.notInterested = '-';
            this.clients = '-';
            this.schedulingMetting = '-';
            this.meetingBooked = '-';
            this.negotiating = '-';
            this.emailFollowup = '-';
            this.phoneFollowup = '-';
            this.inQueueofProspectContactEveryMonth = '-';
            this.forwardedtoColleagueFriendDoNotContactAgain = '-';
            this.notAnswered = '-';
            this.irrelevantLeadDNC = '-';
            this.clientLOST = '-';
            this.unresponsiveEmailSequence = '-';
            this.badContactInfoMissingEmailPhone = '-';
            this.noStage = '-';
            this.getOutreachData(1);
        },
        getOutreachData(pno) {
            document.getElementById("check-all").checked = false;
            this.$Progress.start();
            let query = '';
            if(this.form.dateRange.startDate){
                var endDate = JSON.stringify(this.form.dateRange.endDate).slice(1, -1)
                var startDate = JSON.stringify(this.form.dateRange.startDate).slice(1, -1)
                query = query + "startDate="+startDate+'&endDate='+endDate
            }
            if(this.form.stage){
                query = query + '&stage='+this.form.stage
            }
            query = query + '&sort='+this.form.sortType+'&sortby='+this.form.sortBy+'&page='+pno
            axios.get('/api/get-outreach-all-prospects-dashboard?'+query).then((response) => {
                
                this.records = response.data.results;
                this.page = response.data.page;
                this.totalRecords = response.data.page.total;
                this.start = response.data.page.start;
                this.end = response.data.page.end;
                this.paginationArray = response.data.paginationArray;
                this.$Progress.finish();
                for(var i = 0; i < this.records.length; i++){
                    this.allProspects[this.records[i].id] = this.records[i].attributes;
                }
                this.totalNumberOfRecords = response.data.page.total
                
            });
        },        
        applyTemplate() {
            this.loader = 2;
            let vm = this;
            let tmplat = vm.templates.filter(function(item) {  return (item.id == vm.sform.template_id)?item:''});
            if(tmplat == '') {
                vm.parse_header.forEach(function (key, index) {
                    vm.tform.dfields[index] = '';
                });
            } else {
                let mapd = JSON.parse(tmplat[0].mapped);
                let df = mapd.dest;
                let sf = mapd.sourced;
                this.tform.dfields = []
                this.sform.fields = []
                this.sform.efields = []
                for(let i = 0; i < this.options.length; i++){                    
                    let optionkey = this.options[i]
                    let parseHeader = this.parse_header[i]
                    for(let j = 0; j < df.length; j++){
                        if(df[j] == optionkey){
                            this.tform.dfields[i] = parseHeader
                            this.setMapField(optionkey, i);
                        }
                    }
                }
            }
            this.loader = false;
        },
         ChooseTemplate(type) {
            this.tform.source = 'five9'; //this.form.destination;
            
            if(type == 3) {
                if(this.sform.efields.includes('mobilePhones') == false) {
                    this.$toasted.show("Field name 'Number 1' is required to start mapping !!", { 
                        theme: "toasted-primary", 
                        position: "bottom-center", 
                        className:'alert-danger alert py-2 px-3 fw-600',
                        duration : 5000
                    });
                    return false;
                }
            }
            if(this.sform.fields.length == 0) {
                this.$toasted.show("Please select atleast one fields to start syncing !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    className:'alert-danger alert py-2 px-3 fw-600',
                    duration : 5000
                });
                return false;
            }
            if(type == 2) {
                if(this.tform.name == '') {
                    this.$toasted.show("Please enter name for new template !!", { 
                        theme: "toasted-primary", 
                        position: "bottom-center", 
                        className:'alert-danger alert py-2 px-3 fw-600',
                        duration : 5000
                    });
                }
                else {
                    //this.tform.sfields = this.tform.dfields
                    //this.tform.dfields = this.sform.fields;
                    this.tform.post('/api/add-template')
                    .then(() => {
                        this.step = 2;
                    });
                }
            } else if(type == 3 || type == 1) {
                this.step = 2;
            }
                this.makingTestRecords()
                this.makingGroupedRecords()
        },
        backStep(){
            this.step = 1;
        },
        setMapField(mfield, sfield) { 
            if(this.tform.dfields[sfield] != '') {
                this.sform.fields[sfield] = mfield
                //this.sform.fields.push(mfield); 
                this.sform.efields[sfield] = this.tform.dfields[sfield]
                //this.sform.efields.push(this.tform.dfields[sfield]); 
            }
        },
        startMapping() {
            if(this.form.destination == '') {
                this.$toasted.show("Please select the destination to start mapping !!", { 
                    theme: "toasted-danger", 
                    position: "bottom-center", 
                    className:'alert-danger alert py-2 px-3 fw-600',
                    duration : 5000
                });
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
                axios.get('/api/get-type-template/'+this.form.destination).then((response) => {
                    this.templates = response.data;
                })
                this.step = 1;
            } else {
                this.options = [];
            }
        },
        startUpload() {
            if(this.form.fdata.length > this.climit) {
                this.$toasted.show("Don't select records within the limit !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    className:'alert-danger alert py-2 px-3 fw-600',
                    duration : 5000
                });
                return false;
            }
            this.step = 3;
            this.sform.records = this.recordContainer;
        },
        startSyncing() {
            if(this.sform.destination == 'five9' && this.sform.name == '' && this.sform.lid == '0') {
                this.$toasted.show("List name is mandatory !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    className:'alert-danger alert py-2 px-3 fw-600',
                    duration : 5000
                });
                return false;
            }
            let counter = 0            
            for (var prop in this.groupedRecords) {                
                this.sform.fdata[counter++] = {
                    "first_name" : this.groupedRecords[prop][0].firstName,
                    "last_name" : this.groupedRecords[prop][0].lastName,
                    "number1" : this.groupedRecords[prop][0].mobilePhones,
                    "number2" : this.groupedRecords[prop][0].workPhones,
                    "state" : this.groupedRecords[prop][0].addressState,
                    "street" : this.groupedRecords[prop][0].addressStreet,
                    "Tag" : this.groupedRecords[prop][0].tags,
                    "zip" : this.groupedRecords[prop][0].addressZip,
                    "city" : this.groupedRecords[prop][0].addressCity,
                    "company" : this.groupedRecords[prop][0].company,
                    "email" : this.groupedRecords[prop][0].email,
                };
            }
            
            this.loader = 1;
            this.sform.post('/api/uploadContacts').then((response) => {
                if(response.data.status == 'success'){
                    this.loader = false;
                    this.form.reset();
                    this.step = 4;
                    this.report = response.data.result; //"Inserted: "+ response.data.listRecordsInserted+", Updated: "+response.data.crmRecordsUpdated;
                } else {
                    this.$toasted.show(response.data.result, { 
                        theme: "toasted-primary", 
                        position: "bottom-center", 
                        className:'alert-danger alert py-2 px-3 fw-600',
                        duration : 5000
                    });
                }
               
            })
        },
        showDuplicateRecords(){
            this.showUnique = true;
            this.showDuplicate = false;
        },
        showUniqueRecords(){
            this.showUnique = false;
            this.showDuplicate = true;
        },        
        showAllRecords(){
            this.showUnique = true;
            this.showDuplicate = true;
        },
        deleteRecords(record){
            let records = []
            let counter = 0
            for (let i = 0; i < this.testing.length; i++) {
                if( (this.testing[i].mobilePhones == record.mobilePhones) && (this.testing[i].workPhones == record.workPhones) && (this.testing[i].firstName == record.firstName) && (this.testing[i].lastName == record.lastName) ){
                    
                }else{
                    records[counter++] = this.testing[i]
                }                
            }
            this.testing = []
            this.testing = records
            this.makingGroupedRecords()
        },
        refreshGroupedRecords(){
            this.makingTestRecords()
            this.ChooseTemplate(3)
        },
        swapRecords(record){
            if( (record.workPhones == '') || (record.workPhones == null) ){
                this.$toasted.show("Can't swap !!", { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    className:'alert-danger alert py-2 px-3 fw-600',
                    duration : 5000
                });
                return false;
            }
            
            var interChange = 0
            
            for (let i = 0; i < this.testing.length; i++) {
                if( (this.testing[i].mobilePhones == record.mobilePhones) && (this.testing[i].workPhones == record.workPhones) ){
                    interChange = this.testing[i].mobilePhones
                    this.testing[i].mobilePhones = this.testing[i].workPhones
                    this.testing[i].workPhones = interChange
                }
            }
            this.makingGroupedRecords()
        },        
        makingGroupedRecords(){
            const groups = this.testing.reduce((groups, item) => {                    
                const mobilePhones = (groups[item.mobilePhones] || []);
                if(item.mobilePhones){
                    var mobile = item.mobilePhones;
                    mobile = mobile.replace(/[^0-9]/g, "")
                    if(mobile.length > 10){
                        mobile = mobile.substring(mobile.length-10)
                    }
                    item.mobilePhones = mobile
                }
                if(item.workPhones){
                    var work = item.workPhones;
                    work = work.replace(/[^0-9]/g, "")
                    if(work.length > 10){
                        work = work.substring(work.length-10)
                    }
                    item.workPhones = work
                }
                mobilePhones.push(item);
                groups[item.mobilePhones] = mobilePhones;
                return groups;
            }, {});
            this.groupedRecords = groups;
            this.uniqueRecordCounter = 0;
            this.duplicateRecordCounter = 0;
            for (var prop in this.groupedRecords) {
                if(this.groupedRecords[prop].length > 1){
                    this.duplicateRecordCounter += this.groupedRecords[prop].length;
                }else{
                    this.uniqueRecordCounter +=1;
                }
            }            
        },
        makingTestRecords(){
            for(var i = 0; i < this.recordContainer.length; i++){
                if(this.allProspects[this.recordContainer[i]].mobilePhones.length > 0){
                    var mobilePhones = this.allProspects[this.recordContainer[i]].mobilePhones[this.allProspects[this.recordContainer[i]].mobilePhones.length-1]
                }else{
                    var mobilePhones = '2231231230';
                }
                if(this.allProspects[this.recordContainer[i]].firstName){
                    var firstName = this.allProspects[this.recordContainer[i]].firstName;
                }else{
                    var firstName = null;
                }
                if(this.allProspects[this.recordContainer[i]].lastName){
                    var lastName = this.allProspects[this.recordContainer[i]].lastName;
                }else{
                    var lastName = null;
                }
                if(this.allProspects[this.recordContainer[i]].homePhones){
                    var homePhones = this.allProspects[this.recordContainer[i]].homePhones[this.allProspects[this.recordContainer[i]].homePhones.length-1]
                }else{
                    var homePhones = null;
                }
                if(this.allProspects[this.recordContainer[i]].workPhones){
                    var workPhones = this.allProspects[this.recordContainer[i]].workPhones[this.allProspects[this.recordContainer[i]].workPhones.length-1]
                }else{
                    var workPhones = null;
                }
                if(this.allProspects[this.recordContainer[i]].addressState){
                    var addressState = this.allProspects[this.recordContainer[i]].addressState;
                }else{
                    var addressState = null;
                }
                if(this.allProspects[this.recordContainer[i]].addressStreet){
                    var addressStreet = this.allProspects[this.recordContainer[i]].addressStreet;
                }else{
                    var addressStreet = null;
                }
                if(this.allProspects[this.recordContainer[i]].tags){
                    let tagArray = []
                    for(let j = 0; j <= this.allProspects[this.recordContainer[i]].tags.length; j++){
                        tagArray[j] = this.allProspects[this.recordContainer[i]].tags[j] 
                    }
                    var tags = tagArray.join()
                }else{
                    var tags = null;
                }
                if(this.allProspects[this.recordContainer[i]].addressZip){
                    var addressZip = this.allProspects[this.recordContainer[i]].addressZip;
                }else{
                    var addressZip = null;
                }
                if(this.allProspects[this.recordContainer[i]].addressCity){
                    var addressCity = this.allProspects[this.recordContainer[i]].addressCity;
                }else{
                    var addressCity = null;
                }
                if(this.allProspects[this.recordContainer[i]].company){
                    var company = this.allProspects[this.recordContainer[i]].company;
                }else{
                    var company = null;
                }
                if(this.allProspects[this.recordContainer[i]].emails){
                    var emails = this.allProspects[this.recordContainer[i]].emails[0]
                }else{
                    var emails = null;
                }
                this.testing[i] = {
                    "firstName" : firstName,
                    "lastName" : lastName,
                    "mobilePhones" : mobilePhones,
                    "homePhones" : this.allProspects[this.recordContainer[i]].homePhones[this.allProspects[this.recordContainer[i]].homePhones.length-1],
                    "workPhones" : workPhones,
                    "addressState" : addressState,
                    "addressStreet": addressStreet,
                    "tags" : tags,
                    "addressZip" : addressZip,
                    "addressCity" : addressCity,
                    "company" : company,
                    "email" : emails,
                }
            }
        },
        checkRecords(){
            let counter = 0
            for (var prop in this.groupedRecords) {
                counter++
            }
            let trs = counter - this.uniqueRecordCounter;
            if(this.duplicateRecordCounter >= 1) {            
                this.$toasted.show('You still have '+this.duplicateRecordCounter +' duplicate records. If you proceed, than '+ trs +' records will be added as unique. ', { 
                    theme: "toasted-primary", 
                    position: "bottom-center", 
                    className:'alert-danger alert py-2 px-3 fw-600',
                    duration : 2000
                });
            }
              this.step = 3;
            
        },
        isExist(value){
            for(var i=0; i < this.tform.dfields.length; i++){
                if( this.tform.dfields[i] == value){
                    return true
                }
            }
            return false
        },
        smartMapping(){
            for(let i = 0; i < 11; i++){
                this.tform.dfields[i] = this.parse_header[i];
                this.setMapField(this.options[i], i);
            }
        },

        getSynopsisData(){
                this.owner = 0;
                this.totalNumberOfRecords = '-';
                this.approaching = '-';
                this.notStarted = '-';
                this.replied = '-';
                this.unresponsive = '-';
                this.doNotContact = '-';
                this.badContactInfoMissingEmail = '-';
                this.badContactInfoMissingPhone = '-';
                this.interested = '-';
                this.notInterested = '-';
                this.clients = '-';
                this.schedulingMetting = '-';
                this.meetingBooked = '-';
                this.negotiating = '-';
                this.emailFollowup = '-';
                this.phoneFollowup = '-';
                this.inQueueofProspectContactEveryMonth = '-';
                this.forwardedtoColleagueFriendDoNotContactAgain = '-';
                this.notAnswered = '-';
                this.irrelevantLeadDNC = '-';
                this.clientLOST = '-';
                this.unresponsiveEmailSequence = '-';
                this.badContactInfoMissingEmailPhone = '-';
                this.noStage = '-';
                this.$Progress.start();
                
                
            },
             getAllData(){
                axios.get('/api/get-outreach-prospect-synopsis-all-data').then((response) => {
                    this.totalNumberOfRecords = response.data.total;
                    this.approaching = response.data.approaching;
                    this.notStarted = response.data.not_started;
                    this.replied = response.data.replied;
                    this.unresponsive = response.data.unresponsive;
                    this.doNotContact = response.data.do_not_contact;
                    this.badContactInfoMissingEmail = response.data.bad_contact_info_missing_email;
                    this.badContactInfoMissingPhone = response.data.bad_contact_info_missing_phone;
                    this.interested = response.data.interested;
                    this.notInterested = response.data.not_answered;
                    this.clients = response.data.clients;
                    this.schedulingMetting = response.data.scheduling_metting;
                    this.meetingBooked = response.data.meeting_booked;
                    this.negotiating = response.data.negotiating;
                    this.emailFollowup = response.data.email_follow_up;
                    this.phoneFollowup = response.data.phone_follow_up
                    this.inQueueofProspectContactEveryMonth = response.data.in_qQueue_of_prospect_contact_every_month;
                    this.forwardedtoColleagueFriendDoNotContactAgain = response.data.forwarded_to_colleague_friend_do_not_contact_again;
                    this.notAnswered = response.data.not_started;
                    this.irrelevantLeadDNC = response.data.irrelevant_lead_DNC;
                    this.clientLOST = response.data.client_LOST;
                    this.unresponsiveEmailSequence = response.data.unresponsive_Email_Sequence;
                    this.badContactInfoMissingEmailPhone = response.data.bad_ContactInfo_Missing_Email_Phone;
                    this.noStage = response.data.no_Stage;
                });
            },
    },
    beforeMount() {
        // axios.get('/api/get-f9-list').then((response) => {
        //     this.five9_list = response.data;
        // });
        // axios.get('/api/get-f9-campaigns').then((response) => {
        //     this.five9_campaigns = response.data;
        // });
    },
    mounted() {
        this.getOutreachData(1);
        axios.get('/api/get-outreach-stages/').then((response) => {
            this.stages = response.data.result;
            //this.totalNumberOfRecords = response.data.results.meta.count;
            this.notStarted = response.data.stageRecords.notStarted;
            this.approaching = response.data.stageRecords.approaching;
            this.replied = response.data.stageRecords.replied;
            this.unresponsive = response.data.stageRecords.unresponsive;
            this.doNotContact = response.data.stageRecords.doNotContact;
            this.badContactInfoMissingEmail = response.data.stageRecords.badContactInfoMissingEmail;
            this.badContactInfoMissingPhone = response.data.stageRecords.badContactInfoMissingPhone;
            this.interested = response.data.stageRecords.interested;
            this.notInterested = response.data.stageRecords.notInterested;
            this.clients = response.data.stageRecords.clients;
            this.schedulingMetting = response.data.stageRecords.schedulingMetting;
            this.meetingBooked = response.data.stageRecords.meetingBooked;
            this.negotiating = response.data.stageRecords.negotiating;
            this.emailFollowup = response.data.stageRecords.emailFollowup;
            this.inQueueofProspectContactEveryMonth = response.data.stageRecords.inQueueofProspectContactEveryMonth;
            this.phoneFollowup = response.data.stageRecords.phoneFollowup;
            this.notAnswered = response.data.stageRecords.notAnswered;
            this.irrelevantLeadDNC = response.data.stageRecords.irrelevantLeadDNC;
            this.forwardedtoColleagueFriendDoNotContactAgain = response.data.stageRecords.forwardedtoColleagueFriendDoNotContactAgain;
            this.clientLOST = response.data.stageRecords.clientLOST;
            this.unresponsiveEmailSequence = response.data.stageRecords.unresponsiveEmailSequence;
            this.badContactInfoMissingEmailPhone = response.data.stageRecords.badContactInfoMissingEmailPhone;
            this.noStage = response.data.stageRecords.noStage;
        });
        // axios.get('/api/get-outreach-stages-record/').then((response) => {
        //     this.stages = response.data.result;
        // });
        //this.getOutreachRecords(1);
    }
}
</script>