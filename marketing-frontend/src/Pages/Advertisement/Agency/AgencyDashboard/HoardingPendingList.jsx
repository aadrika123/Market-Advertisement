import axios from 'axios'
import Modal from 'react-modal';
import React, { useEffect, useState } from 'react'
import AdvertisementApiList from '../../../../Compnents/AdvertisementApiList'
import ApiHeader from '../../../../Compnents/ApiHeader'
import Loader from '../../Loader'
import HoardingApplicationFullDetail from '../Hoarding/HoardingApplicationFullDetail';
import { useNavigate } from 'react-router-dom';

const customStyles = {
    content: {
        top: '50%',
        left: '50%',
        right: 'auto',
        bottom: 'auto',
        marginRight: '-50%',
        transform: 'translate(-50%, -50%)',
        backgroundColor: 'white',
        border: 'none'
    },
};
Modal.setAppElement('#root');


function HoardingPendingList(props) {

    const { api_getHoardingPendingApplicationList } = AdvertisementApiList()

    let labelStyle = "mt-6 -ml-7 text-xs text-gray-600 font-semibold"
    let inputStyle = "mt-6 -ml-7 mb-2 text-sm text-gray-800 text-left font-bold"

    const navigate = useNavigate()

    const [applicationDetail, setapplicationDetail] = useState()
    const [applicationType, setapplicationType] = useState()
    const [applicationNo, setapplicationNo] = useState()
    const [modalIsOpen, setIsOpen] = useState(false);
    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }

    const [documentList, setdocumentList] = useState()

    const modalAction = (applicationId, applicationType) => {
        console.log("..............application id..............", applicationId)
        console.log("..............application type..............", applicationType)
        setapplicationNo(applicationId)
        setapplicationType(applicationType)
        openModal()
    }

    ///////////{*** GET APPLICATION LIST***}/////////
    useEffect(() => {
        getApplicationDetail()
    }, [])
    const getApplicationDetail = () => {
        props.showLoader(true);
        const requestBody = {
            // applicationId: applicationId,
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getHoardingPendingApplicationList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('hoarding active applications', response)
                setapplicationDetail(response.data.data)
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
    }
    console.log("hoarding application...1", applicationDetail)

    return (
        <>

            {applicationDetail == undefined || applicationDetail == null || applicationDetail == '' ?
                <div className=''>
                    <h1 className='text-lg ml-2 font-semibold text-center'>NO DATA
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-violet-600 mx-auto">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                        </svg>

                    </h1>
                </div>

                :
                <>
                    {applicationDetail?.data?.slice(0, 4)?.map((data) => (
                        <div className="col-span-3  p-2">
                            <div className="bg-violet-100 ">
                                <div className="flex p-2 text-gray-600  text-xs">
                                    <h1 className="flex-1 ">Application No.</h1>
                                    <h1 className="flex-1 font-bold">{data?.application_no}</h1>
                                </div>
                                <div className="flex p-2 text-gray-600  text-xs">
                                    <h1 className="flex-1">Applied Date</h1>
                                    <h1 className="flex-1 font-bold">{data?.application_date}</h1>
                                </div>
                                <div className='text-right'>
                                    <button id={data?.id} value='Active' className=' px-1 text-violet-700 text-sm border-b border-violet-700  font-semibold' onClick={(e) => modalAction(data?.id, e.target.value)}>view</button>
                                </div>
                            </div>
                        </div>
                    ))}
                </>
            }
                <div className='text-right'><button className='mt-20 text-sm bg-indigo-500  shadow-lg px-2 text-white' onClick={() => navigate
                    (`/hoarding-pending-list`)}>View All</button>
                </div>
            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0 " style={{ 'width': '80vw', 'height': '100%' }}>
                    <HoardingApplicationFullDetail data={applicationNo} applicationType={applicationType} showLoader={props.showLoader} closeModal={closeModal} />
                </div>
            </Modal>

        </>
    )
}

export default HoardingPendingList