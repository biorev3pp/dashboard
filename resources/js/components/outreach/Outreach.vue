<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Prospects</h4>
            </div>
        </div>
        <div class="filterbox">
            <div class="row m-0">
                <div class="col-md-3 pl-0">
                    <!-- Total : <b>{{ page.total }}</b> -->
                </div>
                <div class="col-md-3 p-0 text-center">
                    Showing From <b>{{ page.start }}</b> To <b>{{ page.end }}</b> records
                </div>
                <div class="col-md-6 text-right">
                    <ul class="paginate" v-if="parseInt(page.page) > 5">
                        <li v-for="p in paginationArray" :key="'page'+p">
                            <span v-if="p == page.page"> {{p}} </span>
                            <a href="javascript:;" @click="getOutreach(p)" v-else> {{p}} </a>
                        </li>
                    </ul>
                    <ul class="paginate" v-else>
                        <li v-for="p in parseInt(page.pager)" :key="'page'+p" v-if="p <= 10">
                            <span v-if="p == page.page"> {{p}} </span>
                            <a href="javascript:;" @click="getOutreach(p)" v-else> {{p}} </a>
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
                        <th>
                            <input id="check-all" class="" type="checkbox" value="" aria-label="..." @click="addAndRemoveAllRecordToContainer"> #ID
                        </th>
                        <th>Name</th>
                        <th>Company Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Tags</th>
                        <th>Engaged At</th>
                        <th width="100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="prospect in prospects.data.results" :key="'aut'+prospect.id">
                        <td>
                            <div class="form-check">
                                <input :id="'record-'+prospect.id" class="form-check-input me-1" type="checkbox" :value="prospect.id" @click="addAndRemoveRecordToContainer(prospect.id)">
                                {{ prospect.id }}
                            </div>
                        </td>
                        <td>{{ prospect.attributes.name }}</td>
                        <td>{{ prospect.attributes.company }}</td>
                        <td>
                            <ul class="nostylelist">
                                <li v-for="email in prospect.attributes.emails" :key="email">{{ email }}</li>
                            </ul>
                        </td>
                        <td>
                            <ul class="nostylelist">
                                <li v-for="mobile in prospect.attributes.mobilePhones" :key="mobile">{{ mobile }}(m)</li>
                            </ul>
                            <ul class="nostylelist">
                                <li v-for="mobile in prospect.attributes.workPhones" :key="mobile">{{ mobile }}(w)</li>
                            </ul>
                        </td>
                        <td>
                            <ul class="nostylelist">
                                <li v-for="tag in prospect.attributes.tags" :key="tag">{{ tag }}</li>
                            </ul>
                        </td>
                        <td>{{ prospect.attributes.engagedAt | setFulldate }}</td>
                        <td> <router-link class="btn btn-sm btn-primary" :to="'/prospect-details/'+prospect.id"> View</router-link> </td>
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
            prospects:{},
            page:{page:1,start:1,end:1,count:1,total:1,pager:1},
            paginationArray: {},
            recordContainer:[],
            json_fields: {
            "Complete name": "name",
            City: "city",
            Telephone: "phone.mobile",
            "Telephone 2": {
                    field: "phone.landline",
                    callback: (value) => {
                    return `Landline Phone - ${value}`;
                    },
                },
            },
            json_data: [
            {
                name: "Tony Peña",
                city: "New York",
                country: "United States",
                birthdate: "1978-03-15",
                phone: {
                mobile: "1-541-754-3010",
                landline: "(541) 754-3010",
                },
            },
            {
                name: "Thessaloniki",
                city: "Athens",
                country: "Greece",
                birthdate: "1987-11-23",
                phone: {
                mobile: "+1 855 275 5071",
                landline: "(2741) 2621-244",
                },
            },
            ],
            json_meta: [
                [
                    {
                    key: "charset",
                    value: "utf-8",
                    },
                ],
            ],
        }
    },
    computed:{

    },
    methods:{
        getOutreach(pno) {
            $("#check-all").prop("checked", false);
            this.$Progress.start();
            axios.get('/api/get-outreach-prospects/'+pno).then((response) => {
                this.prospects = response;
                this.page = response.data.page;
                this.paginationArray = response.data.paginationArray;
                this.$Progress.finish();
                this.checkExportRecords();
            });
        },
        addAndRemoveAllRecordToContainer(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            for(var i = 0; i < aa.length; i++){
                if(document.getElementById("record-"+aa[i].value)){
                    this.addAndRemoveRecordToContainer(aa[i].value);
                }
            } console.log(this.recordContainer);
        },
        checkExportRecords(){
            for(var i = 0; i < this.recordContainer.length; i++){
                if(document.getElementById("record-"+this.recordContainer[i])){
                    document.getElementById("record-"+this.recordContainer[i]).checked = true;
                }
            }
        },
        addAndRemoveRecordToContainer(id){
            if((this.recordContainer.indexOf(id) == -1) && (document.getElementById("record-"+id).checked == false)){
                this.recordContainer.push(id);
                document.getElementById("record-"+id).checked = true;
            }else{
                this.recordContainer.splice(this.recordContainer.indexOf(id), 1);
                document.getElementById("record-"+id).checked = false;
            }
        },
    },
    mounted() {
        this.getOutreach(1);
        this.recordContainer = [];
    }
}
</script>
<style>
.nostylelist{
    list-style: none;
}
</style>
