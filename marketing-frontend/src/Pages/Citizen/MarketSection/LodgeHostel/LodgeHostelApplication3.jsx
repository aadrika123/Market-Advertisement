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
import { FiUpload } from 'react-icons/fi'
import { useFormik, Formik, Form, ErrorMessage } from 'formik'
import * as yup from 'yup'
// import { getCurrentDate, allowFloatInput } from '../../../Components/Common/PowerUps/PowerupFunctions'
// import { inputContainerStyle, commonInputStyle, inputErrorStyle, inputLabelStyle } from '../../../Components/Common/CommonTailwind/CommonTailwind'
import axios from 'axios'
import { Navigate, useNavigate } from 'react-router-dom'
import noImage from '../../../../assets/images/nophoto.png'
import pdfImage from '../../../../assets/images/pdf.png'
import imageIcon from '../../../../assets/images/photo.png'


import { FcElectroDevices } from 'react-icons/fc'
import { FcHome } from 'react-icons/fc'
import DocUploadModal from './DocUploadModal'
import ViewUploadedDoc from './ViewUploadedDoc'
// import DocUploadModal from './DocUploadModal'


function LodgeHostelApplication3(props) {
  const [openDocumentModal, setOpenDocumentModal] = useState(0)
  const [openDocviewModal, setOpenDocviewModal] = useState(0)



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
      props.screen3Data(values)



    },
    // validationSchema
  })



  const ownerDocList = [
    { "docName": "Upload Fire Extinguishers Photograph", "isMadatory": true, uploadDoc: { "document_path": "abc.pdf" }, },
    { "docName": "Upload Fire Extinguishers Photograph", "isMadatory": true, uploadDoc: null, },
    { "docName": "Upload Name Plate With Mobile No Photograph", "isMadatory": true, uploadDoc: { "document_path": "abc.png" }, },
    { "docName": "Upload Building Plan Photograph", "isMadatory": true, uploadDoc: null, },
    { "docName": "Upload Holding Tax Receipt Photograph", "isMadatory": true, uploadDoc: null, },
    { "docName": "Upload Aadhar No Photograph", "isMadatory": true, uploadDoc: null, },
    { "docName": "Upload CCTV Camera Photograph", "isMadatory": true, uploadDoc: null, },
    { "docName": "Upload Entry and Exit Photograph", "isMadatory": true, uploadDoc: null, },
    { "docName": "Upload Solid Waste Photograph", "isMadatory": true, uploadDoc: null, },
  ]

  const handleUploadBtn = (docName) => {

    const payload = {
      "docName": docName,
      "applicationId": props?.uploadDocBtnId,
    }
    // setPayloadData(payload)
    setOpenDocumentModal(pre => pre + 1)
  }

  const openDocView = () => {
    setOpenDocviewModal(prev => prev + 1)
  }

  const documentUrl = "http://192.168.0.16:8000/api/getImageLink?path=uploads/Water/water_conn_doc/40.pdf"

  return (
    <>
      <DocUploadModal openDocumentModal={openDocumentModal} />
      <ViewUploadedDoc openDocviewModal={openDocviewModal} documentUrl={documentUrl} />
      <div className="bg-white shadow-2xl border border-sky-200 p-5 m-2 rounded-md">
        <p>Upload Require Documents</p>
        <div className="md:inline-block min-w-full overflow-hidden hidden">
          <table className="min-w-full leading-normal border">
            <thead className='bg-sky-100'>
              <tr className='font-semibold '>
                <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                  #
                </th>
                <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                  Document Name
                </th>
                <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                  Remark
                </th>
                <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                  View
                </th>
                <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800 text-center text-sm uppercase">
                  Status
                </th>
                <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                  Upload
                </th>
              </tr>
            </thead>
            <tbody>
              {
                ownerDocList?.map((e, i = 1) => (
                  <tr>
                    <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                      <p className="text-gray-900 whitespace-no-wrap">
                        {i + 1}
                      </p>
                    </td>
                    <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                      <p className="text-gray-900 whitespace-no-wrap">
                        {e?.docName}{e?.isMadatory && <span className="text-red-500 font-semibold mx-1">*</span>}
                      </p>
                    </td>
                    <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                      <p className="text-gray-900 whitespace-no-wrap">
                        {e?.uploadDoc == null ? <p className="text-red-500">Not Upload</p> :
                          <div>
                            {e?.uploadDoc?.verify_status == 0 && <p className="text-green-700">Pending</p>}
                            {e?.uploadDoc?.verify_status == 1 && <p className="text-green-700">Verified</p>}
                            {e?.uploadDoc?.verify_status == 2 && <p className="text-red-700">Rejected</p>}

                          </div>
                        }
                      </p>
                    </td>
                    <td onClick={() => openDocView()} className="px-5 py-2 border-b border-gray-200 bg-white text-sm" > {/*onClick={() => props.openModal('http://192.168.0.16:822/RMCDMC/public/assets/img/pdf_logo.png')}*/}
                      <div className="flex items-center">
                        <div className="flex-shrink-0">
                          <a href="#" className="block relative">
                            {e?.uploadDoc == null && <img alt="profil" src={noImage} className="mx-auto object-cover rounded-none h-10 w-10 cursor-not-allowed" />}
                            {e?.uploadDoc?.document_path?.split('.').pop() == "pdf" && <img alt="profil" src={pdfImage} className="mx-auto object-cover rounded-none h-10 w-10 " />}
                            {e?.uploadDoc?.document_path?.split('.').pop() == "jpg" && <img alt="profil" src={imageIcon} className="mx-auto object-cover rounded-none h-10 w-10 " />}
                            {e?.uploadDoc?.document_path?.split('.').pop() == "png" && <img alt="profil" src={imageIcon} className="mx-auto object-cover rounded-none h-10 w-10 " />}
                          </a>
                        </div>
                      </div>
                    </td>
                    <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                      <p className="text-center text-black font-medium">
                        {e.uploadDoc == null && <p className='bg-red-400 rounded-xl px-2 py-0.5'>{e.docStatus}</p>}
                        {e.uploadDoc != null && <p className='bg-green-400 rounded-xl py-0.5'>{e.docStatus}</p>}
                      </p>
                    </td>
                    <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                      <button onClick={() => handleUploadBtn(e.docName)} className="border px-4 py-2 hover:bg-blue-600 hover:text-white">Upload</button>
                    </td>
                  </tr>
                ))
              }

            </tbody>
          </table>
        </div>
      </div>


      <div className="block p-4 md:py-3 mx-auto">
        {/* <h1 className=' font-serif font-semibold text-gray-600'><FaHome className="inline mr-2" />Basic Details</h1> */}
        <div className='md:px-10'></div>
        <div className='md:px-10 text-right space-x-8'>
          <button type="button" onClick={() => props.goBack(1)} className=" px-12 py-2.5 bg-blue-600 text-white font-medium text-sm leading-tight  rounded  hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Back</button>
          <button type="button" onClick={()=>props.screen3Data()} className=" px-10 py-2.5 bg-green-600 text-white font-medium text-sm leading-tight  rounded  hover:bg-green-700 hover:shadow-lg focus:bg-green-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-800 active:shadow-lg transition duration-150 ease-in-out">Submit</button>
        </div>

      </div>
    </>
  )
}

export default LodgeHostelApplication3

/*
Exported to -
1) IndexLodgeHostel.js
*/