import CustomerDataViewOnly from './oci-user/customer-data-view-only.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('CustomerDataViewOnly', CustomerDataViewOnly, '[customer-data-view-only]');
