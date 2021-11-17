<template>
    <div>
        <div class="mapping-div">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                       <th width="50px;">Sno</th>
                       <th width="100px;">Name
                            <i class="bi bi-caret-up pointer" @click="sorting='name',sortingType='asc'"></i>
                            <i class="bi bi-caret-down pointer" @click="sorting='name',sortingType='desc'"></i>
                       </th>
                        <th width="300px;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(skil, index) in orderedContacts" :key="'skil-' + index">
                        <td> {{ index + 1 }}</td>
                        <td> {{ skil.name }} </td>
                        <td> {{ skil.description }} </td>
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
            five9_skill : [],
            sorting : 'name',
            sortingType : 'asc',
            name : '',
        }
    },
    computed: {
        orderedContacts: function () {            
            return _.orderBy(this.five9_skill, this.sorting, this.sortingType)
        }
    },
    methods: {
    },
    mounted() {
        this.$Progress.start()
        axios.get('/api/get-five-nine-all-list-report').then((response) => {
            this.five9_skill = response.data.results
            this.$Progress.finish()
        })
        axios.get('/api/get-five-nine-all-list-report').then((response) => {
            if(response.data.hasOwnProperty("id")){
                var id = response.data.id;
                axios.get('/api/get-five-nine-report-response/' + id).then((responseR) => {
                    if(responseR.data.result == true){
                        axios.get('/api/get-five-nine-all-list-report-results/' + id).then((responseReport) => {     
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