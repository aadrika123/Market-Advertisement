import axios from 'axios'
import React, { useState } from 'react'
import AdvrtSuccessScreen from '../../AdvrtSuccessScreen'
import { ToastContainer } from 'react-toastify'
import Loader from '../../Loader'
import ApiHeader from '../../../../Compnents/ApiHeader'
import BackButton from '../../BackButton'
import HoardingForm1 from './HoardingForm1'
import HoardingForm2 from './HoardingForm2'
import HoardingForm3 from './HoardingForm3'
import HoardingFormDoc from './HoardingFormDoc'
import AdvertisementApiList from '../../../../Compnents/AdvertisementApiList'
import HoardingReview from './HoardingReview'
function HoardingIndex() {

    const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
    const [animateform1, setAnimateform1] = useState('translate-x-0') //slide animation control state for self advertisement form
    const [animateform2, setAnimateform2] = useState('pl-20 translate-x-full')//slide animation control state for document form
    const [animateform3, setAnimateform3] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [animateform4, setAnimateform4] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [animateform5, setAnimateform5] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [allFormData, setAllFormData] = useState({})
    const [responseScreen, setresponseScreen] = useState()
    const [reviewData, setreviewData] = useState({})
    const [show, setshow] = useState(false)

    const showLoader = (val) => {
        setshow(val);
    }


    const { api_postHoardingApplication } = AdvertisementApiList()

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
            setFormIndex(3)
            setAnimateform3('translate-x-0')
            setAnimateform4('pl-20 translate-x-full')
        }
        if (tempFormIndex == 5) {
            setFormIndex(4)
            setAnimateform4('translate-x-0')
            setAnimateform5('pl-20 translate-x-full')
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
        if (tempFormIndex == 5) {
            setFormIndex(5)
            setAnimateform4('-translate-x-full right-80')
            setAnimateform5('pl-0 translate-x-0')
        }
    }


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

    const collectAllFormData = (key, formData, reviewIdName) => {
        console.log('prev of all Data', allFormData)
        console.log("review name by id in index...", reviewIdName)
        setAllFormData({ ...allFormData, [key]: formData })

        if (key == 'hoarding1') {
            console.log("data collecting by key", key, 'formData', formData, 'reviewData', reviewIdName)
            setreviewData({ ...reviewData, [key]: reviewIdName })
        }
        else {
            console.log('data not in review ===', key, '===', formData, 'preview...', reviewIdName)
            setreviewData({ ...reviewData, [key]: formData })
        }
    }
    console.log("all form data in index", allFormData)
    console.log("all form data in index for doc", allFormData?.selfAdvertisementDoc?.[0])

    ///// SUBMIT FORM /////
    const submitButtonToggle = () => {
        console.log('final form ready to submit...', allFormData)
        submitHoardingApplication()
    }

    const submitHoardingApplication = () => {
        const requestBody = {
            // ulbId: allFormData?.agency?.ulb,
            ulbId: 2,
            // deviceId: "privateLand",
            accountNo: allFormData?.hoarding1?.accountNo,
            applicationNo: allFormData?.hoarding1?.applicationNo,
            bankName: allFormData?.hoarding1?.bankName,
            city: allFormData?.hoarding1?.city,
            dateGranted: allFormData?.hoarding1?.dateGranted,
            district: allFormData?.hoarding1?.district,
            ifscCode: allFormData?.hoarding1?.ifscCode,
            permitExpiredIssue: allFormData?.hoarding1?.permitExpireDate,
            permitDateIssue: allFormData?.hoarding1?.permitIssueDate,
            permitNo: allFormData?.hoarding1?.permitNumber,
            roadStreetAddress: allFormData?.hoarding1?.roadStreetAddress,
            totalCharge: allFormData?.hoarding1?.totalFeeCharged,
            wardId: allFormData?.hoarding1?.wardNo,
            zoneId: allFormData?.hoarding1?.zone,
            typology: allFormData?.hoarding2?.checked,
            displayArea: allFormData?.hoarding3?.displayArea,
            displayLandMark: allFormData?.hoarding3?.displayLandmark,
            displayLocation: allFormData?.hoarding3?.displayLocation,
            displayStreet: allFormData?.hoarding3?.displayStreet,
            illumination: allFormData?.hoarding3?.illumination,
            indicateFacing: allFormData?.hoarding3?.indicateFace,
            material: allFormData?.hoarding3?.material,
            heigth: allFormData?.hoarding3?.mediaHeight,
            length: allFormData?.hoarding3?.mediaLength,
            size: allFormData?.hoarding3?.mediaSize,
            propertyOwnerAddress: allFormData?.hoarding3?.ownerAddress,
            propertyOwnerCity: allFormData?.hoarding3?.ownerCity,
            propertyOwnerName: allFormData?.hoarding3?.ownerName,
            propertyOwnerMobileNo: allFormData?.hoarding3?.ownerPhoneNo,
            propertyOwnerPincode: allFormData?.hoarding3?.ownerPinCode,
            propertyType: allFormData?.hoarding3?.propertyType,
            documents: allFormData?.hoardingDoc?.[0]

        }

        console.log('request body...', requestBody)
        axios.post(`${api_postHoardingApplication}`, requestBody, ApiHeader())
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
        <>

            <div className=''>
                <Loader show={show} />
            </div>
            <ToastContainer position="top-right"
                autoClose={2000} />
            <div className='overflow-x-clip'>
                <div className='bg-white p-1 rounded-md shadow-md shadow-violet-200 '>
                    <div className='flex flex-row '>
                        <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold '>Hoarding</h1>
                    </div>
                    <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1>
                    <div className='flex flex-row float-right'>
                        {/* {FirmStep == 1 && */}
                        <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle -mt-10'>&emsp; <strong className='text-2xl text-violet-600'>{5 - formIndex}
                        </strong> more screen</span>
                        <BackButton />
                        <img src='https://cdn-icons-png.flaticon.com/512/1684/1684121.png' className='h-10 mr-4  opacity-80 float-right -mt-12 ml-4' />
                    </div>

                </div>

                <div className={`${animateform1} transition-all relative`}><HoardingForm1 showLoader={showLoader} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>
                <div className={`${animateform2} transition-all relative -mt-[40.5rem]  `}><HoardingForm2 allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>

                <div className={`${animateform3} transition-all relative -mt-[50rem]`}><HoardingForm3 collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>

                <div className={`${animateform4} transition-all relative -mt-[40.5rem]`}><HoardingFormDoc reviewIdNameData={reviewData} allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>
                <div className={`${animateform5} transition-all relative -mt-[40.5rem]`}><HoardingReview reviewIdNameData={reviewData} allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>

            </div>
        </>
    )
}

export default HoardingIndex