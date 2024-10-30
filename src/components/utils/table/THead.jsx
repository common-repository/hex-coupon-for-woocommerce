/* eslint-disable react/prop-types */


const THead = ({ children, className }) => {
    return (
        <thead className={className}><tr>{children}</tr></thead>
    )
};

export default THead;