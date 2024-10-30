/* eslint-disable react/no-children-prop */
/* eslint-disable react/prop-types */
import { MdClose } from "react-icons/md";

const Modal = ({ isOpen, onClose, modalTitle, modalPara, children, modalPosition, modalSize, ...restProps }) => {
    if (!isOpen) {
        return null;
    }

    return (
        <>
            <div className="modalOverlay" onClick={onClose}></div>

            <div className={`modalWrap ${modalPosition ?? ''}`} {...restProps}>
                <div className={`modalWrap__inner ${modalSize ?? ''}`}>
                    <div className="modalWrap__header">
                        <div className="modalWrap__header__left">
                            <h4 className="modalWrap__header__title">{modalTitle ?? ''}</h4>
                            {modalPara && <p className="modalWrap__header__para mt-1">{modalPara}</p>}
                        </div>
                        <div className="modalWrap__close" onClick={onClose}><MdClose /></div>
                    </div>
                    <div className="modalWrap__body">
                        {children}
                    </div>
                </div>
            </div>
        </>
    );
};

export default Modal;