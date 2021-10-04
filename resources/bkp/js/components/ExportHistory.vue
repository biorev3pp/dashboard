<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Export History</h4>
            </div>
        </div>
        <div class="table-responsive fit-content">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="wf-50">SNo</th>
                        <th class="wf-250">Name</th>
                        <th class="wf-150">Uploaded By</th>
                        <th class="wf-150">Status</th>
                        <th class="wf-220">Progress</th>
                        <th class="wf-220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(history, ano) in histories.data" :key="'history'+history.id">
                        <td>{{ histories.to - histories.per_page + ano + 1 }}</td>
                        <td>
                            <span v-if="history.file_name">{{ history.file_name }}</span>
                            <span v-else></span>    
                        </td>
                        <td>{{ history.user.name }}</td>
                        <td>Finished On<br>{{history.created_at | setFulldate }}</td>
                        <td>
                            <span v-if="history.total >= 1" class="complete-progress">
                                <router-link class="plink" :to="'/export-history/'+history.identifier">
                                    <span v-if=" (history.inserted + history.updated) == history.total">
                                        100% complete
                                    </span>
                                    <span v-else>
                                        {{ (history.inserted + history.updated)*100/history.total | formatNumber }}% complete
                                    </span>
                                    <span class="fail-progress">
                                        {{ history.total - (history.inserted + history.updated)}} of {{ history.total }} failed
                                    </span>
                                </router-link>
                            </span>
                            <span v-else> -- </span>
                        </td>
                        <td> 
                            <span class="row m-0" v-if="history.total >= 1">
                                <span class="cnt-jh"><b>{{ history.total }}</b>Total</span>
                                <span class="cnt-jh"><b>{{ history.inserted }}</b>Inserted</span>
                                <span class="cnt-jh"><b>{{ history.updated }}</b>Updated</span>
                                <span class="cnt-jh"><b>{{ history.total - (history.inserted + history.updated) }}</b>Failed</span>
                            </span>
                            <span v-else>
                                <img :src="loader_url" v-if="loader">
                                <button type="button" v-else @click="generateReport(history.identifier)" class="btn btn-sm btn-primary">Generate Report</button>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <pagination :limit="5" :data="histories" @pagination-change-page="getExortRecords"></pagination>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            histories : [],
        }
    },
    filters: {
        date: function (mydate) {
            moment.locale('en');
            return moment(mydate).format('M d, Y');
        },
    },
    computed:{

    },
    methods:{
        getExortRecords(pno) {
            this.$Progress.start();
            axios.get('/api/get-history-export?page='+ pno).then((response) => {
                this.histories = response.data.records
                this.$Progress.finish();
            });
        },
        generateReport(id){
            this.loader = true;
            axios.get('/api/view-export-reports/'+ id).then((response) => {    
                if(response.data.status == 'success'){
                    this.getExortRecords(1); 
                    this.loader = false;
                } else {
                    this.loader = false;
                    Vue.$toast.error(response.data.message);
                }
            }).catch(function (error) {
                Vue.$toast.error(error.response.data.message);
            }).then((res) => {
                this.loader = false;
            })
        }
    },
    mounted() {
        this.getExortRecords(1);  
    }
}
</script>