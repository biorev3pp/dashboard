<template>
    <span class="stack-row d-block" :class="[(title)?'active':'', (rcall >= 1)?'bg-warning':'']">
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
    },
    data() {
        return {
           
        }
    },
    computed: {
        
    },
    methods: {
        
        
        
    },
    mounted() {

}
}
</script>