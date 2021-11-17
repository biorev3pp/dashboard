<template>
    <div class="synops">          
        <div class="inner-synop cursor-pointer">
            <h4 class="number">{{ total | freeNumber }} </h4><p class="text-success">Total</p>
        </div>
        <div class="inner-synop cursor-pointer" v-for="(datatset, i) in datasets"  :key="'row-'+i" :class="[(datatset.id == activeDataset)?'active':'']">
            <h4 class="number">
                <img :src="loader_url" alt="Loading..." v-if="loader">
                <span v-else>
                    <span v-if="dcount.length >= 1">
                        {{ dcount[datatset.id]  | freeNumber  }}
                    </span>
                    <span v-else>
                        --
                    </span>
                </span>
            </h4><p>{{ datatset.name }}</p>
        </div>
        <div class="inner-synop cursor-pointer">
            <h4 class="number">
                <img :src="loader_url" alt="Loading..." v-if="loader">
                <span v-else>
                    {{ dtotal | freeNumber }}
                </span>
            </h4><p class=" text-danger">No Connection</p>
        </div>
        <div class="inner-synop cursor-pointer text-primary">
            <h4 class="number">
                <img :src="loader_url" alt="Loading..." v-if="loader">
                <i class="bi bi-bootstrap-reboot" v-else @click="resetDataset"></i>    
            </h4><p class=" text-primary">REFRESH</p>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        'total': [String, Number],
        'activeDataset': Number,
    },
    data() {
        return {
            loader:false,
            loader_url: '/img/spinner.gif',
            dcount:[],
            dtotal:0
        }
    },
    computed: {
        datasets() {
            return this.$store.getters.datasets
        },
        
    },
    methods: {
        resetDataset() {
            this.loader = true;
            axios.post('/api/reset-dataset').then((response) => {
                this.dcount = response.data.results;
                this.dtotal = response.data.total;
                this.loader = false;
           //     this.$parent.getDatasets(1);
            });
        }
    },
    created() {
        axios.get('/api/get-dataset').then((response) => {
            this.dcount = response.data.results;
            this.dtotal = response.data.total;
            this.loader = false;
        });
    }
}
</script>