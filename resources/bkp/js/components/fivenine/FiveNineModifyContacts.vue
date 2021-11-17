<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
                    <div class="form-group">
                        <input class="form-control" type="text" placeholder="search company" v-model="search" v-on:keyup="filter"/>
                    </div>
                </div>
                <div class="col-md-4 col-12 pl-0">
                    <div class="form-group">
                        <v-select 
                        id="first"
                        label="name" 
                        class="form-control" 
                        :options="dispositions" 
                        v-model="disposition"                            
                        @input="filter"
                        placeholder="Search disposition"
                        >
                        </v-select>                        
                    </div>
                </div>
                <div class="col-md-4 col-12 pl-0" v-if="filteredRecords > 0">
                    <span class="filter"> {{ filteredRecords }} RECORDS FOUND</span>
                    <button type="button" class="btn btn-primary icon-btn-right theme-btn  mr-3" @click="resetFilter">
                        Reset
                    </button>
                </div>
            </div>
            <div class="row m-0">
                <div class="col-md-2 col-12 pl-0">
                    <div class="form-group">
                        <label for="first">Select List To Modify Contacts</label>
                        <v-select 
                        id="first"
                        label="name" 
                        class="form-control" 
                        :options="five9_list_new" 
                        v-model="formlid"                            
                        @input="getContacts"
                        placeholder="Search list"
                        >
                        </v-select>
                        
                    </div>
                </div>
                <div class="col-md-2 col-12 pl-0" v-if="recordContainer.length > 0">
                    <div class="form-group">
                        <select v-model="action" class="form-control action" @change="showswap">
                            <option value="">Action</option>
                            <option value="delete">Delete</option>
                            <option value="move">Move</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-12 pl-0" v-if="swap == 'move' && recordContainer.length > 0">
                    <div >
                        <div class="form-group">
                            <label for="first">Select List To Move-In</label>
                            <v-select 
                            label="name" 
                            class="form-control" 
                            :options="five9_list_new1" 
                            v-model="formlid1"
                            placeholder="Search list"
                            >
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 p-0">
                    <div class="row">
                        <div class="col-md-4"  v-if="recordContainer.length > 0">
                            <p class="text-uppercase inline action-1"> {{ recordContainer.length }} record(s) selected.</p>
                        </div>
                        <div class="col-md-4" v-if="swap == 'move' && recordContainer.length > 0">
                            <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3 action" @click="moveContacts">
                                Move Contacts <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-double-right text-ligth" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M3.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L9.293 8 3.646 2.354a.5.5 0 0 1 0-.708z"/>
                                <path fill-rule="evenodd" d="M7.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L13.293 8 7.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </button>                            
                        </div>
                        <div class="col-md-4">
                             <button type="button" class="btn btn-primary icon-btn-right theme-btn mr-3 action" @click="reset">
                                Reset
                            </button> 
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="mapping-div">
            <table class="table table-bordered table-striped" v-if="recordsCount > 0">
                <thead>
                    <tr>
                        <th width="50px;">
                            <input type="checkbox" name="" id="check-all" value="0" aria-label="..." @click="selectAllRecords">
                        </th>
                        <th>Sno</th>
                        <th v-for="(hed, index) in header" :key="'header'+index" v-if="hed != 'CONTACT_ID'">
                            <span v-if="hed == 'DIAL_ATTEMPTS'">List Dial Attempts</span>
                            <span v-else-if="hed == 'Dial_Attempts'">Contact Dial Attempts</span>
                            <span v-else>{{ hed | titleLabel }}</span>
                            <div v-if="hed == 'first_name' || hed == 'record_id' || hed == 'number1' || hed == 'DIAL_ATTEMPTS' || hed == 'Dial_Attempts' || hed == 'company' || hed == 'Last_Dispo'">
                            <i class="bi bi-caret-up pointer" @click="sorting=hed,sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting=hed,sortingType='desc'"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr  v-for="(record, index) in orderedContacts" :key="'record'+index" :class="'row-'+record.CONTACT_ID +' '+ 'records record-'+parseInt((index+parseInt(per_page))/parseInt(per_page))" v-show="parseInt((index+parseInt(per_page))/parseInt(per_page)) == 1">
                        <td>
                            <input :id="'record-'+record.CONTACT_ID" class="" type="checkbox" :value="record.CONTACT_ID" @click="selectAllRecord(record.CONTACT_ID)"/>                            
                        </td>
                        <td> {{ index + 1 }}</td>
                        <td> {{ record.record_id }} </td>
                        <td> {{ record.first_name }} </td>
                        <td> {{ record.last_name }} </td>
                        <td> {{ record.number1 }} </td>
                        <td> 
                            <span v-if="record.number2 == '[]'" ></span>
                            <span v-else>{{ record.number2 }}</span> 
                        </td>
                        <td>
                            <span v-if="record.number3 == '[]'" ></span>
                            <span v-else>{{ record.number3 }}</span> 
                        </td>
                        <td> {{ record.DIAL_ATTEMPTS }} </td>
                        <td> {{ record.Dial_Attempts }} </td>
                        <td> {{ record.company  }} </td>
                        <td> {{ record.Last_Dispo }} </td>
                    </tr>
                </tbody>
            </table>
            <div class="class" v-if="recordsCount > 0">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="per_page" @change="setPerPage()">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="999999999">All</option>
                            </select>
                        </span>
                        <ul class="pagination" id="paginator">
                            <li v-for="p in paginationArray" :key="'page'+p" class="page-item pagination-page-nav">
                                <a class="page-link page-link-active" v-if="p == pno"> {{p}} </a>
                                <a class="page-link" href="javascript:;" @click="pagination(p)" v-else> {{p}} </a>
                            </li>
                        </ul>
                    </div>
            </div>
        </div>
    </div>
</template>
<script>
import 'vue-search-select/dist/VueSearchSelect.css'

export default {
    data() {
        return {
            loader:false,
            swap : false,
            loader_url: '/img/spinner.gif',
            formlid:'',
            formlid1:'',
            five9_list:'',
            five9_list_new : [],
            five9_list_new1 : [],
            header : [],
            headerOld : [],
            allRecords : [],
            allRecordCount : 0,
            recordList : [],
            recordsCount : 0,
            recordContainer : [],
            form : new Form({
                listname : '',
                number1 : ''
            }),
            tform : new Form({
                listname : '',
                fdata : '',
                header : [],
                headerOld : [],
            }),
            action : '',
            pages : 0,
            pno : 1,
            per_page : 10,
            paginationArray : {},
            nextShow : false,
            prevShow : false,
            linkNext : 0,
            linkPrev : 0,
            disposition : '',
            dispositions : [],
            sorting : 'first_name',
            sortingType : 'asc',
            search : '',
            filteredRecords : 0,
        }
    },
    computed: {
        orderedContacts: function () {            
            return _.orderBy(this.recordList, this.sorting, this.sortingType)
        }
    },
    filters: {
        titleLabel(str) {
            let nstr = str.split('_').join(' ');
            let sentence = nstr.toLowerCase().split(' ');
            for(var i = 0; i< sentence.length; i++){
                sentence[i] = sentence[i][0].toUpperCase() + sentence[i].slice(1);
            }
            return sentence.join(' ');
        },
        titleFormat: function (str) {
            let nstr = str.charAt(0).toUpperCase() + str.slice(1);
            return nstr.replace('_', ' ');
        },
        capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        },
    },
    methods: {
        getList(){            
            axios.get('/api/get-f9-list').then((response) => {
                this.five9_list = response.data;
                var newArray = []
                var newArrayA = []
                var counter = 0
                newArray[counter] = {
                    value : 0,
                    name : 'Select List'
                }
                newArrayA [counter] = "Select List"
                counter++
                for (const key of this.five9_list) {
                    //if(key.name.startsWith('Ankit')){
                        newArrayA[counter] = key.name
                        newArray[counter] = {
                            value : key.name,
                            name : key.name
                            //name : key.name + ' (' + key.size + ')'
                        }
                        counter++
                    //}
                }
                // this.five9_list_new = newArray
                this.five9_list_new = newArrayA
            })
        },
        reset(){
            this.paginationArray = {}
            this.pages = 0
            this.formlid = ''
            this.formlid1 = ''
            this.header = []
            this.recordList = []
            this.recordsCount = 0
            this.action = ''
            this.recordContainer = []
            this.five9_list_new1 = []
            this.nextShow = false
            this.prevShow = false
            this.linkNext = 0
            this.linkPrev = 0
            this.dispositions = []
            this.allRecords = []
            this.disposition = ''
            this.search = ''
            this.filteredRecords = 0
        },
        getContacts(){
            if(this.formlid){
                this.action = ''
                this.formlid1 = ''
                this.header = []
                this.paginationArray = {}
                this.pages = 0
                this.recordList = []
                this.recordsCount = 0
                this.recordContainer = []
                this.five9_list_new1 = []
                this.nextShow = false
                this.prevShow = false
                this.linkNext = 0
                this.linkPrev = 0
                var list =  this.five9_list_new
                var newList = []
                var count =  0
                for(const key in list){
                    if(list[key] != this.formlid){
                        newList[count++] = list[key]
                    }
                }
                this.five9_list_new1 = newList
                this.header = []
                this.recordList = []
                this.recordsCount = 0
                this.$Progress.start()
                axios.get('/api/get-five-nine-report?listname=' + this.formlid).then((response) => {
                    if(response.data.hasOwnProperty("id")){
                        var id = response.data.id;
                        axios.get('/api/get-five-nine-report-response/' + id).then((responseR) => {
                            if(responseR.data.result == true){
                                axios.get('/api/get-five-nine-report-result/' + id).then((responseReport) => {     
                                    this.disposition = ''         
                                    this.search = ''
                                    this.filteredRecords = 0
                                    this.headerOld = responseReport.data.headerOld                  
                                    this.header = responseReport.data.header
                                    this.recordList = responseReport.data.records
                                    this.allRecords = responseReport.data.records
                                    this.dispositions = responseReport.data.dispositionArray
                                    this.recordsCount = responseReport.data.recordsCount
                                    this.allRecordCount = responseReport.data.recordsCount
                                    this.pages = Math.ceil(this.recordsCount/this.per_page)
                                    Vue.$toast.info(this.recordsCount + ' Records found !! !!');
                                    this.pagination(1)
                                    this.$Progress.finish()
                                });
                            }  
                        })
                    } else {
                        this.$Progress.finish()     
                        Vue.$toast.warning(' Please try again !! !!')
                    }
                })
            } else {
                this.reset()
                return false
            }
        },
        pagination(current_page){
            this.pno = current_page
            $(".records").css("display", "none")
            $(".record-"+current_page).css("display", "table-row")
            var start = 1;
            var end = this.pages
            if( current_page <= 5){
                if(this.pages >= 11){
                    start = 1
                    end = 11
                } else {
                    start = 1
                    end = this.pages
                }
            } else { 
                if(this.pages >= 11){
                    start = current_page -5
                    end = start + 11
                    if(end > this.pages){
                        end = this.pages
                        start = end - 11
                    }
                }
            }
            this.paginationArray = {}
            for(var i = start; i <= end; i++){
                this.paginationArray[i] = i
            }
        },
        selectAllRecords(){
            var a = document.getElementById('check-all');
            var aa = document.querySelectorAll("input[type=checkbox]");
            if(document.getElementById("check-all").checked == true){
                for (var i = 0; i < aa.length; i++){
                    if(parseInt(aa[i].value) > 1){
                        if((this.recordContainer.indexOf(parseInt(aa[i].value)) == -1)){
                            aa[i].checked = true;
                            this.recordContainer.push(Number(aa[i].value));
                        }else{
                            aa[i].checked = true;
                        }
                    }
                }
            }
            if(document.getElementById("check-all").checked == false){
                var record = [];
                for (var i = 0; i < aa.length; i++){
                    if(Number(aa[i].value) > 0){
                        if((this.recordContainer.indexOf(parseInt(aa[i].value)) >= 0)){
                            this.recordContainer.splice(this.recordContainer.indexOf(parseInt(aa[i].value)), 1);
                            aa[i].checked = false;
                        }
                    }
                }
            }
        },
        selectAllRecord(index){
            if((this.recordContainer.indexOf(parseInt(index)) == -1) && (document.getElementById("record-"+index).checked == true)){
                this.recordContainer.push(parseInt(index));
            }else{
                this.recordContainer.splice(this.recordContainer.indexOf(parseInt(index)), 1);
            }
        },
        deleteRecords(){
            if(this.recordContainer.length == 0){
                this.showMessage('warning', '', 'Please select contacts first')
                return false
            }
            var rc = this.recordContainer
            var rcl =  this.recordList
            var data = []
            var count = 0
            for(var i = 0; i < rc.length; i++){
                for(const key in rcl){
                    if(rc[i] == rcl[key]['CONTACT_ID']){
                        data[count++] = rcl[key]
                    }
                }
            }
            
            this.tform.listname = this.formlid
            this.tform.fdata = data
            this.tform.header = this.headerOld
            this.tform.headerOld = this.header
            this.$swal({
                title: 'Are you sure ? ',
                text: "Do you want to delete these contacts form this list?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.$Progress.start()
                    Vue.$toast.info(' Please wait, Deleting contacts !! !!');
                    this.tform.post('/api/delete-selected-form-list').then((response) => {
                        if(response.data.hasOwnProperty('id') === true){
                            Vue.$toast.info(' Selected record(s) deleted successfully !! !!');
                            Vue.$toast.info(' Please wait, Fetching contacts !! !!');
                            this.recordContainer = []
                            this.getContacts()
                        } else {
                            Vue.$toast.warning(' Please try again !! !!');
                        }
                        this.$Progress.start()
                    })
                    this.swap = false
                    this.action = ''
                }
            })
        },
        moveContacts(){
            if(this.formlid1 == ''){
                Vue.$toast.warning(' Please, selecte list to move in contacts !! !!')
                return false
            }
            var rc = this.recordContainer
            var rcl =  this.recordList
            var data = []
            var count = 0
            for(var i = 0; i < rc.length; i++){
                for(const key in rcl){
                    if(rc[i] == rcl[key]['CONTACT_ID']){
                        data[count++] = rcl[key]
                    }
                }
            }
            this.$swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Move it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.$Progress.start()
                    Vue.$toast.info(' Please wait, Contacts moving.. !! !!')
                    this.tform.listname1 = this.formlid1
                    this.tform.listname = this.formlid
                    this.tform.fdata = data
                    this.tform.header = this.headerOld
                    this.tform.headerOld = this.header
                    this.tform.post('/api/transfer-contacts').then((response) => {
                        if(response.data.hasOwnProperty('message') === true){
                            this.recordContainer = []
                            Vue.$toast.info(' Contacts moved successfully !! !!')
                            this.getContacts()
                        } else {
                            Vue.$toast.warning('Please try again !! !!')
                        }
                        this.$Progress.finish()
                    })
                    this.swap = false
                    this.action = ''
                }
            })
        },
        showswap(){
            if(this.action == 'move'){                
                this.swap = 'move'
                Vue.$toast.info(' Please, selecte list to move in contacts !! !!');
            } 
            if(this.action == 'delete'){
                //delete
                this.swap = 'delete'
                this.deleteRecords()
            }
        },
        showMessage(icon, text, message){
            this.$swal({
                icon: icon,
                title: text,
                text: message,
            })
        },
        filter(){
            this.recordContainer = []
            var recordList = this.allRecords
            if(this.disposition){
                var newRecordList = recordList.filter((element) => element.Last_Dispo && element.Last_Dispo == this.disposition)
                recordList = newRecordList
            }
            if(this.search){
                var newRecordList = recordList.filter((element) => element.company && element.company.includes(this.search.toUpperCase()))
                recordList = newRecordList
            }
            this.recordList = []
            this.recordList = recordList
            this.filteredRecords = this.recordsCount = this.recordList.length
            //Vue.$toast.info(this.recordsCount + ' record(s) filtered !! !!')
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)  
        },
        setPerPage(){
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            
            this.pagination(1)
        },
        sortResult(){

        },
        resetFilter(){
            this.disposition = ''
            this.search = ''
            this.recordList = this.allRecords
            this.recordsCount = this.allRecordCount
            this.filteredRecords = 0
        }
    },
    mounted() {
        if(this.$route.params.name){
            this.formlid = this.$route.params.name
            this.getList()
            this.getContacts()
        } else {
            this.$Progress.start()
            this.getList()
            this.$Progress.finish()
        }
        //testing
        // axios.get('/api/get-five-nine-all-list-report').then((response) => {
        //     if(response.data.hasOwnProperty("id")){
        //         var id = response.data.id;
        //         axios.get('/api/get-five-nine-report-response/' + id).then((responseR) => {
        //             if(responseR.data.result == true){
        //                 axios.get('/api/get-five-nine-all-list-report-results/' + id).then((responseReport) => {              
        //                     // this.headerOld = responseReport.data.headerOld                  
        //                     // this.header = responseReport.data.header
        //                     // this.recordList = responseReport.data.records
        //                     // this.allRecords = responseReport.data.records
        //                     // this.dispositions = responseReport.data.dispositionArray
        //                     // this.recordsCount = responseReport.data.recordsCount
        //                     // this.allRecordCount = responseReport.data.recordsCount
        //                     // this.pages = Math.ceil(this.recordsCount/this.per_page)
        //                     // Vue.$toast.info(this.recordsCount + ' Records found !! !!');
        //                     // this.pagination(1)
        //                     // this.$Progress.finish()
        //                 });
        //             }  
        //         })
        //     } else {
        //         this.$Progress.finish()     
        //         Vue.$toast.warning(this.recordsCount + ' Please try again !! !!')
        //     }
        // })
    }
}
</script>
<style>
.inline {
    display: inline;
}
.width-80{
    width: 80%;
}
.vs__dropdown-toggle{
    position: relative;
    border : 0px;
    bottom: 3px;
    height: 15px;
    left: 0px;
    right: -18px;
    padding: 0;
    margin: 0;
}

.action {
    position: relative;
    top: 26px;
}
.action-1 {
    position: relative;
    top: 34px;
}
.page-link-active{
    background-color: #3F1245;
    color: #fff;
}
.pointer{
    cursor: pointer;
}
.inline{
    display : inline;
}
.filter{
    display : relative;
}
</style>