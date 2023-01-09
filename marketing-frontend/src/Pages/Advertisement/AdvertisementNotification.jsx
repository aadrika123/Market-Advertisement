import React, { useState } from 'react'

function AdvertisementNotification() {
    const [dummyNotification, setdummyNotification] = useState([
        {
            id: "1",
            notification: "License has been approved",
            applicationNo: "SELF1234567",
            action: 'pay',
        },
        {
            id: "2",
            notification: "License has been approved",
            applicationNo: "SELF1234567",
            action: '',
        },
        {
            id: "3",
            notification: "License has been approved",
            applicationNo: "SELF1234567",
            action: '',
        },

    ])


    return (
        <>
            <div>
                <div className='flex flex-row justify-center p-2 mt-3'>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500 text-center animate-pulse">
                        <path d="M5.85 3.5a.75.75 0 00-1.117-1 9.719 9.719 0 00-2.348 4.876.75.75 0 001.479.248A8.219 8.219 0 015.85 3.5zM19.267 2.5a.75.75 0 10-1.118 1 8.22 8.22 0 011.987 4.124.75.75 0 001.48-.248A9.72 9.72 0 0019.266 2.5z" />
                        <path fill-rule="evenodd" d="M12 2.25A6.75 6.75 0 005.25 9v.75a8.217 8.217 0 01-2.119 5.52.75.75 0 00.298 1.206c1.544.57 3.16.99 4.831 1.243a3.75 3.75 0 107.48 0 24.583 24.583 0 004.83-1.244.75.75 0 00.298-1.205 8.217 8.217 0 01-2.118-5.52V9A6.75 6.75 0 0012 2.25zM9.75 18c0-.034 0-.067.002-.1a25.05 25.05 0 004.496 0l.002.1a2.25 2.25 0 11-4.5 0z" clip-rule="evenodd" />
                    </svg>
                    <h1 className='font-BreeSerif text-center text-gray-700'>NOTIFICATION</h1>
                </div>
                {dummyNotification.map((items) => (
                    <div className='flex bg-white shadow-sm p-2 mb-3 '>
                        <h1 className='text-xs text-gray-800 ml-4'>
                            {items.notification} <br /> <span className='text-xs font-bold'>APPLICATION NO. :- {items.applicationNo}</span>
                        </h1>
                        {items.action != null || items.action != undefined || items.action != '' &&
                            <button type="button" class="text-xs ml-16 mt-2 lg:ml-20 md:ml-20 px-4 inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">pay</button>}

                    </div>
                ))}

            </div>
        </>
    )
}

export default AdvertisementNotification