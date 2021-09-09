<template>
    <div>
        <div>
            <div class="filterbox">
                <div class="row mx-0 mb-2">
                    <div class="col-md-3 pl-0">
                        <div class="input-group in-search-group">
                            <input type="text" class="form-control" v-model="form.textSearch" placeholder="Search by name, email or company">
                            <div class="input-group-append">
                                <button class="btn" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12"></div>
                    <div class="col-md-3 col-12 p-0 text-right pt-2">
                        <img :src="loader_url" alt="Loading..." v-show="loader">
                        <span v-if="recordContainer.length >= 1"> Selected  <b>{{ recordContainer.length | freeNumber }}</b> of  </span>
                        <span v-else>Showing</span>
                        <span><b>{{ totalNumberOfRecords | freeNumber }}</b> Results</span>
                    </div>
                </div>
            </div> 
            <div class="divtable border-top">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-45 text-center">
                            <input type="checkbox" name="" id="check-all" value="0" aria-label="...">
                        </div>
                        <div class="divthead-elem wf-100">
                            Record ID
                        </div>
                        <div class="divthead-elem mwf-200">
                            Name                            
                        </div>
                        <div class="divthead-elem wf-250">
                            Old Number                        
                        </div>
                        <div class="divthead-elem wf-250">
                            New Number
                        </div>
                    </div>
                </div>
                <div class="divtbody  fit-divt-content2">
                    <div class="divtbody-row" v-for="record in records.data" :key="'dsg-'+record.id" :class="[(active_row.id == record.record_id)?'expended':'']">
                        <div class="divtbody-elem  wf-45 text-center">
                            <div class="form-check">
                                <input :id="'record-'+record.id" class="form-check-input me-1" type="checkbox" :value="record.id">
                            </div>
                        </div>
                        <div class="divtbody-elem  wf-125">
                            <a :href="'https://app1e.outreach.io/prospects/'+record.record_id+'/overview'" target="_blank"> {{ record.record_id }}</a>
                        </div>
                        <div class="divtbody-elem mwf-200">
                            <router-link target="_blank" :to="'prospects/' + record.record_id" class=""><b>{{ record.first_name }} {{ record.last_name }} </b></router-link>
                            <br>
                            <small class="fw-500" v-title="record.designation" v-if="record.designation">{{ record.designation }}  in </small> 
                            <span class="company-sm" v-title="record.company" v-if="record.company">{{ record.company }}</span>
                        </div>
                        <div class="divtbody-elem wf-250">
                            <span><b>VOIP Phone - </b> {{ record.voipPhones }} </span><br>
                            <span><b>Mobile Phone - </b> {{ record.mobilePhones }} </span><br>
                            <span><b>Work Phone - </b> {{ record.workPhones }} </span><br>
                            <span><b>Home Phone - </b> {{ record.homePhones }} </span><br>
                            <span><b>Other Phone - </b> {{ record.otherPhones }} </span><br>
                            <span><b>Company HQ Phone - </b> {{ record.company_hq_phone }} </span>
                        </div>
                        <div class="divtbody-elem wf-250">
                            <span><b>Mobile Phone - </b> {{ record.voipPhones }} </span><br>
                            <span><b>Work Phone - </b> {{ record.otherPhones }} </span><br>
                            <span><b>HQ Phone (Other) - </b> {{ record.company_hq_phone }} </span>
                        </div>
                    </div>
                </div>
                <div class="divtfoot border-top">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="form.recordPerPage" @change="getDatasets">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </span>
                        <pagination :limit="5" :data="records" @pagination-change-page="getDatasets"></pagination>
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
            loader:false,
            step:0,
            records:{},
            totalRecords : 0,
            active_row:'',
            form: new Form({
                sortType:'outreach_touched_at',
                sortBy:'desc',
                recordPerPage:20,
                pageno:1,
                datatset:''
            }),
            loader_url: '/img/spinner.gif',
            totalNumberOfRecords:'',
            recordContainer:''
        }
    },
    filters: {
        
    },
    computed: {
        
    },
    methods: {
        getDatasets(page) {
            this.loader = true;
            this.$Progress.start();  
            if(page == undefined) { page = 1; }          
            axios.get('/api/data-check?page='+page).then((response) => {                
                this.records = response.data;
                this.totalRecords = response.data.total;                
                this.$Progress.finish();               
                this.totalNumberOfRecords = response.data.total;
                this.loader = false;
            });
        }
    },
    beforeMount() {
       
    },
    mounted() {
        this.getDatasets(1);
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
</style>