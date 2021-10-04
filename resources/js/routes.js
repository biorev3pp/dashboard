import Dashboard from './components/dashboard/Dashboard.vue';
import DatasetDashboard from './components/dataset/Dashboard.vue';
import UDashboard from './components/UDashboard.vue';
import Settings from './components/Settings.vue';
import History from './components/History.vue';
import Export from './components/Export.vue';
import ExportHistory from './components/ExportHistory.vue';
import ViewExportHistory from './components/ViewExportHistory.vue';
import FiveNineCampaigns from './components/FiveNineCampaigns.vue';
import FiveNineDispositions from './components/FiveNineDispositions.vue';
import FiveNineAllListData from './components/FiveNineAllListData.vue';
import FiveNineSkills from './components/FiveNineSkills.vue';
import FiveNineList from './components/FiveNineList.vue';
import FiveNineModifyContacts from './components/FiveNineModifyContacts.vue';
import FiveNineCallReport from './components/FiveNineCallReport.vue';
import FiveNineCallReportOne from './components/FiveNineCallReportOne.vue';
import OutreachAccountsDetails from './components/OutreachAccountsDetails.vue';
import OutreachAccountsProspects from './components/OutreachAccountsProspects.vue';
import OutreachProspects from './components/OutreachProspects.vue';
import SyncReport from './components/reports/Sync.vue';
import SyncReportDetail from './components/reports/SyncDetail.vue';

export const routes = [{
        path: '/',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
        }
    },
    {
        path: '/dashboard',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
        }
    },
    {
        path: '/universal-dashboard',
        component: UDashboard,
        meta: {
            requiresAuth: true,
            title: 'Universal Dashboard',
        }
    },
    {
        path: '/exports',
        component: Export,
        meta: {
            requiresAuth: true,
            title: 'Exports & Imports',
        }
    },
    {
        path: '/export-history',
        component: ExportHistory,
        meta: {
            requiresAuth: true,
            title: 'Export History',
        }
    },
    {
        path: '/export-history/:id',
        component: ViewExportHistory,
        meta: {
            requiresAuth: true,
            title: 'View Export History',
        }
    },
    {
        path: '/job-history',
        component: History,
        meta: {
            requiresAuth: true,
            title: 'Scheduled Job History',
        }
    },
    {
        path: '/five9-all-list-data',
        component: FiveNineAllListData,
        meta: {
            requiresAuth: true,
            title: 'Five9 All List Data',
        }
    },
    {
        path: '/five9-campaigns',
        component: FiveNineCampaigns,
        meta: {
            requiresAuth: true,
            title: 'Five9 Campaigns',
        }
    },
    {
        path: '/five9-dispositions',
        component: FiveNineDispositions,
        meta: {
            requiresAuth: true,
            title: 'Five9 Dispositions',
        }
    },
    {
        path: '/five9-skills',
        component: FiveNineSkills,
        meta: {
            requiresAuth: true,
            title: 'Five9 Skills',
        }
    },
    {
        path: '/five9-lists',
        component: FiveNineList,
        meta: {
            requiresAuth: true,
            title: 'Five9 Lists',
        }
    },
    {
        path: '/five9-modify-contacts',
        component: FiveNineModifyContacts,
        meta: {
            requiresAuth: true,
            title: 'Five9 Modify Contacts',
        }
    },
    {
        path: '/five9-modify-contacts/:name',
        component: FiveNineModifyContacts,
        meta: {
            requiresAuth: true,
            title: 'Five9 Modify Contacts',
        }
    },
    {
        path: '/five9-call-report',
        component: FiveNineCallReport,
        meta: {
            requiresAuth: true,
            title: 'Five9 Call Report',
        }
    },
    {
        path: '/five9-call-report-01',
        component: FiveNineCallReportOne,
        meta: {
            requiresAuth: true,
            title: 'Five9 Call Report - 01',
        }
    },
    {
        path: '/settings',
        component: Settings,
        meta: {
            requiresAuth: true,
            title: 'Settings',
        }
    },
    {
        path: '/datasets',
        component: DatasetDashboard,
        meta: {
            requiresAuth: true,
            title: 'Dataset Groups',
        }
    },
    {
        path: '/outreach-accounts-details/:id',
        component: OutreachAccountsDetails,
        meta: {
            requiresAuth: true,
            title: 'Outreach Account Details',
        }
    },
    {
        path: '/accounts/:id',
        component: OutreachAccountsProspects,
        meta: {
            requiresAuth: true,
            title: 'Outreach Accounts Prospects',
        }
    },
    {
        path: '/prospects/:id',
        component: OutreachProspects,
        meta: {
            requiresAuth: true,
            title: 'Outreach Prospects Details',
        }
    },
    {
        path: '/log/sync-logs',
        component: SyncReport,
        meta: {
            requiresAuth: true,
            title: 'Sync Data Log',
        }
    },
    {
        path: '/log/sync-log-details',
        component: SyncReportDetail,
        meta: {
            requiresAuth: true,
            title: 'Sync Data Log Details',
        }
    }
];