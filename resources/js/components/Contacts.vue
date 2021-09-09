<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Contacts</h4>
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
                        <li>
                            <span v-if="page.startpage == parseInt(page.page)"> {{ page.startpage }} </span>
                            <a href="javascript:;" @click="getContact(page.startpage)" v-else> {{ page.startpage }} </a>
                        </li>
                        <li v-for="p in page.pager_left" :key="'page'+p">
                            <span v-if="p == page.page"> {{p}} </span>
                            <a href="javascript:;" @click="getContact(p)" v-else> {{p}} </a>
                        </li>
                        <li>..</li>
                        <li v-for="p in page.pager_right" :key="'page'+p">
                            <span v-if="p == page.page"> {{p}} </span>
                            <a href="javascript:;" @click="getContact(p)" v-else> {{p}} </a>
                        </li>
                        <li>
                            <span v-if="page.endpage == parseInt(page.page)"> {{ page.endpage }} </span>
                            <a href="javascript:;" @click="getContact(page.endpage)" v-else> {{ page.endpage }} </a>
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Tags</th>
                        <th width="120px">Created</th>
                        <th width="100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(contact, ano) in contacts" :key="'aut'+contact.id">
                        <td>{{ (page.page - 1)*page.count + ano + 1 }}</td>
                        <td> {{ contact.first_name }} {{ contact.last_name }} </td>
                        <td> {{ contact.email }} </td>
                        <td> {{ (contact.fields[2].val)?contact.fields[2].val:'--' }} </td>
                        <td>
                            <span class="badge badge-secondary m-1" v-for="(tag, t) in contact.tags" :key="'tag'+t"> {{ tag}} </span>
                        </td>
                        <td> {{ contact.cdate | setusdate }} </td>
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
            contacts:{},
            page:{page:1,start:1,end:1,count:1,total:1,pager:1},
        }
    },
    computed:{

    },
    methods:{
        getContact(pno) {
            this.$Progress.start();
            axios.get('/api/get-contacts/'+pno).then((response) => {
                this.contacts = response.data.results;
                this.page = response.data.page;
                this.$Progress.finish();
            });
        }
    },
    mounted() {
        this.getContact(1);  
    }
}
</script>