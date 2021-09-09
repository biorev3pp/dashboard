<template>
    <div>
        <div class="top-form">
            <div class="row m-0">
                <div class="col-md-4 col-12 pl-0">
                    <input class="form-control" type="text" v-model="search" placeholder="Search list.." v-on:keyup="filter"/>
                </div>                
            </div>
        </div>
        <div class="mapping-div">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                       <th>Sno</th>
                       <th>Name</th>
                        <th>Total number of contacts</th>
                        <th width="200px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(list, index) in orderedContacts" :key="'list-' + index" :class="'records record-'+parseInt((index+parseInt(per_page))/parseInt(per_page))" v-show="parseInt((index+parseInt(per_page))/parseInt(per_page)) == 1">
                        <td> {{ index + 1 }}</td>
                        <td> {{ list.name }} </td>
                        <td> {{ list.size }} </td>
                        <td>
                            <router-link  :to="'/five9-modify-contacts/' + list.name " class="btn btn-primary icon-btn-right theme-btn">
                               <i class="bi bi-eye"></i> View
                            </router-link> 
                        </td>
                    </tr>   
                </tbody>
            </table>    
                <ul class="pagination" id="paginator">
                    <li v-for="p in paginationArray" :key="'page'+p" class="page-item pagination-page-nav">
                        <a class="page-link page-link-active" v-if="p == pno"> {{p}} </a>
                        <a class="page-link" href="javascript:;" @click="pagination(p)" v-else> {{p}} </a>
                    </li>
                </ul>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            five9_list : [],
            allRecords : [],
            search : '',
            pno : 1,
            pages : 0,
            pno : 1,
            per_page : 10,
            paginationArray : {},
        }
    },
    computed: {
        orderedContacts: function () {            
            return _.orderBy(this.five9_list, 'name', 'asc')
        },
    },
    methods: {
        filter(){

            this.five9_list = this.allRecords.filter(element => {
                return element.name.toUpperCase().includes(this.search.toUpperCase())
            })
            this.recordsCount = this.five9_list.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)
        },
        pagination(current_page){
            console.log(current_page)
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
    },
    mounted() {
        this.$Progress.start()
        axios.get('/api/get-f9-list').then((response) => {
            this.five9_list = response.data;
            this.allRecords = response.data;
            this.recordsCount = this.five9_list.length
            this.pages = Math.ceil(this.recordsCount/this.per_page)
            this.pagination(1)
            this.$Progress.finish()
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