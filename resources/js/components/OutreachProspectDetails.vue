<template>
    <div>
        <div class="row pt-3 m-0 pb-1">
            <div class="col-md-6 col-12">
                <h4>Prospects Details</h4>
            </div>
        </div>
        <div class="filterbox">
            <div class="row m-0">
                <div class="col-md-3 pl-0">
                </div>
                <div class="col-md-3 p-0 text-center">
                </div>
                <div class="col-md-6 text-right">
                </div>
            </div>
        </div>
        <div class="">
            <div class="row m-0">
                <div class="col-md-9 pl-0 bg-white">
                    <p class="fs-1 text-center pt-2">Call Activity</p>
                    <p class=" px-4 ">Note : {{ prospect.attributes.custom7 }}</p>
                    <div class="table-responsive fit-content px-4 py-4">

                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr v-for="(call, ano) in calls" :key="call.id">
                                    <td>{{ ano + 1 }}</td>
                                    <td>
                                        {{ prospect.attributes.name }}<br>
                                        <small>{{ call.attributes.to }}</small>
                                    </td>
                                    <td>
                                        <span v-for="callDisposition in callDispositions" :key="callDisposition.id">
                                            <span v-if="(call.relationships.callDisposition.data != nulol) && (callDisposition.id == call.relationships.callDisposition.data.id)" >{{ callDisposition.attributes.name }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span v-for="callPurpose in callPurposes" :key="callPurpose.id">
                                            <span v-if="(call.relationships.callPurpose.data != null) && (callPurpose.id == call.relationships.callPurpose.data.id)" >{{ callPurpose.attributes.name }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span title="Answered At">{{ call.attributes.answeredAt | setusdate }}</span>
                                    </td>
                                    <td>
                                        <span title="Answered At">{{ call.attributes.answeredAt | setTime }}</span>
                                        -
                                        <span title="Completed At">{{ call.attributes.completedAt | setTime }}</span>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-3 pl-0 bg-white px-4 py-4 border">
                    <div class="text-center">
                        <h3>{{ prospect.attributes.name }}</h3>
                        <span class="fs-5">{{ prospect.attributes.company }}</span><br>
                        <span class="fs-5">{{ prospect.attributes.title }}</span><br>
                    </div><br>
                    <b>CONTACT</b> :<br>
                    <ul class="nostylelist">
                        <li v-for="email in prospect.attributes.emails" :key="email">
                            <i class="bi bi-envelope"></i> {{ email }}
                        </li>
                        <li v-for="mobile in prospect.attributes.mobilePhones" :key="mobile">
                            <i class="bi bi-phone"></i> {{ mobile }}
                        </li>
                        <li v-for="mobile in prospect.attributes.workPhones" :key="mobile">
                            <i class="bi bi-telephone"></i>  {{ mobile }}
                        </li>
                        <i class="bi bi-house"></i> {{ prospect.attributes.addressStreet }}<br>
                        <span class="fs-5">
                        {{ prospect.attributes.addressCity }}
                        {{ prospect.attributes.addressState }}
                        {{ prospect.attributes.addressZip }}
                        {{ prospect.attributes.addressCountry }}<br>
                        </span>
                        <small><i class="bi bi-arrow-right"></i>  Touched  {{ prospect.attributes.touchedAt | setFulldate}}</small><br>
                        <small><i class="bi bi-eye"></i>  Engaged {{ prospect.attributes.engagedAt | setFulldate}}</small>
                    </ul>
                    <!-- <b>ACCOUNT</b> : <span>{{ accountname }}</span><br> -->
                    <!-- <b>STAGE</b> : <span>{{ stagename }}</span><br> -->
                    <!-- <b>PERSON</b> : <span>{{ personaname }}</span><br> -->
                    <!-- <b>OWNER</b> : <span>{{ ownername }}</span><br><br> -->
                    <b>GENERAL INFO</b>
                    <ul class="nostylelist">
                        <li>First Name : {{ prospect.attributes.firstName }}</li>
                        <li>Last Name : {{ prospect.attributes.lastName }}</li>
                        <li>Source : {{ prospect.attributes.source }}</li>
                    </ul>
                    <b>EMPLOYMENT</b>
                    <ul class="nostylelist">
                        <li>Title : {{ prospect.attributes.title }}</li>
                        <li>Company : {{ prospect.attributes.company }}</li>
                    </ul>
                    <b>TAGS</b><br>
                    <ul class="nostylelist">
                        <li v-for="tag in prospect.attributes.tags" :key="tag">{{ tag }}</li>
                    </ul>
                    <br>
                    <b>CUSTOM FIELDS</b><br>
                    <b title="Interested In">Interested. : </b>{{ prospect.attributes.custom6 }}<br>
                    <b>S&M Tools : </b>{{ prospect.attributes.custom7 }}<br>
                    <b>VR / AR : </b>{{ prospect.attributes.custom8 }}<br>
                    <b title="Options & Selection Sys">Options & S : </b>{{ prospect.attributes.custom9 }}<br>
                    <b title="Home Tech Opportunity">Home Tech. : </b>{{ prospect.attributes.personalNote1 }}<br>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
            prospect: null,
            ownername: null,
            accountname: null,
            stagename: null,
            personaname: null,
            calls:{},
            callDispositions:{},
            callPurposes:{},
        }
    },
    computed:{

    },
    methods:{
        getOutreach() {
            let path = location.pathname.split("/");
            this.$Progress.start();
            axios.get('/api/get-outreach-prospect-details/'+path[path.length - 1]).then((response) => {
                this.prospect = response.data.details.data;

                this.getCallsInfo(path[path.length - 1]);
                this.$Progress.finish();
                // this.getOwnerInfo(response.data.details.data.relationships.owner.data.id);
                // this.getAccountInfo(response.data.details.data.relationships.account.data.id);
                // this.getStageInfo(response.data.details.data.relationships.stage.data.id);
                // this.getPersonaInfo(response.data.details.data.relationships.persona.data.id);
            });
        },
        getOwnerInfo(id) {
            axios.get('/api/get-outreach-prospect-details-owner/'+ id).then((response) => {
                this.ownername = response.data.details.data.attributes.name;
            });
        },
        getAccountInfo(id){
            axios.get('/api/get-outreach-prospect-details-account/'+ id).then((response) => {
                this.accountname = response.data.details.data.attributes.name;
            });
        },
        getStageInfo(id){
            axios.get('/api/get-outreach-prospect-details-stage/'+ id).then((response) => {
                this.stagename = response.data.details.data.attributes.name;
            });
        },
        getPersonaInfo(id){
            axios.get('/api/get-outreach-prospect-details-persona/'+ id).then((response) => {
                this.personaname = response.data.details.data.attributes.name;
            });
        },
        getCallsInfo(id){
            axios.get('/api/get-outreach-prospect-details-calls/'+ id).then((response) => {
                this.calls = response.data.details.data.reverse();
            });
        },
        getCallDispositions(){
            axios.get('/api/get-call-dispositions').then((response) => {
                this.callDispositions = response.data.details.data;
            });
        },
        getCallPurpose(){
            axios.get('/api/get-call-purpose').then((response) => {
                this.callPurposes = response.data.details.data;
            });
        }
    },
    mounted() {
        this.getOutreach();
        this.getCallDispositions();
        this.getCallPurpose();
    }
}
</script>
<style>
.nostylelist{
    list-style: none;
}
</style>
