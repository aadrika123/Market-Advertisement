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


function LodgeHostelApplication1(props) {


    const commonInputStyle = `form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none shadow-sm`
    const inputContainerStyle = `form-group col-span-4 md:col-span-1 mb-6 md:px-4`
    const inputLabelStyle = `form-label inline-block mb-1 text-gray-600 text-sm font-semibold`


    const validationSchema = yup.object({
        applicantName: yup.string().required('Required'),
        fatherName: yup.string().required('Required'),
        mobile: yup.string().required('Required').min(10, 'Enter 10 digit number').max(10, 'Enter 10 digit number'),
        email: yup.string().required('Required'),
        resWardNo: yup.string().required('Required'),
        resAddress: yup.string().required('Required'),
        perWardNo: yup.string().required('Required'),
        perAddress: yup.string().required('Required'),

    })

    const navigate = useNavigate()
    const initialValues = {
        applicantName: '',
        fatherName: '',
        mobile: '',
        email: '',
        resWardNo: '',
        resAddress: '',
        perWardNo: '',
        perAddress: ''
    }



    const handleOnChange = (event) => {

    };

    const formik = useFormik({
        initialValues: initialValues,
        enableReinitialize: true,
        onSubmit: (values, resetForm) => {
            props.screen1Data(values)

        },
        validationSchema
    })
    return (
        <>
            <div className="block p-4 md:py-3 bg-white  mx-auto">
                {/* <h1 className=' font-serif font-semibold text-gray-600'><FaHome className="inline mr-2" />Basic Details</h1> */}

                <form onSubmit={formik.handleSubmit} onChange={handleOnChange}>

                    <div className="grid grid-cols-1 md:grid-cols-3">
                        <div className='col-span-4 border-b mx-1 mb-4 bg-gray-100 shadow-lg shadow-gray-200 rounded-md pt-3 '>
                            <p className='col-span-4 font-semibold flex text-xl'>
                                <span className='mx-1 mt-1 pl-4'>
                                    <FcAbout size={20} /> </span>Basic Information
                            </p>
                            <p className='block text-gray-400 text-sm pl-4'>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi, neque!</p>

                            <p className='border-b border-gray-300 ml-3 my-2 mr-10'></p>

                            <div className="col-span-4 grid grid-cols-1 md:grid-cols-2 mt-5">
                                <div className={`${inputContainerStyle}`}>
                                    <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Applicant Name</label>
                                    <input type="text" name="applicantName" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                    <p className='text-red-500 text-xs absolute'>{formik.touched.applicantName && formik.errors.applicantName ? formik.errors.applicantName : null}</p>
                                </div>

                                <div className={`${inputContainerStyle}`}>
                                    <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Father's Name</label>
                                    <input type="text" name="fatherName" className={`${commonInputStyle} `} onChange={formik.handleChange} />

                                    <p className='text-red-500 text-xs absolute'>{formik.touched.fatherName && formik.errors.fatherName ? formik.errors.fatherName : null}</p>
                                </div>
                                <div className={`${inputContainerStyle}`}>
                                    <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Mobile</label>
                                    <input type="number" name="mobile" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                                    <p className='text-red-500 text-xs absolute'>{formik.touched.mobile && formik.errors.mobile ? formik.errors.mobile : null}</p>
                                </div>

                                <div className={`${inputContainerStyle}`}>
                                    <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Email</label>
                                    <input type="email" name="email" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                                    <p className='text-red-500 text-xs absolute'>{formik.touched.email && formik.errors.email ? formik.errors.email : null}</p>
                                </div>
                            </div>

                        </div>

                        <div className='col-span-4 border-b mx-1 mb-4 bg-gray-100 shadow-lg shadow-gray-200 rounded-md p-5'>
                            <p className='col-span-4 font-semibold flex text-xl'>
                                <span className='mx-1 mt-1'><FcHome size={20} /> </span>Residence Address                            </p>
                            <p className='block text-gray-400 text-sm'>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi, neque!</p>
                            <div className='col-span-4'>
                                <div className='grid grid-cols-6 space-x-5'>
                                    <div className="col-span-1">
                                        <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Ward No</label>
                                        <select name="resWardNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                                            <option value="">Select</option>
                                            <option value="3A">3A</option>
                                        </select>
                                        <p className='text-red-500 text-xs absolute'>{formik.touched.resWardNo && formik.errors.resWardNo ? formik.errors.resWardNo : null}</p>
                                    </div>
                                    <div className="col-span-5">
                                        <div className="">
                                            <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Address</label>
                                            <input type="text" name="resAddress" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.resAddress && formik.errors.resAddress ? formik.errors.resAddress : null}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div className='col-span-4 border-b mx-1 mb-4 bg-gray-100 shadow-lg shadow-gray-200 rounded-md p-5'>
                            <p className='col-span-4 font-semibold flex text-xl'>
                                <span className='mx-1 mt-1'>
                                    <RiHomeHeartLine size={20} /> </span>Permanent Address
                            </p>
                            <p className='block text-gray-400 text-sm'>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi, neque!</p>
                            <p className='block border-b border-gray-300 my-2 mr-10'></p>
                            <div className='col-span-4'>
                                <div className='grid grid-cols-6 space-x-5'>
                                    <div className="col-span-1">
                                        <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Ward No</label>
                                        <select name="perWardNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                                            <option value="">Select</option>
                                            <option value="3A">3A</option>
                                        </select>
                                        <p className='text-red-500 text-xs absolute'>{formik.touched.perWardNo && formik.errors.perWardNo ? formik.errors.perWardNo : null}</p>
                                    </div>
                                    <div className="col-span-5">
                                        <div className="">
                                            <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Address</label>
                                            <input type="text" name="perAddress" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                            <p className='text-red-500 text-xs absolute'>{formik.touched.perAddress && formik.errors.perAddress ? formik.errors.perAddress : null}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div></div>
                        <div className="col-span-4 grid grid-cols-2 pt-10">
                            <div className='md:px-10'></div>
                            <div className='md:px-10 text-right space-x-8'>

                            <button type="button" onClick={() => navigate("#")} class="py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">Back</button>
<button type="submit" class="py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next</button>


                                {/* <button type="button" onClick={() => navigate("#")} className=" px-12 py-2.5 bg-blue-600 text-white font-medium text-sm leading-tight  rounded  hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Back</button> */}
                                {/* <button type="submit" className=" px-10 py-2.5 bg-green-600 text-white font-medium text-sm leading-tight  rounded  hover:bg-green-700 hover:shadow-lg focus:bg-green-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-800 active:shadow-lg transition duration-150 ease-in-out">Save & Next</button> */}
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

export default LodgeHostelApplication1

/*
Exported to -
1) IndexLodgeHostel.js
*/