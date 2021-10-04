<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-3 col-12 pl-0">
                    <input id="start-id" class="form-control" type="text" v-model="form.start_id" placeholder="start id"/>
                </div>    
                <div class="col-md-3 col-12 pl-0">
                    <input id="end-id" class="form-control" type="text" v-model="form.end_id" placeholder="end id"/>
                </div>  
                <div class="col-md-3 col-12 pl-0">
                    <input id="page_size" class="form-control" type="text" v-model="form.page_size" placeholder="page size"/>
                </div>  
                <div class="col-md-3 col-12 pl-0">
                    <button type="button" class="btn btn-sm btn-primary" @click="submit">Submit</button>   
                </div>           
            </div>
        </div>
        <div class="mapping-div">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                       <th>Sno</th>
                       <th>Record ID</th>
                       <th>First Name</th>
                        <th>Last Name</th>
                        <th>Emails</th>
                        <th>Mobile</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(record, index) in records" :key="index">
                        <td> {{ index + 1 }} </td>
                        <td> {{ record.id }} </td>
                        <td> {{ record.attributes.firstName }} </td>
                        <td> {{ record.attributes.lastName }} </td>
                        <td> {{ record.attributes.emails }} </td>
                        <td> {{ record.attributes.mobilePhones }} </td>
                    </tr>
                </tbody>
            </table>    
                   
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            form : new Form({
                start_id : '',
                end_id : '',
                page_size : '',
            }),
            records : [],
        }
    },
    computed: {
        
    },
    methods: {
        submit(){
            this.$Progress.start()
            this.records = []
            this.form.post('/api/outreach-records').then((response) => {
                this.records = response.data.results.data
                this.$Progress.finish()                
            })

        }
    },
    mounted() {
    }
}
</script>
<style>
 .submit{
     display : relative;
 }
</style>