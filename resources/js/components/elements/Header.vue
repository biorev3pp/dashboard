<template>
    <div>
        <nav class="navbar-light" :class="[(fullmenu)?'':'navbar-light-expand']">
            <div class="row m-0">
                <div class="col-7 p-0">
                    <button class="side-menu-toggler" type="button" @click="toggleMenu">
                        <i class="bi bi-list bt-icon"></i>
                    </button>
                    <select  v-model="dash" class="dash-select" v-if="title == 'Dashboard'" @change="chageDashabord">
                        <option value="">Prospect </option>
                        <option value="calls">Calls </option>
                        <option value="agents">Agents </option>
                        <option value="emails">Emails </option>
                    </select>
                    <span class="page-title" v-html="title"></span>
                </div>
                <div class="col-5 text-right">
                    <ul class="top-menu">
                        <li>
                            <a href="javascript:;" class="text-secondary notification">
                                <i class="bi bi-sticky"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;" class="text-secondary notification">
                                <i class="bi bi-bell"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;" class="text-secondary notification">
                                <i class="bi bi-exclamation-triangle"></i>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown profile-dropdown">
                                <a class="d-block" href="javascipt:void(0)" role="button" data-toggle="dropdown">
                                     <i class="bi bi-person-circle"></i>
                                    <span v-show="desktopview >= 1">Hi Admin,</span>
                                </a>
                                <div class="dropdown-menu text-left p-0 logoutbox">
                                    <router-link to="/" class="dropdown-item border-bottom">
                                        <i class="bi bi-pencil-square"></i> <span>Profile </span> 
                                    </router-link>
                                    <router-link to="/" class="dropdown-item border-bottom">
                                        <i class="bi bi-tools"></i> <span>Settings </span> 
                                    </router-link>
                                    <button type="button" class="dropdown-item text-danger" @click="logout()">
                                        <i class="bi bi-power"></i> <span>Logout</span> </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="menu-backdrop" @click="toggleMenu" v-if="desktopview == 0 && fullmenu"></div>
        <aside :class="[(fullmenu)?'side-menu':'side-menu side-menu-condensed']">
            <div class="logo">
                <router-link to="/dashboard/" class="navbar-brand" >
                    <img :src="currentConfig.company_logo" :alt="currentConfig.company_name" width="160px" v-if="fullmenu"> 
                    <img :src="currentConfig.company_icon" :alt="currentConfig.company_name" width="33px" v-else> 
                </router-link>
            </div>
            <ul class="side-menu-list">
                <li class="side-menu-item">
                    <router-link to="/dashboard/" class="side-menu-link" @click="this.dash = ''"> 
                        <i class="bi bi-speedometer2 mr-1"></i>
                        <span v-show="fullmenu">Dashboard</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link to="/universal-dashboard" class="side-menu-link"> 
                        <i class="bi bi-view-list mr-1"></i>
                        <span v-show="fullmenu">All-In Dashboard</span>
                    </router-link>
                </li>
                 <li class="side-menu-item">
                    <router-link to="/datasets" class="side-menu-link"> 
                        <i class="bi bi-grid mr-1"></i>
                        <span v-show="fullmenu">Dataset Groups</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <a href="javascript:void(0)" class="side-menu-link has-submenu" @click="ToggleSubmenu(1)" :class="[(submenu == 1)?'active':'']"> 
                        <i class="five9-icon mr-1"></i>
                        <span v-show="fullmenu">Five9</span>
                    </a>
                    <ul class="side-submenu" v-show="submenu == 1">
                        <li>
                            <router-link to="/five9/campaigns" class="side-menu-link" @click="ToggleSubmenu(1)"> 
                                <i class="bi bi-arrow-right-short mr-1"></i>
                                <span>Campaigns</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/five9/dispositions" class="side-menu-link" @click="ToggleSubmenu(1)"> 
                                <i class="bi bi-arrow-right-short mr-1"></i>
                                <span>Dispositions</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/five9/skills" class="side-menu-link" @click="ToggleSubmenu(1)"> 
                                <i class="bi bi-arrow-right-short mr-1"></i>
                                <span>Skills</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/five9/lists" class="side-menu-link" @click="ToggleSubmenu(1)"> 
                                <i class="bi bi-arrow-right-short mr-1"></i>
                                <span>List</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/five9/modify-contacts" class="side-menu-link" @click="ToggleSubmenu(1)"> 
                                <i class="bi bi-arrow-right-short mr-1"></i>
                                <span>Modify Contacts</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/five9/call-report" class="side-menu-link" @click="ToggleSubmenu(1)"> 
                                <i class="bi bi-arrow-right-short mr-1"></i>
                                <span>Call Reports</span>
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li class="side-menu-item">
                    <router-link to="/exports" class="side-menu-link"> 
                        <i class="bi bi-arrow-bar-right mr-1"></i>
                        <span v-show="fullmenu"> Export &amp; Import</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link to="/export-history" class="side-menu-link"> 
                        <i class="bi bi-handbag mr-1"></i>
                        <span v-show="fullmenu"> Export History</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/job-history" class="side-menu-link"> 
                        <i class="bi bi-clock-history mr-1"></i>
                        <span v-show="fullmenu"> Scheduled Jobs</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/log/sync-logs" class="side-menu-link"> 
                        <i class="bi bi-layout-text-window-reverse  mr-1"></i>
                        <span v-show="fullmenu"> Sync Log</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/cron-jobs" class="side-menu-link"> 
                        <i class="bi bi-hourglass-split  mr-1"></i>
                        <span v-show="fullmenu"> Cron Jobs</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/data-enrichment" class="side-menu-link"> 
                        <i class="bi bi-clipboard-data  mr-1"></i>
                        <span v-show="fullmenu"> Data enrichment</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/data-health" class="side-menu-link"> 
                        <i class="bi bi-activity mr-1"></i>
                        <span v-show="fullmenu"> Data Health</span>
                    </router-link>
                </li>
                <li class="side-menu-item">
                    <router-link to="/settings" class="side-menu-link"> 
                        <i class="bi bi-gear mr-1"></i>
                        <span v-show="fullmenu">Configuration</span>
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
            dash:'',
            fullmenu:false,
            desktopview: 2,
            submenu:0
        }
    },
    computed: {
        currentConfig() {
            return this.$store.getters.currentConfig
        },
        title() {
            return this.$route.meta.title
        },
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
        ToggleSubmenu(sm) {
            if(this.submenu == sm) {
                this.submenu = 0;
            } else {
                this.submenu = sm;
            }
        },
        chageDashabord() {
            this.$router.push({ path: '/dashboard/'+this.dash })
        },
        logout() {
            this.$swal({
                title: 'Are you sure?',
                text: "You want lo logout !!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Please Logout!'
                }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/logout', {}).then(() => {location.reload();});
                }
            })
        }
    },
    beforeMount() {
       // this.toggleMenu();
        this.submenu = (this.$route.meta.parent >= 1)?this.$route.meta.parent:0;
    },
    mounted() {
        
    },
    created() {
        window.addEventListener('resize', this.handleResize);
        this.handleResize();
        if(this.$route.meta.hasOwnProperty('subtitle')) {
           this.dash = (this.$route.meta.subtitle == 'prospect')?'':this.$route.meta.subtitle
         //  this.chageDashabord()
        } else {
            this.dash = ''
        }
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
    },
}
</script>