require('./bootstrap');

window.Vue = require('vue').default;

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

import VueRouter from 'vue-router';
import moment from 'moment';
import Vuex from 'vuex';
import numeral from 'numeral';
import { routes } from './routes';
import StoreData from './store';
import BiorevApp from './components/BiorevApp.vue';
import { setConfigs } from './helpers/general';
import { Form, HasError, AlertError } from 'vform';
import Vue2Filters from 'vue2-filters';
import VueProgressBar from 'vue-progressbar';
import vTitle from 'vuejs-title';
import vSelect from 'vue-select';
import JsonExcel from "vue-json-excel";
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { TabsPlugin, BSpinner, VBTooltip, BModal, BFormCheckbox } from 'bootstrap-vue';
import VueApexCharts from 'vue-apexcharts'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

Vue.use(TabsPlugin)
Vue.directive('b-tooltip', VBTooltip)
Vue.component('b-modal', BModal)
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('b-spinner', BSpinner)
Vue.component('b-form-checkbox', BFormCheckbox)



import VueToast from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
Vue.use(VueToast, {
    position: 'top-right',
    duration: 5000
});

Vue.use(VueSweetalert2);
window.Form = Form;
// window.swal = swal;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError)
Vue.component("downloadExcel", JsonExcel);
Vue.use(VueProgressBar, {
    color: '#38c172',
    failedColor: '#D91E18',
    thickness: '5px',
    location: 'top',
});

window.Fire = new Vue();

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(Vue2Filters);
Vue.component('v-select', vSelect)
Vue.use(vTitle, { fontSize: '10px' });

const store = new Vuex.Store(StoreData);

const router = new VueRouter({
    routes,
    mode: 'history'
});

Vue.filter('setdate', function(mydate) {
    return moment(mydate).format('DD.MM.YYYY');
});
Vue.filter('sidedate', function(mydate) {
    return moment(mydate).format('D MMM');
});
Vue.filter('setusdate', function(mydate) {
    return moment(mydate).format('MM-DD-YYYY');
});
Vue.filter('setbtdate', function(mydate) {
    return moment(mydate).format('Do MMM YY');
});
Vue.filter('setusdateSlash', function(mydate) {
    return moment(mydate).format('MM/DD/YYYY');
});
Vue.filter('logdateFull', function(mydate) {
    return moment(mydate).format('ddd, MMM DD @ hh:mm A');
});
Vue.filter('logdateYearFull', function(mydate) {
    return moment(mydate).format('ddd, MMM DD, YYYY @ hh:mm A');
});
Vue.filter('setFulldate', function(mydate) {
    moment.locale('en');
    return moment(mydate).format('MM-DD-YYYY hh:mm A');
});
Vue.filter("formatNumber", function(value) {
    return numeral(value).format("0,0.00");
});
Vue.filter("freeNumber", function(value) {
    return numeral(value).format("0,0");
});

Vue.filter('setTime', function(mydate) {
    moment.locale('en');
    return moment(mydate).format('hh:mm:ss A');
});
Vue.filter('date01', function(mydate) {
    moment.locale('en');
    return moment(mydate).format('MMM D');
});
Vue.filter('date03', function(arr1, arr2) {
    if (arr1 == null || arr2 == null) {
        return '00:00'
    }
    var arrr1 = moment(arr1)
    var arrr2 = moment(arr2)
    var secs = parseInt(arrr2.diff(arrr1) / 1000)
    var s = ''
    if (secs < 60) {
        if (secs < 10) {
            return '00:0' + secs
        } else {
            return '00:' + secs
        }
    } else {
        var min = parseInt(secs / 60)
        var sec = secs - min * 60
        if (sec < 10) {
            s = '0' + sec
        } else {
            s = sec
        }
        if (min < 10) {
            return '0' + min + ':' + s
        } else {
            return min + ':' + secs
        }
    }
    var mins = parseInt(secs / 60)
    var sec = secs - mins * 60
    return arrr2.diff(arrr1)
})
Vue.filter('date02', function(mydate) {
    moment.locale('en');
    var year = moment().year();
    var Cyear = moment(mydate).format('Y');
    if (year == Cyear) {
        return moment(mydate).format('MMM D');
    } else {
        return moment(mydate).format('MM-DD-YYYY');

    }
});
Vue.filter('convertInDayMonth', function(mydate) {
    moment.locale('en');
    if (mydate == null) {
        return '--';
    } else {
        var a = moment();
        var b = moment(mydate);

        if (Number(a.diff(b, 'hours')) > 24) {
            if (Number(a.diff(b, 'days')) > 30) {
                var months = parseInt(Math.ceil(Number(a.diff(b, 'days'))) / 30)
                var days = 1 + Math.ceil(Number(a.diff(b, 'days'))) - months * 30
                return months + 'm ' + days + 'd '
            } else {

                return Math.ceil(Number(a.diff(b, 'hours')) / 24) + 'd ' + Number(a.diff(b, 'hours')) % 24 + 'h';
            }
        } else {
            return Number(a.diff(b, 'hours')) % 24 + 'h';
        }

    }
});
Vue.filter('getStringWithSpace', function(str) {
    str = str.toUpperCase();
    str = str.split("_");
    str = str.join(" ")
    return str
});

Vue.filter('phoneFormatting', function(str) {
    if (str != null && str != 0 && str != 'undefined' && str != '') {
        let fst_str = str.substr(0, 1);
        const regex = / /gi;
        str = str.replace(/[^0-9+]/g, '')
        if (fst_str == '+') {
            str = str.substr(1, str.length - 1);
        }
        fst_str = str.substr(0, 1);
        if (fst_str == 1) {
            str = str.substr(1, str.length - 1);
        }
        str = str.substr(0, 10);

        if (str.length != 10) {
            return 0;
        } else {
            return parseInt(str);
        }
    } else {
        return 0;
    }
});


setConfigs(store);

const app = new Vue({
    el: '#app',
    router,
    store,
    components: {
        BiorevApp
    },
});