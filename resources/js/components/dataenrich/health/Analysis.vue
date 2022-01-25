<template>
    <div>
        <div class="brv-card">
            <div class="brv-card-header">
                <h4 class="brv-card-mtitle">Prospects Data Completeness Score</h4>
            </div>
            <div class="brv-card-body">
                <div class="row ">
                    <div class="col-lg-9 col-md-8 col-sm-12">
                        <p class="brv-card-subtitle">{{ completenessTouchPoint.total_count | freeNumber }} prospects analyzed based on their values.</p>
                        <div class="row">
                            <div class="col-sm-6 col-12">
                                <div class="brv-subcard">
                                    <div class="brv-subcard-header">
                                        <h5>Based on Touch Points <i class="bi bi-info-circle-fill text-secondary ml-1" v-title="'This calculation is based on Business Emails, Mobile Phones, Work Phones, Home Phones and Company Name'"></i> </h5>
                                    </div>
                                    <div class="brv-subcard-body" v-if="completenessTouchPoint">
                                        <div class="d-table brv-subcard-main-content">
                                            <div class="d-table-cell align-middle text-center w-100">
                                                <h4 class="text-success" v-title="(completenessTouchPoint.total_count - completenessTouchPoint.and_count)+' prospects out of '+completenessTouchPoint.total_count">{{ ((completenessTouchPoint.total_count - completenessTouchPoint.and_count)/completenessTouchPoint.total_count)*100 | freeNumber }}% prospects have touch points</h4>
                                                <h5 v-title="completenessTouchPoint.or_count+' prospects out of '+completenessTouchPoint.total_count">{{ (completenessTouchPoint.or_count/completenessTouchPoint.total_count)*100 | freeNumber }}% prospects missing some touch points</h5>
                                            </div>
                                        </div>
                                        <div class="brv-subcard-sidebar vertical-process-holder d-table-cell">
                                            <div class="bg-success vertical-process-bar" :style="'height:'+(((completenessTouchPoint.total_count - completenessTouchPoint.and_count)/completenessTouchPoint.total_count)*100)+'%'">

                                            </div>
                                            <div class="bg-secondary vertical-process-bar" :style="'height:'+(((completenessTouchPoint.total_count - completenessTouchPoint.or_count)/completenessTouchPoint.total_count)*100 )+'%'">

                                            </div>
                                        </div>
                                    </div>
                                    <div class="brv-subcard-body" v-else>
                                        <div class="d-table w-100">
                                            <div class="d-table-cell align-middle text-center">
                                                <div class="spinner-grow text-secondary" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="brv-subcard">
                                    <div class="brv-subcard-header">
                                        <h5>Based on Other Criteria <i class="bi bi-info-circle-fill text-secondary ml-1" v-title="'This calculation is based on First Name, Last Name, Stage, Industry, Purchase Authorization, Tag, Title, Timezone, Country, State, City and zipcode'"></i> </h5>
                                    </div>
                                    <div class="brv-subcard-body" v-if="completenessCriteria">
                                        <div class="d-table brv-subcard-main-content">
                                            <div class="d-table-cell align-middle text-center w-100">
                                                <h4 class="text-danger" v-title="(completenessCriteria.and_count)+' prospects out of '+completenessCriteria.total_count">{{ ((completenessCriteria.and_count)/completenessCriteria.total_count)*100 | freeNumber }}% prospects don't have required criterias</h4>
                                                <h5 v-title="completenessCriteria.or_count+' prospects out of '+completenessCriteria.total_count">{{ (completenessCriteria.or_count/completenessCriteria.total_count)*100 | freeNumber }}% prospects missing some criterias</h5>
                                            </div>
                                        </div>
                                        <div class="brv-subcard-sidebar vertical-process-holder d-table-cell">
                                            <div class="bg-success vertical-process-bar" :style="'height:'+(((completenessCriteria.total_count - completenessCriteria.and_count)/completenessCriteria.total_count)*100)+'%'">

                                            </div>
                                            <div class="bg-secondary vertical-process-bar" :style="'height:'+(((completenessCriteria.total_count - completenessCriteria.or_count)/completenessCriteria.total_count)*100)+'%'">

                                            </div>
                                        </div>
                                    </div>
                                    <div class="brv-subcard-body" v-else>
                                        <div class="d-table w-100">
                                            <div class="d-table-cell align-middle text-center">
                                                <div class="spinner-grow text-secondary" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <h4 class="brv-card-title">Key Hightlights About The Data</h4>
                        <div class="brv-sublist" v-if="missingData.length >= 8">
                            <p><b :class="[(missingData[0]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[0]['count']">{{ missingData[0]['percent'] }}%</b> prospects missing First Name</p>
                            <p><b :class="[(missingData[1]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[1]['count']">{{ missingData[1]['percent'] }}%</b> prospects missing Last Name</p>
                            <p><b :class="[(missingData[2]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[2]['count']">{{ missingData[2]['percent'] }}%</b> prospects missing Email</p>
                            <p><b :class="[(missingData[3]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[3]['count']">{{ missingData[3]['percent'] }}%</b> prospects missing Mobile Phone</p>
                            <p><b :class="[(missingData[4]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[4]['count']">{{ missingData[4]['percent'] }}%</b> prospects missing Company Name</p>
                            <p><b :class="[(missingData[5]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[5]['count']">{{ missingData[5]['percent'] }}%</b> prospects missing Country</p>
                            <p><b :class="[(missingData[6]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[6]['count']">{{ missingData[6]['percent'] }}%</b> prospects missing Industry</p>
                            <p><b :class="[(missingData[7]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[7]['count']">{{ missingData[7]['percent'] }}%</b> prospects missing Timezone</p>
                            <p><b :class="[(missingData[8]['percent'] >= 50)?'brv-txt-danger':'']" v-title="missingData[8]['count']">{{ missingData[8]['percent'] }}%</b> prospects missing Stage</p>
                        </div>
                        <div class="brv-subcard border-none" v-else>
                            <div class="brv-subcard-body">
                                <div class="d-table w-100">
                                    <div class="d-table-cell align-middle text-center">
                                        <div class="spinner-grow text-secondary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
        <div class="brv-card">
            <div class="brv-card-header">
                <h4 class="brv-card-mtitle">Company Based Analysis of Prospects Data</h4>
                <p class="brv-card-subtitle">{{ completenessTouchPoint.total_count | freeNumber }} prospects analyzed based on their values.</p>
            </div>
            <div class="brv-card-body">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                        <div class="brv-subcard">
                            <div class="brv-subcard-header">
                                <h5>Company Management Level</h5>
                            </div>
                            <div class="brv-subcard-body" v-if="ManagementLabel.length">
                                <apexchart type="donut" width="96%" :options="chatOptionManagementLabel" :series="sManagementLabelStatus"></apexchart>
                            </div>
                            <div class="brv-subcard-body" v-else>
                                <div class="d-table w-100">
                                    <div class="d-table-cell align-middle text-center">
                                        <div class="spinner-grow text-secondary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                        <div class="brv-subcard">
                            <div class="brv-subcard-header">
                                <h5>Top Job Titles</h5>
                            </div>
                            <div class="brv-subcard-body" v-if="jobTitlesData.length">
                                <div class="brv-fstatus text-secondary" v-for="(jfdata, jfkey) in jobTitlesData" :key="'jf-'+jfkey">
                                    <h6> {{ jfdata.value }} <span class="float-right">{{ jfdata.percent }}</span> </h6>
                                    <div class="progress" v-title="jfdata.count">
                                        <div class="progress-bar" :style="'width:'+jfdata.percent"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="brv-subcard-body" v-else>
                                <div class="d-table w-100">
                                    <div class="d-table-cell align-middle text-center">
                                        <div class="spinner-grow text-secondary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                        <div class="brv-subcard">
                            <div class="brv-subcard-header">
                                <h5>Top Company Functions</h5>
                            </div>
                            <div class="brv-subcard-body" v-if="jobFunctionData.length">
                                <div class="brv-fstatus text-secondary" v-for="(jfdata, jfkey) in jobFunctionData" :key="'jf-'+jfkey">
                                    <h6> {{ jfdata.value }} <span class="float-right">{{ jfdata.percent }}</span> </h6>
                                    <div class="progress" v-title="jfdata.count">
                                        <div class="progress-bar" :style="'width:'+jfdata.percent"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="brv-subcard-body" v-else>
                                <div class="d-table w-100">
                                    <div class="d-table-cell align-middle text-center">
                                        <div class="spinner-grow text-secondary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                        <div class="brv-subcard">
                            <div class="brv-subcard-header">
                                <h5>Company Revenue</h5>
                            </div>
                            <div class="brv-subcard-body" v-if="CompanyRevenueLabel.length">
                                <apexchart type="donut" width="96%" :options="chatOptionCompanyRevenue" :series="sCompanyRevenueStatus"></apexchart>
                            </div>
                            <div class="brv-subcard-body" v-else>
                                <div class="d-table w-100">
                                    <div class="d-table-cell align-middle text-center">
                                        <div class="spinner-grow text-secondary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                        <div class="brv-subcard">
                            <div class="brv-subcard-header">
                                <h5>Top Company Industry</h5>
                            </div>
                            <div class="brv-subcard-body" v-if="companyIndustryData.length">
                                <div class="brv-fstatus text-secondary" v-for="(jfdata, jfkey) in companyIndustryData" :key="'jf-'+jfkey">
                                    <h6> {{ jfdata.value }} <span class="float-right">{{ jfdata.percent }}</span> </h6>
                                    <div class="progress" v-title="jfdata.count">
                                        <div class="progress-bar" :style="'width:'+jfdata.percent"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="brv-subcard-body" v-else>
                                <div class="d-table w-100">
                                    <div class="d-table-cell align-middle text-center">
                                        <div class="spinner-grow text-secondary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                        <div class="brv-subcard">
                            <div class="brv-subcard-header">
                                <h5>Company Location</h5>
                            </div>
                            <div class="brv-subcard-body"></div>
                        </div>
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
            jobFunctionData:[],
            jobTitlesData:[],
            companyIndustryData:[],
            ManagementLabel:[],
            sManagementLabelStatus:[],
            CompanyRevenueLabel:[],
            sCompanyRevenueStatus:[],
            missingData:[],
            completenessTouchPoint:'',
            completenessCriteria:''
        }
    },
    computed: {
        chatOptionManagementLabel() {
            return {
                stroke: {
                    show: false,
                    curve: 'stepline',
                    lineCap: 'butt',
                    width: 2,
                    dashArray: 0,      
                },
                colors:['#F44336', '#FEB019', '#9C27B0', '#01E396', '#E91E63', '#018FFB', '#6cb2eb'],
                labels: this.ManagementLabel,
            }
        },
        chatOptionCompanyRevenue() {
            return {
                stroke: {
                    show: false,
                    curve: 'stepline',
                    lineCap: 'butt',
                    width: 2,
                    dashArray: 0,      
                },
                colors:['#F44336', '#FEB019', '#9C27B0', '#01E396', '#E91E63', '#018FFB', '#6cb2eb'],
                labels: this.CompanyRevenueLabel,
            }
        }
    },
    methods: {
        getTouchPointsData(){
            axios.get('/api/touch-points-data')
            .then((response) => {
                this.completenessTouchPoint = response.data
            })
        },
        getTouchPointsDataOther(){
            axios.get('/api/touch-points-data-others')
            .then((response) => {
                this.completenessCriteria = response.data
            })
        },
        getMissingData() {
            axios.get('/api/prospects-field-missing-data')
            .then((response) => {
                this.missingData = response.data
            })
        },
        companyRevenueRange(){
            this.sCompanyRevenueStatus = []
            axios.get('/api/company-revenue-range')
            .then((response) => {
                this.CompanyRevenueLabel = response.data.label
                this.sCompanyRevenueStatus = response.data.series
            })
        },
        ManagementLabelRange(){
            this.sManagementLabelStatus = []
            axios.get('/api/management-level')
            .then((response) => {
                this.ManagementLabel = response.data.label
                this.sManagementLabelStatus = response.data.series
            })
        },
        jobFunction() {
            this.sManagementLabelStatus = []
            axios.get('/api/job-functions').then((response) => { 
                this.jobFunctionData = response.data
            }).catch((error) => {
                console.log(error.message)
            })
        },
        jobTitles() {
            this.sManagementLabelStatus = []
            axios.get('/api/job-titles').then((response) => { 
                this.jobTitlesData = response.data
            }).catch((error) => {
                console.log(error.message)
            })
        },
        companyIndustry() {
            this.sManagementLabelStatus = []
            axios.get('/api/company-industry').then((response) => { 
                this.companyIndustryData = response.data
            }).catch((error) => {
                console.log(error.message)
            })
        }
    },
    created() {
        this.getTouchPointsData();
        this.getTouchPointsDataOther();
        this.getMissingData();
        this.ManagementLabelRange();
        this.jobTitles();
        this.jobFunction();
        this.companyRevenueRange();
        this.companyIndustry();
    }
    
}
</script>
