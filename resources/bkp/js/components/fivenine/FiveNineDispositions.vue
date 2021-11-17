<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-3 col-12 pl-0">
                    <input class="form-control" type="text" v-model="name" placeholder="Search disposition by name" v-on:keyup="filter"/>
                </div> 
                <div class="col-md-3 col-12 pl-0">
                    <select v-model="type" class="form-control" @change="filter">
                        <option value="">Select Type</option>
                        <option v-for="t in types" :key="t" :value="t"> {{ t }} </option>
                    </select>
                </div>  
                <div class="col-md-3 col-12 pl-0">
                    <button class="btn btn-sm btn-primary" @click="reset">Reset</button>
                </div>           
            </div>                
        </div>
        <div class="mapping-div">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                       <th width="50px;">Sno</th>
                       <th width="100px;">Name
                            <i class="bi bi-caret-up pointer" @click="sorting='name',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='name',sortingType='desc'"></i>
                       </th>
                        <th width="100px;">Type
                            <i class="bi bi-caret-up pointer" @click="sorting='type',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='type',sortingType='desc'"></i>
                        </th>
                        <th width="300px;">Description</th>
                        <th width="50px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(disposition, index) in orderedContacts" :key="'disposition-' + index" :class="'records record-'+parseInt((index+parseInt(per_page))/parseInt(per_page))" v-show="parseInt((index+parseInt(per_page))/parseInt(per_page)) == 1">
                        <td> {{ index + 1 }}</td>
                        <td> {{ disposition.name }} </td>
                        <td> {{ disposition.type }} </td>
                        <td> 
                            <span v-if="disposition.description.length > 0">{{ disposition.description }}</span>
                            <span v-else></span> 
                        </td>
                        <td><button type="button" class="btn btn-sm btn-primary" @click="showDetails(disposition)"><i class="bi bi-pen"></i>  Edit</button></td>
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
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"> Update Disposition</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="disposition-name">Disposition</label>
                            <input id="disposition-name" type="text" class="form-control" v-model="dform.disposition" readonly/>
                        </div>
                        <div class="form-group">
                            <label for="new-disposition">New Disposition</label>
                            <input id="new-disposition" type="text" class="form-control" v-model="dform.dispositionUpdate" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="close">Close</button>
                        <button type="button" class="btn btn-primary" @click="updateDisposition">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            five9_disposition : [],
            sorting : 'name',
            sortingType : 'asc',
            mode : '',
            modes : [],
            state : '',
            states : [],
            type : '',
            types : [],
            recordsCount : 0,
            name : '',
            //pagination
            pno : 1,
            pages : 0,
            pno : 1,
            per_page : 10,
            paginationArray : {},
            filteredRecords : 0,
            allRecords : [],
            dform: new Form({
                disposition : '',
                dispositionUpdate : '',
            })
        }
    },
    computed: {
        orderedContacts: function () {            
            return _.orderBy(this.five9_disposition, this.sorting, this.sortingType)
        }
    },
    methods: {
        getDisposition(){
            axios.get('/api/get-f9-dispositions').then((response) => {
                this.five9_disposition = response.data.results
                this.allRecords = response.data.results
                this.types = response.data.type
                this.recordsCount = this.five9_disposition.length
                this.pages = Math.ceil(this.recordsCount/this.per_page)
                this.pagination(1)
                this.$Progress.finish()
            })
        },
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
            this.five9_disposition = recordList
            this.filteredRecords = this.recordsCount = this.five9_disposition.length
            Vue.$toast.info(this.filteredRecords + ' record(s) filtered !! !!')
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)  
        },
        reset(){
            this.type = ''
            this.state = ''
            this.mode = ''
            this.name = ''
            this.five9_disposition = this.allRecords
            this.recordsCount = this.five9_disposition.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)
        },
        showDetails(disposition){
            this.dform.disposition = disposition.name   
            this.dform.dispositionUpdate = ''
            $('#detailModal').modal('show');
        },
        updateDisposition(){
            this.$Progress.start()
            this.dform.post('/api/update-disposition').then((response) => {
                if(response.data.status == 'fail'){
                    Vue.$toast.warning(response.data.msg)
                    this.$Progress.finish()
                }
                if(response.data.status == 'success'){
                    this.close()
                    Vue.$toast.info(response.data.msg)
                    this.reset()
                    this.getDisposition()
                }
            })
        },
        close(){
            this.dform.reset()
            $('#detailModal').modal('hide');
        }
    },
    mounted() {
        this.$Progress.start()
        this.getDisposition()
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