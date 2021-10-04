<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Contacts | <small> {{ automation.name }}  <i class="bi bi-chevron-double-right"></i>  <span class="text-secondary"> {{ campaign.name }}  </span> </small> </h4>
            </div>
            <div class="col-md-6 col-12 text-right">
                <router-link class="btn btn-dark btn-sm" :to="'/automations/campaigns/'+aid">
                    <i class="bi bi-arrow-left"></i>
                    <span>Campaigns List</span>
                </router-link>
                <router-link class="btn btn-dark btn-sm" :to="'/automations/campaign-links/'+cid+'/'+aid">
                    <i class="bi bi-arrow-left"></i>
                    <span>Links List</span>
                </router-link>
            </div>
        </div>
        <div class="filterbox">
            <div class="row m-0">
                <div class="col-md-4 pl-0">
                    Total Clickable:  <b> {{ campaign.send_amt | freeNumber}} </b>
                </div>
                <div class="col-md-4 p-0 text-center">
                    Total Link Clicks: <b> {{ campaign.linkclicks | freeNumber}} </b> ({{ (campaign.linkclicks/campaign.send_amt)*100 | formatNumber}}%)
                </div>
                <div class="col-md-4  pr-0 text-right">
                    Unique Link Clicks: <b> {{ campaign.uniquelinkclicks | freeNumber}} </b> ({{ (campaign.uniquelinkclicks/campaign.send_amt)*100 | formatNumber}}%)
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Date</th>
                        <th width="100px">Click</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(contact, ano) in contacts" :key="'aut'+ano">
                        <td>{{ ano + 1 }}</td>
                        <td> {{ contact.customer_acct_name }} </td>
                        <td> {{ contact.email }} </td>
                        <td> {{ contact.phone }} </td>
                        <td> {{ contact.tstamp | setFulldate }} </td>
                        <td> {{ contact.times }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
            campaign:'',
            links:[],
            contacts:[],
            aid:'',
            cid:'',
            automation:''
        }
    },
    computed:{
        filterdlinks: function() {
            return [];
           // return this.links.filter(link => (link.tracked == 1 && link.link != 'open'));
        }
    },
    methods:{
        getCampaign() {
            this.$Progress.start();
            axios.get('/api/get-automation-campaign-link-contacts/'+this.$route.params.lid+'/'+this.$route.params.cid).then((response) => {
                this.links = response.data.results
                this.contacts = response.data.results.info;
                this.$Progress.finish();
            });
        },
        getAutomation() {
            axios.get('/api/get-campaign-detail/'+this.$route.params.cid).then((response) => {
                this.campaign = response.data.result.campaign;
            });
            axios.get('/api/get-automation-detail/'+this.$route.params.aid).then((response) => {
                this.automation = response.data.result.automation;
            });
        }
    },
    beforeMount() {
        this.aid = this.$route.params.aid;
        this.cid = this.$route.params.cid;
    },
    mounted() {
        this.getCampaign();  
        this.getAutomation();  
    }
}
</script>