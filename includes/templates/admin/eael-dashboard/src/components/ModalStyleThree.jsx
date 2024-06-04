import consumer from "../context/index.js";

function ModalStyleThree() {
    const {eaState, eaDispatch} = consumer(),
        eaData = localize.eael_dashboard.modal[eaState.modalID],
        clickHandler = (param) => {
            eaDispatch({type: 'MODAL_ACCORDION', payload: {key: param}});
        },
        changeHandler = (e, key) => {
            eaDispatch({type: 'MODAL_ON_CHANGE', payload: {key, value: e.target.value}});
        };

    return (
        <>
            {Object.keys(eaData.accordion).map((item, index) => {
                return <div className="ea__api-key-according" key={index}>
                    <div className="ea__according-title" onClick={() => clickHandler(item)}>
                        <div className="flex justify-between items-center gap-2 mb-4 pointer">
                        <span className="flex gap-2 items-center">
                            <img src={localize.eael_dashboard.reactPath + eaData.accordion[item].icon} alt="icon"/>
                            <h4 className="flex items-center">{eaData.accordion[item].title}
                                {eaData.accordion[item]?.info !== undefined && <i className="ea-dash-icon ea-info"></i>}
                            </h4>
                        </span>
                            <i className={item === eaState.modalAccordion ? 'ea-dash-icon ea-dropdown rotate-180' : 'ea-dash-icon ea-dropdown'}></i>
                        </div>
                    </div>
                    <div
                        className={item === eaState.modalAccordion ? 'ea__according-content flex flex-col gap-2 accordion-show' : 'ea__according-content flex flex-col gap-2'}>
                        {eaData.accordion[item].fields.map((subItem, subIndex) => {
                            return (<div className="flex gap-4 items-center" key={subIndex}>
                                <label>{subItem.label}</label>
                                <input name={subItem.name} value={eaState.modals[subItem.name]}
                                       onChange={(e) => changeHandler(e, subItem.name)} className="input-name"
                                       type="text" placeholder={subItem.placeholder}/>
                            </div>);
                        })}
                    </div>
                </div>
            })}
        </>
    );
}

export default ModalStyleThree;