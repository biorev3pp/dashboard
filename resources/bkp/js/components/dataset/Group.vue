<template>
    <span>
        <span :class="css" v-title="datatTitle">{{ txtmsg }} </span>
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
        'i': [String, Number],
        'j': Number,
        'k': Number
    },
    data() {
        return {
            css:'',
            datatSetTitle:'',
            txtmsg:''
        }
    },
    computed: {
        datasets() {
            return this.$store.getters.datasets
        },
        datatTitle() {
            return this.datatSetTitle;
        }
        
    },
    methods: {
        selectDataset() 
        {
            let ds = parseInt(this.i);
            let te = (this.a)?this.a:0;
            let tc = (this.b)?this.b:0;
            let to = (this.c)?this.c:0;
            let cc = (this.d)?this.d:0;
            let wcc = (this.f)?this.f:0;
            let rcc = (this.e)?this.e:0;
            let rwcc = (this.g)?this.g:0;
            let hcc = (this.j)?this.j:0;
            let hrcc = (this.k)?this.k:0;
            let c1, c2, c3, c4, c5, t, score;
            let dsg = 1;

            if(ds >= 1) {
                let dgs = this.datasets.filter(value => value.id == ds);
                this.txtmsg = dgs[0].name;
                this.css = dgs[0].css;
                this.datatSetTitle = 'Auto Calculated';
            }

            if(this.h == 10) {
                let dgs = this.datasets.filter(value => value.points == 99);
                this.txtmsg = dgs[0].name;
                this.css = dgs[0].css;
            }
            else if(this.h == 18 || this.h == 20 || this.h == 6) {
                let dgs = this.datasets.filter(value => value.points == 98);
                this.txtmsg = dgs[0].name;
                this.css = dgs[0].css;
            }
            else if(this.h == 11 || this.h == 12 || this.h == 13) {
                let dgs = this.datasets.filter(value => value.points == 3);
                this.txtmsg = dgs[0].name;
                this.css = dgs[0].css;
            }
            else if(this.h == 8) {
                let dgs = this.datasets.filter(value => value.points == 5);
                this.txtmsg = dgs[0].name;
                this.css = dgs[0].css;
            }
            else {

                if(te == 0) { c1 = 0; c2 = 0; t = 0; }
                else { c2 = tc*100/te; c1 = to*100/te; t = 200;}
                
                if(cc == 0) { c3 = 0; } 
                else { c3 = rcc*100/cc; t = t + 100; }

                if(wcc == 0) { c4 = 0; } 
                else { c4 = rwcc*100/wcc; t = t + 100; }

                if(hcc == 0) { c5 = 0; } 
                else { c5 = hrcc*100/hcc; t = t + 100; }

                if(t == 0) {
                    dsg = 0;
                    this.txtmsg =  'No Connection';
                    this.css =  'badge badge-info';
                    this.datatSetTitle = 'No calls or emails';
                    return true;
                } 
                score = Math.round((c1+c2+c3+c4+c5)*100/t);
                dsg = Math.round(score/20);

                if(dsg == 0) {
                    let dgs = this.datasets.filter(value => value.points == 1);
                    this.txtmsg = dgs[0].name;
                    this.css = dgs[0].css;
                } else {
                    let dgs = this.datasets.filter(value => value.points == dsg);
                    this.txtmsg = dgs[0].name;
                    this.css = dgs[0].css;
                }
            }
        }
    },
    mounted() {
        this.selectDataset();
    }
}
</script>