import React from 'react';

const HexCardHeaderLeft = (props) => {
    const {children} = props;
    return (
        <>
            <div className="hexDashboard__card__header__left">
                {children}
            </div>
        </>
    );
};

export default HexCardHeaderLeft;