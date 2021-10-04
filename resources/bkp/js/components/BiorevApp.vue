<template>
    <div>
      <Header ref="menu" />
        <main :class="[(fullmenu)?'main-content':'main-content main-content-expand']">
            <vue-progress-bar></vue-progress-bar>
            <transition name="fade" mode="out-in">
                 <router-view></router-view>
            </transition>
        </main>
        <Footer />
    </div>
</template>
<style lang="scss">
.fade-enter-active {
  transition: all .4s ease;
}
.fade-leave-active {
  transition: all .4s ease;
}
.fade-enter
/* .slide-fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
 .fade-leave-to
/* .slide-fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
<script>
    import Footer from './elements/Footer.vue';
    import Header from './elements/Header.vue';
    import {mapGetters} from 'vuex';
    export default {
        name: 'biorev-app',
        components: {Header, Footer},
        data() {
            return {
              
            }
        },
        computed: {
          ...mapGetters(['fullmenu']),
        },
        methods:{
            
        },
        mounted() {
        }
    }
</script>
