import React from 'react'
import imgSuccess from '../../../../assets/images/success.png'

function LodgeHostelApplicationSubmitted() {
    return (
        <>
            <div className='border border-green-200 bg-green-100 rounded-md shadow-lg shadow-gray-200 text-center mx-40 py-10'>
                <div className='flex justify-center'>
                    <img src={imgSuccess} className="h-24" alt="Success Image" />
                </div>
                <p className='text-2xl font-BreeSerif text-gray-700'>Your Applicatin has been submitted.</p>
                <p>Application No</p>
                <p className='font-BreeSerif text-3xl fong'>APP2834432342</p>
            </div>
        </>
    )
}

export default LodgeHostelApplicationSubmitted