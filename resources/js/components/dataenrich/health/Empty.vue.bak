<template>
    <div class="dh-sections">
        <div class="dh-headings">            
            <div class="dh-heading-sort">
                <div class="row m-0">
                    <div class="col-10 pl-0">
                        <h5 class="dh-heading-title">Prospects with Empty Fields</h5>
                    </div>
                    <div class="col-2 pr-2">
                        
                    </div>
                </div>
            </div>
            <div class="dh-body-content">
                <div class="dh-body-items" v-if="loader == true">
                    <div class="text-center p-4">
                        <div class="icn-spinner bi-gear text-danger" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="dh-body-items" v-else-if="qtype == 'or'">
                    <div class="dhb-item-group" v-for="(dd, dk) in ddata" :key="'ddata'+dk" :class="[(active_data_key == dk)?'active':'']">
                        <div class="dhb-item-group-heading">
                            {{ dk | capitalize }} ({{ dd.length | freeNumber }}) 
                            <i @click="active_data_key = dk" class="bi" :class="[(active_data_key == dk)?'bi-dash-circle':'bi-plus-circle']"></i>
                            <i @click="showProspect(dd, dk)" class="bi bi-arrow-right-circle mr-2" v-show="active_data_key == dk"></i>
                        </div>
                        <div class="dhb-item-group-content">
                            <p v-for="(dvalue, dvk) in dd" :key="'dval'+dvk">
                                {{ dvalue.first_name+' '+dvalue.last_name }}
                                <b class="float-right">{{ '--' }}</b>
                            </p>
                        </div>
                    </div>                    
                </div>
                <div class="dh-body-items" v-else>                
                    <div class="dhb-item-group active" v-if="ddata">
                        <div class="dhb-item-group-heading">
                            All Fields <b>( {{ ddata.length | freeNumber  }} )</b>
                            <i class="bi bi-dash-circle"></i>
                            <i @click="showProspect(ddata, allFields)" class="bi bi-arrow-right-circle mr-2"></i>
                        </div>
                        <div class="dhb-item-group-content">
                            <p  v-for="(record, rkey) in fields" :key="'all-'+rkey">
                            {{ record | capitalize }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dh-contents">
            <div class="w-100" v-if="loader">
                <div class="text-center p-4">
                    <div class="icn-spinner bi-gear text-danger" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="d-table w-100" v-else-if="mloader">
                <div class="d-table-cell align-middle text-center p-4">
                    <div class="spinner-grow text-secondary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div v-else-if="active_data == ''">
                <p class="text-center p-4 text-danger">Please select a group for detailed display of prospects</p>
            </div>
            <div v-else-if="pdata.length == 0">
                <p class="text-center p-4 text-danger">No data found to display</p>
            </div>
            <div class="divtable border-top" v-else>
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-50">
                            SNo
                        </div>
                        <div class="divthead-elem mwf-150">
                            Name (Company)                            
                        </div>
                        <div class="divthead-elem wf-175">
                            Emails                        
                        </div>
                        <div class="divthead-elem wf-150">
                            Stage
                        </div>
                        <div class="divthead-elem wf-150">
                            Mobile Phone
                        </div>
                        <div class="divthead-elem wf-150">
                            Work Phone
                        </div>
                        <div class="divthead-elem wf-150">
                            Home Phone
                        </div>
                    </div>
                </div>
                <div class="divtbody table-without-search">
                    <div class="divtbody-row" v-for="(record, rid) in pdata" :key="'pdata-'+rid">
                        <div class="divtbody-elem wf-50">
                            {{ rid+1 }}
                        </div>
                        <div class="divtbody-elem mwf-150">
                           <router-link target="_blank" :to="'prospects/' + record.record_id" class="text-capitalize"><b>{{ record.first_name }} {{ record.last_name }} </b></router-link>
                            <br>
                            <small class="fw-500" v-title="record.title" v-if="record.title">{{ record.title }}  in </small> 
                            <span class="company-sm" v-title="record.company" v-if="record.company">{{ record.company }}</span>
                        </div>
                        <div class="divtbody-elem wf-175">
                            {{ record.emails }}
                        </div>
                        <div class="divtbody-elem wf-150">
                            <span v-if="record.stage_name" :class="record.stage_css">
                            {{ record.stage_name }}
                            </span>
                            <span class="no-stage" v-else>No Stage</span>
                        </div>
                        <div class="divtbody-elem wf-150">
                            {{ record.mobilePhones }}
                        </div>
                        <div class="divtbody-elem wf-150">
                            {{ record.workPhones }}
                        </div>
                        <div class="divtbody-elem wf-150">
                            {{ record.homePhones }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        'qtype': String,
        'loader': Boolean,
        'ddata': [Array, Object],
        'fields': Array
    },
    data() {
        return {
            form: new Form({
                select : 0
            }),
            mloader:false,
            active_data_key:'',
            active_data:'',
            active_field:'',
            pdata:[]
        }
    },
    computed: {
        allFields(){
            let s = this.fields.map( a => a.charAt(0).toUpperCase() + a.substr(1) );
            return s.join(', ')
            
        }
    },
    watch: {
        qtype(newValue, oldValue) {
            if(newValue != oldValue) {
                this.active_data_key = '';
                this.active_data = '';
                this.active_field = '';
                this.pdata = [];
            }
        },
        
    },
    methods: {
        showProspect(dd, dk) {
            this.active_data = dd;
            this.active_field = dk;
            this.mloader = true;
            axios.post('/api/get-related-prospects', {data:dd}).then((response) => {
                this.pdata = response.data
                this.mloader = false
            })
        }
    }
}
</script>