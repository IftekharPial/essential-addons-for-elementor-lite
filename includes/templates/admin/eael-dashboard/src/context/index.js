import {createContext, useContext} from "react";

const context = createContext();
const consumer = () => {
    return useContext(context);
}

const eaData = localize.eael_dashboard,
    licenseData = typeof wpdeveloperLicenseData === 'undefined' ? {} : wpdeveloperLicenseData,
    initValue = {
        menu: 'General',
        integrations: {},
        extensions: [],
        widgets: {},
        elements: {},
        proElements: [],
        extensionAll: false,
        widgetAll: false,
        licenseStatus: licenseData?.license_status,
        hiddenLicenseKey: licenseData?.hidden_license_key,
        modals: {},
        elementsActivateCatIndex: 0,
        isDark: false,
        isTemplatelyInstalled: eaData.is_templately_installed
    };

Object.keys(eaData.integration_box.list).map((item) => {
    initValue.integrations[item] = eaData.integration_box.list[item].status;
});

Object.keys(eaData.extensions.list).map((item) => {
    initValue.extensions.push(item);
    initValue.elements[item] = eaData.extensions.list[item].is_activate;

    // set false for pro elements if ea pro is not activated
    if (!eaData.is_eapro_activate && eaData.extensions.list[item].is_pro) {
        initValue.proElements.push(item);
        initValue.elements[item] = false
    }
});

Object.keys(eaData.widgets).map((item) => {
    initValue.widgets[item] = [];
    Object.keys(eaData.widgets[item].elements).map((subitem) => {
        initValue.widgets[item].push(subitem);
        initValue.elements[subitem] = eaData.widgets[item].elements[subitem].is_activate;

        // set false for pro elements if ea pro is not activated
        if (!eaData.is_eapro_activate && eaData.widgets[item].elements[subitem].is_pro) {
            initValue.proElements.push(subitem);
            initValue.elements[subitem] = false
        }
    });
});

Object.keys(eaData.modal).map((item) => {
    const key = eaData.modal[item]?.name;
    if (key !== undefined) {
        initValue.modals[key] = eaData.modal[item].value;
    } else if (item === 'loginRegisterSetting') {
        const accordion = eaData.modal[item].accordion;
        Object.keys(accordion).map((subItem) => {
            accordion[subItem].fields.map((childItem) => {
                const key = childItem?.name;
                if (key !== undefined) {
                    initValue.modals[key] = childItem?.value;
                }
            });
        });
    }
});

export const ContextProvider = context.Provider;
export {initValue};
export default consumer