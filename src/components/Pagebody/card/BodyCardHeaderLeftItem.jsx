/* eslint-disable react/prop-types */

const BodyCardHeaderLeftItem = ({ children, className }) => {
    return (
        <div className={`bodyCard__header__left__item ${className ?? ""}`}>
            {children}
        </div>
    );
};

export default BodyCardHeaderLeftItem;