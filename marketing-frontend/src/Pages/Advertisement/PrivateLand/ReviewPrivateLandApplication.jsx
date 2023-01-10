import React from 'react'

function ReviewPrivateLandApplication(props) {
    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600"
    let inputStyle = "mt-6 -ml-7 text-md text-gray-800 font-bold"

    const submitForm = () => {
        props.submitFun()
    }
    console.log("data in review form...", props.allFormData)


    return (
        <div>
            {/* <div className=' -mt-[39rem]   border border-dashed border-violet-800'>
                <div class=" p-1 mt-3 bg-white w-5/6 mx-auto">
                    <h1 className='text-center text-lg ml-12 text-gray-600 font-sans font-semibold'>REVIEW APPLICATION</h1>
                </div>
                <div >
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 ga  pl-40 '>
                        <div className=''>
                            <p className={`${labelStyle}`}>License From</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.licenseYear}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>License To </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.applicantName}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Applicant </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.fatherName}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Father Name</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.email}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1  pl-40 '>
                        <div className=''>
                            <p className={`${labelStyle}`}>Residence Address</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.residenceAddress}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Residence Ward No </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.residenceWardNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Permanent Address </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.permanentAddress}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Permanent Ward No </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.permanentWardNo}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1  pl-40 '>
                        <div className=''>
                            <p className={`${labelStyle}`}>Mobile No. </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.mobileNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Aadhar No</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.aadharNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>E-mail </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.entityName}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Entity Name</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.entityAddress}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1  pl-40 '>
                       
                        <div className=''>
                            <p className={`${labelStyle}`}>GST No</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.entityWardNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Vehicle No</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.installationLocation}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Vehicle Name</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.brandDisplayName}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Vehicle Type</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.holdingNo}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1  pl-40 '>
                        <div className=''>
                            <p className={`${labelStyle}`}>Brand in Vehicle</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.tradeLicenseNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Front Area(Sq ft)</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.gstNo}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Rear Area(Sq ft) </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.displayArea}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Side 1 Area(Sq ft)</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.displayType}</span>
                        </div>
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1 mb-3 pl-40 '>
                        <div className=''>
                            <p className={`${labelStyle}`}>Top Area(Sq ft) </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.longitude}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Display Type </p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.latitude}</span>
                        </div>
                        <div className=''>
                            <p className={`${labelStyle}`}>Trade License No</p>
                            <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.latitude}</span>
                        </div>
                    </div>
                </div>
            </div> */}
            <div className="grid grid-cols-12 w-full p-3 -mt-96">
                <div className='md:pl-0 col-span-6'>
                    <button type="button" class="py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(3)}>back</button>

                </div>
                <div className='col-span-6'>
                    <button type="submit" class="float-right py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0" onClick={submitForm}>Submit</button>

                </div>
            </div>
        </div>
    )
}

export default ReviewPrivateLandApplication