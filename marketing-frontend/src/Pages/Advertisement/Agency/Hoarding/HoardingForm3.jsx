import React from 'react'
import { useFormik } from 'formik';
import SelfAdvrtInformationScreen from '../../SelfAdvertisement/SelfAdvrtInformationScreen';

function HoardingForm3(props) {
    let labelStyle = "mt-2 text-xs text-gray-600"
    let inputStyle = "text-xs rounded-md shadow-md px-1.5 py-1 w-[16rem] md:w-[13rem] h-8 md:h-8  mt-2 -ml-2 "

    const initialValues = {
        applicant: '',
        directorName: '',
        omdId: '',
        mailingAddress: '',
        city: '',
        state: '',
        mobileNo: '',
        registrationNo: '',
        permanentAddress: '',
        permanentCity: '',
        permanentState: '',
        pinCode: '',

        ownerName: '',
        ownerAddress: '',
        ownerCity: '',
        ownerPinCode: '',
        ownerPhoneNo: '',
        propertyType: '',
        displayArea: '',
        displayLocation: '',
        displayStreet: '',
        displayLandmark: '',
        mediaHeight: '',
        mediaLength: '',
        mediaSize: '',
        material: '',
        illumination: '',
        indicateFace: '',
    }

    const formik = useFormik({
        initialValues: initialValues,
        onSubmit: values => {
            alert(JSON.stringify(values, null, 2));
            console.log("hoarding3", values)
            // props.collectFormDataFun('hoarding3', values, reviewIdName)
            props.collectFormDataFun('hoarding3', values)
            props?.nextFun(4)
        },
    });

    const handleChange = (e) => {
        let name = e.target.name
        let value = e.target.value

        { name == 'ulb' && getMasterDataFun(value) }
        { name == 'ulb' && setstoreUlbValue(value) }
        console.log("ulb id...", value)
    }
    return (
        <>
            <div>
                <form onSubmit={formik.handleSubmit} onChange={handleChange}>
                    <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto  '>
                        <div className='col-span-8 '>

                            {/* applicant details */}
                            {/* <div className='border border-dashed border-violet-800 pb-4 p-2'>
                                <h1 className='font-semibold border-b bg-white px-2 p-2 rounded leading-5'>Applicant</h1>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-8 '>
                                        <div className='grid grid-cols-2 md:grid-cols-6 lg:grid-cols-6 gap-1'>
                                            <div className='col-span-4'>
                                                <p className={`${labelStyle} `}> Name of the applicant (Please print or type name of firm or individual desiring permit) <span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.entityName}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Name of the directors<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.entityName}
                                                />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>OMD ID<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='panNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.panNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Mailing Address<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='gstNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.gstNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>City<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.entityName}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>State<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='panNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.panNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Office phone no./Mobile no.<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='gstNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.gstNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Unique Registration No.<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.entityName}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Permanent Address<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='panNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.panNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>City<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='gstNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.gstNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>State<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='gstNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.gstNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Pin Code<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='panNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.panNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> */}

                            {/* property details */}
                            <div className='border border-dashed border-violet-800 pb-4 p-2 mt-4'>
                                <h1 className='font-semibold border-b bg-white px-2 p-2 rounded leading-5'>Property</h1>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Owner Name (person in control of property)<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='ownerName' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.ownerName}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Address<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='ownerAddress' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.ownerAddress}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>City<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='ownerCity' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.ownerCity}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Pin Code<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='ownerPinCode' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.ownerPinCode}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Phone No.<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='ownerPhoneNo' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.ownerPhoneNo}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Property Type<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <select type="text" name='propertyType' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.propertyType}
                                                >
                                                    <option value=''>select</option>
                                                    <option value='public'>Public</option>
                                                    <option value='private'>Private</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/*display location information*/}
                            <div className='border border-dashed border-violet-800 pb-4 p-2 mt-4'>
                                <h1 className='font-semibold border-b bg-white px-2 p-2 rounded leading-5'>Display location information</h1>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Area<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='displayArea' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.displayArea}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Location<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='displayLocation' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.displayLocation}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Street<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='displayStreet' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.displayStreet}
                                                />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Land mark<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='displayLandmark' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.displayLandmark}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/*media specification*/}
                            <div className='border border-dashed border-violet-800 pb-4 p-2 mt-4'>
                                <h1 className='font-semibold border-b bg-white px-2 p-2 rounded leading-5'>Media Specification</h1>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Height<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='mediaHeight' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.mediaHeight}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Length<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='mediaLength' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.mediaLength}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Size(in sq. ft)<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <input type="text" name='mediaSize' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.mediaSize}
                                                />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4'>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Material<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <select type="text" name='material' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.material}
                                                >
                                                    <option value=''>select</option>
                                                    <option value='metal'>Metal</option>
                                                    <option value='wood'>Wood</option>
                                                    <option value='others'>Others</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Illumination<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <select type="text" name='illumination' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.illumination}
                                                >
                                                    <option value=''>select</option>
                                                    <option value='0'>No</option>
                                                    <option value='1'>Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='col-span-4 '>
                                        <div className='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1'>
                                            <div className=''>
                                                <p className={`${labelStyle}`}>Indicate Facing<span className='text-red-600'> *</span></p>
                                            </div>
                                            <div className='col-span-2'>
                                                <select type="text" name='indicateFace' placeholder='' className={`${inputStyle}`}
                                                    onChange={formik.handleChange}
                                                    value={formik.values.indicateFace}
                                                >
                                                    <option value=''>select</option>
                                                    <option value='north'>North</option>
                                                    <option value='south'>South</option>
                                                    <option value='east'>East</option>
                                                    <option value='west'>West</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div className='text-left'>
                                <button type="button" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(3)}>back</button>
                            </div>
                            <div className='float-right -mt-12'>
                                <button type="submit" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0" >Save & Next</button>
                            </div>
                        </div>
                        <div className='col-span-4 hidden lg:block md:block'>
                            <div className='-mt-16'>
                                <SelfAdvrtInformationScreen />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </>
    )
}

export default HoardingForm3