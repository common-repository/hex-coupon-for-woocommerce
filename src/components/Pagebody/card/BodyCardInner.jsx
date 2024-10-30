/* eslint-disable react/prop-types */

// import '../../../assets/sass/component/body_card_inner.scss'

const BodyCardInner = ({ children, className }) => {
    return (
        <div className={`bodyCard__inner ${className ?? ""}`}>
            {children}
        </div>
    );
};

export default BodyCardInner;