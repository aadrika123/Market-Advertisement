import React, { useEffect, useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList'
import SelfAdvertisementDocForm from './SelfAdvertisementDocForm'
import SelfAdvertisementForm1 from './SelfAdvertisementForm1'
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import axios from 'axios'
import ReviewSelfAdvertForm from './ReviewSelfAdvertForm'
import AdvrtSuccessScreen from '../AdvrtSuccessScreen';
import ApiHeader from '../../../Compnents/ApiHeader'



function SelfAdvertisementIndexForm() {
    const [formIndex, setFormIndex] = useState(1) //formindex specifies type of form  at index 1 ...
    const [animateform1, setAnimateform1] = useState('translate-x-0') //slide animation control state for self advertisement form
    const [animateform2, setAnimateform2] = useState('pl-20 translate-x-full')//slide animation control state for document form
    const [animateform4, setAnimateform4] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [animateform3, setAnimateform3] = useState('pl-20  translate-x-full')//slide animation control state for ElectricityWaterDetails form
    const [allFormData, setAllFormData] = useState([])
    const [responseScreen, setresponseScreen] = useState()
    const [reviewData, setreviewData] = useState({})


    const { api_postSelfAdvertApplication } = AdvertisementApiList()

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
        // alert("submitted")
        console.log('final form ready to submit...', allFormData)
        submitSelfAdvertForm()
    }

    const submitSelfAdvertForm = () => {
        const requestBody = {
            // ulbId: allFormData?.selfAdvertisement?.ulb,
            ulbId: 1,
            deviceId: "selfAdvert",
            applicantName: allFormData?.selfAdvertisement?.applicantName,
            licenseYear: allFormData?.selfAdvertisement?.licenseYear,
            fatherName: allFormData?.selfAdvertisement?.fatherName,
            email: allFormData?.selfAdvertisement?.email,
            residenceAddress: allFormData?.selfAdvertisement?.residenceAddress,
            wardId: allFormData?.selfAdvertisement?.residenceWardNo,
            permanentAddress: allFormData?.selfAdvertisement?.permanentAddress,
            permanentWardId: allFormData?.selfAdvertisement?.permanentWardNo,
            entityName: allFormData?.selfAdvertisement?.entityName,
            entityAddress: allFormData?.selfAdvertisement?.entityAddress,
            entityWardId: allFormData?.selfAdvertisement?.entityWardNo,
            mobileNo: allFormData?.selfAdvertisement?.mobileNo,
            aadharNo: allFormData?.selfAdvertisement?.aadharNo,
            tradeLicenseNo: allFormData?.selfAdvertisement?.tradeLicenseNo,
            holdingNo: allFormData?.selfAdvertisement?.holdingNo,
            gstNo: allFormData?.selfAdvertisement?.gstNo,
            longitude: allFormData?.selfAdvertisement?.longitude,
            latitude: allFormData?.selfAdvertisement?.latitude,
            displayArea: allFormData?.selfAdvertisement?.displayArea,
            displayType: allFormData?.selfAdvertisement?.displayType,
            installationLocation: allFormData?.selfAdvertisement?.installationLocation,
            brandDisplayName: allFormData?.selfAdvertisement?.brandDisplayName,

            documents: allFormData?.selfAdvertisementDoc?.[0]

        }

        console.log('request body...', requestBody)
        axios.post(`${api_postSelfAdvertApplication}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('response after data submitted', response.data.data)
                setresponseScreen(response.data.data)
                notify("submitted successfully", "success")
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                notify("failed to submit", "error")

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
            <ToastContainer position="top-right"
                autoClose={2000} />
            <div className='overflow-x-clip '>
                <div className='bg-white p-1 rounded-md shadow-md shadow-violet-200  '>
                    <div className='flex flex-row '>
                        <h1 className='text-2xl ml-4 text-gray-600 font-sans font-semibold '>Self Advertisement</h1>
                    </div>
                    <h1 className='text-xs ml-3 p-1 text-gray-600 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1>
                    <div className='flex flex-row float-right'>

                        <span className='text-md font-bold md:text-xl text-violet-600 text-center  transition-all animate-wiggle -mt-10'>&emsp; <strong className='text-2xl text-violet-600 '>{3 - formIndex}
                        </strong> more screen</span>
                        <img src='https://cdn-icons-png.flaticon.com/512/2376/2376320.png' className='h-10 mr-4  opacity-80 float-right -mt-12 ml-4' />
                    </div>
                </div>


                <div className={`${animateform1} transition-all relative `}><SelfAdvertisementForm1 collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>
                <div className={`${animateform2} transition-all relative `}><SelfAdvertisementDocForm collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} /></div>
                <div className={`${animateform3} transition-all relative `}><ReviewSelfAdvertForm reviewIdNameData={reviewData} allFormData={allFormData} collectFormDataFun={collectAllFormData} backFun={backFun} nextFun={nextFun} toastFun={notify} submitFun={submitButtonToggle} /></div>
            </div>
        </>
    )
}

export default SelfAdvertisementIndexForm