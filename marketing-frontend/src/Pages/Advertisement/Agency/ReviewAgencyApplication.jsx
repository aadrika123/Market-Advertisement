import React from 'react'

function ReviewPrivateLandApplication(props) {
    let labelStyle = " text-xs text-gray-600"
    let inputStyle = " text-md text-gray-800 font-bold"

    const submitForm = () => {
        props.submitFun()
    }
    console.log("data in review form...", props?.allFormData)


    return (
        <div>
            {/* <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 -mt-[34rem] '>
                <div className='col-span-7 border border-dashed border-violet-800 '>
                    <h1 className='text-center p-3 mb-2 bg-white text-gray-600 font-sans font-semibold'>APPLICATION DETAILS</h1>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>
                        <div className=''>
                            <p className={`${labelStyle}`}>Ulb</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Entity Type</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Entity Name</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Address</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>
                        <div className=''>
                            <p className={`${labelStyle}`}>PAN No.</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Email</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Mobile No</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Official Telephone</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                        <div className=''>
                            <p className={`${labelStyle}`}>FAX</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>

                        <div className=''>
                            <p className={`${labelStyle}`}>GST No.</p>
                            <span className={`${inputStyle}`}>tygiu</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                        <div className=''>
                            <p className={`${labelStyle}`}>Blacklisted in RMC</p>
                            <span className={`${inputStyle}`}>yes</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Pending Court Case</p>
                            <span className={`${inputStyle}`}>yes</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Pending Amount (If any)</p>
                            <span className={`${inputStyle}`}>500.00</span>
                        </div>
                    </div>
                    <table class="table-auto text-slate-700 w-10/12 mx-auto mt-4 ">
                        <thead>
                            <tr className="bg-violet-200 text-gray-600 text-xs h-8  uppercase">
                                <th>Director Name</th>
                                <th>Director Mobile No.</th>
                                <th>Director Email</th>

                            </tr>
                        </thead>
                        <tbody>

                            <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                <td>
                                    <span>fvgfd </span>
                                </td>
                                <td>
                                    <span>fgvfd</span>
                                </td>
                                <td>
                                    <span>dfgvdf</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                document
                <div className='col-span-5 border border-dashed border-violet-800'>
                    <h1 className='text-center p-3 mb-2 bg-white text-gray-600 font-sans font-semibold '>DOCUMENTS UPLOADED</h1>

                    <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 p-2 ml-12'>
                        <div className=''>
                            <p className={`${labelStyle}`}>Aadhar Document</p>
                            <div className='flex'>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                            </div>
                        </div>
                        <div className='mt-4'>
                            <p className={`${labelStyle}`}>Trade License</p>
                            <div className='flex'>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                            </div>
                        </div>
                        <div className='mt-4'>
                            <p className={`${labelStyle}`}>GPS Mapped Camera</p>
                            <div className='flex'>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                            </div>
                        </div>
                        <div className='mt-4'>
                            <p className={`${labelStyle}`}>Holding No. Photograph</p>
                            <div className='flex'>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                            </div>
                        </div>
                        <div className='mt-4'>
                            <p className={`${labelStyle}`}>GST Document</p>
                            <div className='flex'>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                            </div>
                        </div>
                        <div className='mt-4'>
                            <p className={`${labelStyle}`}>Brand Display Permission</p>
                            <div className='flex'>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                                <span className={`${inputStyle} flex-1`}>ffcgb</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> */}
            <div className="grid grid-cols-12 w-full p-3 ">
                <div className='md:pl-0 col-span-6'>
                    <button type="button" className=" px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out" onClick={() => props.backFun(4)}>back</button>
                </div>
                <div className='col-span-6'>
                    <button type='submit' className="float-right px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0  active:shadow-lg transition duration-150 ease-in-out" onClick={submitForm}>Submit</button>
                </div>
            </div>
        </div>
    )
}

export default ReviewPrivateLandApplication