<template>
    <span class="stack-row d-block" :class="[(title)?'active':'']">
        <span v-title="title?title:''"> {{  label }}</span>
        <em>
            <i :class="(title && (te >= 1))?'active':'deactive'" class="status-icon bi bi-envelope-fill"></i>
            <span v-show="title && te >= 1" class="topedge">{{ te }}</span>
        </em>
        <em>
            <i :class="(title && (to >= 1))?'active':'deactive'" class="status-icon bi bi-envelope-open-fill"></i>
            <span v-show="title && to >= 1" class="topedge">{{ to }}</span>
        </em>
        <em>
            <i :class="(title && (tc >= 1))?'active':'deactive'" class="status-icon bi bi-hand-index-fill"></i>
            <span v-show="title && tc >= 1" class="topedge">{{ tc }}</span>
        </em>
        <em>
            <i :class="(title && (tr >= 1))?'active':'deactive'" class="status-icon bi bi-reply-fill"></i>
            <span v-show="title && tr >= 1" class="topedge">{{ tr }}</span>
        </em>
        <em>
            <i :class="(title && (tb >= 1))?'active':'deactive'" class="status-icon bi bi-box-arrow-up-right"></i>
            <span v-show="title && tb >= 1" class="topedge">{{ tb }}</span>
        </em>
    </span>
</template>
<script>
export default {
    props: {
        'label': String,
        'title': String,
        'te':Number,
        'to':Number,
        'tc':Number,
        'tr':Number,
        'tb':Number,
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