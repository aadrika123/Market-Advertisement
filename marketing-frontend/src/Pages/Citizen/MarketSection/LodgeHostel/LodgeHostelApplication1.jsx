//////////////////{*****}//////////////////////////////////////////
// >Author - Dipu Singh
// >Version - 1.0
// >Date - 
// >Revision - 1
// >Project - JUIDCO
// >Component  - 
// >DESCRIPTION -
//////////////////{*****}//////////////////////////////////////////

import { useState, useEffect } from 'react'
import { FcAbout } from 'react-icons/fc'
import { RiHomeHeartLine } from 'react-icons/ri'
import { useFormik, Formik, Form, ErrorMessage } from 'formik'
import * as yup from 'yup'
// import { getCurrentDate, allowFloatInput } from '../../../Components/Common/PowerUps/PowerupFunctions'
// import { inputContainerStyle, commonInputStyle, inputErrorStyle, inputLabelStyle } from '../../../Components/Common/CommonTailwind/CommonTailwind'
import axios from 'axios'
import { Navigate, useNavigate } from 'react-router-dom'

import { FcElectroDevices } from 'react-icons/fc'
import { FcHome } from 'react-icons/fc'


function WaterFormScreen1(props) {


    const commonInputStyle = `form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none shadow-sm`
    const inputContainerStyle = `form-group col-span-4 md:col-span-1 mb-6 md:px-4`
    const inputLabelStyle = `form-label inline-block mb-1 text-gray-600 text-sm font-semibold`


    const [mobileTowerStatusToggle, setMobileTowerStatusToggle] = useState(false)

    const validationSchema = yup.object({
        wardNo: yup.string().required('Select ward'),
        ownerShiptype: yup.string().required('Select ownership type'),
        propertyType: yup.string().required('Select property'),

        mobileTowerStatus: yup.string().required('Select mobile tower status'),
        hoardingStatus: yup.string().required('Select hoarding status'),
        petrolPumpStatus: yup.string().required('Select petrol pump status'),
        waterHarvestingStatus: yup.string().required('Select water harvesting status'),
        mobileTowerArea: yup.string('enter numbers only').when('mobileTowerStatus', {
            is: 'yes',
            then: yup.string().required('Field is required')
        }).min(1, 'enter minimum ').max(10, 'Enter max 10 digit'),
        hoardingArea: yup.string().when('hoardingStatus', {
            is: 'yes',
            then: yup.string().required('Field is required')
        }).min(1, 'enter minimum ').max(10, 'Enter max 10 digit'),
        petrolPumpArea: yup.string().when('petrolPumpStatus', {
            is: 'yes',
            then: yup.string().required('Field is required')
        }).min(1, 'enter minimum ').max(10, 'Enter max 10 digit'),
        mobileTowerDate: yup.date().when('mobileTowerStatus', {
            is: 'yes',
            then: yup.date().required('Field is required')
        }),
        hoardingDate: yup.date().when('hoardingStatus', {
            is: 'yes',
            then: yup.date().required('Field is required')
        }),
        petrolPumpDate: yup.date().when('petrolPumpStatus', {
            is: 'yes',
            then: yup.date().required('Field is required')
        }),

    })

    const navigate = useNavigate()
    const initialValues = {
        wardNo: '',
    }



    const handleOnChange = (event) => {
        let name = event.target.name
        let value = event.target.value

        { name === 'propertyType' && ((value == '1') ? setMobileTowerStatusToggle(true) : setMobileTowerStatusToggle(false)) }


        // //allow restricted inputs
        // { name == 'mobileTowerArea' && formik.setFieldValue("mobileTowerArea", allowFloatInput(value, formik.values.mobileTowerArea, 20)) } //(currentValue,oldValue,max,isCapital)
        // { name == 'hoardingArea' && formik.setFieldValue("hoardingArea", allowFloatInput(value, formik.values.hoardingArea, 20, true)) }
        // { name == 'petrolPumpArea' && formik.setFieldValue("petrolPumpArea", allowFloatInput(value, formik.values.petrolPumpArea, 20)) }


    };

    const formik = useFormik({
        initialValues: initialValues,
        // enableReinitialize: true,
        onSubmit: (values, resetForm) => {
            // props.screen1Data(values)

            console.log("Form1 Data", values)


        },
        // validationSchema
    })
    return (
        <>
            <div className="block p-4 md:py-3 bg-white  mx-auto">
                {/* <h1 className=' font-serif font-semibold text-gray-600'><FaHome className="inline mr-2" />Basic Details</h1> */}

                <form onSubmit={formik.handleSubmit} onChange={handleOnChange}>

                    <div className="grid grid-cols-1 md:grid-cols-3">
                        <div className='col-span-4 border-b mx-1 mb-4'>
                            <p className='col-span-4 font-semibold flex text-xl'>
                                <span className='mx-1 mt-1'>
                                    <FcAbout size={20} /> </span>Basic Information
                            </p>
                            <p className='block text-gray-400 text-sm'>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi, neque!</p>
                        </div>
                        <div className="col-span-4 grid grid-cols-1 md:grid-cols-3 bg-red-50">
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Applicant Name</label>
                                <input type="text" name="typeofConnection" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.typeofConnection && formik.errors.typeofConnection ? formik.errors.typeofConnection : null}</p>
                            </div>

                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Father's Name</label>
                                <input type="text" name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} />

                                <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                            </div>
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Mobile</label>
                                <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                            </div>
                        </div>
                        <div className={`${inputContainerStyle}`}>
                            <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Ward No</label>
                            <select name="wardNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                                <option>Select</option>
                                <option value="3">3A</option>
                            </select>
                            <p className='text-red-500 text-xs'>{formik.touched.wardNo && formik.errors.wardNo ? formik.errors.wardNo : null}</p>
                        </div>
                        <div className={`${inputContainerStyle}`}>
                            <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Email</label>
                            <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                            <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                        </div>

                        <p className='border-b col-span-4 mx-1 mb-4 font-semibold flex text-base'> <span className='mx-1'> <FcHome size={20} /> </span>Residence Address</p>

                        <div className='col-span-4  mx-5'>
                            <div className='grid grid-cols-6 space-x-5'>
                                <div className="col-span-1">
                                    <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Ward No</label>
                                    <select name="wardNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                                        <option>Select</option>
                                        <option value="3">3A</option>
                                    </select>
                                    <p className='text-red-500 text-xs'>{formik.touched.wardNo && formik.errors.wardNo ? formik.errors.wardNo : null}</p>
                                </div>
                                <div className="col-span-5">
                                    <div className="">
                                        <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Address</label>
                                        <input type="text" name="address" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                        <p className='text-red-500 text-xs'>{formik.touched.address && formik.errors.address ? formik.errors.address : null}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p className='border-b col-span-4 mx-1 my-5 text-base font-semibold flex'> <span className='mx-1'> <RiHomeHeartLine color='#f78f8f' size={20} /> </span>Permanent Address</p>
                        <div className='col-span-4  mx-5'>
                            <div className='grid grid-cols-6 space-x-5'>
                                <div className="col-span-1">
                                    <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Ward No</label>
                                    <select name="wardNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                                        <option>Select</option>
                                        <option value="3">3A</option>
                                    </select>
                                    <p className='text-red-500 text-xs'>{formik.touched.wardNo && formik.errors.wardNo ? formik.errors.wardNo : null}</p>
                                </div>
                                <div className="col-span-5">
                                    <div className="">
                                        <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Address</label>
                                        <input type="text" name="address" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                        <p className='text-red-500 text-xs'>{formik.touched.address && formik.errors.address ? formik.errors.address : null}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div></div>
                        <div className="col-span-4 grid grid-cols-2 pt-10">
                            <div className='md:px-10'></div>
                            <div className='md:px-10 text-right space-x-8'>
                                <button type="button" onClick={() => navigate("/water")} className=" px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight  rounded  hover:bg-blue-700 hover:shadow-lg focus:bg-green-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-800 active:shadow-lg transition duration-150 ease-in-out">Back</button>
                                <button type="submit" className=" px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight  rounded  hover:bg-green-700 hover:shadow-lg focus:bg-green-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-800 active:shadow-lg transition duration-150 ease-in-out">Save & Next</button>
                            </div>
                        </div>
                        <div className="col-span-4 grid grid-cols-2">

                        </div>
                    </div>
                </form>
            </div>
        </>
    )
}

export default WaterFormScreen1