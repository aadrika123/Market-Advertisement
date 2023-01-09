import React, { useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import AdvrtSuccessScreen from '../AdvrtSuccessScreen'
import MovableVehicleDocForm from './MovableVehicleDocForm'
import MovableVehicleForm from './MovableVehicleForm'
import ReviewMovableApplication from './ReviewMovableApplication'
import axios from 'axios'
import ApiHeader from '../../../Compnents/ApiHeader'


function MovableVehicleIndexForm() {

  const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
  const [animateform1, setAnimateform1] = useState('translate-x-0') //slide animation control state for self advertisement form
  const [animateform2, setAnimateform2] = useState('pl-20 translate-x-full')//slide animation control state for document form
  const [animateform4, setAnimateform4] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
  const [animateform3, setAnimateform3] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
  const [FirmStep, setFirmStep] = useState(1)
  const [regCurrentStep, setRegCurrentStep] = useState(1)
  const [colorCode, setcolorCode] = useState(false)
  const [allFormData, setAllFormData] = useState({})
  const [responseScreen, setresponseScreen] = useState()
  const [reviewData, setreviewData] = useState({})



  const { api_postMovableVehicleApplication } = AdvertisementApiList()

  const backFun = (formIndex) => {
    let tempFormIndex = formIndex
    if (tempFormIndex == 1) { //backward by current form index 2
      setFormIndex(1) // go to form index 1 since back from index 2
      setAnimateform1('translate-x-0') // always setstate one index less than current index
      setAnimateform2('pl-20 translate-x-full') //always current index setstate
    }
    if (tempFormIndex == 2) { //backward by current form index 2
      setFormIndex(1) // go to form index 1 since back from index 2
      setAnimateform1('translate-x-0') // always setstate one index less than current index
      setAnimateform2('pl-20 translate-x-full') //always current index setstate
    }
    if (tempFormIndex == 3) {
      setFormIndex(2)
      setAnimateform2('translate-x-0')
      setAnimateform3('pl-20 translate-x-full')
    }
    if (tempFormIndex == 4) {
      setFormIndex(4)
      setAnimateform3('translate-x-0')
      setAnimateform4('pl-20 translate-x-full')
    }

  }
  const nextFun = (formIndex) => {
    let tempFormIndex = formIndex
    if (tempFormIndex == 1) { //forward by current form index 1
      setFormIndex(2) //go to form index 2 since forward from index 1
      setAnimateform1(' -translate-x-full right-80')  //always current index setstate
      setAnimateform2('pl-0 translate-x-0') // always setstate one index greater than current index
    }
    if (tempFormIndex == 2) {
      setFormIndex(3)
      setAnimateform2('-translate-x-full right-80')
      setAnimateform3('pl-0 translate-x-0')
    }
    if (tempFormIndex == 3) {
      setFormIndex(3)
      setAnimateform2('-translate-x-full right-80')
      setAnimateform3('pl-0 translate-x-0')
    }
    if (tempFormIndex == 4) {
      setFormIndex(4)
      setAnimateform3('-translate-x-full right-80')
      setAnimateform4('pl-0 translate-x-0')
    }
  }


  //activating notification if no owner or no floor added
  const notify = (toastData, actionFlag) => {
    toast.dismiss();
    { actionFlag == 'success' && toast.success(toastData) }
    { actionFlag == 'notice' && toast.warn(toastData) }
    { actionFlag == 'error' && toast.error(toastData) }
  };


  ///////////{*** COLLECTING ALL FORM DATA***}/////////
  const collectAllFormData = (key, formData, reviewIdName) => {
    console.log('prev of all Data', allFormData)
    console.log("review name by id in index...", reviewIdName)
    setAllFormData({ ...allFormData, [key]: formData })


    if (key == 'selfAdvertisement') {
      console.log("data collecting by key", key, 'formData', formData, 'reviewData', reviewIdName)
      setreviewData({ ...reviewData, [key]: reviewIdName })
    }
    else {
      console.log('data not in review ===', key, '===', formData, 'preview...', reviewIdName)
      setreviewData({ ...reviewData, [key]: formData })
    }
  }
  console.log("all form data in index", allFormData)


  ///// SUBMIT FORM /////
  const submitButtonToggle = () => {
    // alert("submitted")
    console.log('final form ready to submit...', allFormData)
    submitMovableVehicleForm()
  }

  const submitMovableVehicleForm = () => {
    const requestBody = {
      ulbId: allFormData?.movableVehicle?.ulb,
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

      aadharDoc: allFormData?.movableVehicleDoc?.aadharDoc,
      tradeDoc: allFormData?.movableVehicleDoc?.tradeLicenseDoc,
      vehiclePhotoDoc: allFormData?.movableVehicleDoc?.vehiclePhoto,
      ownerBookDoc: allFormData?.movableVehicleDoc?.ownerBook,
      drivingLicenseDoc: allFormData?.movableVehicleDoc?.drivingLicense,
      insuranceDoc: allFormData?.movableVehicleDoc?.insurancePhoto,
      gstDoc: allFormData?.movableVehicleDoc?.gstNoPhoto,

    }

    console.log('request body...', requestBody)

    // let token = '4488|gPfHYbujCw4bayISKb2vewyGODpH0CAPckESj9Cb';
    // console.log("token in geotagging", token)
    // const header = {
    //   headers: {
    //     "Authorization": `Bearer ${token}`,
    //     'Content-type': 'multipart/form-data',
    //     "Accept": 'application/json',
    //   }
    // }
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

  console.log("response screen", responseScreen)
  if (responseScreen?.status == true) {
    return (
      <>
        <AdvrtSuccessScreen responseScreenData={responseScreen} />
      </>
    )
  }

  return (
    <div>
      <div className='bg-white p-1 rounded-md shadow-md shadow-violet-200 '>
        <div className='flex flex-row '>
          <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold '>Movable Vehicle</h1>
        </div>
        <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1>
        <div className='flex flex-row float-right'>
          {FirmStep == 1 &&
            <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle -mt-10'>&emsp; <strong className='text-2xl text-violet-600 '>{3 - formIndex}
            </strong> more screen</span>}
          <img src='https://cdn-icons-png.flaticon.com/512/1917/1917802.png' className='h-10 mr-4  opacity-80 float-right -mt-12 ml-4' />
        </div>

      </div>
      <div className={`${animateform1} transition-all relative`}><MovableVehicleForm collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>
      <div className={`${animateform2} transition-all relative`}><MovableVehicleDocForm collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>
      <div className={`${animateform3} transition-all relative `}><ReviewMovableApplication reviewIdNameData={reviewData} allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>
    </div>
  )
}

export default MovableVehicleIndexForm