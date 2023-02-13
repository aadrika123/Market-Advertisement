import React, { useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import AdvrtSuccessScreen from '../AdvrtSuccessScreen'
import PrivateLandDocForm from './PrivateLandDocForm'
import PrivateLandForm from './PrivateLandForm'
import ReviewPrivateLandApplication from './ReviewPrivateLandApplication'
import axios from 'axios'
import ApiHeader from '../../../Compnents/ApiHeader'
import Loader from '../Loader'
import { toast, ToastContainer } from 'react-toastify'
import BackButton from '../BackButton'

function PrivateLandIndexForm() {

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
    const [show, setshow] = useState(false)

    const showLoader = (val) => {
        setshow(val);
    }



    const { api_postPrivateLandApplication } = AdvertisementApiList()
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
    const notify = (toastData, type) => {
        toast.dismiss();
        if (type == 'success') {
            toast.success(toastData)
        }
        if (type == 'error') {
            toast.error(toastData)
        }
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
    console.log("all form data in index for doc", allFormData?.selfAdvertisementDoc?.[0])



    ///// SUBMIT FORM /////
    const submitButtonToggle = () => {
        console.log('final form ready to submit...', allFormData)
        submitPrivateLandForm()
    }

    const submitPrivateLandForm = () => {
        const requestBody = {
            // ulbId: allFormData?.privateLand?.ulb,
            ulbId: 2,
            // deviceId: "privateLand",
            applicant: allFormData?.privateLand?.applicantName,
            father: allFormData?.privateLand?.fatherName,
            email: allFormData?.privateLand?.email,
            residenceAddress: allFormData?.privateLand?.residenceAddress,
            wardId: allFormData?.privateLand?.residenceWardNo,
            permanentAddress: allFormData?.privateLand?.permanentAddress,
            permanentWardId: allFormData?.privateLand?.permanentWardNo,
            mobileNo: allFormData?.privateLand?.mobileNo,
            aadharNo: allFormData?.privateLand?.aadharNo,
            licenseFrom: allFormData?.privateLand?.licenseFrom,
            licenseTo: allFormData?.privateLand?.licenseTo,
            holdingNo: allFormData?.privateLand?.holdingNo,
            tradeLicenseNo: allFormData?.privateLand?.tradeLicenseNo,
            gstNo: allFormData?.privateLand?.gstNo,
            entityName: allFormData?.privateLand?.entityName,
            entityAddress: allFormData?.privateLand?.entityAddress,
            entityWardId: allFormData?.privateLand?.entityWard,
            brandDisplayName: allFormData?.privateLand?.brandDisplayeName,
            brandDisplayAddress: allFormData?.privateLand?.brandDisplayeAddress,
            brandDisplayHoldingNo: allFormData?.privateLand?.holdingNoBrandDisplay,
            displayArea: allFormData?.privateLand?.totalDisplayArea,
            displayType: allFormData?.privateLand?.displayType,
            noOfHoardings: allFormData?.privateLand?.noOfHoarding,
            longitude: allFormData?.privateLand?.longitude,
            latitude: allFormData?.privateLand?.latitude,
            installationLocation: allFormData?.privateLand?.installationLocation,

            documents: allFormData?.privateLandDoc?.[0]

        }

        console.log('request body...', requestBody)
        axios.post(`${api_postPrivateLandApplication}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('response after data submitted', response.data.data)
                setresponseScreen(response.data.data)
                notify('submitted successfully', 'success')
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                notify('failed to submit', 'error')

            })
        console.log("form index", formIndex)
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
            <div className='overflow-x-clip '>
                <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 bg-white p-2 rounded-md shadow-md shadow-violet-200  '>
                    <div className=''>
                        <div className='flex flex-row '>
                            <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold'>Private Land</h1>
                        </div>
                        <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>
                            You Can Get License To Advertise Your Business Name On Your Shop
                        </h1>
                    </div>
                    <div>
                        <div className='flex flex-row mt-2 float-right'>
                            <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle'>&emsp; <strong className='text-2xl text-violet-600 '>{3 - formIndex}
                            </strong> more screen</span>
                            <img src='https://cdn-icons-png.flaticon.com/512/1684/1684121.png' className='h-10 mr-4  opacity-80 float-right ml-4' />
                            <div className='mt-2'>
                                <BackButton />
                            </div>
                        </div>
                    </div>
                </div>
                <div className={`${animateform1} transition-all relative`}><PrivateLandForm values={values} /></div>
                <div className={`${animateform2} transition-all relative md:-mt-[49rem] lg:-mt-[49rem]`}><PrivateLandDocForm values={values} /></div>
                <div className={`${animateform3} transition-all relative md:-mt-[49rem] lg:-mt-[49rem]`}><ReviewPrivateLandApplication values={values} reviewIdNameData={reviewData} allFormData={allFormData} submitFun={submitButtonToggle} /></div>

            </div>
        </>
    )
}

export default PrivateLandIndexForm