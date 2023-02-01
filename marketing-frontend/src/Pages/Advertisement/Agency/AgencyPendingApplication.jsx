import Modal from 'react-modal';
import axios from 'axios'
import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../Compnents/ApiHeader';
import ViewAppliedApplication from '../ViewAppliedApplication';
import Loader from '../Loader';
import ViewAgencyApplicationFullDetails from './ViewAgencyApplicationFullDetails';
import AdvertPaymentModal from '../AdvertPaymentModal';
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

function AgencyPendingApplication(props) {
    const navigate = useNavigate()


    const { api_getAgencyAppliedApplicationList, api_getAgencyApprovedApplicationList, api_getAgencyRejectedApplicationList } = AdvertisementApiList()

    const [applyList, setapplyList] = useState('hidden')
    const [appliedApplication, setappliedApplication] = useState()
    const [approvedApplication, setapprovedApplication] = useState()
    const [rejectedApplication, setrejectedApplication] = useState()
    const [openPaymentModal, setOpenPaymentModal] = useState(0)

    const [modalIsOpen, setIsOpen] = useState(false);
    const [applicationNo, setapplicationNo] = useState()
    const [tabIndex, settabIndex] = useState(1)
    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }


    const modalAction = (e) => {
        let applicationId = e.target.id
        console.log("application id", applicationId)
        setapplicationNo(applicationId)
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
        axios.post(`${api_getAgencyAppliedApplicationList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('applied agency application ', response.data.data)
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
        axios.post(`${api_getAgencyApprovedApplicationList}`, requestBody, ApiHeader())
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
        axios.post(`${api_getAgencyRejectedApplicationList}`, requestBody, ApiHeader())
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
        console.log("Clicked PAy")
        setOpenPaymentModal(prev => prev + 1)

    }


    console.log("rejected application list...", rejectedApplication)

    console.log("application  list...1", appliedApplication?.data)
    console.log("application  no.", applicationNo)

    return (
        <>
            {/***** section 3 ******/}
            <AdvertPaymentModal openPaymentModal={openPaymentModal} />

            <div className=''>
                <div className='flex bg-white p-2 mb-4 shadow-md  text-lg  font-semibold text-gray-500  '>
                    <h1 className='flex-1'>Agency Application</h1>
                    <div className='flex-1'>
                        <button type='button' className='text-xs bg-indigo-300 px-2 py-1 mr-2 text-indigo-600 border border-indigo-500  rounded leading-5 float-right focus:bg-indigo-500  focus:shadow-lg focus:text-white dark:bg-white' onClick={() => settabIndex(3)}>Rejected Application</button>
                        <button type='button' className='text-xs bg-indigo-300 px-2 py-1 mr-2 text-indigo-600 border border-indigo-500  rounded leading-5 float-right focus:bg-indigo-500  focus:shadow-lg focus:text-white dark:bg-white' onClick={() => settabIndex(2)}>Approved Application</button>
                        <button type='button' className='text-xs bg-indigo-300 px-2 py-1 mr-2 text-indigo-600 border border-indigo-500  rounded leading-5 float-right focus:bg-indigo-500  focus:shadow-lg focus:text-white dark:bg-white' onClick={() => settabIndex(1)}>Pending Application</button>

                    </div>
                </div>
                {/* pending application list for self advertisement */}
                {tabIndex == 1 &&
                    <div className=''>
                        {appliedApplication?.data?.map((items) => (
                            <div className='col-span-3 p-3 h-32 mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                                <div className='bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md '>
                                    <h1 className='text-xs -mt-3 text-center text-white '>Agency Application</h1>
                                </div>
                                <div className='flex flex-row space-x-8 p-2'>
                                    <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}-({items?.id})</span></h1>
                                    <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                    <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                                </div>
                                <div className='flex flex-row space-x-8 p-2'>
                                    <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                    {/* <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address
                                    }</span></h1> */}
                                </div>

                                <div className='flex space-x-3 p-2'>
                                    <div className='flex-1 justify-end'>
                                        <button type="button" id={items?.id} class="  float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={modalAction}>View</button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                }

                {/* approved application list for self advertisement */}
                {tabIndex == 2 &&
                    <div>
                        {approvedApplication.data.length == 0 || approvedApplication == undefined || approvedApplication == null ?
                            <img src='https://img.freepik.com/free-vector/no-data-concept-illustration_114360-536.jpg?w=826&t=st=1674804842~exp=1674805442~hmac=00dcb272b055b7c82777562fa066650f13c6eaa2d78a2dc5709ae5bbda951e20' className='mx-auto h-96' />
                            :
                            <div className=''>
                                {approvedApplication?.data?.map((items) => (
                                    <div className='col-span-3 p-3 h-32 mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                                        <div className='bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md '>
                                            <h1 className='text-xs -mt-3 text-center text-white '>Agency Application</h1>
                                        </div>
                                        <div className='flex flex-row space-x-8 p-2'>
                                            <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}-({items?.temp_id
                                            })</span></h1>
                                            <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                            <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                                        </div>
                                        <div className='flex flex-row space-x-8 p-2'>
                                            <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                            {/* <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address */}
                                            {/* }</span></h1> */}

                                        </div>

                                        <div className='flex space-x-3 p-2'>
                                            <div className='flex-1 justify-end'>
                                                <button type="button" id={items?.temp_id
                                                } class="float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={modalAction}>View</button>
                                                <button type="button" id={items?.temp_id
                                                } value={items?.payment_amount
                                                } class="float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0 mr-2" onClick={handlePayment}>Pay</button>
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
                        {rejectedApplication.data.length == 0 || rejectedApplication == undefined || rejectedApplication == null ?
                            <img src='https://img.freepik.com/free-vector/no-data-concept-illustration_114360-536.jpg?w=826&t=st=1674804842~exp=1674805442~hmac=00dcb272b055b7c82777562fa066650f13c6eaa2d78a2dc5709ae5bbda951e20' className='mx-auto h-96' />
                            // <img src={NoData}/>
                            :
                            <div className=''>
                                {rejectedApplication?.data?.map((items) => (
                                    <div className='col-span-3 p-3 h-32 mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                                        <div className='bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md '>
                                            <h1 className='text-xs -mt-3 text-center text-white '>Agency Application</h1>
                                        </div>
                                        <div className='flex flex-row space-x-8 p-2'>
                                            <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}-({items?.temp_id
                                            })</span></h1>
                                            <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                            <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                                        </div>
                                        <div className='flex flex-row space-x-8 p-2'>
                                            <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                            {/* <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address
                                            }</span></h1> */}

                                        </div>

                                        <div className='flex space-x-3 p-2'>
                                            <div className='flex-1 justify-end'>
                                                <button type="button" id={items?.temp_id
                                                } class="  float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={modalAction}>View</button>
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
                    <ViewAgencyApplicationFullDetails data={applicationNo} showLoader={props.showLoader} />
                </div>
            </Modal>

        </>
    )
}

export default AgencyPendingApplication