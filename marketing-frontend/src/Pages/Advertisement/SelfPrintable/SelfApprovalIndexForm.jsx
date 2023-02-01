import React, { useRef } from 'react'
import ReactToPrint from 'react-to-print-advanced'
import ComponentToPrint from './SelfApprovalForm'

function SelfApprovalIndexForm() {

    const componentRef = useRef();
    return (
        <>
            <div>
                {/* <NonBlockingLoader show={show} /> */}
                <ReactToPrint
                    trigger={() => <button></button>}
                    content={() => componentRef.current}
                />
                <ComponentToPrint ref={componentRef}  />
            </div>
        </>
    )
}

export default SelfApprovalIndexForm