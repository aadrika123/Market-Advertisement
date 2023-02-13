import React from 'react'

function ReviewSelfAdvertForm(props) {


    const { setFormIndex, showLoader, collectFormDataFun, toastFun } = props?.values

    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600"
    let inputStyle = "mt-6 -ml-7 text-md text-gray-700 font-bold"


    const submitForm = () => {
        props.submitFun()
    }
    console.log("data in review form...", props?.allFormData?.selfAdvertisementDoc)
    console.log("select data in review form ...", props?.reviewIdNameData)

    return (
        <>
            <div className='absolute w-full top-4 '>
                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container mx-auto  '>
                    <div className='col-span-8 border border-dashed border-violet-800 '>
                        <h1 className='text-center p-3 mb-2 bg-white text-gray-600 font-sans font-semibold'>APPLICATION DETAILS</h1>
                        <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>
                            <div className=''>
                                <p className={`${labelStyle}`}>License Year</p>
                                <span className={`${inputStyle}`}>{props?.reviewIdNameData?.selfAdvertisement?.licenseYear}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Applicant</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.applicantName}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Father Name </p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.fatherName}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>E-mail </p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.email}</span>
                            </div>
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>
                            <div className=''>
                                <p className={`${labelStyle}`}>Residence Address</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.residenceAddress}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Residence Ward No </p>
                                <span className={`${inputStyle}`}>{props?.reviewIdNameData?.selfAdvertisement?.residenceWardNo}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Permanent Address</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.permanentAddress}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Permanent Ward No</p>
                                <span className={`${inputStyle}`}>{props?.reviewIdNameData?.selfAdvertisement?.permanentWardNo}</span>
                            </div>
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                            <div className=''>
                                <p className={`${labelStyle}`}>Mobile No.</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.mobileNo}</span>
                            </div>

                            <div className=''>
                                <p className={`${labelStyle}`}>Aadhar No</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.aadharNo}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Entity Name</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.entityName}</span>
                            </div>

                            <div className=''>
                                <p className={`${labelStyle}`}>Entity Address</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.entityAddress}</span>
                            </div>
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                            <div className=''>
                                <p className={`${labelStyle}`}>Entity Ward No.</p>
                                <span className={`${inputStyle}`}>{props?.reviewIdNameData?.selfAdvertisement?.entityWardNo}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Installation Location</p>
                                <span className={`${inputStyle}`}>{props?.reviewIdNameData?.selfAdvertisement?.installationLocation}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Brand Display Name</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.brandDisplayName}</span>
                            </div><div className=''>
                                <p className={`${labelStyle}`}>Holding No.</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.holdingNo}</span>
                            </div>
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                            <div className=''>
                                <p className={`${labelStyle}`}>Trade License No</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.tradeLicenseNo}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>GST No.</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.gstNo}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Display Area</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.displayArea}</span>
                            </div><div className=''>
                                <p className={`${labelStyle}`}>Display Type</p>
                                <span className={`${inputStyle}`}>{props?.reviewIdNameData?.selfAdvertisement?.displayType}</span>
                            </div>
                        </div>
                        <div className='grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 p-2 ml-12'>

                            <div className=''>
                                <p className={`${labelStyle}`}>Longitude</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.longitude}</span>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Latitude</p>
                                <span className={`${inputStyle}`}>{props?.allFormData?.selfAdvertisement?.latitude}</span>
                            </div>

                        </div>
                    </div>
                    {/* document */}
                    <div className='col-span-4 border border-dashed border-violet-800 '>
                        <h1 className='text-center p-3 mb-2 bg-white text-gray-600 font-sans font-semibold '>DOCUMENTS UPLOADED</h1>

                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 p-2 ml-12'>
                            {props?.allFormData?.selfAdvertisementDoc?.map((data, index) => (

                                <div className=''>
                                    {/* {data?.map((data) => ( */}
                                        <>
                                            <p className={`${labelStyle}`}>{data?.relativeName} Document</p>
                                            <div className='flex -mt-2'>
                                                <span className={`${inputStyle} flex-1`}>{data?.image}</span>
                                                <span className={`mt-4  flex-1`}><img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={` w-8`} /></span>
                                            </div>
                                        </>
                                    {/* ))} */}
                                </div>

                            ))}
                            <div className=''>
                                <p className={`${labelStyle}`}>Trade License</p>
                                <div className='flex -mt-2'>
                                    <span className={`${inputStyle} flex-1`}>new-product.png</span>
                                    <span className={`mt-4 flex-1`}><img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={` w-8`} /></span>
                                </div>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>GPS Mapped Camera</p>
                                <div className='flex -mt-2'>
                                    <span className={`${inputStyle} flex-1`}>new-product.png</span>
                                    <span className={`mt-4 flex-1`}><img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={` w-8`} /></span>
                                </div>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Holding No. Photograph</p>
                                <div className='flex -mt-2'>
                                    <span className={`${inputStyle} flex-1`}>new-product.png</span>
                                    <span className={`mt-4 flex-1`}><img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={` w-8`} /></span>
                                </div>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>GST Document</p>
                                <div className='flex -mt-2'>
                                    <span className={`${inputStyle} flex-1`}>new-product.png</span>
                                    <span className={`mt-4 flex-1`}><img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={` w-8`} /></span>
                                </div>
                            </div>
                            <div className=''>
                                <p className={`${labelStyle}`}>Brand Display Permission</p>
                                <div className='flex -mt-2'>
                                    <span className={`${inputStyle} flex-1`}>new-product.png</span>
                                    <span className={`mt-4 flex-1 `}><img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={` w-8`} /></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-12 w-full p-3">
                    <div className='md:pl-0 col-span-6'>
                        <button type="button" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => setFormIndex(2)}>back</button>
                    </div>
                    <div className='col-span-6'>
                        <button type="submit" class="float-right text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0" onClick={submitForm}>Submit</button>
                    </div>
                </div>
            </div>
        </>
    )
}

export default ReviewSelfAdvertForm