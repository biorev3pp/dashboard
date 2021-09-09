<template>
    <div>
        <div class="synops">          
            <div class="inner-synop">
                <h4 class="number">{{ totalNumberOfRecords | freeNumber }} </h4><p>Total</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ notStarted | freeNumber }} </h4><p>Not Started</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ approaching | freeNumber }} </h4><p>Approaching</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ replied | freeNumber }} </h4><p>Replied</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ unresponsive | freeNumber }} </h4><p>Unresponsive</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ doNotContact | freeNumber }} </h4><p>Do Not Contact</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ badContactInfoMissingEmail | freeNumber }} </h4><p> Missing Email</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ badContactInfoMissingPhone | freeNumber }} </h4><p>Missing Phone</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ interested | freeNumber }} </h4><p>Interested</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ notInterested | freeNumber }} </h4><p>Not Interested</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ clients | freeNumber }} </h4><p>Clients</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ schedulingMetting | freeNumber }} </h4><p>Scheduling Meeting</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ meetingBooked | freeNumber }} </h4><p>Meeting Booked</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ negotiating | freeNumber }} </h4><p>Negotiating</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ emailFollowup | freeNumber }} </h4><p>Email Followup</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ phoneFollowup | freeNumber }} </h4><p>Phone Followup</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ inQueueofProspectContactEveryMonth | freeNumber }} </h4><p>In Queue</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ forwardedtoColleagueFriendDoNotContactAgain | freeNumber }} </h4><p>Do Not Contact</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ notAnswered | freeNumber }} </h4><p>Not Answered</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ irrelevantLeadDNC | freeNumber }} </h4><p>Irrelevant Lead DNC</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ clientLOST | freeNumber }} </h4><p>Client LOST</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ unresponsiveEmailSequence | freeNumber }} </h4><p>Unresponsive Email</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ badContactInfoMissingEmailPhone | freeNumber }} </h4><p>Missing Email/Phone</p>
            </div>
            <div class="inner-synop">
                <h4 class="number">{{ noStage | freeNumber }} </h4><p>No Stage</p>
            </div>
        </div>
        <button type="button" class="btn btn-warning reload-btn" @click="getSynopsisData">
            <i class="bi bi-arrow-repeat "></i>
        </button>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                totalNumberOfRecords : 0,
                approaching: 0,
                notStarted:0,
                replied:0,
                unresponsive:0,
                doNotContact:0,
                badContactInfoMissingEmail:0,
                badContactInfoMissingPhone:0,
                interested:0,
                notInterested:0,
                clients:0,
                schedulingMetting:0,
                meetingBooked:0,
                negotiating:0,
                emailFollowup:0,
                phoneFollowup:0,
                inQueueofProspectContactEveryMonth:0,
                forwardedtoColleagueFriendDoNotContactAgain:0,
                notAnswered:0,
                irrelevantLeadDNC:0,
                clientLOST:0,
                unresponsiveEmailSequence:0,
                badContactInfoMissingEmailPhone:0,
                noStage:0,
            }
        },
        methods: {
            getSynopsisData(){
                this.owner = 0;
                this.totalNumberOfRecords = '-';
                this.approaching = '-';
                this.notStarted = '-';
                this.replied = '-';
                this.unresponsive = '-';
                this.doNotContact = '-';
                this.badContactInfoMissingEmail = '-';
                this.badContactInfoMissingPhone = '-';
                this.interested = '-';
                this.notInterested = '-';
                this.clients = '-';
                this.schedulingMetting = '-';
                this.meetingBooked = '-';
                this.negotiating = '-';
                this.emailFollowup = '-';
                this.phoneFollowup = '-';
                this.inQueueofProspectContactEveryMonth = '-';
                this.forwardedtoColleagueFriendDoNotContactAgain = '-';
                this.notAnswered = '-';
                this.irrelevantLeadDNC = '-';
                this.clientLOST = '-';
                this.unresponsiveEmailSequence = '-';
                this.badContactInfoMissingEmailPhone = '-';
                this.noStage = '-';
                this.$Progress.start();
                axios.get('/api/get-outreach-prospect-synopsis/'+this.owner).then((response) => {
                    this.totalNumberOfRecords = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-not-started/'+this.owner).then((response) => {
                    this.notStarted = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-approaching/'+this.owner).then((response) => {
                    this.approaching = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-replied/'+this.owner).then((response) => {
                    this.replied = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-unresponsive/'+this.owner).then((response) => {
                    this.unresponsive = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-do-not-contact/'+this.owner).then((response) => {
                    this.doNotContact = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-bad-contact-info-missing-email/'+this.owner).then((response) => {
                    this.badContactInfoMissingEmail = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-bad-contact-info-missing-phone/'+this.owner).then((response) => {
                    this.badContactInfoMissingPhone = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-interested/'+this.owner).then((response) => {
                    this.interested = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-not-interested/'+this.owner).then((response) => {
                    this.notInterested = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-clients/'+this.owner).then((response) => {
                    this.clients = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-scheduling-metting/'+this.owner).then((response) => {
                    this.schedulingMetting = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-meeting-booked/'+this.owner).then((response) => {
                    this.meetingBooked = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-negotiating/'+this.owner).then((response) => {
                    this.negotiating = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-email-follow-up/'+this.owner).then((response) => {
                    this.emailFollowup = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-phone-follow-up/'+this.owner).then((response) => {
                    this.phoneFollowup = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-in-qQueue-of-prospect-contact-every-month/'+this.owner).then((response) => {
                    this.inQueueofProspectContactEveryMonth = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-forwarded-to-colleague-friend-do-not-contact-again/'+this.owner).then((response) => {
                    this.forwardedtoColleagueFriendDoNotContactAgain = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-not-answered/'+this.owner).then((response) => {
                    this.notAnswered = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-irrelevant-lead-DNC/'+this.owner).then((response) => {
                    this.irrelevantLeadDNC = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-client-LOST/'+this.owner).then((response) => {
                    this.clientLOST = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-unresponsive-Email-Sequence/'+this.owner).then((response) => {
                    this.unresponsiveEmailSequence = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-bad-ContactInfo-Missing-Email-Phone/'+this.owner).then((response) => {
                    this.badContactInfoMissingEmailPhone = response.data.results.meta.count;
                });
                axios.get('/api/get-outreach-prospect-synopsis-no-Stage/'+this.owner).then((response) => {
                    this.noStage = response.data.results.meta.count;
                    this.$Progress.finish();
                });
            },
             getAllData(){
                axios.get('/api/get-outreach-prospect-synopsis-all-data').then((response) => {
                    this.totalNumberOfRecords = response.data.total;
                    this.approaching = response.data.approaching;
                    this.notStarted = response.data.not_started;
                    this.replied = response.data.replied;
                    this.unresponsive = response.data.unresponsive;
                    this.doNotContact = response.data.do_not_contact;
                    this.badContactInfoMissingEmail = response.data.bad_contact_info_missing_email;
                    this.badContactInfoMissingPhone = response.data.bad_contact_info_missing_phone;
                    this.interested = response.data.interested;
                    this.notInterested = response.data.not_answered;
                    this.clients = response.data.clients;
                    this.schedulingMetting = response.data.scheduling_metting;
                    this.meetingBooked = response.data.meeting_booked;
                    this.negotiating = response.data.negotiating;
                    this.emailFollowup = response.data.email_follow_up;
                    this.phoneFollowup = response.data.phone_follow_up
                    this.inQueueofProspectContactEveryMonth = response.data.in_qQueue_of_prospect_contact_every_month;
                    this.forwardedtoColleagueFriendDoNotContactAgain = response.data.forwarded_to_colleague_friend_do_not_contact_again;
                    this.notAnswered = response.data.not_started;
                    this.irrelevantLeadDNC = response.data.irrelevant_lead_DNC;
                    this.clientLOST = response.data.client_LOST;
                    this.unresponsiveEmailSequence = response.data.unresponsive_Email_Sequence;
                    this.badContactInfoMissingEmailPhone = response.data.bad_ContactInfo_Missing_Email_Phone;
                    this.noStage = response.data.no_Stage;
                });
            },
        },
        mounted() {
            this.getAllData();
        }
    }
</script>
