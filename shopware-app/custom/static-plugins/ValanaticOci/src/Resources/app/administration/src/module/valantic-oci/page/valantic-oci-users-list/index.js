const { Criteria } = Shopware.Data;
import template from './valantic-oci-users-list.html.twig';

Shopware.Component.register('valantic-oci-users-list', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    data: function () {
        return {
            repository: null,
            ociUsers: null,
        }
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('oci_user');

        const criteria = new Criteria();
        criteria.addAssociation('customer');

        this.repository
            .search(criteria, Shopware.Context.api)
            .then(result => {
                this.ociUsers = result;
            });
    },

    computed: {
        columns() {
            return [{
                property: 'customer',
                dataIndex: 'customer.lastName,customer.firstName',
                label: this.$tc('valantic-oci.createForm.labelCustomer'),
                allowResize: true,
            },{
                property: 'customer.company',
                label: this.$tc('valantic-oci.list.labelCompany'),
                allowResize: true,
            }, {
                property: 'name',
                label: this.$tc('valantic-oci.createForm.labelName'),
                routerLink: 'valantic.oci.users_edit',
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'active',
                label: this.$tc('valantic-oci.createForm.labelActive'),
                inlineEdit: 'boolean',
                allowResize: true,
            }];
        }
    },
});
