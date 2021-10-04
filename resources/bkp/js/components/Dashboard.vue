<template>
    <div>
       <div class="row card-box m-0 py-3">
           <div class="col-md-3 col-sm-6 col-12">
               <a href="/outreach/dashboard">
                    <div class="card">
                        <div class="card-body">
                            <h3>Outreach Dashboard</h3>
                        </div>
                    </div>
                </a>
           </div>
           <div class="col-md-3 col-sm-6 col-12">
               <a href="#">
                    <div class="card">
                        <div class="card-body">
                            <h3>Activecampaign Dashboard</h3>
                        </div>
                    </div>
                </a>
           </div>
           <div class="col-md-3 col-sm-6 col-12">
               <a href="#">
                    <div class="card">
                        <div class="card-body">
                            <h3>Five9 Dashboard</h3>
                        </div>
                    </div>
                </a>
           </div>
           <div class="col-md-3 col-sm-6 col-12">
               <a href="/exports">
                    <div class="card">
                        <div class="card-body">
                            <h3>CSV Import</h3>
                        </div>
                    </div>
                </a>
           </div>
       </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            loader:false,
            view:'list',
            step:0,
            loader_url: '/img/spinner.gif',
        }
    },
    methods: { 

    },
    mounted() {

    }
}
</script>