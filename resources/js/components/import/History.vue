<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-2 col-12 pl-0">
                    <input type="text" placeholder="enter name" class="form-control" v-model="form.name" />
                </div>
                <div class="col-md-2 col-12 pl-0">
                    <input type="email" placeholder="search by email" class="form-control" v-model="form.email" />
                </div>
                <div class="col-md-2 col-12 pl-0">
                    <input type="text" placeholder="mobile/record-id" class="form-control" v-model="form.mobile" />
                </div>
                <div class="col-md-2 col-12 pl-0">
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
                <div class="col-md-4 col-12 p-0">
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3" @click="getRecords">
                        <i class="bi bi-filter-circle"></i> Filter 
                    </button>
                    <img :src="loader_url" v-show="loader == 1">
                    <button type="button" class="btn btn-danger icon-btn theme-btn " @click="resetForm">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3 float-right" @click="crone">
                        <i class="bi bi-arrow-repeat"></i> Crone 
                    </button>
                    <img :src="loader_url" v-show="loader == 2">
                </div>
            </div>
        </div>
        <div class="mapping-div">
            <div class="row m-0">
                <div class="col-md-12 col-12 p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th class="wf-75">SNo</th>
                                    <th>Job Title</th>
                                    <th>Outreach</th>
                                    <th>Five9</th>
                                    <th>Activecampaign</th>
                                    <th class="wf-100">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(record, rkey) in records" :key="record.id" :class="'records record-'+parseInt((rkey+per_page)/per_page)" v-show="parseInt((rkey+per_page)/per_page) == 1">
                                    <td>
                                        {{ Number(rkey)+1 }}
                                    </td>
                                    <td>
                                        {{ record.title }}
                                    </td>
                                    <td>
                                        <a @click="showDetails(1, record.outreach_contacts)" href="javascript:;"> {{ record.outreach_count }} out of {{ record.outreach_count+record.ac_count+record.five9_count }}</a>
                                    </td>
                                    <td>
                                       <a @click="showDetails(2, record.five9_contacts)" href="javascript:;"> {{ record.five9_count }} out of {{ record.outreach_count+record.ac_count+record.five9_count }}</a>
                                    </td>
                                    <td>
                                        <a @click="showDetails(3, record.ac_contacts)" href="javascript:;"> {{ record.ac_count }} out of {{ record.outreach_count+record.ac_count+record.five9_count }}</a>
                                    </td>
                                    <td>
                                        {{ record.status }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="class">
                            <ul class="paginate" id="paginator">
                                <li v-for="p in parseInt(pages)" :key="'page'+p">
                                    <span v-if="p == pno"> {{p}} </span>
                                    <a href="javascript:;" @click="pagination(p)" v-else> {{p}} </a>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade modal-full" tabindex="-1" role="dialog" id="detailModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ active_title }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                         <img :src="loader_url" v-if="loader == 2">
                        <table class="table table-bordered table-striped" v-else>
                            <thead>
                                <tr>
                                    <th>Sno</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th v-if="active_type == 1">Last OR Activity</th>
                                    <th v-if="active_type == 1">Engagement</th>
                                    <th v-if="active_type == 2">Last Call</th>
                                    <th v-if="active_type == 2">Dial Attempts</th>
                                    <th v-if="active_type == 2">Disposition</th>
                                    <th v-if="active_type == 2">Last Agent</th>
                                    <th v-if="active_type == 3">Last AC Activity</th>
                                </tr>
                            </thead>
                            <tbody v-if="active_data.length >= 1">
                                <tr v-for="(content, ano) in active_data" :key="'row'+ano">
                                    <td>{{ ano+1 }}</td>
                                    <td>{{ content.first_name+ ' ' +content.last_name }}<br>
                                    <small>{{ content.position }} at <b>{{ content.company }}</b> </small>
                                    </td>
                                    <td> <span  v-if="active_type == 2"> {{ content.number1 }} </span> 
                                    <span v-else>{{ content.mobilePhones }}</span></td>
                                    <td>{{ content.email }}</td>
                                    <th v-if="active_type == 1"> {{ content.last_outreach_activity }} </th>
                                    <th v-if="active_type == 1"> {{ content.engage_score }} </th>
                                    <th v-if="active_type == 2"> {{ content.last_call }} </th>
                                    <th v-if="active_type == 2"> {{ content.dial_attempts }} </th>
                                    <th v-if="active_type == 2"> {{ content.last_dispo }} </th>
                                    <th v-if="active_type == 2"> {{ content.last_agent }} </th>
                                    <th v-if="active_type == 3"> {{ content.last_ac_activity }} </th>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="8">
                                        <p class="text-danger">No Data Found</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import DateRangePicker from 'vue2-daterange-picker';

export default {
    components: { DateRangePicker },
    data() {
        return {
            loader:false,
            records:{},
            loader_url: '/img/spinner.gif',
            form: new Form({
                dateRange : {},
                name : '',
                email : '',
                mobile : '',
            }),
            active_data:{},
            active_title:'',
            active_type:'',
            pages : 0,
            pno : 1,
            pageArray : [],
            per_page : 20,
        }
    },
    methods: {
        dateFormat(classes, date) {
            if(!classes.disabled) {
               // classes.disabled = date.getTime() > new Date()
            }
            return classes
        },
        resetForm() {
            window.location.reload();
        },
        getRecords() {
            this.$Progress.start()
            this.form.post('/api/get-job-history')
            .then((response) => {
                this.records = response.data.results;
                this.pages = Math.ceil(this.records.length/this.per_page)
                for(var i = 0; i < this.pages; i++){
                    this.pageArray[i] = i
                }
                this.$Progress.finish()
            });
        },
        showDetails(vr, content) { 
            this.loader = 2;
            this.active_type = vr;
            this.active_data = [];
            $('#detailModal').modal('show');
            if(vr == 1) {
                this.active_title = 'Outreach Contacts';
            } else if(vr ==2) {
                this.active_title = 'Five9 Contacts';
            } else if(vr ==3) {
                this.active_title = 'Activecampaign Contacts';
            }
            axios.post('/api/get-outreach-updated-data', {data:content, type:vr}).then((response) => {
                this.active_data = response.data.result;
                this.loader = false;
            });
        },
        convert(current_datetime){
            let cy, cm, cd, gm;
            cy = current_datetime.getFullYear();
            gm = current_datetime.getMonth()+1;
            cm = (current_datetime.getMonth() < 10)?'0' + gm:gm;
            cd = (current_datetime.getDate() < 10)?'0' + current_datetime.getDate():current_datetime.getDate();
            let formatted_date = cy + "-" + cm + "-" + cd;
            return formatted_date;
        },
        crone(){
            this.loader = 2
            this.$Progress.start()
            axios.get('/api/hit-crone-manually').then((response) => {
                this.loader = false;
                this.$Progress.finish()
                this.getRecords();
            });
        },
        pagination(current_page){
            //var element = document.getElementById("paginator").parentElement.nodeName

            $(".records").css("display", "none")
            $(".record-"+current_page).css("display", "table-row")
        }
    },
    mounted() {
        this.getRecords();
    }
}
</script>