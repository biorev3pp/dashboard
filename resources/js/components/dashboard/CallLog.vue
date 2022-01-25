<template>
    <span class="stack-row d-block" :class="[(title)?'active '+calledGreen:calledGreen]">
        <span class="span-title" v-title="title?title:''">{{  label }}</span>
        <em>
            <i :class="(title && (call >= 1))?'cactive':'deactive'" class="status-icon bi bi-telephone-forward-fill"></i>
            <span v-show="title && call >= 1" class="topedge">{{ call }}</span>
        </em>
        <em>
            <i :class="(title && (rcall >= 1))?'cactive':'deactive'" class="status-icon bi bi-telephone-plus-fill"></i>
            <span v-show="title && rcall >= 1" class="topedge">{{ rcall }}</span>
        </em>
    </span>
</template>
<script>
export default {
    props: {
        'label': String,
        'title': [String, Number],
        'rcall': Number,
        'call': Number,
        'called_numbers' :Array
    },
    data() {
        return {
           
        }
    },
    computed: {
        calledGreen() {
            let nmbr;
            let str = this.title
            str = str.replace(/([-() ])+/g, '');
            if(str != null && str != 0 && str != 'undefined' && str != '') {
                let fst_str = str.substr(0, 1);
                const regex = / /gi;
                str = str.replace(regex, '');
                if(fst_str == '+') {
                    str = str.substr(1, str.length-1);
                }
                fst_str = str.substr(0, 1);
                if(fst_str == 1) {
                    str = str.substr(1, str.length-1);
                }
                str = str.substr(0, 10);
                if(str.length != 10) {
                    nmbr = 0;
                } else {
                    nmbr = parseInt(str);
                }
            } else {
                nmbr = 0;
            }
            if(this.called_numbers.indexOf(nmbr) >= 0) {
                return 'bg-success text-white'
            } else {
                return '';
            }
        },
    },
    methods: {
        
        
        
    },
    mounted() {

}
}
</script>