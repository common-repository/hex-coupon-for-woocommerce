import React from 'react';
import CountUp from 'react-countup';
const Counter = ({ leftIcon, start, end, duration, separator, rightIcon, counterPara, isAllowedDecimal }) => (
    <div className="hexpSingle__promo radius-10">
        <h2 className="hexpSingle__promo__title">
            {leftIcon && <span>{leftIcon}</span>}
            <CountUp start={start} end={end} duration={duration} separator={separator} decimals={isAllowedDecimal && 2}/>
            {rightIcon && <span>{rightIcon}</span>}
        </h2>
        <p className="hexpSingle__promo__para mt-2">{counterPara}</p>
    </div>
);
export default Counter;
