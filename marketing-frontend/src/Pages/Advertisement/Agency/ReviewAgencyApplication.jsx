import React from 'react'

function ReviewPrivateLandApplication(props) {

    const { setFormIndex, showLoader, collectFormDataFun, toastFun } = props?.values

    let labelStyle = " text-xs text-gray-600"
    let inputStyle = " text-md text-gray-800 font-bold"

    const submitForm = () => {
        props.submitFun()
    }
    console.log("data in review form...", props?.allFormData)
    console.log("data in review form master id...", props?.reviewIdNameData)


    return (
        <div className='absolute w-full top-4 '>
            <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 '>
                <div className='col-span-7 border border-dashed border-violet-800 '>
                    <h1 className='text-center p-3 mb-2 bg-white text-gray-600 font-sans font-semibold'>APPLICATION DETAILS</h1>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>
                        <div className=''>
                            <p className={`${labelStyle}`}>Ulb</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.ulb}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Entity Type</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.entityType}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Entity Name</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.entityName}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Address</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.address}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>
                        <div className=''>
                            <p className={`${labelStyle}`}>PAN No.</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.panNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Email</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.email}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Mobile No</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.mobileNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Official Telephone</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.officialTelephone}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                        <div className=''>
                            <p className={`${labelStyle}`}>FAX</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.fax}</span>
                        </div>

                        <div className=''>
                            <p className={`${labelStyle}`}>GST No.</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.gstNo}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                        <div className=''>
                            <p className={`${labelStyle}`}>Blacklisted in RMC</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.blacklisted == true ? 'Yes' : 'No'}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Pending Court Case</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.pendingCourtCase == true ? 'Yes' : 'No'}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Pending Amount (If any)</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.agency?.pendingAmount}</span>
                        </div>
                    </div>
                    <div className='mt-4'>
                        <h1 className='px-10 text-md text-gray-800 font-bold'>DIRECTORS INFORMATION</h1>
                        <table class="table-auto text-slate-700 w-11/12 mx-auto mt-1 mb-4">
                            <thead>
                                <tr className="bg-violet-200 text-gray-600 text-xs h-8  uppercase">
                                    <th>Director Name</th>
                                    <th>Director Mobile No.</th>
                                    <th>Director Email</th>

                                </tr>
                            </thead>
                            <tbody>

                                {props?.allFormData?.agencyDirector?.map((data) => (
                                    <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                        <td>
                                            <span>{data?.name} </span>
                                        </td>
                                        <td>
                                            <span>{data?.mobile}</span>
                                        </td>
                                        <td>
                                            <span>{data?.email}</span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                </div>


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
            </div>
            <div className="grid grid-cols-12 w-full p-3 ">
                <div className='md:pl-0 col-span-6'>
                    <button type="button" className=" px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out" onClick={() => setFormIndex(3)}>back</button>
                </div>
                <div className='col-span-6'>
                    <button type='submit' className="float-right px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0  active:shadow-lg transition duration-150 ease-in-out" onClick={submitForm}>Submit</button>
                </div>
            </div>
        </div>
    )
}

export default ReviewPrivateLandApplication