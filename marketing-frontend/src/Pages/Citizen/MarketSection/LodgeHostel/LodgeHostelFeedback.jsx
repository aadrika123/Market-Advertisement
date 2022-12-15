import React from 'react'
import { BsInfoCircleFill } from 'react-icons/bs'
import { HiDocumentText } from 'react-icons/hi'
import { FaUserTie } from 'react-icons/fa'

function LodgeHostelFeedback(props) {

    const screen1 = props?.data;

    return (
        <>
            <div className='flex border-b mr-5 space-x-2 mb-3'>
                <BsInfoCircleFill size={18} className="mt-1" color='#6786F6' />
                <p className='font-semibold text-blue-500'>Basic Info</p>
            </div>
            <div className='grid grid-cols-12'>
                <div className='col-span-4 text-sm font-Alice'>
                    <p>Applicant Name </p>
                    <p>Father Name</p>
                    <p>Mobile No</p>
                    <p>Email Id</p>
                    <p>Reg. Ward</p>
                    <p>Reg. Add</p>
                    <p>Perm. Ward</p>
                    <p>Perm Add</p>
                </div>
                <div className='col-span-8 font-BreeSerif text-sm'>
                    <p>: {screen1?.applicantName}</p>
                    <p>: {screen1?.fatherName}</p>
                    <p>: {screen1?.mobile}</p>
                    <p>: {screen1?.email}</p>
                    <p>: {screen1?.resWardNo}</p>
                    <p>: {screen1?.resAddress}</p>
                    <p>: {screen1?.resWardNo}</p>
                    <p>: {screen1?.perAddress}</p>
                </div>
            </div>

            <div className='flex border-b mr-5 space-x-2 my-8'>
                <FaUserTie size={18} className="mt-1" color='#6786F6' />
                <p className='font-semibold text-blue-500'>Applicant Details</p>
            </div>


            <div className='flex border-b mr-5 space-x-2 my-8'>
                <HiDocumentText size={18} className="mt-1" color='#6786F6' />
                <p className='font-semibold text-blue-500'>Applicant Details</p>
            </div>

        </>
    )
}

export default LodgeHostelFeedback