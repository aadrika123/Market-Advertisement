import Modal from 'react-modal';
import axios from 'axios'
import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../Compnents/ApiHeader';
import MovablePaymentModal from './MovablePaymentModal';
import ViewAppliedApplication from '../ViewAppliedApplication';
import MovableVehicleApplicationFullDetails from './MovableVehicleApplicationFullDetails';
// import MovablePaymentModal from './MovablePaymentModal';
// import ViewAgencyApplicationFullDetails from './ViewAgencyApplicationFullDetails';
// import NoData from '../../../assets/gifFolder/NoData.gif    '



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

function MovablePendingApplicationList(props) {
    const navigate = useNavigate()


    const { api_getMovableAppliedApplicationList, api_getMovableApprovedApplicationList, api_getMovableRejectedApplicationList, api_getMovableVehicleApplicationDetailForPayment } = AdvertisementApiList()

    const [applyList, setapplyList] = useState('hidden')
    const [appliedApplication, setappliedApplication] = useState()
    const [approvedApplication, setapprovedApplication] = useState()
    const [rejectedApplication, setrejectedApplication] = useState()
    const [openPaymentModal, setOpenPaymentModal] = useState(0)
    const [applicationIdDetail, setapplicationIdDetail] = useState()

    const [modalIsOpen, setIsOpen] = useState(false);
    const [applicationNo, setapplicationNo] = useState()
    const [applicationType, setapplicationType] = useState()
    const [tabIndex, settabIndex] = useState(1)
    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }


    const modalAction = (applicationId, applicationType) => {
        console.log("..............application id..............", applicationId)
        console.log("..............application type..............", applicationType)
        console.log("application id", applicationId)
        setapplicationNo(applicationId)
        setapplicationType(applicationType)
        openModal()
    }
    console.log("application no. for modal", applicationNo)

    const showApplyList = () => {
        applyList == 'hidden' ? setapplyList('') : setapplyList('hidden')
    }

    ///////////{*** GET APPLICATION LIST***}/////////
    useEffect(() => {
        getApplicationList()
    }, [])
    const getApplicationList = () => {
        props.showLoader(true);
        const requestBody = {
            deviceId: "selfAdvert",
        }
        axios.post(`${api_getMovableAppliedApplicationList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('applied application in self advertisement', response.data.data)
                setappliedApplication(response.data.data)
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

    ///////////{*** APPROVED APPLICATION LIST***}/////////
    useEffect(() => {
        getApprovedApplicationList()
    }, [])
    const getApprovedApplicationList = () => {
        props.showLoader(true);
        const requestBody = {
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getMovableApprovedApplicationList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('APPROVED LIST', response.data.data)
                setapprovedApplication(response.data.data)
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

    console.log("approved application list...agency", approvedApplication)

    ///////////{*** REJECTED APPLICATION LIST***}/////////
    useEffect(() => {
        getRejectedApplicationList()
    }, [])
    const getRejectedApplicationList = () => {
        props.showLoader(true);
        const requestBody = {
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getMovableRejectedApplicationList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('rejected application in self advertisement', response.data.data)
                setrejectedApplication(response.data.data)
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


    const handlePayment = () => {
        props.showLoader(true);
        console.log("application id", e.target.id)
        let applicationId = e.target.id
        const requestBody = {
            applicationId: applicationId
        }
        axios.post(`${api_getMovableVehicleApplicationDetailForPayment}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('application detail for payment 1', response.data.data)
                setapplicationIdDetail(response.data.data)
                setOpenPaymentModal(prev => prev + 1)
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


    console.log("rejected application list...", rejectedApplication)
    console.log("application  list...1", appliedApplication?.data)
    console.log("application  no.", applicationNo)

    return (
        <>
            {/***** section 3 ******/}
            {/* <MovablePaymentModal openPaymentModal={openPaymentModal} applicationDetails={applicationIdDetail} showLoader={props.showLoader} /> */}
            <MovablePaymentModal openPaymentModal={openPaymentModal} applicationDetails={applicationIdDetail} showLoader={props.showLoader} />

            <div className=''>
                <div className='flex flex-col md:flex lg:flex bg-white p-2 mb-4 shadow-md  text-lg  font-semibold text-gray-500  '>
                    {tabIndex == 1 && <h1 className='flex-1'>Movable Vehicle <span className='text-indigo-500 underline'>Pending Application</span> </h1>}
                    {tabIndex == 2 && <h1 className='flex-1'>Movable Vehicle <span className='text-indigo-500 underline'>Approved Application</span></h1>}
                    {tabIndex == 3 && <h1 className='flex-1'>Movable Vehicle <span className='text-indigo-500 underline'>Rejected Application</span></h1>}
                    <div className='flex-1  md:-mt-7 lg:-mt-7'>
                        <button type='button' className='text-xs bg-indigo-300 px-1 py-1 mr-2 text-indigo-600 border border-indigo-500  rounded leading-5 float-right focus:bg-indigo-500  focus:shadow-lg focus:text-white dark:bg-white' onClick={() => settabIndex(3)}>Rejected Application</button>
                        <button type='button' className='text-xs bg-indigo-300 px-1 py-1 mr-2 text-indigo-600 border border-indigo-500  rounded leading-5 float-right focus:bg-indigo-500  focus:shadow-lg focus:text-white dark:bg-white' onClick={() => settabIndex(2)}>Approved Application</button>
                        <button type='button' className='text-xs bg-indigo-300 px-1 py-1 mr-2 text-indigo-600 border border-indigo-500  rounded leading-5 float-right focus:bg-indigo-500  focus:shadow-lg focus:text-white dark:bg-white' onClick={() => settabIndex(1)}>Pending Application</button>

                    </div>
                </div>
                {/* pending application list for self advertisement */}
                {tabIndex == 1 &&
                    <div>
                        {appliedApplication == undefined || appliedApplication == null ?
                            <>
                                <h1 className='text-center text-2xl text-gray-500'>No Pending Application Found ... </h1>
                                <img src='https://cdn-icons-png.flaticon.com/512/7466/7466140.png' className='mx-auto h-36 mt-4' />

                            </>
                            :
                            <div className=''>
                                {appliedApplication?.data?.map((items) => (
                                    <div className='col-span-3 p-3 h-auto mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md top-0 absolute'>
                                            <h1 className='text-xs text-center text-white '>Movable Application</h1>
                                        </div>
                                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 p-2'>
                                            <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}-({items?.id})</span></h1>
                                            <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                            <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                                        </div>
                                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 p-2'>
                                            <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                            {/* <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address
                                    }</span></h1> */}
                                        </div>

                                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1  p-2'>
                                            <div className='flex-1 justify-end'>
                                                <button type="button" id={items?.id} value='Active' class="  float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={(e) => modalAction(items?.id, e.target.value)}>View</button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        }
                    </div>
                }

                {/* approved application list for self advertisement */}
                {tabIndex == 2 &&
                    <div>
                        {approvedApplication == undefined || approvedApplication == null ?
                            <>
                                <h1 className='text-center text-2xl text-gray-500'>No Approved Application Found ... </h1>
                                <img src='https://cdn-icons-png.flaticon.com/512/7466/7466140.png' className='mx-auto h-36 mt-4' />

                            </>
                            :
                            <div className=''>
                                {approvedApplication?.data?.map((items) => (
                                    <div className='col-span-3 p-3 h-auto mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md top-0 absolute '>
                                            <h1 className='text-xs  text-center text-white '>Movable Application</h1>
                                        </div>
                                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 p-2'>
                                            <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}-({items?.temp_id
                                            })</span></h1>
                                            <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                            <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                                        </div>
                                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 p-2'>
                                            <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                            {/* <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address */}
                                            {/* }</span></h1> */}

                                        </div>

                                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1  p-2'>
                                            <div className='flex-1 justify-end'>
                                                <button type="button" id={items?.id
                                                } value='Approve' class="float-right shadow-lg text-xs  px-4 py-1 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={(e) => modalAction(items?.id, e.target.value)}>View</button>

                                                {items.payment_status == 1 ?
                                                    <h1 className='text-xs text-green-500'><span><button className='float-right  shadow-lg mr-2 rounded leading-5 border-gray-50 bg-indigo-500 px-2 py-1 text-white' onClick={() => navigate('/approvalLetter')}>Download Approval Letter</button></span></h1>
                                                    :
                                                    <button type="button" id={items?.id
                                                    } value='Reject' class="float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0 mr-2" onClick={handlePayment}>Pay</button>
                                                }
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        }
                    </div>
                }


                {/* rejected  application list for self advertisement */}
                {tabIndex == 3 &&
                    <div>
                        {rejectedApplication == undefined || rejectedApplication == null ?
                            <>
                                <h1 className='text-center text-2xl text-gray-500'>No Rejected Application Found ... </h1>
                                <img src='https://cdn-icons-png.flaticon.com/512/7466/7466140.png' className='mx-auto h-36 mt-4' />
                            </>
                            :
                            <div className=''>
                                {rejectedApplication?.data?.map((items) => (
                                    <div className='col-span-3 p-3 h-auto mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md top-0 absolute '>
                                            <h1 className='text-xs  text-center text-white '>Movable Application</h1>
                                        </div>
                                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 p-2'>
                                            <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}-({items?.temp_id
                                            })</span></h1>
                                            <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                            <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                                        </div>
                                        <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 p-2'>
                                            <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                            {/* <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address
                                            }</span></h1> */}

                                        </div>

                                        <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1  p-2'>
                                            <div className='flex-1 justify-end'>
                                                <button type="button" id={items?.id
                                                } class="  float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={(e) => modalAction(items?.id, e.target.value)}>View</button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        }
                    </div>
                }
            </div>
            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0" style={{ 'width': '80vw', 'height': '100%' }}>
                    <MovableVehicleApplicationFullDetails data={applicationNo} applicationType={applicationType} showLoader={props.showLoader} closeModal={closeModal} />
                </div>
            </Modal>

        </>
    )
}

export default MovablePendingApplicationList