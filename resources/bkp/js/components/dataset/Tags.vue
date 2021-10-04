<template>
    <span>
        <span class="stack-box">
            <label for="email">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
            </label>
            <span :class="(email_status)?'active':''" v-title="email_status">E</span>
            <span :class="(workcall_status)?'active':''" v-title="workcall_status">W</span>
            <span :class="(mobilecall_status)?'active':''" v-title="mobilecall_status">M</span>
        </span>
    </span>
</template>
<script>
export default {
    props: {
        'a': Number,
        'b': Number,
        'c': Number,
        'd': [String, Number],
        'e': Number,
        'f': [String, Number],
        'g': Number,
        'h': Number,
    },
    data() {
        return {
           
        }
    },
    computed: {
        datasets() {
            return this.$store.getters.datasets
        },
        email_status() {
            let te = this.a;
            let tc = this.b;
            let to = this.c;
            let c1, c2;
            if(te == 0) { c1 = 0; c2 = 0; return ''; }
            else { 
                c2 = Math.round((tc*100)/te); 
                c1 = Math.round((to*100)/te);
                return 'Open rate - '+c1+'% | Click rate - '+c2+'%';
            }
        },
        mobilecall_status() {
            let cc = parseInt(this.d);
            let c3;
            
            if(cc == 0) { c3 = 0; return ''; } 
            else { c3 = Math.round((this.e*100)/cc); 
                return 'Call Receiving Rate - '+c3+'%'; 
            }
        },
        workcall_status() {
            let wcc = parseInt(this.f);
            let c4;

            if(wcc == 0) { c4 = 0; return ''; } 
            else { c4 = Math.round((this.g*100)/wcc); 
                return 'Call Receiving Rate - '+c4+'%';  
            }
        }
        
    },
    methods: {
        
        
        
    },
    mounted() {

}
}
</script>