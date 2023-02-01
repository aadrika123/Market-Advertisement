import React from 'react'

function HoardingReview(props) {

    const submitForm = () => {
        props.submitFun()
    }
    return (
        <>
            <div className="grid grid-cols-12 w-full p-3">
                <div className='md:pl-0 col-span-6'>
                    <button type="button" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(5)}>back</button>
                </div>
                <div className='col-span-6'>
                    <button type="submit" class="float-right text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0" onClick={submitForm}>Submit</button>
                </div>
            </div>
        </>
    )
}

export default HoardingReview