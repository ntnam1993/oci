import deDE from './snippet/de-DE';
import enGB from './snippet/en-GB';

import './page/valantic-oci-users-list';
import './page/valantic-oci-users-create';
import './page/valantic-oci-users-edit';

Shopware.Module.register('valantic-oci', {
    type: 'plugin',
    name: 'Valantic OCI Integration',
    title: 'valantic-oci.general.mainMenuItemGeneral',
    description: 'valantic-oci.general.descriptionTextModule',
    color: '#ff4b4b',
    icon: 'default-view-split',
    settingsItem: {
        group: 'shop',
        to: 'valantic.oci.settings',
        icon: 'default-view-split'
    },
    navigation: [{
        label: 'OCI Users',
        color: '#ff4b4b',
        path: 'valantic.oci.users_list',
        icon: 'default-view-split',
        parent: 'sw-customer',
        position: 100
    }],

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },
    routes: {
        settings: {
            component: 'valantic-oci-settings',
            redirect: 'sw/extension/config/ValanticOci'
        },
        users_list: {
            component: 'valantic-oci-users-list',
            path: 'users/list'
        },
        users_edit: {
            component: 'valantic-oci-users-edit',
            path: 'users/edit/:id',
            meta: {
                parentPath: 'valantic.oci.users_list'
            }
        },
        users_create: {
            component: 'valantic-oci-users-create',
            path: 'users/create',
            meta: {
                parentPath: 'valantic.oci.users_list'
            }
        }
    }
});
