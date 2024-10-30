/* eslint-disable react/prop-types */

const Th = ({ children, className }) => {
    return (
        <>
            <th className={`${className ?? ""}`}>{children}</th>
        </>
    );
};

export default Th;