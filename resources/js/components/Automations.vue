<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Automations</h4>
            </div>
        </div>
        <div class="filterbox">
            <div class="row m-0">
                <div class="col-md-3 pl-0">
                    Total : <b>{{ page.total }}</b>
                </div>
                <div class="col-md-3 p-0 text-center">
                    Showing From <b>{{ page.start }}</b> To <b>{{ page.end }}</b> records
                </div>
                <div class="col-md-6 text-right">
                    <ul class="paginate">
                        <li v-for="p in parseInt(page.pager)" :key="'page'+p">
                            <span v-if="p == page.page"> {{p}} </span>
                            <a href="javascript:;" @click="getAutomation(p)" v-else> {{p}} </a>
                        </li>
                    </ul>
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
                        <th>Automations</th>
                        <th width="150px">Total Contacts</th>
                        <th width="150px">Active Contacts</th>
                        <th width="150px">Status</th>
                        <th width="200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(automation, ano) in automations.automations" :key="'aut'+automation.id">
                        <td>{{ (page.page - 1)*page.count + ano + 1 }}</td>
                        <td> {{ automation.name }} </td>
                        <td> {{ parseInt(automation.entered) }}</td>
                        <td> <router-link :to="'/automations/contacts/'+automation.id" class="btn btn-primary btn-sm"> {{ ((parseInt(automation.entered) - parseInt(automation.exited)) >= 1)?parseInt(automation.entered) - parseInt(automation.exited):0 }} </router-link></td>
                        <td>
                            <label class="table-badge badge-success" v-if="automation.status == 1">Active</label>
                            <label class="table-badge badge-danger" v-else>Inactive</label>
                        </td>
                        <td> <router-link class="btn btn-sm btn-primary" :to="'/automations/campaigns/'+automation.id"> Campaigns</router-link> </td>
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
            automations:{},
            page:{page:1,start:1,end:1,count:1,total:1,pager:1},
        }
    },
    computed:{

    },
    methods:{
        getAutomation(pno) {
            this.$Progress.start();
            axios.get('/api/get-automations/'+pno).then((response) => {
                this.automations = response.data.results;
                this.page = response.data.page;
                this.$Progress.finish();
            });
        }
    },
    mounted() {
        this.getAutomation(1);  
    }
}
</script>