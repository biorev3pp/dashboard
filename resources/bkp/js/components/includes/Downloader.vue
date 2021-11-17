<template>
    <div class="btn-group btn-group-sm" role="group">
        <router-link :to="'/automations/campaign-links/'+cid+'/'+sid" class="btn btn-sm btn-primary wf-35">  {{ sclicks | freeNumber }} </router-link>
        <downloadexcel class="btn btn-sm btn-dark" :fields="fields" type="xls" :fetch="fetchClick" :before-generate="startDownload" :before-finish="finishDownload" :name="'click-rate-'+cid+'.xls'">
            <i class="bi bi-download"></i>
        </downloadexcel>
    </div>
</template>
<script>
import downloadexcel from "vue-json-excel";

export default {
  name: 'downloader',
  props: ['cid', 'sid', 'sclicks'],
  components:{downloadexcel},
  data(){
        return {
            fields:{'name':'name', 'email':'email', 'phone':'mobile'},
        }
    },
  methods: {
      async fetchClick() {
            let id = this.cid;
            let response = await axios.get('/api/get-automation-json-campaigns/'+id);
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