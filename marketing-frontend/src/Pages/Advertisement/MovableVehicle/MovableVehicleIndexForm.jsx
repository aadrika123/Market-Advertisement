import React, { useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import AdvrtSuccessScreen from '../AdvrtSuccessScreen'
import MovableVehicleDocForm from './MovableVehicleDocForm'
import MovableVehicleForm from './MovableVehicleForm'
import ReviewMovableApplication from './ReviewMovableApplication'
import axios from 'axios'
import ApiHeader from '../../../Compnents/ApiHeader'
import BackButton from '../BackButton'
import Loader from '../Loader';
import { ToastContainer } from 'react-toastify'


function MovableVehicleIndexForm() {

  const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
  const [FirmStep, setFirmStep] = useState(1)
  const [allFormData, setAllFormData] = useState({})
  const [responseScreen, setresponseScreen] = useState()
  const [reviewData, setreviewData] = useState({})
  const [show, setshow] = useState(false)

  const showLoader = (val) => {
    setshow(val);
  }

  const { api_postMovableVehicleApplication } = AdvertisementApiList()

  //activating notification if no owner or no floor added
  const notify = (toastData, type) => {
    toast.dismiss();
    if (type == 'success') {
      toast.success(toastData)
    }
    if (type == 'error') {
      toast.error(toastData)
    }
  };


  //{*** COLLECTING ALL FORM DATA***}//
  const collectAllFormData = (key, formData, reviewIdName) => {
    console.log('prev of all Data', allFormData)
    console.log("review name by id in index...", reviewIdName)
    setAllFormData({ ...allFormData, [key]: formData })


    if (key == 'movableVehicle') {
      console.log("data collecting by key", key, 'formData', formData, 'reviewData', reviewIdName)
      setreviewData({ ...reviewData, [key]: reviewIdName })
    }
    else {
      console.log('data not in review ===', key, '===', formData, 'preview...', reviewIdName)
      setreviewData({ ...reviewData, [key]: formData })
    }
  }
  console.log("all form data in index", allFormData)


  // SUBMIT FORM //
  const submitButtonToggle = () => {
    console.log('final form ready to submit...', allFormData)
    submitMovableVehicleForm()
  }

  const submitMovableVehicleForm = () => {
    const requestBody = {
      // ulbId: allFormData?.movableVehicle?.ulb,
      ulbId: 2,
      deviceId: "movableVehicle",
      applicant: allFormData?.movableVehicle?.applicantName,
      father: allFormData?.movableVehicle?.fatherName,
      email: allFormData?.movableVehicle?.email,
      residenceAddress: allFormData?.movableVehicle?.residenceAddress,
      wardId: allFormData?.movableVehicle?.residenceWardNo,
      permanentAddress: allFormData?.movableVehicle?.permanentAddress,
      permanentWardId: allFormData?.movableVehicle?.permanentWardNo,
      mobile: allFormData?.movableVehicle?.mobileNo,
      aadharNo: allFormData?.movableVehicle?.aadharNo,
      licenseFrom: allFormData?.movableVehicle?.licenseFrom,
      licenseTo: allFormData?.movableVehicle?.licenseTo,
      entityName: allFormData?.movableVehicle?.entityName,
      tradeLicenseNo: allFormData?.movableVehicle?.tradeLicenseNo,
      gstNo: allFormData?.movableVehicle?.gstNo,
      vehicleNo: allFormData?.movableVehicle?.vehicleNo,
      vehicleType: allFormData?.movableVehicle?.vehicleType,
      vehicleName: allFormData?.movableVehicle?.vehicleName,
      brandDisplayed: allFormData?.movableVehicle?.brandDisplayedInVehicle,
      frontArea: allFormData?.movableVehicle?.frontArea,
      rearArea: allFormData?.movableVehicle?.rearArea,
      sideArea: allFormData?.movableVehicle?.sideOneArea,
      topArea: allFormData?.movableVehicle?.topArea,
      displayType: allFormData?.movableVehicle?.displayType,

      documents: allFormData?.movableVehicleDoc?.[0]

    }
    console.log('request body...', requestBody)
    axios.post(`${api_postMovableVehicleApplication}`, requestBody, ApiHeader())
      .then(function (response) {
        console.log('response after data submitted', response.data.data)
        setresponseScreen(response.data.data)
        notify('submitted successfully', 'success')
      })
      .catch(function (error) {
        console.log('errorrr.... ', error);
        notify('failed to submit', 'error')

      })
  }


  // passing values in components
  const values = {
    setFormIndex: setFormIndex,
    showLoader: showLoader,
    collectFormDataFun: collectAllFormData,
    toastFun: notify,
  }

  console.log("response screen", responseScreen)
  if (responseScreen?.status == true) {
    return (
      <>
        <AdvrtSuccessScreen responseScreenData={responseScreen} />
      </>
    )
  }


  return (
    <>
      <div className=''>
        <Loader show={show} />
      </div>
      <ToastContainer position="top-right"
        autoClose={2000} />
      <div className='overflow-x-clip'>
        <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 bg-white p-2 rounded-md shadow-md shadow-violet-200  '>
          <div className=''>
            <div className='flex flex-row '>
              <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold'>Movable Vehicle</h1>
            </div>
            <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>
              You Can Get License To Advertise Your Business Name On Your Shop
            </h1>
          </div>
          <div>
            <div className='flex flex-row mt-2 float-right'>
              <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle'>&emsp; <strong className='text-2xl text-violet-600 '>{3 - formIndex}
              </strong> more screen</span>
              <img src='https://cdn-icons-png.flaticon.com/512/1917/1917802.png' className='h-10 mr-4  opacity-80 float-right ml-4' />
              <div className='mt-2'>
                <BackButton />
              </div>
            </div>
          </div>
        </div>
        <div className={`${formIndex == 1 ? 'translate-x-0' : 'translate-x-full'} transition-all `}><MovableVehicleForm values={values} /></div>
        <div className={`${formIndex == 2 ? 'translate-x-0' : 'translate-x-full'} transition-all  `}><MovableVehicleDocForm values={values} /></div>
        <div className={`${formIndex == 3 ? 'translate-x-0' : 'translate-x-full'} transition-all  `}><ReviewMovableApplication values={values} reviewIdNameData={reviewData} allFormData={allFormData} submitFun={submitButtonToggle} /></div>
      </div>
    </>
  )
}

export default MovableVehicleIndexForm