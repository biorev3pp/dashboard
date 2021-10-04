<template>
    <div>
        <div class="row m-0">
            <div class="col-6">
                <div class="title-bg text-left p-2"><b class="text-uppercase fw-500">Primary Filter:</b>  Stage</div> 
                <div class="position-relative text-left">
                    <button type="button" class="btn btn-outline-dark icon-btn-left theme-btn mt-2 mb-1 mr-1" @click="showFilter()">
                        <i class="bi bi-plus-square"></i> Add More filters
                    </button>
                    <button type="button" class="btn btn-outline-danger icon-btn-left theme-btn mt-2 mb-1 float-right" @click="showFilter()">
                        <i class="bi bi-x-square"></i> Clear All
                    </button>
        
                    <div class="stage-select-box selection-box top-44" v-show="showFilterView">
                        <div class="stage-box-header">
                            <i class="bi bi-x close" @click="showFilterView = false"></i>
                            <span class="control-label text-uppercase">Select Filters</span>
                        </div>
                        <div class="stage-box-body">
                            <p class="search-filter">
                                <input type="text" placeholder="Search Your Field" v-model="filter_keyword">
                            </p>
                            <div class="fh-250">
                                <a href="javascript:;" class="stage-link" v-for="(fitem, fk) in filterScanItems" :key="'fkey-'+fk" @click="showFilterOption(fitem)">{{ fitem.filter }}</a>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="col-6">
                <table class="table table-striped table-condesned table-bordered">
                    <tbody>
                        <tr>
                            <th class="text-uppercase text-left" width="50%">Total Prospects</th>
                            <td class="text-left fw-500">43,000</td>
                        </tr>
                        <tr>
                            <th class="text-uppercase text-left" width="50%">Total Emails</th>
                            <td class="text-left fw-500">45,000</td>
                        </tr>
                        <tr>
                            <th class="text-uppercase text-left" width="50%">Total Calls</th>
                            <td class="text-left fw-500">65,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        'filter1': {
            type: [Number, String],
            required: true
        },
    },
    data() {
        return {
           
        }
    },
    computed: {
        
    },
    methods: {
        
        
        
    },
    mounted() {

    }
}
</script>