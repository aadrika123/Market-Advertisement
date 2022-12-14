import axios from 'axios';
import { useFormik } from 'formik';
import { values } from 'lodash';
import React, { useEffect, useState } from 'react'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../Compnents/ApiHeader';
import AdvertisementDocument from '../AdvertisementDocument';
import SelfAdvertisementDocTable from './SelfAdvertisementDocTable';
import SelfAdvrtInformationScreen from './SelfAdvrtInformationScreen';
// import * as yup from 'yup'


function SelfAdvertisementDocForm(props) {

    const { api_getSelfAdvertDocList } = AdvertisementApiList()
    const [collectDoc, setcollectDoc] = useState([]);
    const [docList, setdocList] = useState()

    const collectAllData = (key, formData) => {
        console.log("prev data in document", collectDoc);
        setcollectDoc({ ...collectDoc, [key]: formData });
    };
    console.log("all data doc", collectDoc)

    // const validationSchema = yup.object({
    //     aadharDoc: yup.mixed(),
    //     tradeLicenseDoc: yup.mixed(),
    //     photoWithGps: yup.mixed(),
    //     holdingNoDoc: yup.mixed(),
    //     gstDocPhoto: yup.mixed(),
    //     proceedingPhoto1: yup.mixed(),
    //     proceedingPhoto2: yup.mixed(),
    //     proceedingPhoto3: yup.mixed(),
    //     uploadExtraDoc1: yup.mixed(),
    //     uploadExtraDoc2: yup.mixed(),
    // })


    const handleDocument = () => {
        // alert("save")
        props.collectFormDataFun('selfAdvertisementDoc', [collectDoc])
        props?.nextFun(2)
    }

    ///////////{*** GET DOCUMENT LIST***}/////////
    useEffect(() => {
        getDocumentList()
    }, [])
    const getDocumentList = () => {
        const requestBody = {
            deviceId: "selfAdvert",
        }
        axios.post(`${api_getSelfAdvertDocList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('self advertisement document list', response.data.data)
                setdocList(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }

    return (
        <>
            {/* <AdvertisementDocument openDocviewModal={openDocviewModal} /> */}
            {/* upload document */}
            <div className='-mt-[44rem]'>
                {/* <form onSubmit={formik.handleSubmit} onChange={handleOnChange} encType="multipart/form-data"> */}
                <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto pb-8 p-2 mt-3'>
                    <div className='col-span-8 p-1 border border-dashed border-violet-800'>
                        <div className="p-1">
                            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1 p-1 bg-white">
                                <div>
                                    <h1 className='text-left  text-lg ml-12 text-gray-600 font-sans font-semibold '>Document</h1>
                                </div>

                                <div>
                                    <h1 className='text-center text-lg ml-4 text-gray-600 font-sans font-semibold'>Preview</h1>
                                </div>
                                <div>
                                    <h1 className='text-center text-lg ml-4 text-gray-600 font-sans font-semibold'>Upload</h1>
                                </div>
                                <div>
                                    <h1 className='text-center text-lg ml-4 text-gray-600 font-sans font-semibold'>Action</h1>
                                </div>
                            </div>
                            <div className='mt-2'>
                                {docList?.SelfAdvertisements?.map((items, index) => (
                                    <SelfAdvertisementDocTable data={items} collectAllDataFun={collectAllData} mykey={index} />
                                ))}
                            </div>
                        </div>
                        <div className="grid grid-cols-12 w-full p-3">
                            <div className='md:pl-0 col-span-6'>
                                <button type="button" class="py-2 px-4 text-xs inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(2)}>back</button>
                            </div>
                            <div className='col-span-6'>
                                <button type="button" className="float-right text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0" onClick={handleDocument}>Save & Next</button>
                            </div>
                        </div>
                    </div>
                    <div className='col-span-4'>
                        <div className='-mt-20'>
                            <SelfAdvrtInformationScreen />
                        </div>
                    </div>
                </div>
                {/* </form> */}
            </div>
        </>
    )
}

export default SelfAdvertisementDocForm