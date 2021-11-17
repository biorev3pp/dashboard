<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Contacts  | <small> <b> {{ automation.name }}  </b> </small> </h4>
            </div>
            <div class="col-md-6 col-12 text-right">
                <router-link class="btn btn-dark btn-sm" to="/automations">
                    <i class="bi bi-arrow-left"></i>
                    <span>Automations List</span>
                </router-link>
            </div>
        </div>
        <div class="filterbox">
            <!-- <div class="row m-0">
                <div class="col-md-3 pl-0">
                    Total : <b>{{ contacts.length }}</b>
                </div>
                <div class="col-md-3 p-0 text-center">

                </div>
                <div class="col-md-6 text-right">
                    <ul class="paginate">
                      
                      
                    </ul>
                </div>
            </div> -->
            <div class="row m-0">
                <div class="col-md-3 pl-0">
                    <select name="status" id="status" class="form-control form-control-sm" v-model="status">
                        <option value="">Select Status</option>
                        <option value="scheduled">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                   
                </div>
                <div class="col-md-3">
                    <button class="btn btn-dark btn-sm" type="button">Filter</button>
                    <button class="btn btn-danger btn-sm" type="button">Reset</button>
                </div>
                 <div class="col-md-3">
                     Total : <b>{{ filteredContacts.length }}</b>
                </div>
            </div> 
        </div>
        <div class="table-responsive fit-content">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="70px">SNo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th width="150px">Status</th>
                        <th width="150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(contact, ano) in filteredContacts" :key="'aut'+contact.id">
                        <td>{{ ano + 1 }}</td>
                        <td> {{ contact.name }} </td>
                        <td> {{ contact.name }} </td>
                        <td></td>
                        <td>
                            <label class="table-badge badge-dark" v-if="contact.completed == 1">completed</label>
                            <label class="table-badge badge-success" v-else-if="contact.status == 1">Active</label>
                            <label class="table-badge badge-danger" v-else>Inactive</label>
                        </td>
                        <td> <router-link class="btn btn-sm btn-primary" :to="'/contacts/'+contact.id"> View</router-link> </td>
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
            contacts:[],
            automation:'',
            status:''
        }
    },
    computed:{
        filteredContacts: function() {
            if(this.status == '') 
            {
                return this.contacts;
            }
            else {
                return this.contacts.filter(contact => (contact.batchid == this.status));
            }
        }
    },
    methods:{
        getContact() {
            this.$Progress.start();
            axios.get('/api/get-automation-contacts/'+this.$route.params.id).then((response) => {
                //console.log(response.data);
                this.contacts = response.data.results.contactAutomations;
                this.$Progress.finish();
            });
        },
        getAutomationDetail() {
            axios.get('/api/get-automation-detail/'+this.$route.params.id).then((response) => {
                this.automation = response.data.result.automation;
            });
        }
    },
    mounted() {
        this.getContact();  
        this.getAutomationDetail();
    }
}
</script>