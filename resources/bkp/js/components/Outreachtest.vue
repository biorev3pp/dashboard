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
                        <th><input id="check-all" class="" type="checkbox" value="" aria-label="..." @change="checkAll"><button class="form-control" type="button" @click="makeCheckBoxChecked">Btn</button></th>
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
                                <input :id="'record-'+prospect.id" class="form-check-input me-1" type="checkbox" :value="prospect.id" aria-label="..." @click="allRecordToConatiner(prospect.id)">
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
        }
    },
    computed:{

    },
    methods:{
        getOutreach(pno) {
            $("#check-all").prop("checked", false);
            this.$Progress.start();
            this.makeCheckBoxChecked();
            axios.get('/api/get-outreach-prospects/'+pno).then((response) => {
                this.prospects = response;
                this.page = response.data.page;
                this.paginationArray = response.data.paginationArray;
                this.$Progress.finish();
                this.makeCheckBoxChecked();
            });
        },
        checkAll(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            if(document.getElementById("check-all").checked == true){
                for (var i = 0; i < aa.length; i++){
                    aa[i].checked = true;
                    if((this.recordContainer.indexOf(parseInt(aa[i].value)) == -1) && (parseInt(aa[i].value) > 0)){
                        this.recordContainer.push(parseInt(aa[i].value));
                    }
                }

            }
            if(document.getElementById("check-all").checked == false){
                for (var i = 0; i < aa.length; i++){
                    aa[i].checked = false;
                    if((this.recordContainer.indexOf(parseInt(aa[i].value)) == -1) && (parseInt(aa[i].value) > 0)){
                        this.recordContainer.push(parseInt(aa[i].value));
                    }
                }
                // var records = [];
                // var aa = document.querySelectorAll("input[type=checkbox]");
                // for (var i = 0; i < aa.length; i++){
                //     aa[i].checked = false;
                //     if(parseInt(aa[i].value) > 0){
                //         records.push(parseInt(aa[i].value));
                //     }
                // }
                // document.getElementById("check-all").checked = false;
                // this.recordContainer = this.diffArray(records, this.recordContainer);
                this.makeCheckBoxChecked();
            }
            console.log(this.recordContainer);
        },
        diffArray(a1, a2) {
            var a = [], diff = [];
            for (var i = 0; i < a1.length; i++) {
                a[a1[i]] = true;
            }
            for (var i = 0; i < a2.length; i++) {
                if (a[a2[i]]) {
                    delete a[a2[i]];
                } else {
                    a[a2[i]] = true;
                }
            }
            for (var k in a) {
                diff.push(k);
            }
            return diff;
        },
        makeCheckBoxChecked(){
            for(var i = 0; i < this.recordContainer.length; i++){
                if(document.getElementById('record-'+this.recordContainer[i])){
                    document.getElementById('record-'+this.recordContainer[i]).checked = true;
                }
            }
        },
        allRecordToConatiner(id){
            var record = document.getElementById('record-'+id);
            if((this.recordContainer.indexOf(id) == -1) && (record.checked == true)){
                this.recordContainer.push(id);
            }else{
                for(var i = 0; i < this.recordContainer.length; i++){
                    if(this.recordContainer[i] == id){
                        this.recordContainer.splice(i,1)
                    }
                }
            }
            console.log(this.recordContainer);
        }
    },
    mounted() {
        this.getOutreach(1);
    }
}
</script>
<style>
.nostylelist{
    list-style: none;
}
</style>
