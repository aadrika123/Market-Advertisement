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
import { FaHome } from 'react-icons/fa'
import { useFormik, Formik, Form, ErrorMessage } from 'formik'
import * as yup from 'yup'
// import { getCurrentDate, allowFloatInput } from '../../../Components/Common/PowerUps/PowerupFunctions'
// import { inputContainerStyle, commonInputStyle, inputErrorStyle, inputLabelStyle } from '../../../Components/Common/CommonTailwind/CommonTailwind'
import axios from 'axios'
import { Navigate, useNavigate } from 'react-router-dom'

import { FcElectroDevices } from 'react-icons/fc'
import { FcHome } from 'react-icons/fc'


function WaterFormScreen1(props) {


    const commonInputStyle = `form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none shadow-md`
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

            props.screen1Data(values) //sending BasicDetails data to parent to store all form data at one container
            // props.nextFun(1) //forwarding to next form level


        },
        // validationSchema
    })
    return (
        <>
            <h1 className='mt-6 mb-2 font-serif font-semibold text-gray-600'><FaHome className="inline mr-2" />Basic Details</h1>
            <div className="block p-4 md:py-6 shadow-lg bg-white border border-gray-200  mx-auto">

                <form onSubmit={formik.handleSubmit} onChange={handleOnChange}>

                    <div className="grid grid-cols-1 md:grid-cols-3">
                        <div className="col-span-4 grid grid-cols-1 md:grid-cols-3">
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Type of Connection</label>
                                <select name="typeofConnection" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange}
                                >
                                    <option >select</option>
                                    <option value="1">New Connection</option>
                                    <option value="2">Regularization</option>
                                </select>
                                <p className='text-red-500 text-xs'>{formik.touched.typeofConnection && formik.errors.typeofConnection ? formik.errors.typeofConnection : null}</p>
                            </div>

                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Connection Through</label>
                                <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange}
                                >
                                    <option> Select </option>
                                    <option value="1">Holding Proof</option>
                                    <option value="2">SAF</option>
                                    <option value="3">ID Proof</option>
                                </select>
                                <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                            </div>
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Owner Type</label>
                                <select name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange}
                                >

                                    <option >select</option>
                                    <option value="1">Owner</option>
                                    <option value="2">Tenant</option>
                                </select>
                                <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                            </div>
                        </div>
                        <div className={`${inputContainerStyle}`}>
                            <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Property Type ?</label>
                            <select name="propertyType" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange}
                            >
                                <option >Select</option>
                                <option value="1">Residencial</option>
                                <option value="3">Commercial</option>
                                <option value="4">Government and PSU</option>
                                <option value="5">Institutinal</option>
                                <option value="6">SSL Unit</option>
                                <option value="7">Industrial</option>
                                <option value="8">Appartment/Multi Stored Building</option>

                            </select>
                            <p className='text-red-500 text-xs'>{formik.touched.propertyType && formik.errors.propertyType ? formik.errors.propertyType : null}</p>
                        </div>
                        <div className={`${inputContainerStyle}`}>
                            <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Category Type</label>
                            <select disabled={!mobileTowerStatusToggle} name="categoryType" type="text" className={`${commonInputStyle} ${!mobileTowerStatusToggle && 'bg-gray-300 opacity-30'}`} onChange={formik.handleChange} >
                                <option >Select</option>
                                <option value="1">APL</option>
                                <option value="2">BPL</option>
                            </select>
                            <p className='text-red-500 text-xs'>{formik.touched.categoryType && formik.errors.categoryType ? formik.errors.categoryType : null}</p>

                        </div>

                        <div className={`${inputContainerStyle}`}>
                            <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Pipeline Type</label>
                            <select disabled={!mobileTowerStatusToggle} name="pipelineType" type="date" className={`${commonInputStyle} ${!mobileTowerStatusToggle && 'bg-gray-300 opacity-30'}`} onChange={formik.handleChange} >
                                <option >Select</option>
                                <option value="1">New Pipeline</option>
                                <option value="2">Old Pipeline</option>
                            </select>
                            <p className='text-red-500 text-xs'>{formik.touched.pipelineType && formik.errors.pipelineType ? formik.errors.pipelineType : null}</p>
                        </div>

                        <p className='border-b col-span-4 mx-5 my-3 text-sm font-semibold flex'> <span className='mx-1'> <FcHome size={16} /> </span>Applicant Property Details</p>

                        <div className="col-span-4 grid grid-cols-4">
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Ward No</label>
                                <select name="wardNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                                    <option>Select</option>
                                    <option value="3">3A</option>
                                </select>
                                <p className='text-red-500 text-xs'>{formik.touched.wardNo && formik.errors.wardNo ? formik.errors.wardNo : null}</p>
                            </div>
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Total Area</label>
                                <input type="number" name="totalArea" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.totalArea && formik.errors.totalArea ? formik.errors.totalArea : null}</p>
                            </div>

                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Landmark</label>
                                <input type="text" name="landmark" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.landmark && formik.errors.landmark ? formik.errors.landmark : null}</p>
                            </div>
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Pincode</label>
                                <input type="number" name="pincode" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.pincode && formik.errors.pincode ? formik.errors.pincode : null}</p>
                            </div>

                        </div>
                        <div className="col-span-4 grid grid-cols-1">
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Address</label>
                                <input type="text" name="address" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.address && formik.errors.address ? formik.errors.address : null}</p>
                            </div>
                        </div>

                        <p className='border-b col-span-4 mx-5 my-3 text-sm font-semibold flex'> <span className='mx-1'> <FcElectroDevices size={16} /> </span>Applicant Electricity Details</p>

                        <div className="col-span-4 grid grid-cols-4">
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Khata No</label>
                                <input type="number" name="khataNo" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.khataNo && formik.errors.khataNo ? formik.errors.khataNo : null}</p>
                            </div>

                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Binding Book No</label>
                                <input type="number" name="bindingBookNo" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.bindingBookNo && formik.errors.bindingBookNo ? formik.errors.bindingBookNo : null}</p>
                            </div>
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Account No</label>
                                <input type="number" name="accountNo" className={`${commonInputStyle}`} onChange={formik.handleChange} />
                                <p className='text-red-500 text-xs'>{formik.touched.accountNo && formik.errors.accountNo ? formik.errors.accountNo : null}</p>
                            </div>
                            <div className={`${inputContainerStyle}`}>
                                <label className={`${inputLabelStyle}`}><small className="mt-1 text-sm font-semibold text-red-600 inline ">*</small>Category Type</label>
                                <select name="eleCategoryType" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange}
                                >
                                    <option>Select</option>
                                    <option value="1">Residential - DS I/II</option>
                                    <option value="2">Commercial - NDS II/III</option>
                                    <option value="3">Agriculture - IS I/II</option>
                                    <option value="4">Low Tension - LTS</option>
                                    <option value="5">High Tension - HTS</option>
                                </select>
                                <p className='text-red-500 text-xs'>{formik.touched.eleCategoryType && formik.errors.eleCategoryType ? formik.errors.eleCategoryType : null}</p>
                            </div>
                        </div>

                        <div></div>
                        <div className="col-span-4 grid grid-cols-2">
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