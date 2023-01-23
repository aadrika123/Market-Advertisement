import Modal from 'react-modal';
import axios from 'axios'
import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../Compnents/ApiHeader';
import ViewAppliedApplication from '../ViewAppliedApplication';



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

function PrivateLandPendingApplications(props) {
    const navigate = useNavigate()


    const { api_getPrivateLandAppliedApplicationList } = AdvertisementApiList()

    const [applyList, setapplyList] = useState('hidden')
    const [appliedApplication, setappliedApplication] = useState()

    const [modalIsOpen, setIsOpen] = useState(false);
    const [applicationNo, setapplicationNo] = useState()
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
        axios.post(`${api_getPrivateLandAppliedApplicationList}`, requestBody, ApiHeader())
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


    console.log("application  list...1", appliedApplication?.data)
    console.log("application  no.", applicationNo)

    return (
        <>
            {/***** section 3 ******/}
            <div className=''>
                <div className=''>
                    {appliedApplication?.data?.map((items) => (
                        <div className='col-span-3 p-3  mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                            <div className='bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md '>
                                <h1 className='text-xs -mt-3 text-center text-white '>Private Land</h1>
                            </div>
                            <div className='flex flex-row space-x-8 p-2'>
                                <h1 className='text-xs'>Application No. :- <span className='font-bold text-sm text-gray-700'>{items?.application_no}</span></h1>
                                <h1 className='text-xs'>Applicant Name :- <span className='font-bold text-sm text-gray-700'>{items?.applicant}</span></h1>
                                <h1 className='text-xs'>Apply Date :- <span className='font-bold text-sm text-gray-700'>{items?.application_date}</span></h1>
                            </div>
                            <div className='flex flex-row space-x-8 p-2'>
                                <h1 className='text-xs'>Entity Name :- <span className='font-bold text-sm text-gray-700'>{items?.entity_name}</span></h1>
                                <h1 className='text-xs'>Entity Address :- <span className='font-bold text-sm text-gray-700'>{items?.entity_address
                                }</span></h1>
                                <h1 className='text-xs'>Status :- <span className=' text-xs text-gray-700 bg-yellow-200 px-4 rounded-full'>Pending</span></h1>
                            </div>

                            <div className='flex space-x-3 p-2'>
                                <div className='flex-1 justify-end'>
                                    <button type="button" id={items?.id} class="  float-right text-xs  px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={modalAction}>View</button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0" style={{ 'width': '80vw', 'height': '100%' }}>
                    <ViewAppliedApplication data={applicationNo} showLoader={props.showLoader} />
                </div>
            </Modal>

        </>
    )
}

export default PrivateLandPendingApplications