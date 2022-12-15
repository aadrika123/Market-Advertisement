import React from 'react'
import { RiCloseFill } from 'react-icons/ri'
import { MdDone } from 'react-icons/md'


function Stepper() {

    const active = 'green';
    const done = 'green';
    const notdone = 'gray';



    return (
        <>
            <div className='mx-20 my-8'>
                <div className='flex'>
                    <div className='rounded-full h-5 w-14 border border-green-600 bg-green-500'><MdDone color='white' size={18} /></div>
                    <div className='border-b-2 border-green-400 mb-2 w-full'></div>
                    <div className='rounded-full h-5 w-14 border border-gray-600 bg-blue-300'><MdDone size={18} /></div>
                    <div className='border-b-2 border-gray-300 mb-2 w-full'></div>
                    <div className='rounded-full h-5 w-14 border border-blue-600 bg-blue-300'><RiCloseFill size={18} /></div>
                    <div className='border-b-2 border-gray-300 mb-2 w-full'></div>
                    <div className='rounded-full h-5 w-14 border border-gray-600 bg-gray-300'><RiCloseFill size={18} /></div>
                </div>
            </div>
        </>
    )
}

export default Stepper