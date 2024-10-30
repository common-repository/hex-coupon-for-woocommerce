/* eslint-disable react/prop-types */

const BodyCardHeaderRightItem = ({ children, className }) => {
    return (
        <div className={`bodyCard__header__right__item ${className ?? ""}`}>
            {children}
        </div>
    );
};

export default BodyCardHeaderRightItem;