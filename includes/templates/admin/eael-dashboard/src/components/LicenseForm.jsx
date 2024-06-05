import {useRef} from "react";
import consumer from "../context";

function LicenseForm() {
    const licenseRef = useRef(),
        {eaState, eaDispatch} = consumer(),
        submitHandler = () => {
            eaDispatch({type: 'ON_PROCESSING', payload: {licenseBtn: true}});

            if (eaState.licenseStatus !== 'valid') {
                eaDispatch({type: 'LICENSE_ACTIVATE', payload: licenseRef.current.value});
            } else {
                eaDispatch({type: 'LICENSE_DEACTIVATE'});
            }
        },
        disabled = eaState.otp === true || eaState.licenseStatus === 'valid';

    return (
        <>
            <div className="ea__license-key">
                <div className="license-key-items flex items-center">
                    <i className="ea-dash-icon  ea-key"></i>
                    <input ref={licenseRef} disabled={disabled} className="input-api" type="text"
                           placeholder={eaState.hiddenLicenseKey || "Place Your License Key and Active"}/>
                    <button
                        className={eaState.licenseStatus === 'valid' ? 'primary-btn install-btn deactivated' : 'primary-btn install-btn'}
                        onClick={submitHandler}
                        disabled={eaState.otp === true}>{eaState.licenseStatus === 'valid' ? "Deactivate" : "Active License"}
                    </button>
                </div>
            </div>
        </>
    );
}

export default LicenseForm;