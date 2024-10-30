/* eslint-disable react/prop-types */

const FormWrapper = ({ action, formClass, children, wrapperClass, ...props }) => {
    return (
        <div className={`formWrapper ${wrapperClass ?? ''}`}>
            <form action={action} className={formClass} {...props}>
                {children}
            </form>
        </div>
    );
};

export default FormWrapper;