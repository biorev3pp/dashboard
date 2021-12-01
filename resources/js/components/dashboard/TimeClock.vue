<template>
    <h6>
        <span v-if="timezone">
            ({{ fulltime }})
            <i v-if="callTime" class="bi bi-circle-fill text-success"></i>
            <i v-else class="bi bi-circle-fill text-danger"></i>
        </span>
    </h6>
</template>
<script>
// import moment from 'moment';
import momenttz from 'moment-timezone';
export default {
    props: {
        'timezone': String,
    },
    data() {
        return {
            date : new Date,
            loader:false,
            fulltime:'',
            callTime:'',
            loader_url: '/img/spinner.gif',
        }
    },
    computed: {

    },
    methods: {
        setTime(){
            if(this.timezone) {
                this.fulltime = momenttz(new Date()).tz(this.timezone).format('hh:mm A')
                var hour = momenttz(new Date()).tz(this.timezone).format('H')
                // return hour;
                if(Number(hour) > 8 && Number(hour) < 17){
                    this.callTime = 1;
                }else{
                    this.callTime = 0;
                }
            } else {
                this.fulltime = '--';
            }
        }
    },
    created() {
        setInterval(() => {
            this.setTime();
        }, 1000)
    }
}
</script>