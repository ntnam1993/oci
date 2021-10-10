
Shopware.Component.extend('valantic-oci-users-edit', 'valantic-oci-users-create', {
    methods: {
        getOciUser() {
            this.newUser = false;
            this.repository
                .get(this.$route.params.id, Shopware.Context.api)
                .then((entity) => {
                    this.ociUser = entity;
                });
        },

        async onSave() {
            this.isLoading = true;

            if (this.ociUser.passwordNew) {
                this.ociUser.password = this.ociUser.passwordNew;

                if (!(await this.validPassword(this.ociUser))) {
                    this.isLoading = false;
                    return false;
                }
            }

            this.repository
                .save(this.ociUser, Shopware.Context.api)
                .then(() => {
                    this.getOciUser();
                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('swag-ociUser.detail.errorTitle'),
                    message: exception
                });
            });
        },
    }
});
