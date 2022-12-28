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
import { FaWpforms } from 'react-icons/fa'
import { RiHomeHeartLine } from 'react-icons/ri'
import { useFormik, Formik, Form, ErrorMessage } from 'formik'
import * as yup from 'yup'
// import { getCurrentDate, allowFloatInput } from '../../../Components/Common/PowerUps/PowerupFunctions'
// import { inputContainerStyle, commonInputStyle, inputErrorStyle, inputLabelStyle } from '../../../Components/Common/CommonTailwind/CommonTailwind'
import axios from 'axios'
import { Navigate, useNavigate } from 'react-router-dom'

import { FcElectroDevices } from 'react-icons/fc'
import { FcHome } from 'react-icons/fc'


function LodgeHostelApplication2(props) {


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
      props.screen2Data(values)



    },
    // validationSchema
  })
  return (
    <>
      <div className="block p-4 md:py-3 bg-white  mx-auto">
        {/* <h1 className=' font-serif font-semibold text-gray-600'><FaHome className="inline mr-2" />Basic Details</h1> */}

        <form onSubmit={formik.handleSubmit} onChange={handleOnChange}>

          <div className="grid grid-cols-1 md:grid-cols-3">
            <div className='col-span-4 mx-1 mb-4 bg-gray-100 shadow-lg rounded-md pt-3 '>
              <p className='col-span-4 font-semibold flex text-xl'>
                <span className='mx-1 mt-1 pl-4'>
                  <FaWpforms size={18} className="mt-0.5" /> </span>Shop Esrablishment Details of Applicant
              </p>
              <p className='block text-gray-400 text-sm pl-4'>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi, neque!</p>

              <p className='border-b border-gray-300 ml-3 my-2 mr-10'></p>

              <div className="col-span-4 grid grid-cols-1 md:grid-cols-3 mt-5">
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>License Year</label>
                  <select name="typeofConnection" className={`${commonInputStyle} cursor-pointer`} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">2018 - 19</option>
                    <option value="">2019 - 20</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.typeofConnection && formik.errors.typeofConnection ? formik.errors.typeofConnection : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Established Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">Lodge</option>
                    <option value="">Hostel</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Entity Name</label>
                  <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Entity Address</label>
                  <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Entity Ward</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Holding No</label>
                  <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Trade License No</label>
                  <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Longitude</label>
                  <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Latitude</label>
                  <input type="text" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Organization Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Land Deed Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Water Supply Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Electricity  Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Security Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Lodge Type</label>
                  <select name="connectionThrough" className={`${commonInputStyle} cursor-pointer `} onChange={formik.handleChange} >
                    <option value="">Select</option>
                    <option value="">1</option>
                    <option value="">2</option>
                  </select>
                  <p className='text-red-500 text-xs'>{formik.touched.connectionThrough && formik.errors.connectionThrough ? formik.errors.connectionThrough : null}</p>
                </div>

                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of CCTV Camera</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of Beds</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of Rooms</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of Fire Extinguishers</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of Entry Gate</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of Exit Gate</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of  Two Wheelers Parking</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>No of Four Wheelers Parking</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>Aadhar  No</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>
                <div className={`${inputContainerStyle}`}>
                  <label className={`${inputLabelStyle}`}><small className="block mt-1 text-sm font-semibold text-red-600 inline ">*</small>PAN Card No</label>
                  <input type="number" name="ownerType" className={`${commonInputStyle} `} onChange={formik.handleChange} />
                  <p className='text-red-500 text-xs'>{formik.touched.ownerType && formik.errors.ownerType ? formik.errors.ownerType : null}</p>
                </div>


              </div>

            </div>


            <div></div>
            <div className="col-span-4 grid grid-cols-2 pt-10">
              <div className='md:px-10'></div>
              <div className='md:px-10 text-right space-x-8'>
                <button type="button" onClick={() => props.goBack(0)} className=" px-12 py-2.5 bg-blue-600 text-white font-medium text-sm leading-tight  rounded  hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Back</button>
                <button type="submit" className=" px-10 py-2.5 bg-green-600 text-white font-medium text-sm leading-tight  rounded  hover:bg-green-700 hover:shadow-lg focus:bg-green-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-800 active:shadow-lg transition duration-150 ease-in-out">Save & Next</button>
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

export default LodgeHostelApplication2

/*
Exported to -
1) IndexLodgeHostel.js
*/