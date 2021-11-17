<template>
    <div class="btn-group btn-group-sm" role="group">
        <button class="btn btn-sm btn-secondary wf-35">  {{ uopens | freeNumber }} </button> 
        <downloadexcel class="btn btn-sm btn-dark" :fields="fields" type="xls" :fetch="fetchOpen" :before-generate="startDownload" :before-finish="finishDownload" :name="'open-rate-'+cid+'.xls'">
            <i class="bi bi-download"></i>
        </downloadexcel>
    </div>
</template>
<script>
import downloadexcel from "vue-json-excel";

export default {
  name: 'downloader',
  props: ['cid', 'sid', 'uopens'],
  components:{downloadexcel},
  data(){
        return {
            fields:{'name':'name', 'email':'email', 'phone':'mobile'},
        }
    },
  methods: {
      async fetchOpen() {
            let id = this.cid;
            let response = await axios.get('/api/get-automation-campaign-open-contacts/'+id);
            return response.data.results; 
        },
        startDownload(){
            this.$toasted.show("Excel file is being downloaded !!", { 
	            theme: "bubble", 
	            position: "bottom-center", 
	            duration : 2000
            });
        },
        finishDownload(){
            this.$toasted.show("Excel file downloaded successfully !!", { 
	            theme: "toasted-primary", 
	            position: "bottom-center", 
	            duration : 2000
            });
        }
    }
}  
</script>