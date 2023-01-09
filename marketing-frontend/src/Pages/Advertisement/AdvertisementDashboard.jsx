import Modal from 'react-modal';
import axios from 'axios'
import React, { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import AdvertisementApiList from '../../Compnents/AdvertisementApiList'
import ApiHeader from '../../Compnents/ApiHeader'
import ViewAppliedApplication from './ViewAppliedApplication';
import AdvertisementNotification from './AdvertisementNotification';


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

function AdvertisementDashboard() {
    const navigate = useNavigate()
    const { api_getAppliedApplicationList } = AdvertisementApiList()

    const [applyList, setapplyList] = useState('hidden')
    const [appliedApplication, setappliedApplication] = useState()

    const [modalIsOpen, setIsOpen] = useState(false);
    const [applicationNo, setapplicationNo] = useState()

    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }


    const modalAction = (e) => {
        // alert(e.target.id)
        let applicationId = e.target.id
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
        const requestBody = {
            deviceId: "selfAdvert",
        }
        axios.post(`${api_getAppliedApplicationList}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('ulb list in self advertisement', response.data.data)
                setappliedApplication(response.data.data)
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
            })
    }
    console.log("application list...1", appliedApplication)

    return (
        <>

            {/***** section 1 ******/}
            <div className='mb-2 '>
                <div className='flex justify-start  h-18 bg-white rounded leading-5  w-full '>
                    <div className=''>
                        <img src='https://cdn.dribbble.com/users/1092072/screenshots/3306775/cubeloader2.2.gif' className='h-16 w-16  ' />
                    </div>
                    <div className='p-2 '>
                        <h1 className=' text-2xl  font-semibold text-gray-700 '> Advertisement Dashboard</h1>
                        <h1 className='text-xs  text-gray-500 '>You Can Get License To Advertise Your Business Name </h1>
                    </div>
                </div>
            </div>

            {/***** section 2 ******/}
            <div className={` grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 container mx-auto gap-4 mb-2 mt-1 p-3`}>
                <div className='col-span-3 p-3 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl ' >
                    <div >
                        <div className='text-indigo-500 '>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mx-auto ">
                                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h1 className='text-lg text-center font-semibold text-gray-700 '>Self Advertisement</h1>
                        <p className='text-xs text-center text-gray-600'>You Can Get License To Advertise Your Business Name On Your Shop</p>

                        <div className='  '>
                            <button type="button" class="text-xs ml-16 mt-2 p-0 px-4 inline-block text-center mb-1 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 bg-gradient-to-b from-indigo-400 to-indigo-600  hover:from-indigo-500 hover:to-indigo-600 focus:from-indigo-400 focus:to-indigo-600 hover:text-white hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => navigate('/selfAdvrt')}>Apply Here</button>
                        </div>
                    </div>
                </div>

                <div className='col-span-3 p-3 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl '>
                    <div className='text-indigo-500 '>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mx-auto">
                            <path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25zM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h.375a3 3 0 116 0h3a.75.75 0 00.75-.75V15z" />
                            <path d="M8.25 19.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0zM15.75 6.75a.75.75 0 00-.75.75v11.25c0 .087.015.17.042.248a3 3 0 015.958.464c.853-.175 1.522-.935 1.464-1.883a18.659 18.659 0 00-3.732-10.104 1.837 1.837 0 00-1.47-.725H15.75z" />
                            <path d="M19.5 19.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                        </svg>

                    </div>
                    <h1 className='text-lg text-center font-semibold text-gray-700 '>Movable Vehicle</h1>
                    <p className='text-xs text-center text-gray-600'>You Can Get License for Ad on Movable Vehicle </p>
                    <div className='  '>
                        <button type="button" class="text-xs ml-16 mt-2 p-0 px-4 inline-block text-center mb-1 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 bg-gradient-to-b from-indigo-400 to-indigo-600  hover:from-indigo-500 hover:to-indigo-600 focus:from-indigo-400 focus:to-indigo-600 hover:text-white hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => navigate('/movableVehicle')}>Apply Here</button>
                    </div>
                </div>

                <div className='col-span-3 p-3 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl '>
                    <div className='text-indigo-500 '>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mx-auto">
                            <path fill-rule="evenodd" d="M3 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5H15v-18a.75.75 0 000-1.5H3zM6.75 19.5v-2.25a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v2.25a.75.75 0 01-.75.75h-3a.75.75 0 01-.75-.75zM6 6.75A.75.75 0 016.75 6h.75a.75.75 0 010 1.5h-.75A.75.75 0 016 6.75zM6.75 9a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zM6 12.75a.75.75 0 01.75-.75h.75a.75.75 0 010 1.5h-.75a.75.75 0 01-.75-.75zM10.5 6a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zm-.75 3.75A.75.75 0 0110.5 9h.75a.75.75 0 010 1.5h-.75a.75.75 0 01-.75-.75zM10.5 12a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zM16.5 6.75v15h5.25a.75.75 0 000-1.5H21v-12a.75.75 0 000-1.5h-4.5zm1.5 4.5a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75h-.008a.75.75 0 01-.75-.75v-.008zm.75 2.25a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75v-.008a.75.75 0 00-.75-.75h-.008zM18 17.25a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75h-.008a.75.75 0 01-.75-.75v-.008z" clip-rule="evenodd" />
                        </svg>

                    </div>
                    <h1 className='text-lg text-center font-semibold text-gray-700 '>Private Land</h1>
                    <p className='text-xs text-center text-gray-600'>You can get license to advertise on your premises</p>
                    <div className='  '>
                        <button type="button" class="text-xs ml-16 mt-2 p-0 px-4 inline-block text-center mb-1 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 bg-gradient-to-b from-indigo-400 to-indigo-600  hover:from-indigo-500 hover:to-indigo-600 focus:from-indigo-400 focus:to-indigo-600 hover:text-white hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => navigate('/privateLand')}>Apply Here</button>
                    </div>
                </div>

                <div className='col-span-3 p-3 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl '>
                    <div className='text-indigo-500 '>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 mx-auto">
                            <path d="M11.584 2.376a.75.75 0 01.832 0l9 6a.75.75 0 11-.832 1.248L12 3.901 3.416 9.624a.75.75 0 01-.832-1.248l9-6z" />
                            <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 010 1.5H3a.75.75 0 010-1.5h.75v-9.918a.75.75 0 01.634-.74A49.109 49.109 0 0112 9c2.59 0 5.134.202 7.616.592a.75.75 0 01.634.74zm-7.5 2.418a.75.75 0 00-1.5 0v6.75a.75.75 0 001.5 0v-6.75zm3-.75a.75.75 0 01.75.75v6.75a.75.75 0 01-1.5 0v-6.75a.75.75 0 01.75-.75zM9 12.75a.75.75 0 00-1.5 0v6.75a.75.75 0 001.5 0v-6.75z" clip-rule="evenodd" />
                            <path d="M12 7.875a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" />
                        </svg>

                    </div>
                    <h1 className='text-lg text-center font-semibold text-gray-700 '>Agency Registration</h1>
                    <p className='text-xs text-center text-gray-600'>Advertisement Agencies can apply to get License</p>
                    <div className='  '>
                        <button type="button" class="text-xs ml-16 mt-2 p-0 px-4 inline-block text-center mb-1 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 bg-gradient-to-b from-indigo-400 to-indigo-600  hover:from-indigo-500 hover:to-indigo-600 focus:from-indigo-400 focus:to-indigo-600 hover:text-white hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => navigate('/agency')}>Apply Here</button>
                    </div>
                </div>
            </div>

            {/***** section 3 ******/}
            <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 container mx-auto gap-4 mt-1 '>

                <div className='col-span-8 '>
                    <div className='mb-4 '>
                        <div className='flex justify-start  h-18 bg-white rounded leading-5  w-full  shadow-lg'>
                            <div className='p-2  '>
                                <h1 className=' text-lg  font-semibold text-gray-500 '>Applied Application</h1>
                                {/* <h1 className='text-xs  text-gray-500 '>You Can Get License To Advertise Your Business Name </h1> */}
                            </div>
                            <div className='p-2 flex space-x-8'>
                                <span>
                                    <span className='font-bold text-gray-700'>5</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="p-1 w-6 h-6 -mt-2 ml-2 bg-indigo-500 text-white shadow-lg  rounded-full">
                                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span>
                                    <span className='font-bold text-gray-700'>0</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="p-1 w-6 h-6 -mt-2 ml-2 bg-indigo-500 text-white shadow-lg  rounded-full">
                                        <path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25zM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h.375a3 3 0 116 0h3a.75.75 0 00.75-.75V15z" />
                                        <path d="M8.25 19.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0zM15.75 6.75a.75.75 0 00-.75.75v11.25c0 .087.015.17.042.248a3 3 0 015.958.464c.853-.175 1.522-.935 1.464-1.883a18.659 18.659 0 00-3.732-10.104 1.837 1.837 0 00-1.47-.725H15.75z" />
                                        <path d="M19.5 19.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                                    </svg>
                                </span>
                                <span>
                                    <span className='font-bold text-gray-700'>0</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="p-1 w-6 h-6 -mt-2 ml-2 bg-indigo-500 text-white shadow-lg  rounded-full">
                                        <path fill-rule="evenodd" d="M3 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5H15v-18a.75.75 0 000-1.5H3zM6.75 19.5v-2.25a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v2.25a.75.75 0 01-.75.75h-3a.75.75 0 01-.75-.75zM6 6.75A.75.75 0 016.75 6h.75a.75.75 0 010 1.5h-.75A.75.75 0 016 6.75zM6.75 9a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zM6 12.75a.75.75 0 01.75-.75h.75a.75.75 0 010 1.5h-.75a.75.75 0 01-.75-.75zM10.5 6a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zm-.75 3.75A.75.75 0 0110.5 9h.75a.75.75 0 010 1.5h-.75a.75.75 0 01-.75-.75zM10.5 12a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zM16.5 6.75v15h5.25a.75.75 0 000-1.5H21v-12a.75.75 0 000-1.5h-4.5zm1.5 4.5a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75h-.008a.75.75 0 01-.75-.75v-.008zm.75 2.25a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75v-.008a.75.75 0 00-.75-.75h-.008zM18 17.25a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75h-.008a.75.75 0 01-.75-.75v-.008z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span>
                                    <span className='font-bold text-gray-700'>0</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="p-1 w-6 h-6 -mt-2 ml-2 bg-indigo-500 text-white shadow-lg  rounded-full">
                                        <path d="M11.584 2.376a.75.75 0 01.832 0l9 6a.75.75 0 11-.832 1.248L12 3.901 3.416 9.624a.75.75 0 01-.832-1.248l9-6z" />
                                        <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 010 1.5H3a.75.75 0 010-1.5h.75v-9.918a.75.75 0 01.634-.74A49.109 49.109 0 0112 9c2.59 0 5.134.202 7.616.592a.75.75 0 01.634.74zm-7.5 2.418a.75.75 0 00-1.5 0v6.75a.75.75 0 001.5 0v-6.75zm3-.75a.75.75 0 01.75.75v6.75a.75.75 0 01-1.5 0v-6.75a.75.75 0 01.75-.75zM9 12.75a.75.75 0 00-1.5 0v6.75a.75.75 0 001.5 0v-6.75z" clip-rule="evenodd" />
                                        <path d="M12 7.875a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" />
                                    </svg>
                                </span>

                            </div>
                        </div>



                    </div>
                    {appliedApplication?.map((items) => (
                        <div className='col-span-3 p-3 h-32 mb-4 shadow-lg rounded leading-5 bg-white transform transition duration-300 ease-in-out hover:-translate-y-2 hover:shadow-xl  '>
                            <div className='bg-yellow-500  w-36 -ml-3 rounded-r-lg shadow-md '>
                                <h1 className='text-xs -mt-3 text-center text-white '>Self Advertisement</h1>
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
                <div className='col-span-4 bg-white shadow-lg rounded leading-5'>
                    {/* <div className='grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3'>
                        <div>1</div>
                        <div>1</div>
                        <div>1</div>
                    </div> */}
                    <AdvertisementNotification/>

                </div>

            </div>


            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >

                <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0" style={{ 'width': '60vw', 'height': '100%' }}>
                    <ViewAppliedApplication data={applicationNo} />
                </div>

            </Modal>

        </>
    )
}

export default AdvertisementDashboard