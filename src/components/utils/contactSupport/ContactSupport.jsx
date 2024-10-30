import { Link } from 'react-router-dom';
import '../../../assets/sass/component/contact_support.scss'

import { MdOutlineHeadsetMic } from "react-icons/md";


const ContactSupport = () => {
    return (
        <div className="contactSupport">
            <div className="contactSupport__box">
                <div className="contactSupport__flex">
                    <div className="contactSupport__icon"><MdOutlineHeadsetMic /></div>
                    <div className="contactSupport__contents">
                        <span className="contactSupport__para">Need a Help? <Link to="#0" className="contactSupport__para__link">Contact Us</Link></span>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ContactSupport;