<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Campaigns | <small> <b> {{ automation.name }}  </b> </small> </h4>
            </div>
            <div class="col-md-6 col-12 text-right">
                <router-link class="btn btn-dark btn-sm" to="/automations">
                    <i class="bi bi-arrow-left"></i>
                    <span>Automations List</span>
                </router-link>
            </div>
        </div>
        <div class="filterbox">
            <div class="row m-0">
                <div class="col-md-3 pl-0">

                </div>
                <div class="col-md-3 p-0 text-center">

                </div>
                <div class="col-md-6 text-right">
                   
                </div>
            </div>
            <!-- <div class="row m-0">
                <div class="col-md-3 pl-0">
                    <select name="date" id="date" class="form-control">
                        <option value="">Select Date Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="date" id="date" class="form-control">
                        <option value="">Select Date Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="date" id="date" class="form-control">
                        <option value="">Select Date Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-dark" type="button">Filter</button>
                    <button class="btn btn-danger" type="button">Reset</button>
                </div>
            </div> -->
        </div>
        <div class="table-responsive fit-content">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="70px">SNo</th>
                        <th>Campaign</th>
                        <th width="150px">Last Sent</th>
                        <th width="80px">Sent</th>
                        <th width="80px">Opens</th>
                        <th width="80px">Clicks</th>
                        <th width="100px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(campaign, ano) in filteredCampaigns" :key="'aut'+campaign.id">
                        <td>{{ ano + 1 }}</td>
                        <td> {{ campaign.name }} </td>
                        <td> {{ campaign.ldate | setusdate }} </td>
                        <td> {{ campaign.send_amt | freeNumber }} </td>
                        <td>
                            <odownloader v-bind:cid="campaign.id" v-bind:sid="campaign.seriesid" v-bind:uopens="campaign.uniqueopens" />                            
                        </td>
                        <td>
                            <downloader v-bind:cid="campaign.id" v-bind:sid="campaign.seriesid" v-bind:sclicks="campaign.subscriberclicks" />
                        </td>
                        <td>
                            <label class="table-badge badge-success" v-if="campaign.status == 1">Active</label>
                            <label class="table-badge badge-danger" v-else>Inactive</label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
import downloader from './Downloader.vue';
import odownloader from './ODownloader.vue';

export default {
    components:{downloader, odownloader},
    data(){
        return {
            campaigns:[],
            aid:'',
            automation:'',
        }
    },
    computed:{
        filteredCampaigns: function() {
            return this.campaigns.filter(campaign => (campaign.status == 1));
        }
    },
    methods:{
        getCampaign() {
            this.$Progress.start();
            axios.get('/api/get-automation-campaigns/'+this.$route.params.id).then((response) => {
                this.campaigns = response.data.results.campaigns;
            });
        },
        getAutomation() {
            axios.get('/api/get-automation-detail/'+this.$route.params.id).then((response) => {
                this.automation = response.data.result.automation;
            });
        },
    },
    beforeMount() {
            this.getCampaign();  
        },
    mounted() {
        
        this.getAutomation();  
       
    }
}
</script>