// Page set for the dark-mode token pass. `prep` runs in the page before measuring.
window.DM_PAGES = [
    { name: 'dashboard', url: '/backend' },
    { name: 'records-create', url: '/backend/winter/test/records/create' },
    { name: 'records-list', url: '/backend/winter/test/records' },
    { name: 'people-list', url: '/backend/winter/test/people' },
    { name: 'people-update', url: '/backend/winter/test/people/update/1' },
    { name: 'gallery-update', url: '/backend/winter/test/galleries/update/1' },
    { name: 'cms', url: '/backend/cms', prep: 'openFileList' },
    { name: 'pages', url: '/backend/winter/pages', prep: 'openTree' },
    { name: 'media', url: '/backend/backend/media' },
    { name: 'settings', url: '/backend/system/settings' },
    { name: 'brand', url: '/backend/system/settings/update/backend/brandsettings' },
    { name: 'eventlogs', url: '/backend/system/eventlogs' },
    { name: 'eventlog-preview', url: '/backend/system/eventlogs/preview/2035' },
    { name: 'admins', url: '/backend/backend/users' },
    { name: 'myaccount', url: '/backend/backend/users/myaccount' },
    { name: 'updates', url: '/backend/system/updates' },
    { name: 'mailtemplates', url: '/backend/system/mailtemplates' },
    { name: 'blog-posts', url: '/backend/winter/blog/posts' },
    { name: 'blog-post', url: '/backend/winter/blog/posts/update/1' },
    { name: 'translate-messages', url: '/backend/winter/translate/messages' },
    { name: 'users-list', url: '/backend/winter/user/users' },
    { name: 'user-update', url: '/backend/winter/user/users/update/1' },
    { name: 'builder', url: '/backend/winter/builder' },
    { name: 'easyforms', url: '/backend/luketowers/easyforms/forms/update/1' },
    { name: 'redirect-stats', url: '/backend/winter/redirect/statistics' },
];

window.DM_PREP = {
    async openFileList() {
        const items = [...document.querySelectorAll('[data-control="filelist"] li.item a')].slice(0, 2);
        for (const a of items) { a.click(); await new Promise((r) => setTimeout(r, 1800)); }
    },
    async openTree() {
        const items = [...document.querySelectorAll('.control-treeview li > div > a')].slice(0, 2);
        for (const a of items) { a.click(); await new Promise((r) => setTimeout(r, 2000)); }
    },
};
