import Dashboard from './components/dashboard/Dashboard.vue';
import CallDashboard from './components/dashboard/Calls.vue';
import CallDetailDashboard from './components/dashboard/DashboardCall.vue';
import EmailDashboard from './components/dashboard/Emails.vue';
import AgentDashboard from './components/dashboard/Agents.vue';
import DatasetDashboard from './components/dataset/Dashboard.vue';
import UDashboard from './components/UDashboard.vue';
import Settings from './components/Settings.vue';
import History from './components/import/History.vue';
import Export from './components/import/Export.vue';
import ExportHistory from './components/import/ExportHistory.vue';
import ViewExportHistory from './components/import/ViewExportHistory.vue';
import FiveNineCampaigns from './components/fivenine/FiveNineCampaigns.vue';
import FiveNineDispositions from './components/fivenine/FiveNineDispositions.vue';
import FiveNineAllListData from './components/fivenine/FiveNineAllListData.vue';
import FiveNineListContacts from './components/fivenine/ListProspects.vue';
import FiveNineSkills from './components/fivenine/FiveNineSkills.vue';
import FiveNineList from './components/fivenine/FiveNineList.vue';
import FiveNineModifyContacts from './components/fivenine/FiveNineModifyContacts.vue';
import FiveNineCallReport from './components/fivenine/FiveNineCallReport.vue';
import OutreachProspects from './components/outreach/OutreachProspects.vue';
import SyncReport from './components/reports/Sync.vue';
import SyncReportDetail from './components/reports/SyncDetail.vue';
import Tasks from './components/tasks/index.vue';
import ProspectsLocation from './components/ProspectsLocation.vue';
import DatabaseUpdate from './components/UpdateDatabaseFields.vue';
import EmailDetailDashboard from './components/dashboard/DashboardEmail.vue';

export const routes = [{
        path: '/',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
            subtitle: 'prospect'
        }
    },
    {
        path: '/dashboard',
        component: Dashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
            subtitle: 'prospect'
        }
    },
    {
        path: '/dashboard/calls',
        component: CallDashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
            subtitle: 'calls'
        }
    },
    ,
    {
        path: '/dashboard/agents',
        component: AgentDashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
            subtitle: 'agents'
        }
    },
    {
        path: '/dashboard/call-details',
        component: CallDetailDashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard - Call Details',
        }
    },
    {
        path: '/dashboard/emails',
        component: EmailDashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard',
            subtitle: 'emails'
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
        path: '/five9/campaigns',
        component: FiveNineCampaigns,
        meta: {
            requiresAuth: true,
            title: 'Five9 Campaigns',
        }
    },
    {
        path: '/five9/dispositions',
        component: FiveNineDispositions,
        meta: {
            requiresAuth: true,
            title: 'Five9 Dispositions',
        }
    },
    {
        path: '/five9/skills',
        component: FiveNineSkills,
        meta: {
            requiresAuth: true,
            title: 'Five9 Skills',
        }
    },
    {
        path: '/five9/lists',
        component: FiveNineList,
        meta: {
            requiresAuth: true,
            title: 'Five9 Lists',
        }
    },
    {
        path: '/five9/lists/prospects/:id',
        component: FiveNineListContacts,
        meta: {
            requiresAuth: true,
            title: 'Five9 List Based Prospects',
        }
    },
    {
        path: '/five9/modify-contacts',
        component: FiveNineModifyContacts,
        meta: {
            requiresAuth: true,
            title: 'Five9 Modify Contacts',
        }
    },
    {
        path: '/five9/modify-contacts/:name',
        component: FiveNineModifyContacts,
        meta: {
            requiresAuth: true,
            title: 'Five9 Modify Contacts',
        }
    },
    {
        path: '/five9/call-report',
        component: FiveNineCallReport,
        meta: {
            requiresAuth: true,
            title: 'Five9 Call Report',
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
        path: '/cron-jobs',
        component: Tasks,
        meta: {
            requiresAuth: true,
            title: 'Cron Jobs',
        }
    },
    {
        path: '/log/sync-log-details',
        component: SyncReportDetail,
        meta: {
            requiresAuth: true,
            title: 'Sync Data Log Details',
        }
    },
    {
        path: '/prospects-location/:id',
        component: ProspectsLocation,
        meta: {
            requiresAuth: true,
            title: 'Prospects Index New',
        }
    },
    {
        path: '/dashboard/email-details',
        component: EmailDetailDashboard,
        meta: {
            requiresAuth: true,
            title: 'Dashboard - Email Details',
        }
    },
    {
        path: '/database-update',
        component: DatabaseUpdate,
        meta: {
            requiresAuth: true,
            title: 'Data Enrichment',
        }
    },
];