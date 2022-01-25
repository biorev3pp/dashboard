<template>
    <div class="health-page">
        <div class="health-bar">
            <h4 class="health-bar-title">Data Health Checkup</h4>
            <div class="health-filter">
                <div class="health-filter-item" :class="[(active_filter == 'analysis')?'active':'']" @click="active_filter = 'analysis'; fields = []; ddata = [];">
                    Analysis
                    <i class="bi" :class="[(active_filter == 'analysis')?'bi-chevron-down':'bi-chevron-right']"></i>
                </div>
                <div class="health-filter-item" :class="[(active_filter == 'empty')?'active':'']" @click="active_filter = 'empty'; fields = []; ddata = [];">
                    Empty Data
                    <i class="bi" :class="[(active_filter == 'empty')?'bi-chevron-down':'bi-chevron-right']"></i>
                </div>
                <div class="health-filter-item" :class="[(active_filter == 'duplicate')?'active':'']" @click="active_filter = 'duplicate'; fields = []; ddata = [];">
                    Duplicate Data
                    <i class="bi" :class="[(active_filter == 'duplicate')?'bi-chevron-down':'bi-chevron-right']"></i>
                </div>
            </div>
            <div class="health-filter-options" v-if="active_filter != 'analysis'">
                <b-form-group>
                    <template #label>
                        <h5 class="health-bar-title">Choose your Fields
                            <span class="spinner-border float-right text-danger" v-if="loader"></span>
                            <i class="bi bi-arrow-clockwise float-right" :class="[(rstatus)?'icn-spinner bi-gear text-danger':'bi-arrow-clockwise ']" @click="toggleRefresh" v-else></i>
                        </h5>
                        <div class="row mx-0 my-2">
                            <div class="col-6 px-2">
                                <button class="btn btn-block theme-btn" :class="[(qtype == 'and'?'btn-biorev':'btn-outline-biorev')]" @click="qtype = 'and'">All</button>
                            </div>
                            <div class="col-6 px-2">
                                <button class="btn btn-block theme-btn" :class="[(qtype == 'or'?'btn-biorev':'btn-outline-biorev')]" @click="qtype = 'or'">Any</button>
                            </div>
                        </div>
                        <b-form-checkbox
                            v-model="allSelected"
                            :indeterminate="indeterminate"
                            class="all-selection"
                            @change="toggleAll">
                            {{ allSelected ? 'Deselect All' : 'Select All' }}
                        </b-form-checkbox>
                    </template>

                    <b-form-checkbox-group
                        id="fields"
                        v-model="fields"
                        :options="all_fields"
                        name="fields"
                        class="sub-selection"
                        aria-label="Individual Field"
                        stacked>
                    </b-form-checkbox-group>
                </b-form-group>
            </div>
        </div>
        <div class="health-option-data">
            <analytic-data v-if="active_filter == 'analysis'" />
            <duplicate-data :qtype="qtype" :loader="rstatus" :fields="fields" :ddata="ddata" v-else-if="active_filter == 'duplicate'" />
            <empty-data :qtype="qtype" :loader="rstatus" :fields="fields" :ddata="ddata" v-else-if="active_filter == 'empty'" />
            <p class="text-danger p-4 text-center" v-else>Please do selection for data health check</p>
        </div>
    </div>
</template>
<script>
import AnalyticData from './health/Analysis.vue'
import DuplicateData from './health/Duplicate.vue'
import EmptyData from './health/Empty.vue'
import {BFormCheckboxGroup , BFormCheckbox, BFormGroup} from 'bootstrap-vue';
export default {
    components:{ AnalyticData, DuplicateData, EmptyData, BFormCheckboxGroup, BFormCheckbox, BFormGroup},
    data() {
        return {
            active_filter:'analysis',
            fields:[],
            allSelected:false,
            indeterminate: false,
            qtype:'and',
            rstatus:false,
            emptyData : [],
            empty_fields:['first name', 'last name', 'Business Email', 'Company', 'Title', 'Mobile Phone', 'Work Phone', 'Home Phone', 'Country', 'State', 'City', 'Zipcode', 'Timezone', 'Timezone Group', 'Purchase Authorization', 'Stage', 'Industry', 'Primary Industry', 'Department'],
            duplicate_fields:['first name', 'last name', 'Business Email', 'Mobile Phone', 'Work Phone', 'Home Phone'],
            loader:false,
            ddata:[],
        }
    },
    computed: {
        all_fields() {
            if(this.active_filter == 'empty') {
                return this.empty_fields
            } else {
                return this.duplicate_fields
            }
        }
    },
    methods: {
        toggleAll(checked) {
            this.fields = checked ? this.all_fields.slice() : []
        },
        toggleRefresh() {
            if(this.rstatus == true) {
                this.loader = true
                if(this.active_filter == 'empty'){
                    axios.post("/api/get-non-empty-column-health", {fields:this.fields, qtype:this.qtype}).then((response) => {
                        this.ddata = response.data
                        this.loader = false
                        this.rstatus = false
                    })
                } else if(this.active_filter == 'duplicate') {
                    axios.post('/api/get-duplicate-column-health', {fields:this.fields, qtype:this.qtype}).then((response) => {
                        this.ddata = response.data
                        this.loader = false
                        this.rstatus = false
                    })
                }
            } else {
                this.rstatus = true
            }
        }
    },
    watch: {
        fields(newValue, oldValue) {
            if (newValue.length === 0) {
                this.indeterminate = false
                this.allSelected = false
            } 
            else if (newValue.length === this.all_fields.length) {
                this.indeterminate = false
                this.allSelected = true
            } else {
                this.indeterminate = true
                this.allSelected = false
            }
            if(newValue != oldValue) {
                this.rstatus = true
            }
        },
        qtype(newValue, oldValue) {
            if(newValue != oldValue) {
                this.rstatus = true
            }
        }
    },
    mounted() {

    }
}
</script>