<template>
    <div>
        <nav class="navbar-light" :class="[(fullmenu)?'':'navbar-light-expand']">
            <div class="row m-0">
                <div class="col-7 p-0">
                    <button class="side-menu-toggler" type="button" @click="toggleMenu">
                        <i class="bi bi-list bt-icon"></i>
                    </button>
                    <span class="page-title">
                        {{ title }}
                    </span>
                </div>
                <div class="col-5 text-right">
                    <ul class="top-menu">
                        <li>
                            <a href="javascript:;" class="text-danger notification">
                                <i class="bi bi-bell"></i>
                                <span class="badge badge-danger">5</span>
                            </a>
                        </li>
                        <li>
                            <div class="profile-dropdown">
                                <router-link class="d-block" to="/">
                                    <i class="bi bi-person-circle"></i>
                                    <span v-show="desktopview >= 1">Welcome<br>Admin,</span>
                                </router-link>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="menu-backdrop" @click="toggleMenu" v-if="desktopview == 0 && fullmenu"></div>
        <aside :class="[(fullmenu)?'side-menu':'side-menu side-menu-condensed']">
            <div class="logo">
                <router-link to="/dashboard" class="navbar-brand">
                    <img :src="currentConfig.company_logo" :alt="currentConfig.company_name" width="200px" v-if="fullmenu"> 
                    <img :src="currentConfig.company_icon" :alt="currentConfig.company_name" width="40px" v-else> 
                </router-link>
            </div>
            <ul class="side-menu-list">
                <li class="side-menu-item">
                    <router-link  to="/dashboard" class="side-menu-link"> 
                        <i class="bi bi-speedometer2 mr-2"></i>
                        <span v-show="fullmenu">Dashboard</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link  to="/outreach" class="side-menu-link"> 
                        <i class="bi bi-person-lines-fill mr-2"></i>
                        <span v-show="fullmenu">Outreach Prospects</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link  to="/automations" class="side-menu-link"> 
                        <i class="bi bi-gem mr-2"></i>
                        <span v-show="fullmenu">Automations</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link  to="/contacts" class="side-menu-link"> 
                        <i class="bi bi-person-bounding-box mr-2"></i>
                        <span v-show="fullmenu">Contacts</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link  to="/exports" class="side-menu-link"> 
                        <i class="bi bi-arrow-bar-right"></i>
                        <span v-show="fullmenu"> Export &amp; Import</span>
                    </router-link>
                </li>
                <li>
                    <router-link  to="/history" class="side-menu-link"> 
                        <i class="bi bi-clock-history"></i>
                        <span v-show="fullmenu"> Export History</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link  to="/settings" class="side-menu-link"> 
                        <i class="bi bi-gear mr-2"></i>
                        <span v-show="fullmenu">Settings</span>
                    </router-link>
                </li>
            </ul>
        </aside>
    </div>
</template>
<script>
import {mapGetters, mapActions} from 'vuex';
export default {
    data() {
        return {
            fullmenu:true,
            desktopview: 2,
        }
    },
    computed: {
        currentConfig() {
            return this.$store.getters.currentConfig
        },
        title() {
            return this.$route.meta.title
        }
    },
    methods:{
        ...mapActions(['isFullMenu']),
        toggleMenu() {
            if(this.fullmenu == true) { this.fullmenu = false; } 
            else{ this.fullmenu = true; }
            this.isFullMenu(this.fullmenu);
        },
        handleResize() {
            if(window.innerWidth <= 414) {
                this.desktopview = 0;
            }
            else if((window.innerWidth > 414) && (window.innerWidth <= 1200)) {
                this.desktopview = 1;
            } else {
                this.desktopview = 2;
            }
        },
    },
    beforeMount() {
        this.toggleMenu();
    },
    created() {
        window.addEventListener('resize', this.handleResize);
        this.handleResize();
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
    },
}
</script>