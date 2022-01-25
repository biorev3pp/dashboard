<template>
    <div>
        <div class="top-form py-2 px-3">
            <div class="row m-0">
                <div class="col-md-8 col-6 pl-0">
										<h5 class="mt-1 mb-0">
											<span class="mr-2"> Column Health Analysis</span>
											<toggle-button @change="is_graph = !is_graph" :margin="3" :width="140" :height="24" :labels="{checked: 'Graphical View', unchecked: 'List View'}" :switch-color="{checked: '#800080', unchecked: '#27408B'}" :color="{checked: '#E599E5', unchecked: '#4E9FFE'}" />
										</h5>
								</div>
								<div class="col-md-4 col-6 pr-0 text-right">
										<button class="btn btn-sm btn-primary theme-btn icon-btn-left" type="button">
											<i class="bi bi-fan"></i> Set Column Parameters</button>
								</div>
						</div>
				</div>
				<div class="ca-div">
						<div class="row m-0" v-if="is_graph">
								<div class="col-md-3 col-sm-6 col-12 ca-chart text-center pt-2 pb-1" v-if="series.first_name">
										<h5>Column - First Name</h5>
										<apexchart type="pie" width="99%" :options="chartOptions" :series="series.first_name"></apexchart>
								</div>
								<div class="col-md-3 col-sm-6 col-12 ca-chart text-center pt-2 pb-1" v-if="series.last_name">
										<h5>Column - Last Name</h5>
										<apexchart type="pie" width="99%" :options="chartOptions" :series="series.last_name"></apexchart>
								</div>
								<div class="col-md-3 col-sm-6 col-12 ca-chart text-center pt-2 pb-1" v-if="series.emails">
										<h5>Column - Business Email</h5>
										<apexchart type="pie" width="99%" :options="chartOptions" :series="series.emails"></apexchart>
								</div>
								<div class="col-md-3 col-sm-6 col-12 ca-chart text-center pt-2 pb-1" v-if="series.mobilePhones">
										<h5>Column - Mobile Phones</h5>
										<apexchart type="pie" width="99%" :options="chartOptions" :series="series.mobilePhones"></apexchart>
								</div>
						</div>
						<div class="row m-0" v-else>
								<div class="divtable border-top w-100">
										<div class="divthead">
												<div class="divthead-row">
														<div class="divthead-elem wf-80">
																SNo
														</div>
														<div class="divthead-elem mwf-150">
																Column                            
														</div>
														<div class="divthead-elem wf-150">
																Total
														</div>
														<div class="divthead-elem wf-150">
																Correct                        
														</div>
														<div class="divthead-elem wf-150">
																Incorrect                        
														</div>
														<div class="divthead-elem wf-150">
																Empty                        
														</div>
														<div class="divthead-elem wf-250">
																Health Bar 
														</div>
												</div>
										</div>
										<div class="divtbody custom-height-220">
												<div class="divtbody-row" v-for="(record, value, index) in series" :key="'series-'+index">
														<div class="divtbody-elem wf-80">
																{{ index+1 }}
														</div>
														<div class="divtbody-elem mwf-150">
																{{ value.replace('_', ' ') | capitalize }}
														</div>
														<div class="divtbody-elem wf-150">
																{{ record.reduce((a, b) => a + b, 0) | freeNumber }}
														</div>
														<div class="divtbody-elem wf-150">
																{{ record[0] | freeNumber }}
														</div>
														<div class="divtbody-elem wf-150">
																{{ record[1] | freeNumber }}
														</div>
														<div class="divtbody-elem wf-150">
																{{ record[2] | freeNumber }}
														</div>
														<div class="divtbody-elem wf-250">
																<div class="progress">
																	<div class="progress-bar fw-500 bg-success" role="progressbar" :style="'width: '+(record[0])*100/(record[0]+record[1]+record[2])+'%'" :aria-valuenow="(record[0])*100/(record[0]+record[1]+record[2])" aria-valuemin="0" :aria-valuemax="record[0]+record[1]+record[2]">
																		{{ (record[0])*100/(record[0]+record[1]+record[2]) | formatNumber }}%
																	</div>
																	<div class="progress-bar fw-500 bg-info" role="progressbar" :style="'width: '+(record[1])*100/(record[0]+record[1]+record[2])+'%'" :aria-valuenow="(record[0])*100/(record[0]+record[1]+record[2])" aria-valuemin="0" :aria-valuemax="record[0]+record[1]+record[2]">
																		{{ (record[1])*100/(record[0]+record[1]+record[2]) | formatNumber }}%
																	</div>
																	<div class="progress-bar fw-500 bg-danger" role="progressbar" :style="'width: '+(record[2])*100/(record[0]+record[1]+record[2])+'%'" :aria-valuenow="(record[0])*100/(record[0]+record[1]+record[2])" aria-valuemin="0" :aria-valuemax="record[0]+record[1]+record[2]">
																		{{ (record[2])*100/(record[0]+record[1]+record[2]) | formatNumber }}%
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
import { ToggleButton } from 'vue-js-toggle-button'
export default {
		components:{ToggleButton},
    data() {
        return {
					is_graph:false,
          series: {},
          chartOptions: {
            labels: ['Correct', 'Incorrect', 'Empty'],
						colors : ['#01E396', '#018FFB' ,'#FF4560'],
						chart: {fontFamily: 'inherit'},
          },
        }
    },
    computed: {

    },
    filters: {

    },
    methods: {
			getColumnHealth() {
				axios.post('/api/get-column-health', {fields: ['first_name','last_name', 'emails', 'mobilePhones']})
							.then((response) => {
								this.series = response.data
							})
			}
    },
    created() {
      this.getColumnHealth();
    }
}
</script>
