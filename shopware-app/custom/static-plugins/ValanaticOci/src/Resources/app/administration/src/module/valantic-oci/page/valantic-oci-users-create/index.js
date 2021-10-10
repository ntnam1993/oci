import template from './valantic-oci-users-create.html.twig';
const { Component, Mixin } = Shopware;

Component.register('valantic-oci-users-create', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data: function() {
        return {
            ociUser: null,
            processSuccess: false,
            isLoading: false,
            repository: null,
            newUser: true,
        };
    },

    created() {
        this.repository = this.repositoryFactory.create('oci_user');
        this.getOciUser();
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        }
    },

    methods: {
        getOciUser() {
            this.ociUser = this.repository.create(Shopware.Context.api);
        },

        saveFinish() {
            this.processSuccess = false;
        },

        async onSave() {
            this.isLoading = true;

            if (!(await this.validPassword(this.ociUser))) {
                this.isLoading = false;
                return false;
            }

            this.repository
                .save(this.ociUser, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({name: 'valantic.oci.users_edit', params: {id: this.ociUser.id}});
                }).catch(() => {
                    this.createNotificationError({
                        message: this.$tc('valantic-oci.createForm.messageSaveError'),
                    });
                    this.isLoading = false;
                });
        },

        async validPassword(ociUser) {
            const { password, passwordConfirm } = ociUser;
            const passwordSet = (password || passwordConfirm);
            const passwordNotEquals = (password !== passwordConfirm);

            if (passwordSet) {
                if (passwordNotEquals) {
                    this.createNotificationError({
                        message: this.$tc('sw-customer.detail.notificationPasswordErrorMessage'),
                    });

                    return false;
                }
            }

            return true;
        },
    }
});
