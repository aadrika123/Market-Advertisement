import React, { useRef } from 'react'
import ReactToPrint from 'react-to-print-advanced'
import ComponentToPrint from './SelfApprovalForm';


function SelfApprovalIndexForm() {

    const componentRef = useRef();
    return (
        <>
            <div>

                {/* <NonBlockingLoader show={show} /> */}
                <ReactToPrint
                    trigger={() => <button className='bg-sky-200 px-4 text-lg '>print</button>}
                    content={() => componentRef.current}
                />
                <ComponentToPrint ref={componentRef} />


            </div>
        </>
    )
}

export default SelfApprovalIndexForm