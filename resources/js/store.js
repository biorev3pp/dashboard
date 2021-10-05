import { getSetting } from "./helpers/configs";
import Axios from "axios";
import axios from "axios";

const options = getSetting();

export default {
    state: {
        currentConfig: options,
        loading: false,
        user: localStorage.getItem('user'),
        status: "",
        fullmenu: false,
        datasets: [],
        datasetFilter: [],
        stages: '',
        stages_data: ''

    },
    getters: {
        authState: state => state.status,
        isLoggedIn: state => !!state.user,
        currentConfig(state) {
            return state.currentConfig;
        },
        fullmenu(state) {
            return state.fullmenu;
        },
        user: state => state.user,
        stages(state) {
            return state.stages
        },
        stages_data(state) {
            return state.stages_data
        },
        datasets(state) {
            return state.datasets
        },
        datasetFilter(state) {
            return state.datasetFilter
        },
    },
    mutations: {
        isFullMenu(state, menu) {
            state.fullmenu = menu
        },
        auth_request(state) {
            state.status = 'loading'
        },
        auth_success(state, user) {
            state.user = user
            state.status = 'success'
        },
        register_request(state) {
            state.status = 'loading'
        },
        register_success(state, user) {
            state.status = 'success'
            state.user = user
        },
        logout(state) {
            state.status = ''
            state.user = ''
        },
        SET_STAGES(state, stages) {
            state.stages = stages
        },
        SET_STAGES_DATA(state, stages) {
            state.stages_data = stages
        },
        SET_DATASETS(state, datasets) {
            state.datasets = datasets
        },
        SET_DATASET_FILTER(state, datasetFilter) {
            state.datasetFilter = datasetFilter
        }
    },
    actions: {
        async login({ commit }, user) {
            commit('auth_request');
            localStorage.setItem('user', user);
            commit('auth_success', user);
        },
        async register({ commit }, user) {
            commit('register_request');
            localStorage.setItem('user', user);
            commit('register_success', user);
        },
        async logout({ commit }) {
            localStorage.removeItem('user');
            commit('logout');
            return
        },
        setStages: async(context, payload) => {
            let { data } = await axios.get('/api/get-all-stages');
            context.commit('SET_STAGES', data);
        },
        setStagesData: async(context, payload) => {
            let { data } = await axios.get('/api/get-stages-data');
            context.commit('SET_STAGES_DATA', data);
        },
        setDatasets: async(context, payload) => {
            let { data } = await axios.get('/api/get-all-datasets');
            context.commit('SET_DATASETS', data);
        },
        setDatasetFilter: async(context, payload) => {
            let { data } = await axios.get('/api/get-all-filter-dataset');
            context.commit('SET_DATASET_FILTER', data.items);
        },
        isFullMenu({ commit }, menu) {
            commit('isFullMenu', menu);
        }
    }
};