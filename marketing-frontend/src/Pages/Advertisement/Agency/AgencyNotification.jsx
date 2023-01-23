
import { Tooltip } from '@material-tailwind/react'
import React, { useState } from 'react'

function AgencyNotification() {
    const [tabIndex, settabIndex] = useState(1)


    const [showPayment, setshowPayment] = useState([
        {
            id: "1",
            notification: "your license is going to expire on 30/01/2023",
            hoardingNo: "123456789",
            action: 'pay & renew',
        },
        {
            id: "2",
            notification: "your license is going to expire on 30/01/2023",
            hoardingNo: "3433456789",
            action: 'pay & renew',
        },
        {
            id: "3",
            notification: "your license is going to expire on 30/01/2023",
            hoardingNo: "1344345688",
            action: 'pay & renew',
        },

    ])
    const [showMessage, setshowMessage] = useState([
        {
            id: "1",
            notification: "Hoarding Has Been Approved",
            hoardingNo: "123456789",
            action: 'pay',
        },
        {
            id: "2",
            notification: "Hoarding Has Been Approved",
            hoardingNo: "3433456789",
            action: 'pay',
        },
        {
            id: "3",
            notification: "Hoarding Has Been Approved",
            hoardingNo: "1344345688",
            action: 'pay',
        },
        {
            id: "4",
            notification: "Hoarding Has Been Approved",
            hoardingNo: "1344345688",
            action: 'pay',
        },

    ])
    const [showValidTime, setshowValidTime] = useState([
        {
            id: "1",
            notification: "hoarding is valid for 21/01/2023 to 21/01/2024",
            hoardingNo: "123456789",
            action: 'pay',
        },
        {
            id: "2",
            notification: "hoarding is valid for 21/01/2023 to 21/01/2024",
            hoardingNo: "3433456789",
            action: 'pay',
        },
        {
            id: "3",
            notification: "hoarding is valid for 21/01/2023 to 21/01/2024",
            hoardingNo: "1344345688",
            action: 'pay',
        },

    ])



    console.log("tabindex", tabIndex)

    return (
        <>
            <div>
                <div className="p-3">
                    <div className="bg-violet-100  flex flex-row ">
                        <h1 className="text-gray-500 p-2 ml-8 text-lg font-semibold">Tasks</h1>
                        <Tooltip className='bg-gray-300 text-xs text-gray-900' content="Notifications">
                            <button type='button' className="p-2 flex flex-row focus:outline-none focus:ring focus:ring-violet-300 " onClick={() => settabIndex(1)} >
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 p-1 rounded-full text-indigo-500 bg-gray-50 dark:bg-pink-900 dark:bg-opacity-40">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </span>
                                <span className="text-red-500 font-bold text-lg -mt-2">4</span>
                            </button>
                        </Tooltip>
                        <Tooltip className='bg-gray-300 text-xs text-gray-900' content="Payments Dues">
                            <button type='button' className="p-2 flex flex-row focus:outline-none focus:ring focus:ring-violet-300 " onClick={() => settabIndex(2)}>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 p-1 rounded-full text-indigo-500 bg-gray-50 dark:bg-pink-900 dark:bg-opacity-40">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 8.25H9m6 3H9m3 6l-3-3h1.5a3 3 0 100-6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>

                                </span>
                                <span className="text-red-500 font-bold text-lg -mt-2">3</span>
                            </button>
                        </Tooltip>
                        {/* <button type='button' className="p-2 flex flex-row " onClick={() => settabIndex(3)}>
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 p-1 rounded-full text-indigo-500 bg-gray-50 dark:bg-pink-900 dark:bg-opacity-40">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                           
                        </button> */}
                        <Tooltip className='bg-gray-300 text-xs text-gray-900' content="Account">
                            <button type='button' className="p-2 flex flex-row focus:outline-none focus:ring focus:ring-violet-300 " onClick={() => settabIndex(4)}>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 p-1 rounded-full text-indigo-500 bg-gray-50 dark:bg-pink-900 dark:bg-opacity-40">
                                        <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 016.709 7.498.75.75 0 01-.372.568A12.696 12.696 0 0112 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 01-.372-.568 6.787 6.787 0 011.019-4.38z" clip-rule="evenodd" />
                                        <path d="M5.082 14.254a8.287 8.287 0 00-1.308 5.135 9.687 9.687 0 01-1.764-.44l-.115-.04a.563.563 0 01-.373-.487l-.01-.121a3.75 3.75 0 013.57-4.047zM20.226 19.389a8.287 8.287 0 00-1.308-5.135 3.75 3.75 0 013.57 4.047l-.01.121a.563.563 0 01-.373.486l-.115.04c-.567.2-1.156.349-1.764.441z" />
                                    </svg>
                                </span>

                            </button>
                        </Tooltip>
                    </div>
                    {/* message */}
                    {tabIndex == 1 &&
                        <div className={``}>
                            {showMessage.map((items) => (
                                <div className='flex bg-white shadow-sm p-2 mb-3 '>
                                    <h1 className='text-md text-gray-800 ml-4'>
                                        {items.notification} <br /> <span className='text-sm font-bold'>HOARDING NO. :- {items.hoardingNo}</span>
                                    </h1>
                                    {items.action != null || items.action != undefined || items.action != '' &&
                                        <button type="button" class="text-sm ml-16 mt-2 lg:ml-20 md:ml-20 px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">{items.action}</button>}
                                </div>
                            ))}
                        </div>
                    }
                    {/* payment notification */}
                    {tabIndex == 2 &&
                        < div className={``}>
                            {showPayment.map((items) => (
                                <div className='flex bg-white shadow-sm p-2 mb-3 '>
                                    <h1 className='text-md text-gray-800 ml-4'>
                                        {items.notification} <br /> <span className='text-sm font-bold'>HOARDING NO. :- {items.hoardingNo}</span>
                                    </h1>

                                    <button type="button" class="text-sm ml-16 mt-2 lg:ml-20 md:ml-20 px-2 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">{items.action}</button>
                                </div>
                            ))}
                        </div>
                    }


                    {/* hoarding valid till */}
                    {tabIndex == 3 &&
                        <div className={``}>
                            {showValidTime.map((items) => (
                                <div className='flex bg-white shadow-sm p-2 mb-3 '>
                                    <h1 className='text-md text-gray-800 ml-4'>
                                        {items.notification} <br /> <span className='text-sm font-bold'>HOARDING NO. :- {items.hoardingNo}</span>
                                    </h1>
                                    {items.action != null || items.action != undefined || items.action != '' &&
                                        <button type="button" class="text-sm ml-16 mt-2 lg:ml-20 md:ml-20 px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">{items.action}</button>}
                                </div>
                            ))}
                        </div>
                    }

                    {/* hoarding valid till */}
                    {tabIndex == 4 &&
                        <div className={``}>
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-20 rounded-full bg-gray-200 mx-auto  text-indigo-500 mt-4">
                                    <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 016.709 7.498.75.75 0 01-.372.568A12.696 12.696 0 0112 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 01-.372-.568 6.787 6.787 0 011.019-4.38z" clip-rule="evenodd" />
                                    <path d="M5.082 14.254a8.287 8.287 0 00-1.308 5.135 9.687 9.687 0 01-1.764-.44l-.115-.04a.563.563 0 01-.373-.487l-.01-.121a3.75 3.75 0 013.57-4.047zM20.226 19.389a8.287 8.287 0 00-1.308-5.135 3.75 3.75 0 013.57 4.047l-.01.121a.563.563 0 01-.373.486l-.115.04c-.567.2-1.156.349-1.764.441z" />
                                </svg></div>
                            <div><h1 className='text-center font-semibold text-xl'>Agency Name</h1></div>
                            <div><h1 className='text-center text-gray-600 font-semibold text-md'>Your License is valid for 5 years <br /> 01/01/2023  to  01/01/2028</h1></div>
                            <div>
                                <table className='border mt-8'>
                                    <thead className='bg-violet-200'>
                                        <tr className='border '>
                                            <td className='border px-12 text-sm'>Members</td>
                                            <td className='border px-12 text-sm'>Mobile No.</td>
                                            <td className='border px-12 text-sm'>Email</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr className='text-center text-sm'>
                                            <td className='border'>Member 1</td>
                                            <td className='border'>1234567890</td>
                                            <td className='border'>member1@gmail.com</td>
                                        </tr>
                                        <tr className='text-center text-sm'>
                                            <td className='border'>Member 2</td>
                                            <td className='border'>1234567890</td>
                                            <td className='border'>member2@gmail.com</td>
                                        </tr>
                                        <tr className='text-center text-sm'>
                                            <td className='border'>Member 3</td>
                                            <td className='border'>1234567890</td>
                                            <td className='border'>member3@gmail.com</td>
                                        </tr>
                                        <tr className='text-center text-sm'>
                                            <td className='border'>Member 4</td>
                                            <td className='border'>1234567890</td>
                                            <td className='border'>member4@gmail.com</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    }
                </div>
            </div>
        </>
    )
}

export default AgencyNotification