<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-11 col-12 pl-0">
                    <div class="row m-0">
                        <div class="col-md-3 col-12 pl-0">
                            <input class="form-control" type="text" v-model="name" placeholder="Search campaign by name" v-on:keyup="filter"/>
                        </div>  
                        <div class="col-md-3 col-12 pl-0">
                            <select v-model="mode" class="form-control" @change="filter">
                                <option value="">Select Mode</option>
                                <option v-for="m in modes" :key="m" :value="m"> {{ m }} </option>
                            </select>
                        </div> 
                        <div class="col-md-3 col-12 pl-0">
                            <select v-model="type" class="form-control" @change="filter">
                                <option value="">Select Type</option>
                                <option v-for="t in types" :key="t" :value="t"> {{ t }} </option>
                            </select>
                        </div> 
                        <div class="col-md-3 col-12 pl-0">
                            <select v-model="state" class="form-control" @change="filter">
                                <option value="">Select State</option>
                                <option v-for="s in states" :key="s" :value="s"> {{ s }} </option>
                            </select>
                        </div>              
                    </div>
                </div>
                <div class="col-md-1 col-12 pl-0">
                    <button class="btn btn-sm btn-primary" @click="reset">Reset</button>
                </div>
            </div>
        </div>
        <div class="mapping-div">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                       <th>Sno</th>
                       <th>Name
                            <i class="bi bi-caret-up pointer" @click="sorting='name',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='name',sortingType='desc'"></i>
                       </th>
                        <th>Mode
                            <i class="bi bi-caret-up pointer" @click="sorting='mode',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='mode',sortingType='desc'"></i>
                        </th>
                        <th>Type
                            <i class="bi bi-caret-up pointer" @click="sorting='type',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='type',sortingType='desc'"></i>
                        </th>
                        <th>State
                            <i class="bi bi-caret-up pointer" @click="sorting='state',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='state',sortingType='desc'"></i>
                        </th>
                        <th>Profile Name</th>                        
                        <th>Training Mode</th>
                        <th width="300px;">Description</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(campaign, index) in orderedContacts" :key="'campaign-' + index" :class="'records record-'+parseInt((index+parseInt(per_page))/parseInt(per_page))" v-show="parseInt((index+parseInt(per_page))/parseInt(per_page)) == 1">
                        <td> {{ index + 1 }}</td>
                        <td> {{ campaign.name }} </td>
                        <td> {{ campaign.mode }} </td>
                        <td> {{ campaign.type }} </td>
                        <td> {{ campaign.state }} </td>
                        <td> {{ campaign.profileName }} </td>
                        <td> {{ campaign.trainingMode }} </td>
                        <td> 
                            <span v-if="campaign.description.length > 0">{{ campaign.description }}</span>
                            <span v-else></span> 
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" @click="showDetails(campaign)" ><i class="bi bi-eye"></i> View</button>
                        </td>
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
        <div class="modal fade" tabindex="-1" role="dialog" id="detailModal" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Campaign : {{ campaignName }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-10 col-sm-8">
                                <div class="form-group">
                                    <select class="form-control" >
                                        <option v-for="cam in campaignList" :key="cam" :value="cam">{{ cam }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add</button>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sno</th>
                                    <th>Campaign Name</th>
                                    <th>Dialing Priority</th>
                                    <th>List Name</th>
                                    <th>Priority</th>
                                    <th width="50px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list, index) in lists" :key="list.campaignName + '-' +index">
                                    <td> {{ index +1 }} </td>
                                    <td> {{ list.campaignName }} </td>
                                    <td> {{ list.dialingPriority }} </td>
                                    <td> {{ list.listName }} </td>
                                    <td> {{ list.priority }} </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" @click="deleteList(list.campaignName, list.listName)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Update</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            allLists : [],
            allRecords : [],
            camList : [],
            campaignName : '',
            filteredRecords : 0,
            five9_campaign : [],
            lists : [],
            loader:false,
            loader_url: '/img/spinner.gif',
            mode : '',
            modes : [],
            name : '',
            pages : 0,
            paginationArray : {},
            pno : 1,
            per_page : 10,
            recordsCount : 0,
            sorting : 'name',
            sortingType : 'asc',
            state : '',
            states : [],
            type : '',
            types : [],
            //pagination
        }
    },
    computed: {
        orderedContacts: function () {            
            return _.orderBy(this.five9_campaign, this.sorting, this.sortingType)
        },
        campaignList: function(){
            return _.orderBy(this.camList)
        }
    },
    methods: {
        setPerPage(){
            this.pages = Math.ceil(this.recordsCount/this.per_page)            
            this.pagination(1)
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
        filter(){
            var recordList = this.allRecords
            if(this.mode){
                var newRecordList = recordList.filter((element) => element.mode && element.mode == this.mode)
                recordList = newRecordList
            }
            if(this.type){
                var newRecordList = recordList.filter((element) => element.type && element.type == this.type)
                recordList = newRecordList
            }
            if(this.state){
                var newRecordList = recordList.filter((element) => element.state && element.state == this.state)
                recordList = newRecordList
            }
            if(this.name){
                var newRecordList = recordList.filter((element) => element.name && element.name.toLowerCase().includes(this.name.toLowerCase()))
                recordList = newRecordList
            }
            this.recordList = []
            this.five9_campaign = recordList
            this.filteredRecords = this.recordsCount = this.five9_campaign.length
            //Vue.$toast.info(this.filteredRecords + ' record(s) filtered !! !!')
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)  
        },
        reset(){
            this.type = ''
            this.state = ''
            this.mode = ''
            this.name = ''
            this.five9_campaign = this.allRecords
            this.recordsCount = this.five9_campaign.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)
        },
        showDetails(campaign){
            this.lists = []
            this.campaignName = campaign.name
            axios.get('/api/get-campaign-list?campaign=' + campaign.name).then((response) => {
                if(response.data.status == 'success'){
                    this.lists = response.data.results
                    var lists = this.lists
                    var allLists = this.allLists
                    var camList = []
                    for(var i = 0; i < allLists.length; i++){
                        var find = 0
                        for(var j = 0; j < lists.length; j++){
                            if(allLists[i] == lists[j]["listName"]){
                                find = 1
                            }
                        }
                        if(find == 0){
                            camList[camList.length] = allLists[i]
                        }
                    }
                    this.camList = camList                    
                }
            })
            $('#detailModal').modal('show');
            
        },
        deleteList(campaignName, listName){
            this.$swal({
                title: 'Are you sure ? ',
                text: "Do you want to delete this list?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.get('/api/delete-list-from-campaign?campaignName=' + campaignName + '&listName=' + listName).then((response) => {                        
                        if(response.data.status == 'success'){
                            Vue.$toast.info('List removed successfully !! !!')
                            this.$Progress.start()
                            axios.get('/api/get-campaign-list?campaign=' + campaignName).then((response) => {
                                if(response.data.status == 'success'){
                                    this.lists = response.data.results
                                    this.$Progress.finish()
                                }
                            })
                        } else {
                            Vue.$toast.warning(response.data.mgs)
                            this.$Progress.finish()
                        }
                    })
                }
            })

        }
    },
    mounted() {
        this.$Progress.start()
        axios.get('/api/get-f9-campaigns').then((response) => {
            this.five9_campaign = response.data.results
            this.allRecords = response.data.results
            this.modes = response.data.mode
            this.states = response.data.state
            this.types = response.data.type
            this.recordsCount = this.five9_campaign.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)
            this.$Progress.finish()
        })
        axios.get('/api/get-f9-list').then((response) => {
            var allList = response.data
            var allListArray = []
            for(var i = 0; i < allList.length; i++){
                allListArray[i] = allList[i]['name']
            }
            this.allLists = allListArray
        })
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
</style>