import Plugin from 'src/plugin-system/plugin.class';

export default class CustomerDataViewOnly extends Plugin {
    init() {
        this.forms = this.el.parentElement.getElementsByTagName('form');
        this._setViewOnlyFormData();
    }

    _setViewOnlyFormData() {
        Array.from(this.forms).forEach(form => {
            Array.from(form.elements).forEach(formElement => {
                formElement.disabled = true
            });
        })
    }
}