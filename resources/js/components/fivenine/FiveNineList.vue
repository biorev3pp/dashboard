<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
                    <div class="input-group in-search-group">
                        <input type="text" class="form-control" v-model="search" placeholder="Search by name">
                        <div class="input-group-append">
                            <button class="btn bg-white" type="button" @click="getData(1)">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>  
                <div class="col-md-8 col-12 pl-0 text-right">
                    <img :src="loader_url" alt="loading..." v-show="loader">
                    <span class="d-inline-block mt-2">Showing <b>{{ five9_list.total | freeNumber }}</b> Results</span>
                </div>                
            </div>
        </div>
        <div>
            <div class="divtable border-top">
                <div class="divthead">
                    <div class="divthead-row">
                        <div class="divthead-elem wf-80"> SNo </div>
                        <div class="divthead-elem  mwf-200"> List Name </div>
                        <div class="divthead-elem wf-150"> Contacts </div>
                        <div class="divthead-elem wf-150"> Updated At </div>
                        <div class="divthead-elem wf-300"> Action </div>
                    </div>
                </div>
                <div class="divtbody full-page-with-search">
                    <div class="divtbody-row" v-for="(list, index) in five9_list.data" :key="'list-' + index">
                        <div class="divtbody-elem  wf-80 text-left"> {{ (five9_list.current_page- 1)*five9_list.per_page + index+1 }} </div>
                        <div class="divtbody-elem  mwf-200 text-left"> <b>{{ list.name }}</b> </div>
                        <div class="divtbody-elem  wf-150 text-center"> <b>{{ list.size }}</b> </div>
                        <div class="divtbody-elem  wf-150 text-center"> <b>{{ list.updated_at }}</b> </div>
                        <div class="divtbody-elem  wf-300 text-center">
                            <router-link  :to="'/five9/lists/prospects/' + list.id " class="btn btn-sm  btn-outline-primary text-uppercase fw-500 p-t-2 p-b-2 fs-11"> View </router-link> 
                            <button class="btn btn-sm btn-outline-success text-uppercase fw-500 p-t-2 p-b-2 fs-11" @click="updateList(list.name)">Sync</button>
                            <button class="btn btn-sm btn-outline-danger text-uppercase fw-500 p-t-2 p-b-2 fs-11" @click="emptyList(list.name)">Empty</button>
                            <button class="btn btn-sm btn-outline-danger text-uppercase fw-500 p-t-2 p-b-2 fs-11" @click="deleteList(list.name)">Delete</button>
                        </div>
                    </div>
                </div>
                <div class="divtfoot border-top">
                    <div class="text-center py-1">
                        <span class="form-inline d-inline-flex mr-3">
                            <label class="form-control  pr-0 border-none"> Show : </label>
                            <select class="form-control" v-model="recordPerPage" @change="getData(1)">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </span>
                        <pagination :limit="5" :data="five9_list" @pagination-change-page="getData"></pagination>
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
            five9_list:{},
            search:'',
            recordPerPage:20
        }
    },
    computed: {


    },
    methods: {
        getData(pno){
            this.loader = true
            this.$Progress.start()
            axios.get('/api/get-f9-list?recordPerPage='+this.recordPerPage+'&page=' + pno + '&search=' + this.search).then((response) => {
                this.five9_list = response.data;
                this.$Progress.finish()
                this.loader = false
            })
        },
        emptyList(listName){
            this.$swal({
                title: 'Are you sure ? ',
                text: "You want to empty this list?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, empty it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.$Progress.start()
                    axios.get('/api/delete-all-from-list?listName='+ listName).then( (response) => {
                        if(response.data.status == "success"){
                            this.getData(1)
                            this.$toasted.show("All records removed successfully from list - " + listName, { 
                                theme: "toasted-primary", 
                                position: "bottom-center", 
                                duration : 2000
                            });
                            this.$Progress.finish()
                        }
                    })
                }
            })
        },
        deleteList(listName){
            this.$swal({
                title: 'Are you sure ? ',
                text: "You want to delete this list...",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.$Progress.start()
                    axios.get('/api/delete-list?listName='+ listName).then( (response) => {
                        console.log(response.data.status)
                        if(response.data.status == "success"){
                            this.getData(1)
                            this.$toasted.show(listName + " has been deleted successfully", { 
                                theme: "toasted-primary", 
                                position: "bottom-center", 
                                duration : 2000
                            });
                            this.$Progress.finish()
                        }
                    })
                }
            })
        },
        updateList(listName){
            this.$swal({
                title: 'Are you sure ? ',
                text: "You want to update this list ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.$Progress.start()
                    axios.get('/api/update-list?listName='+ listName).then( (response) => {
                        if(response.data.status == "success"){
                            this.getData(1)
                            this.$toasted.show(listName + " has been updated successfully", { 
                                theme: "toasted-primary", 
                                position: "bottom-center", 
                                duration : 2000
                            });
                            this.$Progress.finish()
                        }
                    })
                }
            })
        }
    },
    created() {
        this.getData(1)
    }
}
</script>
