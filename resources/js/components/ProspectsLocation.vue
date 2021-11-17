<template>
    <div>
        <div>
            <div class="filterbox">
                <div class="row m-0">
                    <div class="col-md-4 col-12 pl-0">
                        
                    </div>
                    <div class="col-md-8 col-12 pr-0 text-right form-inline d-block">
                        
                    </div>
                </div>
            </div> 
            <div class="divtable">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-45 text-center">
                            Sno
                        </div>
                        <div class="divthead-elem wf-200">
                            Name                            
                        </div>
                        <div class="divthead-elem wf-200">
                            Country
                        </div>
                        <div class="divthead-elem wf-200">
                            State
                        </div>
                        <div class="divthead-elem wf-150">
                            City
                        </div>
                        <div class="divthead-elem wf-120">
                            Timezone
                        </div>
                        <div class="divthead-elem wf-120">
                            Phones
                        </div>
                    </div>
                </div>
                <div class="divtbody table-without-search">
                    <div class="divtbody-row" v-for="(record, i) in records.data" :key="record.id" >
                        <div class="divtbody-elem  wf-45 text-center">
                            <span v-if="page.page == 1">{{ i+1 }}</span>
                            <span v-else>{{ page.start + i }}</span>
                        </div>
                        <div class="divtbody-elem wf-200">
                            {{ record.contact_name }}
                        </div>
                        <div class="divtbody-elem wf-200">
                            <span v-if="record.contact_country">{{ record.contact_country }}</span><span v-else>--</span>
                        </div>                        
                        <div class="divtbody-elem  wf-200">
                            <span v-if="record.contact_state">{{ record.contact_state }} </span><span v-else>--</span>
                            <br> 
                            <span v-if="record.state_name">{{ record.state_name }}</span><span v-else>--</span>
                        </div>
                        <div class="divtbody-elem  wf-150">
                            <span v-if="record.contact_city">{{ record.contact_city }} </span><span v-else>--</span>
                            <br> 
                            <span v-if="record.city_name">{{ record.city_name }}</span><span v-else>--</span>
                        </div>
                        <div class="divtbody-elem  wf-120">
                            <span v-if="record.contact_timezone">{{ record.contact_timezone }} </span><span v-else>--</span>
                            <br> 
                            <span v-if="record.time_timezone">{{ record.time_timezone }}</span><span v-else>--</span>
                        </div>
                        <div class="divtbody-elem  wf-120">
                            <span class="d-block" v-if="record.workPhones"><b>W: </b> {{ record.workPhones }} </span>
                            <span class="d-block" v-if="record.homePhones"><b>H: </b> {{ record.homePhones }} </span>
                            <span class="d-block" v-if="record.mobilePhones"><b>M: </b> {{ record.mobilePhones }} </span>
                        </div>
                    </div>
                </div>
                <div class="divtfoot">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="form.recordPerPage" @change="getOutreachData(1)">
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <!-- <option :value="totalNumberOfRecords">All</option> -->
                            </select>
                        </span>
                        <pagination :limit="5" :data="records" @pagination-change-page="getOutreachData"></pagination>
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Total Records : </label>
                            <b>
                                {{ totalRecords }}
                            </b>
                        </span>
                    </div>                    
                </div>
            </div>
        </div>
        
    </div>
</template>
<script>
export default {
    components: {},
    data() {
        return {
            page:{},
            records:{},
            totalRecords:'',
            form: new Form({
                sortType:'outreach_touched_at',
                sortBy:'desc',
                recordPerPage:15,
                page:1,
                type:0
            }),
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
        
    },
    methods: {
        getOutreachData(pno){
            this.form.page = pno
            this.form.post("/api/get-prospects-location").then((response) => {
                this.records = response.data.results
                this.page = response.data.page
                this.totalRecords = response.data.page.total
            })
        }
    },
    beforeMount() {
        this.form.type = this.$route.params.id
    },
    mounted() {
        this.getOutreachData(1)
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